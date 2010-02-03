<?php
class news_tests_NewsTest extends news_tests_AbstractBaseUnitTest
{
	/**
	 * @var news_persistentdocument_preferences
	 */
	private $prefs;

	/**
	 * @return news_persistentdocument_preferences;
	 *
	 */
	private function buildPrefs()
	{
		if ($this->prefs === null)
		{
			$this->prefs = news_PreferencesService::getInstance()->getNewDocumentInstance();
			$this->prefs->setDetailtitle('TITRE#RESUME#DATE');
			$this->prefs->setDetaildescription('RESUME#DATE#TITRE');
			$this->prefs->setDetailkeywords('DATE#TITRE#RESUME');
			$this->prefs->save();
		}
		return $this->prefs;
	}
	
	public function testGetArchiveTimeSpan()
	{
		$news = news_tests_TestHelper::getFrontpageNews('test');
		$news->setArchiveyear(1);
		$news->setArchivemonth(2);
		$news->setArchiveweek(3);
		$news->save();
		$expectedTimeSpan = $news->getArchiveTimeSpan();
		$this->assertType('date_TimeSpan', $expectedTimeSpan);
		$this->assertEquals(1, $expectedTimeSpan->getYears());
		$this->assertEquals(2, $expectedTimeSpan->getMonths());
		$this->assertEquals(21, $expectedTimeSpan->getDays());
	}
	
	public function testGetHomepageTimeSpan()
	{
		$news = news_tests_TestHelper::getFrontpageNews('test');
		$news->setPublicationyear(3);
		$news->setPublicationmonth(2);
		$news->setPublicationweek(1);
		$news->save();
		$expectedTimeSpan = $news->getHomepagevisibilityTimeSpan();
		$this->assertType('date_TimeSpan', $expectedTimeSpan);
		$this->assertEquals(3, $expectedTimeSpan->getYears());
		$this->assertEquals(2, $expectedTimeSpan->getMonths());
		$this->assertEquals(7, $expectedTimeSpan->getDays());
	}
	
	public function testGetDetailmetatitle()
	{
		$news = $this->getNewsService()->getNewDocumentInstance();
		$news->setDate(date_Calendar::now());
		$news->setLabel('news');
		$news->setSummary('swen');
		$this->assertNull($news->getDetailmetatitle());
		$this->buildPrefs();
		$elements = explode('#', $news->getDetailmetatitle());
		$this->assertCount(3, $elements);
		$this->assertEquals('news', $elements[0]);
		$this->assertEquals('swen', $elements[1]);
		$this->assertEquals(date_DateFormat::format($news->getDate(), 'd/m/Y'), $elements[2]);		
	}
	
	public function testGetDetailkeywords()
	{
		$news = $this->getNewsService()->getNewDocumentInstance();
		$news->setDate(date_Calendar::now());
		$news->setLabel('news');
		$news->setSummary('swen');
		$this->assertNull($news->getDetailkeywords());
		$this->buildPrefs();
		$elements = explode('#', $news->getDetailkeywords());
		$this->assertCount(3, $elements);
		$this->assertEquals('news', $elements[1]);
		$this->assertEquals('swen', $elements[2]);
		$this->assertEquals(date_DateFormat::format($news->getDate(), 'd/m/Y'), $elements[0]);		
	}
	
	public function testGetDetaildescription()
	{
		$news = $this->getNewsService()->getNewDocumentInstance();
		$news->setDate(date_Calendar::now());
		$news->setLabel('news');
		$news->setSummary('swen');
		$this->assertNull($news->getDetaildescription());
		$this->buildPrefs();
		$elements = explode('#', $news->getDetaildescription());
		$this->assertCount(3, $elements);
		$this->assertEquals('news', $elements[2]);
		$this->assertEquals('swen', $elements[0]);
		$this->assertEquals(date_DateFormat::format($news->getDate(), 'd/m/Y'), $elements[1]);		
	}
	
}