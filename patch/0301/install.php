<?php
/**
 * news_patch_0301
 * @package modules.news
 */
class news_patch_0301 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		f_util_System::execChangeCommand('compile-blocks');
		f_util_System::execChangeCommand('compile-locales', array('news'));
	}

	/**
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'news';
	}

	/**
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0301';
	}
}