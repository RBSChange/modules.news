<?php
/**
 * @date 14 avr. 08 11:41:36
 * @author franck.stauffer
 * @package modules.news
 */
class news_ViewFeedAction extends news_Action
{	
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{		
		$parentId = $request->getModuleParameter('news', K::PARENT_ID_ACCESSOR);
		if (null === $parentId)
		{
			$parentId = $request->getParameter(K::PARENT_ID_ACCESSOR);
		}
		$feedWriter = news_NewsService::getInstance()->getRSSFeedWriterByParentId($parentId);
		// Set the link, title and description of the feed
		$this->setHeaders($feedWriter, $request);
		$this->setContentType('text/xml');
		echo $feedWriter->toString();
	}
	
	/**
	 * @param Request $request
	 * @param rss_FeedWriter $feedWriter
	 */
	private function setHeaders($feedWriter, $request)
	{
		$prefs = ModuleService::getInstance()->getPreferencesDocument('news');
		$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
		$pageId = $request->getModuleParameter('news', K::COMPONENT_ID_ACCESSOR);
		if (null === $pageId)
		{
			$pageId = $request->getParameter(K::PARENT_ID_ACCESSOR);
		}
		$page = DocumentHelper::getDocumentInstance($pageId);
		$feedURL = LinkHelper::getDocumentUrl($page);
		$description = $prefs->getRssfeeddescription();
		if (is_null($description))
		{
			$description = f_Locale::translate('&modules.news.frontoffice.Default-feed-description;', array('label' => $website->getLabel()));
		}

		$title = $prefs->getRssfeedtitle();
		if (is_null($title))
		{
			$title = $website->getLabel();
		}
		$feedWriter->setLink($feedURL);
		$feedWriter->setDescription($description);
		$feedWriter->setTitle($title);
	}
	
	public function isSecure()
	{
		return false;
	}
	
	protected function isDocumentAction()
	{
		return false;
	}
}