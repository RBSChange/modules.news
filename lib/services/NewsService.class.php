<?php
/**
 * @date Thu, 07 Jun 2007 10:14:47 +0200
 * @author intstaufl
 */
class news_NewsService extends f_persistentdocument_DocumentService
{
	const HOMEPAGE_DISPLAY = 'homepage';
	const CLASSIC_DISPLAY = 'classic';
	const ARCHIVE_DISPLAY = 'archive';
	/**
	 * @var news_NewsService
	 */
	private static $instance;
	
	/**
	 * @return news_NewsService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	

	/**
	 * @return news_persistentdocument_news
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_news/news');
	}
	
	/**
	 * Create a query based on 'modules_news/news' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_news/news');
	}
	
	/**
	 * @param news_persistentdocument_news $news
	 * @param String $oldPublicationStatus
	 * @param array $params
	 * @return void
	 */
	protected function publicationStatusChanged($news, $oldPublicationStatus, $params)
	{
		if ($oldPublicationStatus == 'PUBLICATED' || $news->isPublished())
		{
			$this->updateVisibilities($news);
			$this->saveVisibilityInfos($news);
		}
	}
	
	/**
	 * @param news_persistentdocument_news $news
	 */
	private function saveVisibilityInfos($news)
	{
		if ($news->isModified())
		{
			try
			{
				$modifiedPropertyNames = $news->getModifiedPropertyNames();
				try
				{
					$this->tm->beginTransaction();
					$this->pp->updateDocument($news);
					$this->tm->commit();
				}
				catch (Exception $e)
				{
					$this->tm->rollBack($e);
				}
				
				f_event_EventManager::dispatchEvent('persistentDocumentUpdated', $this, array("document" => $news, "modifiedPropertyNames" => $modifiedPropertyNames, "oldPropertyValues" => array()));
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}
	}
	
	/**
	 * @return f_persistentdocument_criteria_Query
	 */
	private function createNewsQueryByParentId($docId, $includeChildren)
	{
		$newsQuery = $this->createQuery()->add(Restrictions::published());
		if (!is_null($docId))
		{
			$doc = DocumentHelper::getDocumentInstance($docId);
			if ($doc instanceof website_persistentdocument_topic || $doc instanceof website_persistentdocument_website)
			{
				if ($includeChildren == false)
				{
					$newsQuery->add(Restrictions::childOf($docId));
				}
				else
				{
					$newsQuery->add(Restrictions::descendentOf($docId));
				}
			}
		}
		return $newsQuery;
	}
	

	/**
	 * Get the list of news attached to $docId. If $docId is null, we default to the module's root folder.
	 *
	 * @param Integer $docId
	 * @param String $displayType
	 * @param Boolean $includeChildren
	 * @param Boolean $timeOrdered
	 * @param Integer $limit
	 * @return Array<news_persistentdocument_news>
	 */
	public function getNewsListByParentId($docId, $displayType = 'classic', $includeChildren = true, $timeOrdered = false, $limit = -1)
	{
		$newsQuery = $this->createNewsQueryByParentId($docId, $includeChildren);
		if ($displayType == self::HOMEPAGE_DISPLAY)
		{
			$newsQuery->add(Restrictions::eq('homepagevisibility', 1));
		}
		else
		{
			$newsQuery->add(Restrictions::eq('archivevisibility', 0));
		}
		
		if ($limit > 0)
		{
			$newsQuery->setMaxResults($limit);
		}
		
		if ($timeOrdered)
		{
			$newsQuery->addOrder(Order::asc('date'));
			$newsQuery->addOrder(Order::desc('priority'));
		}
		else
		{
			$newsQuery->addOrder(Order::desc('date'));
			$newsQuery->addOrder(Order::desc('priority'));
		}
		
		return $this->pp->find($newsQuery);
	}
	
	/**
	 * Get a rss_FeedWriter instance of the the news list by "parent id" $docId.
	 *
	 * @param Integer $docId
	 * @return rss_FeedWriter
	 */
	public function getRSSFeedWriterByParentId($docId)
	{
		$maxItemCount = -1;
		$preferenceDocument = ModuleService::getInstance()->getPreferencesDocument('news');
		if (null != $preferenceDocument)
		{
			$maxItemCount = $preferenceDocument->getRssitemcount();
		}
		$writer = new rss_FeedWriter();
		foreach ($this->getNewsListByParentId($docId, self::CLASSIC_DISPLAY, true, false, $maxItemCount) as $news)
		{
			$writer->addItem($news);
		}
		return $writer;
	}
	
	/**
	 * Get the list of visible "filed" news.
	 *
	 * @param Integer $docId
	 * @param date_Calendar $start
	 * @param date_Calendar $stop
	 * @param Boolean $includeChildren
	 * @return Array<news_persistentdocument_news>
	 */
	public function getArchiveNewsListByParentId($docId, $start, $stop, $includeChildren)
	{
		$newsQuery = $this->createNewsQueryByParentId($docId, $includeChildren);
		$newsQuery->add(Restrictions::eq('archivevisibility', 1));
		$newsQuery->add(Restrictions::between('date', date_Converter::convertDateToGMT($start)->toString(), date_Converter::convertDateToGMT($stop->toString())));
		$newsQuery->addOrder(Order::desc('date'));
		$newsQuery->addOrder(Order::desc('priority'));
		return $this->pp->find($newsQuery);
	}
	
	/**
	 * Get an associative array of of visible "filed" news or an empty array if no news available
	 * @example  array( '2005' => Array( '1' => ....,
	 * 									  '5' => ...., ...));
	 * @param Integer $docId
	 * @param date_Calendar $start
	 * @param date_Calendar $stop
	 * @param Boolean $includeChildren
	 * @return Array<String, Array<String, Array<news_persistentdocument_news>>>
	 */
	public function getFormattedArchiveNewsListByParentId($docId, $start, $stop, $includeChildren = true)
	{
		$archives = $this->getArchiveNewsListByParentId($docId, $start, $stop, $includeChildren);
		
		// If we have no archives return an empty list...
		if (f_util_ArrayUtils::isEmpty($archives))
		{
			return array();
		}
		$result = array();
		$arrayPosition = 0;
		for ($i = $stop->getYear(); $i >= $start->getYear(); $i--)
		{
			$result[strval($i)] = array();
			$archiveSize = count($archives);
			for (; $arrayPosition < $archiveSize; $arrayPosition++)
			{
				$date = date_Calendar::getInstance($archives[$arrayPosition]->getDate());
				$uiDate = date_Calendar::getInstance($archives[$arrayPosition]->getUIDate());
				if ($i == $date->getYear())
				{
					$month = strval($uiDate->getMonth());
					$year = strval($uiDate->getYear());
					if (isset($result[$year][$month]))
					{
						$result[strval($year)][$month][] = $archives[$arrayPosition];
					}
					else
					{
						$result[strval($year)][$month] = array($archives[$arrayPosition]);
					}
				}
				else
				{
					break;
				}
			}
		}
		return $result;
	}
	
	private function validateDuration($value)
	{
		$trimmedValue = trim($value);
		if (f_util_StringUtils::isEmpty($trimmedValue) || preg_match('/^[\d]+[dwmy]$/u', $trimmedValue) > 0)
		{
			return;
		}
		throw new BaseException('Invalid duration', '&modules.news.errors.Invalid-duration;');
	}
	
	/**
	 * @param news_persistentdocument_news $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId)
	{
		// FIXME: this "validation" is done here, but should be done elsewere...
		$this->validateDuration($document->getFrontpageduration());
		$this->validateDuration($document->getArchiveduration());
		$start = $document->getStartpublicationdate();
		if ($start !== null)
		{
			$frontpageDuration = $document->getFrontpageduration();
			$frontpageDurationLength = strlen($frontpageDuration);
			$frontpageTimespan = new date_TimeSpan();
			switch ($frontpageDuration[$frontpageDurationLength - 1])
			{
				case 'd':
					$frontpageTimespan->setDays(intval(substr($frontpageDuration, 0, $frontpageDurationLength - 1)));
					break;
				case 'w':
					$frontpageTimespan->setDays(7 * intval(substr($frontpageDuration, 0, $frontpageDurationLength - 1)));
					break;
				case 'm':
					$frontpageTimespan->setMonths(intval(substr($frontpageDuration, 0, $frontpageDurationLength - 1)));
					break;
				case 'y':
					$frontpageTimespan->setYears(intval(substr($frontpageDuration, 0, $frontpageDurationLength - 1)));
					break;
				default:
					break;
			}
			$endHomePage = date_Calendar::getInstance($start)->addTimeSpan($frontpageTimespan)->toString();
			$document->setEndhomepagedate($endHomePage);
			Framework::fatal($endHomePage);
		}
		

		$startAchive = $document->getStartarchivedate();
		if ($startAchive !== null)
		{
			$archiveDuration = $document->getArchiveduration();
			$archiveDurationLength = strlen($archiveDuration);
			$archiveTimespan = new date_TimeSpan();
			switch ($archiveDuration[$archiveDurationLength - 1])
			{
				case 'd':
					$archiveTimespan->setDays(intval(substr($archiveDuration, 0, $archiveDurationLength - 1)));
					break;
				case 'w':
					$archiveTimespan->setDays(7 * intval(substr($archiveDuration, 0, $archiveDurationLength - 1)));
					break;
				case 'm':
					$archiveTimespan->setMonths(intval(substr($archiveDuration, 0, $archiveDurationLength - 1)));
					break;
				case 'y':
					$archiveTimespan->setYears(intval(substr($archiveDuration, 0, $archiveDurationLength - 1)));
					break;
				default:
					break;
			}
			$endArchiveDate = date_Calendar::getInstance($startAchive)->addTimeSpan($archiveTimespan)->toString();
			Framework::fatal($endArchiveDate);
			$document->setEndarchivedate($endArchiveDate);
		}
		$this->updateVisibilities($document);
	}
	
	/**
	 * @param news_persistentdocument_news $document
	 */
	protected function updateVisibilities($document)
	{
		if ($document->isPublished())
		{
			$now = date_Calendar::now()->toString();
			$endHomepage = $document->getEndhomepagedate();
			if ($endHomepage != null && $endHomepage > $now)
			{
				$document->setHomepagevisibility(true);
				$document->setArchivevisibility(false);
			}
			else
			{
				$document->setHomepagevisibility(false);
				$startArchive = $document->getStartarchivedate();
				if ($startArchive != null && $startArchive < $now)
				{
					$document->setArchivevisibility(true);
				}
				else
				{
					$document->setArchivevisibility(false);
				}
			}
		}
		else
		{
			$document->setArchivevisibility(false);
			$document->setHomepagevisibility(false);
		}
	}
	
	/**
	 *	Listener task
	 */
	public function onDayChange()
	{
		$now = date_Calendar::now()->toString();
		$rc = RequestContext::getInstance();
		//Mise à jour des actualités en page d'acceuil
		$query = $this->createQuery()->add(Restrictions::eq('homepagevisibility', 1))->add(Restrictions::lt('endhomepagedate', $now));
		foreach ($this->pp->find($query) as $document)
		{
			foreach ($rc->getSupportedLanguages() as $lang)
			{
				if ($document->isLangAvailable($lang))
				{
					try
					{
						$rc->beginI18nWork($lang);
						$this->updateVisibilities($document);
						$this->saveVisibilityInfos($document);
						$rc->endI18nWork();
					}
					catch (Exception $e)
					{
						$rc->endI18nWork($e);
					}
				}
			}
		}
		
		//Mise a jour des actualités en archive
		$query = $this->createQuery()->add(Restrictions::eq('archivevisibility', 1))->add(Restrictions::lt('startarchivedate', $now));
		
		foreach ($this->pp->find($query) as $document)
		{
			foreach ($rc->getSupportedLanguages() as $lang)
			{
				if ($document->isLangAvailable($lang))
				{
					try
					{
						$rc->beginI18nWork($lang);
						$this->updateVisibilities($document);
						$this->saveVisibilityInfos($document);
						$rc->endI18nWork();
					}
					catch (Exception $e)
					{
						$rc->endI18nWork($e);
					}
				}
			}
		}
	}
	
	/**
	 * Returns the "detail" link of the news document which is either the document's url of the URL of it's linked page.
	 *
	 * @param news_persistentdocument_news $news
	 */
	public function getUrl($news)
	{
		if ($news->getLinkedpage() === null)
		{
			return LinkHelper::getUrl($news);
		}
		return LinkHelper::getUrl($news->getLinkedpage());
	}
	

	public final function getPendingTasksForCurrentUser()
	{
		$newsModel = f_persistentdocument_PersistentDocumentModel::getInstance('news', 'news');
		if (!$newsModel->hasWorkflow())
		{
			return array();
		}
		$query = f_persistentdocument_PersistentProvider::getInstance()->createQuery('modules_task/usertask');
		$query->add(Restrictions::eq('user', users_UserService::getInstance()->getCurrentUser()->getId()));
		$query->add(Restrictions::published());
		$query->add(Restrictions::eq('workitem.transition.taskid', $newsModel->getWorkflowStartTask()));
		$query->addOrder(Order::desc('document_creationdate'));
		$query->setMaxResults(50);
		return $query->find();
	}
	

	/**
	 * @see f_persistentdocument_DocumentService::getResume()
	 *
	 * @param news_persistentdocument_news $document
	 * @param string $forModuleName
	 * @param unknown_type $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections)
	{
		$data = parent::getResume($document, $forModuleName, $allowedSections);
		$data['properties']['frontpage'] = f_Locale::translateUI('&modules.uixul.bo.general.' . ($document->getHomepagevisibility() ? 'Yes;' : 'No;'));
		$data['properties']['endhomepagedate'] = $document->getEndhomepagedate();
		$data['properties']['startarchivedate'] = $document->getStartarchivedate();
		$data['properties']['endarchivedate'] = $document->getEndarchivedate();
		return $data;
	}

}