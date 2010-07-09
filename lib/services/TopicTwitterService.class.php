<?php
/**
 * news_TopicTwitterService
 * @package modules.twittersample
 */
class news_TopicTwitterService extends twitterconnect_BaseTwitterService
{
	/**
	 * Singleton
	 * @var news_TopicTwitterService
	 */
	private static $instance = null;

	/**
	 * @return news_TopicTwitterService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @param website_persistentdocument_topic $document
	 * @return string[]
	 */
	public function getDocumentsModelNamesForTweet($document)
	{
		return array('modules_news/news');
	}
}