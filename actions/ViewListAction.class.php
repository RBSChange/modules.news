<?php

class news_ViewListAction extends news_Action
{
	/**
	 * @return String
	 */
	protected function getTagSuffix()
	{
		return 'list';
	}

	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$page = null;
		$module = AG_ERROR_404_MODULE;
		$action = AG_ERROR_404_ACTION;

		$document = $this->getSiblingDocumentFromRequest($request);
		if ($document !== null)
		{
			$tag = 'functional_news_news-'.$this->getTagSuffix();
			try
			{
				$page = TagService::getInstance()->getDocumentBySiblingTag($tag, $document);
			}
			catch (TagException $e)
			{
				Framework::exception($e);
				$page = $this->getContextualListPage();
			}

		}
		else
		{
			$page = $this->getContextualListPage();
		}
		if (!is_null($page))
		{
			$request->setParameter(K::PAGE_REF_ACCESSOR, $page->getId());
			$module = 'website';
			$action = 'Display';
		}
		$context->getController()->forward($module, $action);
		return View::NONE;
	}

	private function getContextualListPage()
	{
		try
		{
			$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
			return TagService::getInstance()->getDocumentByContextualTag('contextual_website_website_modules_news_page-'.$this->getTagSuffix(), $website);
		}
		catch (TagException $e)
		{
			//No taged Page found
			Framework::exception($e);
		}
		return null;
	}

	private function getSiblingDocumentFromRequest($request)
	{
		$documentId = $request->getModuleParameter('news', K::COMPONENT_ID_ACCESSOR);
		if (null === $documentId)
		{
			$documentId = $request->getParameter(K::COMPONENT_ID_ACCESSOR);
		}
		$document = null;
		if ($documentId !== null)
		{
			return DocumentHelper::getDocumentInstance($documentId);
		}
		return null;
	}

	public function isSecure()
	{
		return false;
	}

	/**
	 * @return Boolean
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}