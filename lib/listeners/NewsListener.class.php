<?php
/**
 * @date 8 févr. 08 15:04:39
 * @author franck.stauffer
 * @package modules.news
 */

class news_NewsListener
{
	public function onDayChange($sender, $params)
	{
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__);
		}
		news_NewsService::getInstance()->onDayChange();
	}
}