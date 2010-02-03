<?php
class news_tests_TestHelperTest extends news_tests_AbstractBaseUnitTest
{
	public function testGetTopic()
	{
		$newTopic1 = news_tests_TestHelper::getNewTopic('Test topic 1');
		$this->assertType('website_persistentdocument_topic', $newTopic1);
		$this->assertEquals('Test topic 1', $newTopic1->getLabel());
		$this->assertPersistentDocumentEquals(website_WebsiteModuleService::getInstance()->getDefaultWebsite(), website_TopicService::getInstance()->getParentOf($newTopic1));
		// -----
		$newTopic2 = news_tests_TestHelper::getNewTopic('Test topic 2', $newTopic1->getId());
		$this->assertType('website_persistentdocument_topic', $newTopic2);
		$this->assertEquals('Test topic 2', $newTopic2->getLabel());
		$this->assertPersistentDocumentEquals($newTopic1, website_TopicService::getInstance()->getParentOf($newTopic2));
	}

	public function testGetFrontpageNews()
	{
		$newTopicA = news_tests_TestHelper::getNewTopic('Test topic 1');
		$newsA = news_tests_TestHelper::getFrontpageNews('toto', $newTopicA->getId(), date_Calendar::yesterday(false));
		$this->assertPublished($newsA);
		$this->assertTrue($newsA->getHomepagevisibility());
		$this->assertFalse($newsA->getArchivevisibility());
		$this->assertEquals('toto', $newsA->getLabel());
		$this->assertPersistentDocumentEquals($this->getNewsService()->getParentOf($newsA), $newTopicA);
		$this->assertEquals(date_Calendar::yesterday(false)->toString(), $newsA->getDate());
	}
	
	public function testGetPublishedNonHomepageNews()
	{	
		$newTopicA = news_tests_TestHelper::getNewTopic('Test topic 1');
		$newsA = news_tests_TestHelper::getPublishedNonHomepageNews('toto', $newTopicA->getId(), date_Calendar::yesterday(false));
		$this->assertPublished($newsA);
		$this->assertFalse($newsA->getHomepagevisibility());
		$this->assertFalse($newsA->getArchivevisibility());
		$this->assertEquals('toto', $newsA->getLabel());
		$this->assertPersistentDocumentEquals($this->getNewsService()->getParentOf($newsA), $newTopicA);
		$this->assertEquals(date_Calendar::yesterday(false)->toString(), $newsA->getDate());
	}
	
	public function testGetArchiveNews()
	{
		$news = news_tests_TestHelper::getArchiveNews('Test');
		$this->assertNotPublished($news);
		$this->assertEquals('FILED', $news->getPublicationstatus());
		$this->assertTrue($news->getArchivevisibility());
		$this->assertEquals('Test', $news->getLabel());

		$newTopic = news_tests_TestHelper::getNewTopic('Test topic');
		$news =  news_tests_TestHelper::getArchiveNews('Test 2', $newTopic->getId(), date_Calendar::yesterday(false));
		$this->assertNotPublished($news);
		$this->assertEquals('FILED', $news->getPublicationstatus());
		$this->assertTrue($news->getArchivevisibility());
		$this->assertEquals('Test 2', $news->getLabel());
		$this->assertPersistentDocumentEquals($this->getNewsService()->getParentOf($news), $newTopic);
		$this->assertEquals(date_Calendar::yesterday(false)->toString(), $news->getDate());
		
	}
}
