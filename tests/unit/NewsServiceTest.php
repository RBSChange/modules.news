<?php
class news_tests_NewsServiceTest extends news_tests_AbstractBaseUnitTest
{

	public function testGetNewsListByParentId()
	{
		$topicA = news_tests_TestHelper::getNewTopic('A topic');
		$dateA = date_Calendar::now()->sub(date_Calendar::YEAR, 1);
		$newsA = news_tests_TestHelper::getArchiveNews('Une archive', $topicA->getId(), $dateA);
		$dateB = date_Calendar::now()->sub(date_Calendar::DAY, 1);
		$newsB = news_tests_TestHelper::getPublishedNonHomepageNews('Une publiée pas accueil', $topicA->getId(), $dateB);
		$dateC = date_Calendar::now()->add(date_Calendar::DAY, 1);
		$newsC = news_tests_TestHelper::getFrontpageNews('Ramucho', $topicA->getId(), $dateC);
		$topicB = news_tests_TestHelper::getNewTopic('B topic', $topicA->getId());
		$dateD = date_Calendar::now()->sub(date_Calendar::DAY, 4);
		$newsD = news_tests_TestHelper::getPublishedNonHomepageNews('Rodrigo', $topicB->getId(), $dateD);
		$newsD->setPriority(8);
		$newsD->save();
		$newsE = news_tests_TestHelper::getFrontpageNews('Pepito', $topicB->getId(), $dateD);
		$newsE->setPriority(10);
		$newsE->save();

		$defaultWebsiteId = $this->getDefaultWebsite()->getId();

		// -----
		$list = $this->getNewsService()->getNewsListByParentId($defaultWebsiteId);
		$this->assertEquals(4, count($list));
		$this->assertContains($newsB, $list);
		$this->assertContains($newsC, $list);
		$this->assertContains($newsD, $list);
		$this->assertContains($newsE, $list);
		$this->assertNotContains($newsA, $list);
		// List should be reversed time-ordered
		$this->assertPersistentDocumentEquals($list[0], $newsC);
		$this->assertPersistentDocumentEquals($list[1], $newsB);
		$this->assertPersistentDocumentEquals($list[2], $newsE);
		$this->assertPersistentDocumentEquals($list[3], $newsD);

		// Should be the same list
		$listB = $this->getNewsService()->getNewsListByParentId($topicA->getId());
		$this->assertEquals(4, count($listB));
		$this->assertContains($newsB, $listB);
		$this->assertContains($newsC, $listB);
		$this->assertContains($newsD, $listB);
		$this->assertContains($newsE, $listB);
		$this->assertNotContains($newsA, $listB);
		// List should be reversed time-ordered
		$this->assertPersistentDocumentEquals($listB[0], $newsC);
		$this->assertPersistentDocumentEquals($listB[1], $newsB);
		$this->assertPersistentDocumentEquals($listB[2], $newsE);
		$this->assertPersistentDocumentEquals($listB[3], $newsD);

		// Should contain only two news
		$listC = $this->getNewsService()->getNewsListByParentId($topicB->getId());
		$this->assertEquals(2, count($listC));
		$this->assertNotContains($newsB, $listC);
		$this->assertNotContains($newsC, $listC);
		$this->assertContains($newsD, $listC);
		$this->assertContains($newsE, $listC);
		$this->assertNotContains($newsA, $listC);
		$this->assertPersistentDocumentEquals($listC[0], $newsE);
		$this->assertPersistentDocumentEquals($listC[1], $newsD);

		// Homepage display
		$listHomepage = $this->getNewsService()->getNewsListByParentId($defaultWebsiteId, news_NewsService::HOMEPAGE_DISPLAY);
		$this->assertEquals(2, count($listHomepage));
		$this->assertNotContains($newsB, $listHomepage);
		$this->assertContains($newsC, $listHomepage);
		$this->assertNotContains($newsD, $listHomepage);
		$this->assertContains($newsE, $listHomepage);
		$this->assertNotContains($newsA, $listHomepage);
		$this->assertPersistentDocumentEquals($listHomepage[0], $newsC);
		$this->assertPersistentDocumentEquals($listHomepage[1], $newsE);

		// Homepage display restricted
		$listHomepageRestricted = $this->getNewsService()->getNewsListByParentId($topicB->getId(), news_NewsService::HOMEPAGE_DISPLAY);
		$this->assertEquals(1, count($listHomepageRestricted));
		$this->assertNotContains($newsB, $listHomepageRestricted);
		$this->assertNotContains($newsC, $listHomepageRestricted);
		$this->assertNotContains($newsD, $listHomepageRestricted);
		$this->assertNotContains($newsA, $listHomepageRestricted);
		$this->assertContains($newsE, $listHomepageRestricted);
		$this->assertPersistentDocumentEquals($listHomepageRestricted[0], $newsE);

		// Test include children
		$listLimit = $this->getNewsService()->getNewsListByParentId($topicA->getId(), news_NewsService::CLASSIC_DISPLAY, false);
		$this->assertEquals(2, count($listLimit));
		$this->assertContains($newsB, $listLimit);
		$this->assertContains($newsC, $listLimit);
		$this->assertNotContains($newsD, $listLimit);
		$this->assertNotContains($newsE, $listLimit);
		$this->assertNotContains($newsA, $listLimit);
		// List should be reversed time-ordered
		$this->assertPersistentDocumentEquals($listLimit[0], $newsC);
		$this->assertPersistentDocumentEquals($listLimit[1], $newsB);



		// Test sorting
		$listInverse = $this->getNewsService()->getNewsListByParentId($defaultWebsiteId, news_NewsService::CLASSIC_DISPLAY, true, true);
		$this->assertEquals(4, count($listInverse));
		$this->assertContains($newsB, $listInverse);
		$this->assertContains($newsC, $listInverse);
		$this->assertContains($newsD, $listInverse);
		$this->assertContains($newsE, $listInverse);
		$this->assertNotContains($newsA, $listInverse);
		// List should be reversed time-ordered
		$this->assertPersistentDocumentEquals($listInverse[0], $newsE);
		$this->assertPersistentDocumentEquals($listInverse[1], $newsD);
		$this->assertPersistentDocumentEquals($listInverse[2], $newsB);
		$this->assertPersistentDocumentEquals($listInverse[3], $newsC);
	}

