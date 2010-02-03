<?php
/**
 * @date 8 févr. 08 15:03:54
 * @author franck.stauffer
 * @package modules.news
 */

class news_BlockNewsTopicAction extends news_BlockNewsListAction
{
	protected function getContainerId()
	{	
		$topic = $this->getDocumentParameter();
		if ($topic === null)
		{
			return parent::getContainerId();
		}
		return $topic->getId();
	}


	protected function getSiblingId($request)
	{
		$siblingId = parent::getSiblingId($request);
		$container = DocumentHelper::getDocumentInstance($this->getContainerId());
		if ($container instanceof website_persistentdocument_topic)
		{
			$indexPage = $container->getIndexPage();
			if ($indexPage != null)
			{
				$siblingId = $indexPage->getId();
			}
		}
		return $siblingId;
	}
}
