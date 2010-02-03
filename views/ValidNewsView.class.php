<?php
class news_ValidNewsView extends f_view_BaseView
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$this->forceModuleName('news');
		$this->setTemplateName('News-ValidNews', K::XUL);
		$task = $request->getAttribute('task');
		$lang = $task->getWorkitem()->getCase()->getlang();

		try
		{
			RequestContext::getInstance()->beginI18nWork($lang);
			$workDocument = DocumentHelper::getDocumentInstance($task->getWorkitem()->getDocumentid());
			$this->setAttribute('document', $workDocument);
			if ($workDocument->getListvisual())
			{
				$url = MediaHelper::getPublicUrl($workDocument->getListvisual());
				$this->setAttribute('photoListvisual', str_replace('&','&amp;', $url));
			}
			if ($workDocument->getDetailvisual())
			{
				$url = MediaHelper::getPublicUrl($workDocument->getDetailvisual());
				$this->setAttribute('photoDetailvisual', str_replace('&','&amp;', $url));
			}
			if ($workDocument->getAccessmap())
			{
				$url = MediaHelper::getPublicUrl($workDocument->getAccessmap());
				$this->setAttribute('photoAccessmap', str_replace('&','&amp;', $url));
			}
			if ($workDocument->getAttachment())
			{
				$url = MediaHelper::getPublicUrl($workDocument->getAttachment());
				$this->setAttribute('photoAttachment', str_replace('&','&amp;', $url));
			}
			$this->setAttribute('task', $task);
			$this->setAttribute('taskId', $task->getId());
			RequestContext::getInstance()->endI18nWork();
		}
		catch (Exception $e)
		{
			RequestContext::getInstance()->endI18nWork($e);
		}

		$this->setAttribute(
           'cssInclusion',
           $this->getStyleService()
	    	  ->registerStyle('modules.uixul.dashboard')
	    	  ->registerStyle('modules.uixul.bindings')
	    	  ->registerStyle('modules.uixul.backoffice')
	    	  ->execute(K::XUL)
	    );

		// include JavaScript
		$scripts = array(
			'modules.uixul.lib.default',
			'modules.dashboard.lib.js.dashboardwidget'
		);
		foreach ($scripts as $script)
		{
			$this->getJsService()->registerScript($script);
		}

        $this->setAttribute('scriptInclusion', $this->getJsService()->executeInline(K::XUL));
	}
}
