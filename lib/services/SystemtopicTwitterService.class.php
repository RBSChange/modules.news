<?php
/**
 * news_SystemtopicTwitterService
 * @package modules.twittersample
 */
class news_SystemtopicTwitterService extends news_TopicTwitterService
{
	/**
	 * Singleton
	 * @var news_SystemtopicTwitterService
	 */
	private static $instance = null;

	/**
	 * @return news_SystemtopicTwitterService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
}