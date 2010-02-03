<?php
class news_PreviewNewsAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$news = $this->getDocumentInstanceFromRequest($request);
		$preference = $this->getPreferences();
		if ($preference !== null && $preference->getPreviewpage() !== null)
		{
			$pageId = $preference->getPreviewpage()->getId();
			$request->setParameter(K::PAGE_REF_ACCESSOR, $pageId);
			
			$newsParam = $request->getParameter('newsParam', array());
			$newsParam['cmpref'] = $news->getId();
			$newsParam['compact'] = true;
			$request->setParameter('newsParam', $newsParam);
			$request->setParameter('DisablePublicationWorkflow', 'true');
			$context->getController()->forward('website', 'Display');
			
    	    return View::NONE;
		}
		return View::ERROR;
	}
	
	/**
	 * @return form_persistentdocument_preferences
	 */
	private function getPreferences()
	{
		return ModuleService::getInstance()->getPreferencesDocument('news');
	}
}