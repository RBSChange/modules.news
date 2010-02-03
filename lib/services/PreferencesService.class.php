<?php
/**
 * @date Wed, 13 Jun 2007 18:01:47 +0200
 * @author intstaufl
 */
class news_PreferencesService extends f_persistentdocument_DocumentService
{
	/**
	 * @var news_PreferencesService
	 */
	private static $instance;

	/**
	 * @return news_PreferencesService
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
	 * @return news_persistentdocument_preferences
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_news/preferences');
	}

	/**
	 * Create a query based on 'modules_news/preferences' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_news/preferences');
	}

	/**
	 * @param news_persistentdocument_preferences $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$document->setLabel('&modules.news.bo.general.Module-name;');
	}

}