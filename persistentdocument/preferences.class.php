<?php
/**
 * news_persistentdocument_preferences
 * @package news
 */
class news_persistentdocument_preferences extends news_persistentdocument_preferencesbase 
{
	/**
	 * @see f_persistentdocument_PersistentDocumentImpl::getLabel()
	 *
	 * @return String
	 */
	public function getLabel()
	{
		return f_Locale::translateUI(parent::getLabel());
	}
}