	public function testGetArchiveNewsListByParentId()
	{
		/**
		 * TEST DATE SET
		 */
		$dateA = date_Calendar::getInstance('1978-06-04 00:00:00');
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
		$newsI = news_tests_TestHelper::getPublishedNonHomepageNews('911 is a joke', $topicA->getId(), $dateI);
		$newsJ = news_tests_TestHelper::getFrontpageNews('Pollywannacracka', $topicC->getId(), $dateJ);
		// let's begin testing



		$startDate = date_Calendar::getInstance('1970-01-01 00:00:00');
		$endDate = date_Calendar::now();
		// Full list
		$fullList = $this->getNewsService()->getArchiveNewsListByParentId($this->getDefaultWebsite()->getId(), $startDate, $endDate, true);
		$this->assertCount(8, $fullList);
		$this->assertContains($newsA, $fullList);
		$this->assertContains($newsB, $fullList);
		$this->assertContains($newsC, $fullList);
		$this->assertContains($newsD, $fullList);
		$this->assertContains($newsE, $fullList);
		$this->assertContains($newsF, $fullList);
		$this->assertContains($newsG, $fullList);
		$this->assertContains($newsH, $fullList);
		$this->assertNotContains($newsI, $fullList);
		$this->assertNotContains($newsJ, $fullList);
		//Ordering
		$this->assertPersistentDocumentEquals($newsH, $fullList[0]);
		$this->assertPersistentDocumentEquals($newsF, $fullList[1]);
		$this->assertPersistentDocumentEquals($newsG, $fullList[2]);
		$this->assertPersistentDocumentEquals($newsE, $fullList[3]);
		$this->assertPersistentDocumentEquals($newsC, $fullList[4]);
		$this->assertPersistentDocumentEquals($newsD, $fullList[5]);
		$this->assertPersistentDocumentEquals($newsB, $fullList[6]);
		$this->assertPersistentDocumentEquals($newsA, $fullList[7]);

		// "Restricted List" under specific parent
		$restrictedList = $this->getNewsService()->getArchiveNewsListByParentId($topicB->getId(), $startDate, $endDate, true);
		$this->assertCount(4, $restrictedList);
		$this->assertNotContains($newsA, $restrictedList);
		$this->assertNotContains($newsB, $restrictedList);
		$this->assertNotContains($newsC, $restrictedList);
		$this->assertNotContains($newsD, $restrictedList);
		$this->assertContains($newsE, $restrictedList);
		$this->assertContains($newsF, $restrictedList);
		$this->assertContains($newsG, $restrictedList);
		$this->assertContains($newsH, $restrictedList);
		$this->assertNotContains($newsI, $restrictedList);
		$this->assertNotContains($newsJ, $restrictedList);
		//Ordering
		$this->assertPersistentDocumentEquals($newsH, $restrictedList[0]);
		$this->assertPersistentDocumentEquals($newsF, $restrictedList[1]);
		$this->assertPersistentDocumentEquals($newsG, $restrictedList[2]);
		$this->assertPersistentDocumentEquals($newsE, $restrictedList[3]);

		// Restriction by date
		$begin2007 = date_Calendar::getInstance('2007-01-01 00:00:00');
		$end2007 = date_Calendar::getInstance('2007-12-31 23:59:59');
		$restrictedDateList = $this->getNewsService()->getArchiveNewsListByParentId($this->getDefaultWebsite()->getId(), $begin2007, $end2007, true);
		$this->assertCount(6, $restrictedDateList);
		$this->assertNotContains($newsA, $restrictedDateList);
		$this->assertContains($newsB, $restrictedDateList);
		$this->assertContains($newsC, $restrictedDateList);
		$this->assertContains($newsD, $restrictedDateList);
		$this->assertContains($newsE, $restrictedDateList);
		$this->assertContains($newsF, $restrictedDateList);
		$this->assertContains($newsG, $restrictedDateList);
		$this->assertNotContains($newsH, $restrictedDateList);
		$this->assertNotContains($newsI, $restrictedDateList);
		$this->assertNotContains($newsJ, $restrictedDateList);
		//Ordering
		$this->assertPersistentDocumentEquals($newsF, $restrictedDateList[0]);
		$this->assertPersistentDocumentEquals($newsG, $restrictedDateList[1]);
		$this->assertPersistentDocumentEquals($newsE, $restrictedDateList[2]);
		$this->assertPersistentDocumentEquals($newsC, $restrictedDateList[3]);
		$this->assertPersistentDocumentEquals($newsD, $restrictedDateList[4]);
		$this->assertPersistentDocumentEquals($newsB, $restrictedDateList[5]);

		// Restriction by date and limit
		$begin2007 = date_Calendar::getInstance('2007-01-01 00:00:00');
		$end2007 = date_Calendar::getInstance('2007-12-31 23:59:59');
		$restrictedDateList = $this->getNewsService()->getArchiveNewsListByParentId($topicA->getId(), $begin2007, $end2007, false);
		$this->assertCount(3, $restrictedDateList);
		$this->assertNotContains($newsA, $restrictedDateList);
		$this->assertContains($newsB, $restrictedDateList);
		$this->assertContains($newsC, $restrictedDateList);
		$this->assertContains($newsD, $restrictedDateList);
		$this->assertNotContains($newsE, $restrictedDateList);
		$this->assertNotContains($newsF, $restrictedDateList);
		$this->assertNotContains($newsG, $restrictedDateList);
		$this->assertNotContains($newsH, $restrictedDateList);
		$this->assertNotContains($newsI, $restrictedDateList);
		$this->assertNotContains($newsJ, $restrictedDateList);
		//Ordering
		$this->assertPersistentDocumentEquals($newsC, $restrictedDateList[0]);
		$this->assertPersistentDocumentEquals($newsD, $restrictedDateList[1]);
		$this->assertPersistentDocumentEquals($newsB, $restrictedDateList[2]);
	}

