<?php
/**
 * news_persistentdocument_news
 * @package modules.news
 */
class news_persistentdocument_news extends news_persistentdocument_newsbase implements indexer_IndexableDocument, rss_Item
{
	private $publicationDurationUnit = null;
	private $publicationDuration = null;
	private $archiveDurationUnit = null;
	private $archiveDuration = null;
	private $detailUrl = null;

	/**
	 * Get the indexable document
	 *
	 * @return indexer_IndexedDocument
	 */
	public function getIndexedDocument()
	{
		$indexedDoc = new indexer_IndexedDocument();
		$indexedDoc->setId($this->getId());
		$indexedDoc->setDocumentModel('modules_news/news');
		$indexedDoc->setLabel($this->getLabel());
		$indexedDoc->setLang(RequestContext::getInstance()->getLang());
		$indexedDoc->setText($this->getFullTextForIndexation());
		return $indexedDoc;
	}
	
	/**
	 * @return String
	 */
	private function getFullTextForIndexation()
	{
		$fullText = "";
		$attachement = $this->getAttachment();
		if ($attachement !== null)
		{
			$idxDoc = $attachement->getIndexedDocument();
			if ($idxDoc !== null)
			{
				$fullText = $idxDoc->getLabel() . " : " . $idxDoc->getText();
			}
		}
		
		$accessMap = $this->getAccessmap();
		if ($accessMap !== null)
		{
			$idxDoc = $accessMap->getIndexedDocument();
			if ($idxDoc !== null)
			{
				$fullText .= " " . $idxDoc->getLabel() . " : " . $idxDoc->getText();
			}
		}
		$fullText .= " " . $this->getSummaryAsHtml();
		$fullText .= " " . $this->getTextAsHtml();
		$fullText .= " " . $this->getDatetimeinfoAsHtml();
		$fullText .= " " . $this->getPlaceAsHtml();
		$fullText .= " " . $this->getContactAsHtml();
		return $fullText;
	}

	/**
	 * @return date_TimeSpan
	 */
	public function getArchiveTimeSpan()
	{
		return new date_TimeSpan(intval($this->getArchiveyear()), intval($this->getArchivemonth()), intval($this->getArchiveweek())*7 );
	}
	
	/**
	 * @return Boolean
	 */
	public function hasArchiveTimeSpan()
	{
	    return (intval($this->getArchiveyear()) + intval($this->getArchivemonth()) + intval($this->getArchiveweek())) > 0;
	}

	/**
	 * @return date_TimeSpan
	 */
	public function getHomepagevisibilityTimeSpan()
	{
		return new date_TimeSpan(intval($this->getPublicationyear()), intval($this->getPublicationmonth()), intval($this->getPublicationweek())*7);
	}

	/**
	 * @return Boolean
	 */
	public function hasHomepagevisibilityTimeSpan()
	{
	    return (intval($this->getPublicationyear()) + intval($this->getPublicationmonth()) + intval($this->getPublicationweek())) > 0;
	}
	
	/**
	 * @return String
	 */
	public function getDetailmetatitle()
	{
		$prefs = ModuleService::getInstance()->getPreferencesDocument('news');
		if (!is_null($prefs))
		{
			$metaTitle = $this->replaceMeta($prefs->getDetailtitle());
		}
		if (f_util_StringUtils::isEmpty($metaTitle))
		{
			return $this->getLabel();
		}
		return $metaTitle;
	}

	/**
	 * @return String
	 */
	public function getDetaildescription()
	{
		$prefs = ModuleService::getInstance()->getPreferencesDocument('news');
		if (!is_null($prefs))
		{
			return $this->replaceMeta($prefs->getDetaildescription());
		}
		return null;
	}

	/**
	 * @return String
	 */
	public function getDetailkeywords()
	{
		$prefs = ModuleService::getInstance()->getPreferencesDocument('news');
		if (!is_null($prefs))
		{
			return $this->replaceMeta($prefs->getDetailkeywords());
		}
		return null;
	}
	
	/**
	 * @return Boolean
	 */
	public function hasLinkedPage()
	{
		return (null !== $this->getLinkedpage());
	}

	/**
	 * @return Integer
	 */
	public function getDateYear()
	{
		return date_Calendar::getInstance($this->getDate())->getYear();
	}
	
	/**
	 * @return String
	 */
	public function getDateMonth()
	{
		return sprintf("%02d", date_Calendar::getInstance($this->getDate())->getMonth());
	}
	
	/**
	 * @return String
	 */
	public function getDateDay()
	{
		return sprintf("%02d", date_Calendar::getInstance($this->getDate())->getDay());
	}
	
	/**
	 * @param String $target
	 * @return String
	 */
	private function replaceMeta($target)
	{
		$format = f_Locale::translate('&framework.date.date.smart-full-short;');
		
		$title = $this->getLabel();
		$summary = $this->getSummaryAsHtml();
		$date = date_Calendar::getInstance($this->getDate());
		$string = str_replace(array('TITLE', 'TITRE'), $title, $target);
		$string = str_replace(array('RESUME', 'SUMMARY'), $summary, $string);
		$string = str_replace('DATE', date_DateFormat::format($date, $format), $string);
		return $string;
	}
	
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */	
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{

		if ($this->isPublished())
		{
    		if ($this->getArchivevisibility())
    		{
    			$nodeAttributes['newsStatus'] = 'archive';
    			$nodeAttributes['specialvisibility'] = f_Locale::translate('&modules.news.document.news.ArchivePage;');
    		}
    		else if ($this->getHomepagevisibility() == true)
    		{
    			$nodeAttributes['newsStatus'] = 'homepage';
    			$nodeAttributes['specialvisibility'] = f_Locale::translate('&modules.news.document.news.HomePage;');
    		} 
		}
		
		$user = users_UserService::getInstance()->getUserByLogin($this->getAuthor());
		if($user !== null)
		{
			$nodeAttributes['author'] = $user->getFullname();
		}
		
		$nodeAttributes['startpub'] = date_DateFormat::format($this->getUIStartpublicationdate(), 'D d M Y H:i', RequestContext::getInstance()->getUILang()); 
		$nodeAttributes['endpub'] = date_DateFormat::format($this->getUIEndpublicationdate(), 'D d M Y H:i', RequestContext::getInstance()->getUILang()); 
	}
	
	/**
	 * utilisé pour le tri back-office
	 * @return String
	 */
	public function getStartpub()
	{
		if ($this->getPersistentModel()->useCorrection() && $this->getCorrectionid())
		{
			return DocumentHelper::getDocumentInstance($this->getCorrectionid())->getStartpublicationdate();
		}
		return $this->getStartpublicationdate();
	}
	
	/**
	 * utilisé pour le tri back-office
	 * @return String
	 */
	public function getEndpub()
	{
		if ($this->getPersistentModel()->useCorrection() && $this->getCorrectionid())
		{
			return DocumentHelper::getDocumentInstance($this->getCorrectionid())->getEndpublicationdate();
		}
		return $this->getEndpublicationdate();
	}
		
	/**
	 * @return String
	 */
	public function getRSSLabel()
	{
		return $this->getLabel();
	}
	
	/**
	 * @return String
	 */
	public function getRSSDescription()
	{
		return $this->getSummaryAsHtml();
	}
	
	/**
	 * @return String
	 */
	public function getRSSGuid()
	{
		return news_NewsService::getInstance()->getUrl($this);
	}
	
	/**
	 * @return String
	 */
	public function getRSSDate()
	{
		return $this->getDate();
	}	
}