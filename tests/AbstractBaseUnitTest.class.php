<?php
abstract class news_tests_AbstractBaseUnitTest extends news_tests_AbstractBaseTest
{
	/**
	 * @return void
	 */
	public function prepareTestCase()
	{
		$this->resetDatabase();
	}
	
	public function prepareTest()
	{
		$this->clearModuleServiceCache();
		$this->resetDatabase();
	}
}