	public function testGetFormattedArchiveNewsListByParentId()
	{
		/**
		 * No archives should yield an empty list
		 */
		$startDate = date_Calendar::getInstance('1970-01-01 00:00:00');
		$endDate = date_Calendar::now();
		// Full list
		$fullList = $this->getNewsService()->getFormattedArchiveNewsListByParentId($this->getDefaultWebsite()->getId(), $startDate, $endDate, true);
		$this->assertEmpty($fullList);
		/**
		 * TEST DATE SET
		 */
		$dateA = date_Calendar::getInstance('1978-06-04 00:00:00');
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
		$newsI = news_tests_TestHelper::getPublishedNonHomepageNews('911 is a joke', $topicA->getId(), $dateI);
		$newsJ = news_tests_TestHelper::getFrontpageNews('Pollywannacracka', $topicC->getId(), $dateJ);
		// let's begin testing

		$startDate = date_Calendar::getInstance('1970-01-01 00:00:00');
		$endDate = date_Calendar::now();
		// Full list
		$fullList = $this->getNewsService()->getFormattedArchiveNewsListByParentId($this->getDefaultWebsite()->getId(), $startDate, $endDate, true);
		$this->assertCount($endDate->getYear()-1970+1, $fullList);
		foreach ($fullList as $year => $monthsArray )
		{
			switch($year)
			{
				case 1978:
				case 2007:
				case 2008:
					$this->assertNotEmpty($monthsArray);
					break;
				default:
					$this->assertEmpty($monthsArray);
			}
		}

		foreach ($fullList[1978] as $month => $eventList)
		{
			switch($month)
			{
				case 6:
					$this->assertCount(1, $eventList);
					$this->assertContains($newsA, $eventList);
					break;
				default:
					$this->assertEmpty($monthsArray);
			}
		}
		$dateB = date_Calendar::getInstance('2007-06-04 00:00:00');
		$dateC = date_Calendar::getInstance('2007-06-05 00:00:00');
		$dateD = date_Calendar::getInstance('2007-06-05 00:00:00');
		$dateE = date_Calendar::getInstance('2007-06-06 00:00:00');
		$dateF = date_Calendar::getInstance('2007-12-25 00:00:00');
		$dateG = date_Calendar::getInstance('2007-07-04 00:00:00');
		foreach ($fullList[2007] as $month => $eventList)
		{
			switch($month)
			{
				case 6:
					$this->assertCount(4, $eventList);
					$this->assertContains($newsB, $eventList);
					$this->assertContains($newsC, $eventList);
					$this->assertContains($newsD, $eventList);
					$this->assertContains($newsE, $eventList);
					$this->assertPersistentDocumentEquals($newsE, $eventList[0]);
					$this->assertPersistentDocumentEquals($newsC, $eventList[1]);
					$this->assertPersistentDocumentEquals($newsD, $eventList[2]);
					$this->assertPersistentDocumentEquals($newsB, $eventList[3]);
					break;
				case 7:
					$this->assertCount(1, $eventList);
					$this->assertContains($newsG, $eventList);
					break;
				case 12:
					$this->assertCount(1, $eventList);
					$this->assertContains($newsF, $eventList);
				default:
					$this->assertEmpty($monthsArray);
			}
		}
		foreach ($fullList[2008] as $month => $eventList)
		{
			switch($month)
			{
				case 1:
					$this->assertCount(1, $eventList);
					$this->assertContains($newsH, $eventList);
					break;
				default:
					$this->assertEmpty($monthsArray);
			}
		}
	}

