<?php
/**
 * @date 8 févr. 08 15:02:36
 * @author franck.stauffer
 * @package  modules.news
 */

class news_BlockNewsContextualListAction extends news_BlockNewsListAction
{
	protected function getContainerId()
	{
		return $this->getPage()->getParent()->getId();
	}
}