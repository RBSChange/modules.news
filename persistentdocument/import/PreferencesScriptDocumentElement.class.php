<?php
class news_PreferencesScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return news_persistentdocument_preferences
     */
    protected function initPersistentDocument()
    {
    	$document = ModuleService::getInstance()->getPreferencesDocument('news');
    	return ($document !== null)? $document : news_PreferencesService::getInstance()->getNewDocumentInstance();
    }
}