<?php
class news_tests_TestHelper
{
	/**
	 * @param String $label
	 * @param Integer $parentId
	 * @return website_persistentdocument_topic
	 */
	public static function getNewTopic($label, $parentId = null, $attachToRootFolder = false)
	{
		$topic = website_TopicService::getInstance()->getNewDocumentInstance();
		$topic->setLabel($label);
		$topic->setNavigationVisibility(WebsiteConstants::VISIBILITY_VISIBLE);
		if (!is_null($parentId))
		{
			$topic->save($parentId);
		}
		else
		{
			$defaultWebsite = website_WebsiteModuleService::getInstance()->getDefaultWebsite();
			$topic->save($defaultWebsite->getId());
		}
		if ($attachToRootFolder === true)
		{
			$rootFolder = DocumentHelper::getDocumentInstance(ModuleService::getInstance()->getRootFolderId('news'));
			$rootFolder->addTopics($topic);
			$rootFolder->save();
		}
		return $topic;
	}

	/**
	 * @param String $label
	 * @param Integer $parentId
	 * @param date_Calendar $startPublicationDate
	 * @param date_Calendar $endPublicationDate
	 * @param date_TimeSpan $archiveSpanWeek
	 * @return news_persistentdocument_news
	 */
	public static function getFrontpageNews($label, $parentId = null, $date = null)
	{
		$news = news_NewsService::getInstance()->getNewDocumentInstance();
		$news->setLabel($label);
		$news->setStartpublicationdate(date_Calendar::now());
		$news->setEndpublicationdate(null);
		$news->setPublicationweek(1);
		if (null == $date)
		{
			$date = date_Calendar::now();
		}
		$news->setSummary('Lorem ipsum dolor sit amet, consectetuer adipiscing elit');
		$news->setText('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus commodo, nunc nec pellentesque mollis, velit eros ullamcorper elit, vel malesuada arcu nunc eget mi. Aenean a nibh. Donec quis nisl at nunc iaculis luctus. Suspendisse elit pede, ornare aliquam, sollicitudin vel, tempus a, dolor. Morbi venenatis tellus non dolor. Donec viverra adipiscing mi. In sagittis semper elit. Proin aliquam lacinia eros. Maecenas molestie consequat dolor. Nam nunc. Phasellus iaculis vehicula mauris. Morbi vestibulum. Aliquam tortor. Praesent varius. Nam et risus id lorem viverra sagittis. Cras nisi nibh, congue eu, sodales fringilla, pharetra nec, turpis. Duis pede risus, porta commodo, dictum at, vestibulum id, lectus. Ut elementum lacinia sapien. Aenean tempor. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;');
		$news->setDate($date);
		$news->save($parentId);
		$news->activate();
		return $news;
	}

	/**
	 * @param String $label
	 * @param Integer $parentId
	 * @param date_Calendar $startPublicationDate
	 * @param date_Calendar $endPublicationDate
	 * @param date_TimeSpan $archiveSpanWeek
	 * @return news_persistentdocument_news
	 */
	public static function getPublishedNonHomepageNews($label, $parentId = null, $date = null)
	{
		$news = news_NewsService::getInstance()->getNewDocumentInstance();
		$news->setLabel($label);
		$news->setStartpublicationdate($date);
		$news->setEndpublicationdate(null);
		if (null == $date)
		{
			$date = date_Calendar::now();
		}
		$news->setSummary('Lorem ipsum dolor sit amet, consectetuer adipiscing elit');
		$news->setText('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus commodo, nunc nec pellentesque mollis, velit eros ullamcorper elit, vel malesuada arcu nunc eget mi. Aenean a nibh. Donec quis nisl at nunc iaculis luctus. Suspendisse elit pede, ornare aliquam, sollicitudin vel, tempus a, dolor. Morbi venenatis tellus non dolor. Donec viverra adipiscing mi. In sagittis semper elit. Proin aliquam lacinia eros. Maecenas molestie consequat dolor. Nam nunc. Phasellus iaculis vehicula mauris. Morbi vestibulum. Aliquam tortor. Praesent varius. Nam et risus id lorem viverra sagittis. Cras nisi nibh, congue eu, sodales fringilla, pharetra nec, turpis. Duis pede risus, porta commodo, dictum at, vestibulum id, lectus. Ut elementum lacinia sapien. Aenean tempor. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;');
		$news->setDate($date);
		$news->save($parentId);
		news_NewsService::getInstance()->activate($news->getId());
		return $news;
	}

	/**
	 * @param String $label
	 * @param Integer $parentId
	 * @param date_Calendar $startPublicationDate
	 * @param date_Calendar $endPublicationDate
	 * @param date_TimeSpan $archiveSpanWeek
	 * @return news_persistentdocument_news
	 */
	public static function getArchiveNews($label, $parentId = null, $date = null)
	{
		$ns = news_NewsService::getInstance();
		$news = self::getPublishedNonHomepageNews($label, $parentId, $date);
		$news->setEndpublicationdate(date_Calendar::yesterday());
		$news->setArchiveweek(1);
		$news->save();
		return $news;
	}

}