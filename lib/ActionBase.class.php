<?php
/**
 * @date Tue, 07 Aug 2007 11:52:32 +0200
 * @author intstaufl
 */
class news_ActionBase extends f_action_BaseAction
{
		
	/**
	 * Returns the news_PreferencesService to handle documents of type "modules_news/preferences".
	 *
	 * @return news_PreferencesService
	 */
	public function getPreferencesService()
	{
		return news_PreferencesService::getInstance();
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