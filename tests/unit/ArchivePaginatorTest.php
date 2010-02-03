<?php
class news_tests_ArchivePaginatorTest extends news_tests_AbstractBaseUnitTest
{
	public function testInstance()
	{
		/**
		 * TEST DATE SET
		 */
		$dateA = date_Calendar::getInstance('2006-06-04 00:00:00');
		$dateB = date_Calendar::getInstance('2007-06-04 00:00:00');
		$dateC = date_Calendar::getInstance('2007-06-05 00:00:00');
		$dateD = date_Calendar::getInstance('2007-06-05 00:00:00');
		$dateE = date_Calendar::getInstance('2007-06-06 00:00:00');
		$dateF = date_Calendar::getInstance('2007-12-25 00:00:00');
		$dateG = date_Calendar::getInstance('2007-07-04 00:00:00');
		$dateH = date_Calendar::getInstance('2008-01-01 00:00:00');
		$dateI = date_Calendar::now();
		$dateJ = date_Calendar::yesterday();
		$topicA = news_tests_TestHelper::getNewTopic('A topic');
		$topicB = news_tests_TestHelper::getNewTopic('Another topic', $topicA->getId());
		$topicC = news_tests_TestHelper::getNewTopic('Yet another topic', $topicB->getId());
		$newsA = news_tests_TestHelper::getArchiveNews(':)', $topicA->getId(), $dateA);
		$newsB = news_tests_TestHelper::getArchiveNews(':) + 29', $topicA->getId(), $dateB);
		$newsC = news_tests_TestHelper::getArchiveNews('Pas prioritaire', $topicA->getId(), $dateC);
		$newsC->setPriority(10);
		$newsC->save();
		$newsD = news_tests_TestHelper::getArchiveNews(':) + 29', $topicA->getId(), $dateD);
		$newsD->setPriority(2);
		$newsD->save();
		$newsE = news_tests_TestHelper::getArchiveNews(':)', $topicB->getId(), $dateE);
		$newsF = news_tests_TestHelper::getArchiveNews(':)', $topicB->getId(), $dateF);
		$newsG = news_tests_TestHelper::getArchiveNews(':)', $topicC->getId(), $dateG);
		$newsH = news_tests_TestHelper::getArchiveNews(':)', $topicC->getId(), $dateH);

		$newsList = news_NewsService::getInstance()->getFormattedArchiveNewsListByParentId( $topicA->getId(),
		date_Calendar::getInstance('2005-01-01'),
		date_Calendar::getInstance('2008-02-01'),
		true );
		$paginator = new news_ArchivePaginator($newsList, 2007, 6, 6);

		// Year pagination
		$maxYear = date_Calendar::now()->getYear();
		$minYear = $maxYear - 6;
		$yearPaginator = $paginator->yearPaginator;
		$i = 0;
		foreach($yearPaginator as $entry)
		{
			$currentYear = ($maxYear-$i);
			$this->assertEquals($currentYear, $entry['value']);
			$this->assertEquals($currentYear, $entry['name']);
			switch ($currentYear)
			{
				case 2008:
				case 2006:
					$this->assertEquals(1, $entry['count']);
					$this->assertFalse($entry['isCurrent']);
					$this->assertFalse($entry['isEmpty']);
					break;
				case 2007:
					$this->assertEquals(3, $entry['count']);
					$this->assertTrue($entry['isCurrent']);
					$this->assertFalse($entry['isEmpty']);
					break;
				default:
					$this->assertEquals(0, $entry['count']);
					$this->assertFalse($entry['isCurrent']);
					$this->assertTrue($entry['isEmpty']);
					break;
			}
			$urlParts = explode('&', substr($entry['url'], 1));
			$this->assertContains('newsParam%5Byear%5D='.$currentYear, $urlParts);
			$this->assertContains('newsParam%5Bpage%5D=1', $urlParts);
			switch ($currentYear)
			{
				case 2008:
					$this->assertContains('newsParam%5Bmonth%5D=1', $urlParts);
					break;
				case 2007:
					$this->assertContains('newsParam%5Bmonth%5D=6', $urlParts);
					break;
				case 2006:
					$this->assertContains('newsParam%5Bmonth%5D=6', $urlParts);
					break;
				default:
					break;
			}
			$i++;
		}
		// monthPaginator
		$currentMonth = 1;
		foreach($paginator->monthPaginator as $entry)
		{
			$this->assertEquals($currentMonth, $entry['value']);
			switch ($currentMonth)
			{
				case 6:
					$this->assertEquals(4, $entry['count']);
					$this->assertTrue($entry['isCurrent']);
					$this->assertFalse($entry['isEmpty']);
					break;
				case 7:
				case 12:
					$this->assertEquals(1, $entry['count']);
					$this->assertFalse($entry['isCurrent']);
					$this->assertFalse($entry['isEmpty']);
					break;
				default:
					$this->assertFalse($entry['isCurrent']);
					$this->assertTrue($entry['isEmpty']);
					break;
			}
			$urlParts = explode('&', substr($entry['url'], 1));
			$this->assertContains('newsParam%5Byear%5D=2007', $urlParts);
			$this->assertContains('newsParam%5Bpage%5D=1', $urlParts);
			$currentMonth++;
		}
	}
}