<?php
/**
 * news_LoadValidNewsDataAction
 * @package modules.news.actions
 */
class news_LoadValidNewsDataAction extends task_LoadDataBaseAction
{
	/**
	 * @param news_persistentdocument_news $document
	 * @return Array
	 */
	protected function getInfoForDocument($document)
	{
		$data = array();
		$data['label'] = $document->getLabel();
		$data['date'] = !is_null($document->getDate()) ? date_DateFormat::format(new date_DateTime($document->getUIDate()), f_Locale::translate('&framework.date.date.localized-user-format;')) : '';
		$data['summary'] = $document->getSummaryAsHtml();
		$data['text'] = $document->getTextAsHtml();
		if ($document->getLinkedpage())
		{
			$data['linkedpage'] = $document->getLinkedpage()->getLabel();
		}
		
		if ($document->getListvisual())
		{
			$url = MediaHelper::getPublicUrl($document->getListvisual());
			$data['listvisual'] = str_replace('&','&amp;', $url);
		}
		if ($document->getDetailvisual())
		{
			$url = MediaHelper::getPublicUrl($document->getDetailvisual());
			$data['detailvisual'] = str_replace('&','&amp;', $url);
		}
		if ($document->getAccessmap())
		{
			$url = MediaHelper::getPublicUrl($document->getAccessmap());
			$data['accessmap'] = str_replace('&','&amp;', $url);
			$data['accessmaplabel'] = $document->getAccessmap()->getLabel();
		}
		if ($document->getAttachment())
		{
			$url = MediaHelper::getPublicUrl($document->getAttachment());
			$data['attachment'] = str_replace('&','&amp;', $url);
			$data['attachmentlabel'] = $document->getAttachment()->getLabel();
		}
			
		$data['place'] = $document->getPlaceAsHtml();
		$data['datetimeinfo'] = $document->getDatetimeinfoAsHtml();
		$data['contact'] = $document->getContactAsHtml();
		
		$data['priority'] = $document->getPriority();
		$data['startpublicationdate'] = !is_null($document->getStartpublicationdate()) ? date_DateFormat::format(new date_DateTime($document->getUIStartpublicationdate()), f_Locale::translate('&framework.date.date.localized-user-format;')) : '';
		$data['endpublicationdate'] = !is_null($document->getEndpublicationdate()) ? date_DateFormat::format(new date_DateTime($document->getUIEndpublicationdate()), f_Locale::translate('&framework.date.date.localized-user-format;')) : '';
		
		$data['endhomepagedate'] = !is_null($document->getEndhomepagedate()) ? date_DateFormat::format(new date_DateTime($document->getUIEndhomepagedate()), f_Locale::translate('&framework.date.date.localized-user-format;')) : '';
		$data['endarchivedate'] = !is_null($document->getEndarchivedate()) ? date_DateFormat::format(new date_DateTime($document->getUIEndarchivedate()), f_Locale::translate('&framework.date.date.localized-user-format;')) : '';
		
		return $data;
	}
}