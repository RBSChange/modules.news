<?php

class news_ViewArchiveAction extends news_ViewListAction
{
	/**
	 * @return String
	 */
	protected function getTagSuffix()
	{
		return 'archive';
	}
}