<?php
/**
 * @date 8 févr. 08 15:02:29
 * @author franck.stauffer
 * @package modules.news
 */
class news_BlockNewsAction extends website_DetailBlockAction
{
	/**
	 * @return array<String, String> couples of (parameter name / value) that are used by the block
	 */
	public function getCacheKeyParameters($request)
	{
		return array('cmpref' => $request->getParameter("cmpref"), 'context->lang' => $this->getLang(), 'pageId' => $this->getPage()->getId());
	}

	/**
	 * @return array<String>
	 */
	public function getCacheDependencies()
	{
		return array('modules_news/news',
					 'module_media/media',
					 'modules_website/website',
					 'modules_website/page',
					 'modules_website/topic',
					 'modules_news/preferences',
					 'tags/functional_news_news-detail');
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
		$request->setAttribute('compact', !$request->hasParameter('fromNewsList'));
		$request->setAttribute('linktoallnews', $this->getConfiguration()->getLinktoallnews());
		$request->setAttribute('linktonewsfromsametopic', $this->getConfiguration()->getLinktonewsfromsametopic());
		$request->setAttribute('linksposition', $this->getConfiguration()->getLinksposition());
		return parent::execute($request, $response);
	}
	
	/**
	 * @return array<String, String>
	 */
	function getMetas()
	{
		$news = $this->getDocumentParameter();
		if ($news instanceof news_persistentdocument_news)
		{
			$uidate = date_Calendar::getInstance($news->getUIDate());
			$format = f_Locale::translate('&framework.date.date.smart-full-short;');
			$date = date_DateFormat::format($uidate, $format);
			
			$shortformat = f_Locale::translate('&framework.date.date.default-date-format;');
			$shortdate = date_DateFormat::format($uidate, $shortformat);
			
			$datetimeformat = f_Locale::translate('&framework.date.date.default-datetime-format;');
			$datetime = date_DateFormat::format($uidate, $datetimeformat);
			
			return array("title" => $news->getLabel(), 
				"resume" => f_util_StringUtils::htmlToText($news->getSummary()),
			 	"date" => $date, "shortdate" => $shortdate, "datetime" => $datetime);
		}
		return array();
	}
}