	/**
	 * test made in the sole purpose to ensure
	 */
	public function testIsPublishable()
	{
		$pp = f_persistentdocument_PersistentProvider::getInstance();
		$ns = $this->getNewsService();
		$news = $ns->getNewDocumentInstance(); 
		$news->setDate(date_Calendar::now());
		$news->setLabel('toto');
		$news->save();
		$news->activate();
		$this->assertTrue($ns->isPublishable($news));
		//
		$news->setStartpublicationdate(date_Calendar::now()->add(date_Calendar::DAY, 1));
		$this->assertFalse($ns->isPublishable($news));
		//
		$news->setStartpublicationdate(null);
		$news->setEndpublicationdate(date_Calendar::tomorrow(false));
		$this->assertTrue($ns->isPublishable($news));
		//
		$news->setEndpublicationdate(date_Calendar::yesterday(false));
		$this->assertFalse($ns->isPublishable($news));
		//
		$news->setStartpublicationdate(date_Calendar::yesterday());
		$news->setEndpublicationdate(date_Calendar::tomorrow());
		$this->assertTrue($ns->isPublishable($news));
		//
		$news->setStartpublicationdate(date_Calendar::yesterday()->sub(date_Calendar::DAY, 1));
		$news->setEndpublicationdate(date_Calendar::yesterday());
		$this->assertFalse($ns->isPublishable($news));
	}
}