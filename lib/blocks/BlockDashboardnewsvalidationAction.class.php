<?php
class news_BlockDashboardnewsvalidationAction extends dashboard_BlockDashboardAction
{	
	/**
	 * @param f_mvc_Request $request
	 * @param boolean $forEdition
	 */
	protected function setRequestContent($request, $forEdition)
	{
		if ($forEdition)
		{
			return;
		}
		
		$tasks = $this->getNewsService()->getPendingTasksForCurrentUser();
		if (count($tasks) > 0)
		{
			$taskAttr = array();
			foreach ($tasks as $task)
			{
				$document = DocumentHelper::getDocumentInstance($task->getWorkitem()->getDocumentid());			
				$lastModification = date_Calendar::getInstance($task->getCreationdate());
				
				if ($lastModification->isToday())
				{
					$status = f_Locale::translateUI('&modules.uixul.bo.datePicker.Calendar.today;') . date_DateFormat::format(date_Converter::convertDateToLocal($lastModification), ', H:i');
				}
				else
				{
					$status = date_DateFormat::format(date_Converter::convertDateToLocal($lastModification), 'l j F Y, H:i');
				}
				$attr = array(
					'id' => $task->getId(),
					'taskLabel' => f_Locale::translateUI('&modules.news.bo.dashboard.Task-label-validate;', array('author' => $task->getDescription())),
					'label' => $document->getPersistentModel()->isLocalized() ? $document->getLabelForLang($task->getLang()) : $document->getLabel(),
					'thread' => $this->getNewsService()->getPathOf($document),
					'comment' => $task->getCommentary(),
					'author' => ucfirst($task->getDescription()),
					'status' => ucfirst($status),
				    'locate' => "locateDocumentInModule(". $document->getId() . ", 'news');"
				);
				$taskAttr[] = $attr;
			}
			$request->setAttribute('tasks', $taskAttr);
		}
	}
	
	/**
	 * Returns the news_NewsService to handle documents of type "modules_news/news".
	 *
	 * @return news_NewsService
	 */
	public function getNewsService()
	{
		return news_NewsService::getInstance();
	}
}