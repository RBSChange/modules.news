<?php
/**
 * @date 8 févr. 08 15:03:15
 * @author franck.stauffer
 * @package modules.news
 */

class news_BlockNewsListAction extends website_BlockAction
{

	private $maxArchiveVisibility = 6;

	/**
	 * @return array<String, String> couples of (parameter name / value) that are used by the block
	 */
	public function getCacheKeyParameters($request)
	{
		$result = array();
		$result['config'] = $this->getConfigurationParameters();
		$result['page'] = $this->findParameterValue("page");
		$result['year'] = $this->findParameterValue("year");
		$result['month'] = $this->findParameterValue("month");
		$result['lang'] = $this->getLang();
		$result['sourceid'] = $this->getContainerId();
		return $result;
	}

	/**
	 * @return array<String>
	 */
	public function getCacheDependencies()
	{
		return array('modules_news/news',
					 'modules_media/media',
					 'modules_website/website',
					 'modules_website/page',
					 'modules_website/topic',
					 'modules_news/preferences',
					 'tags/functional_news_news-detail',
					 'tags/functional_news_news-list',
					 'tags/functional_news_news-archive',
					 'tags/contextual_website_website_modules_news_page-list',
					 'tags/contextual_website_website_modules_news_page-archive');
	}


	/**
	 * @return int
	 */
	protected function getContainerId()
	{
		$containerId = null;
		$currentWebsite = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
		if (null !== $currentWebsite)
		{
			$containerId = $currentWebsite->getId();
		}
		return $containerId;
	}

	/**
	 * @see website_BlockAction::execute()
	 *
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	function execute($request, $response)
	{
		$containerId = $this->getContainerId();
		$page = $request->getParameter("page", 1);
		$preferenceDocument = ModuleService::getInstance()->getPreferencesDocument('news');

		if ($preferenceDocument === null)
		{
			if ($this->isInBackoffice())
			{
				$request->setAttribute('errorMsg', f_Locale::translate('&modules.news.frontoffice.Error-no-preferences;'));
			}
			return $this->getTemplate('Error');
		}

		$itemPerPage =  $preferenceDocument->getItemperpage();
		if (is_null($itemPerPage))
		{
			$itemPerPage = 10;
		}
		$alternator = new news_VisualAlternator($preferenceDocument->getListvisualposition());
		// Block's display parameters
		$configuration = $this->getConfiguration();

		$limit = $configuration->getItemcount();
		$visual = $configuration->getVisual();
		$includechildren = $configuration->getIncludechildren();
		$timeordered = f_util_Convert::toBoolean($configuration->getTimeordered());		
		$displayType = $configuration->getType();
		$displayRssLink = $configuration->getDisplayrsslink();


		// Get the 'news' source (either dropped folder, parent topic, parent website)...
		if ($displayType == news_NewsService::ARCHIVE_DISPLAY)
		{
			$newsList = $this->getArchiveList($containerId, $request, $includechildren, $limit);
		}
		else
		{
			$newsList = news_NewsService::getInstance()->getNewsListByParentId($containerId,  $displayType, $includechildren, $timeordered, $limit);
		}

		$request->setAttribute('isEmpty', count($newsList) == 0);
		$request->setAttribute('paginator', new paginator_Paginator('news', $page, $newsList, $itemPerPage));
		$request->setAttribute('alternator', $alternator);
		$request->setAttribute('displayVisual', $visual);
		$request->setAttribute('containerId', $containerId);
		if ($containerId !== null)
		{
			$request->setAttribute('container', DocumentHelper::getDocumentInstance($containerId));
		}
		$request->setAttribute('displayrsslink', $displayRssLink);
		$request->setAttribute('siblingId', $this->getSiblingId($request));
		$request->setAttribute('hasFeed', $displayType == 'classic');
		$request->setAttribute('hasList', $displayType != 'classic');
		$request->setAttribute('hasArchive', $displayType == 'classic' );

		// RSS Feed in metas
		if ($displayType == "classic")
		{
			$feedUrl = LinkHelper::getActionUrl('news', 'ViewFeed', array('cmpref' =>  $this->getSiblingId($request), 'parentref' => $containerId)) ; // module news; action ViewFeed; cmpref siblingId; parentref containerId
			$this->getPage()->addRssFeed("Change news feed", $feedUrl);
		}
		// For compatibility, the fromNewsList parameter may be added.
		$request->setAttribute('removeFromNewsListParameter', Framework::getConfiguration('modules/news/addFromNewsListParameter', false) != 'true');
		if ($displayType == 'homepage')
		{
			$templateName =  'Homepage-' . ucfirst($preferenceDocument->getHomepagetemplate());
		}
		else
		{
			$templateName = ucfirst($displayType);
		}
		return $this->getTemplateByFullName('modules_news', 'News-Block-News-List-' . $templateName);
	}

	protected function getSiblingId($request)
	{
		return $this->getPage()->getId();
	}

	/**
	 * @return Array<news_persistentdocument_news>
	 */
	private function getArchiveList($source, $request, $includechildren, $limit)
	{
		$absoluteMax = date_Calendar::now();
		$absoluteMin = date_Calendar::now()->sub(date_Calendar::YEAR, $this->maxArchiveVisibility);
		$globalNewsList = news_NewsService::getInstance()->getFormattedArchiveNewsListByParentId($source, $absoluteMin, $absoluteMax, $includechildren, $limit);
		if (f_util_ArrayUtils::isEmpty($globalNewsList))
		{
			return array();
		}
		if ($request->hasParameter('year') && $request->hasParameter('month'))
		{
			$year = intval($request->getParameter('year'));
			$month = intval($request->getParameter('month'));
		}
		else
		{
			$year = $this->getFirstAvailableArchiveYear($globalNewsList);
			$month = $this->getFirstAvailableArchiveMonth($globalNewsList[$year]);
		}
		if (f_util_ArrayUtils::isNotEmpty($globalNewsList[$year][$month]))
		{
			$newsList = $globalNewsList[$year][$month];
		}
		else
		{
			$newsList = array();
		}
		$request->setAttribute('headerDate', date_DateFormat::format(date_Calendar::getInstance("$year-$month-01"), 'F Y'));
		$request->setAttribute('archivePaginator', new news_ArchivePaginator($globalNewsList, $year, $month, $this->maxArchiveVisibility));
		return $newsList;
	}

	/**
	 * @param Array<String, Array<String, Array<news_persistentdocument_news>>>
	 * @return Integer
	 */
	private function getFirstAvailableArchiveYear($globalNewsList)
	{
		$year = date_Calendar::now()->getYear();
		$absoluteMin = date_Calendar::now()->sub(date_Calendar::YEAR, $this->maxArchiveVisibility)->getYear();
		while (f_util_ArrayUtils::isEmpty($globalNewsList[$year]) && $year >= $absoluteMin)
		{
			$year--;
		}
		return $year;
	}

	/**
	 * @param Array<String, Array<news_persistentdocument_news>>
	 * @return Integer
	 */
	private function getFirstAvailableArchiveMonth($yearNewsList)
	{
		$month = 12;
		while (f_util_ArrayUtils::isEmpty($yearNewsList[$month]) && $month > 0)
		{
			$month--;
		}
		return $month;
	}
}
