<?php
class news_SelectNextActorsWorkflowaction extends workflow_BaseWorkflowaction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	function execute()
	{
		$role = $this->getWorkitem()->getTransition()->getRoleid();
		if (is_null($role))
		{
			$role = 'Validator';
		}
		if (Framework::isInfoEnabled())
		{
			Framework::info(__METHOD__ . ' role('.$role.')');
		}
		$addSuperAdmin = $this->getCaseParameter('AFFECT_TASKS_TO_SUPER_ADMIN') == 'true';
		$users = $this->getActorsId($this->getDocumentId(), $role, $addSuperAdmin);
		
		if (count($users) > 0)
		{
			$actorsIds = DocumentHelper::getIdArrayFromDocumentArray($users);
			$this->setCaseParameter('__NEXT_ACTORS_IDS', $actorsIds);
			
			$backenduser = users_UserService::getInstance()->getCurrentBackEndUser();
			if ($backenduser !== null)
			{
				foreach ($users as $user) 
				{
					if (DocumentHelper::equals($backenduser, $user))
					{
						$this->setExecutionStatus('AUTO');
						return true;
					}
				}
			}
		}
		else
		{
			$this->setCaseParameter('__NEXT_ACTORS_IDS', array());
		}
		
		$this->setExecutionStatus('FOUNDED');
		return true;
	}
	
	/**
	 * @param Integer $id
	 * @param string $roleName
	 * @param boolean $addSuperAdmin
	 * @return array<users_persistentdocument_user>
	 */
	private function getActorsId($id, $roleName, $addSuperAdmin)
	{
		$permissionService = f_permission_PermissionService::getInstance();
		$roleName = $permissionService->resolveRole($roleName, $id);
		
		if (Framework::isInfoEnabled())
		{
			Framework::info(__METHOD__ . "($id, $roleName, $addSuperAdmin)");
		}
		
		$actorsIds = $permissionService->getUsersByRoleAndDocumentId($roleName, $id);
		if (Framework::isInfoEnabled())
		{
			Framework::info(var_export($actorsIds, true));
		}		
		if ($addSuperAdmin)
		{
			$rootUsers = users_BackenduserService::getInstance()->getRootUsers();
			foreach ($rootUsers as $rootUser) 
			{
				$rootUserId = $rootUser->getId();
				if (!in_array($rootUserId, $actorsIds))
				{
					$actorsIds[] = $rootUserId;
				}
			}
		}

		$users = array();	
		// If there are user ids, instanciate them.
		foreach ($actorsIds as $actorId)
		{
			try 
			{
				$user = DocumentHelper::getDocumentInstance($actorId);
				if ($user->isPublished())
				{
					$users[] = $user;
				}
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}
		
		return $users;
	}
}