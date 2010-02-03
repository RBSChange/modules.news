<?php
class news_NewsScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return news_persistentdocument_news
	 */
	protected function initPersistentDocument()
	{
		return news_NewsService::getInstance()->getNewDocumentInstance();
	}
	
	public function endProcess()
	{
		$document = $this->getPersistentDocument();
		if ($document->getPublicationstatus() == 'DRAFT')
		{
			$document->getDocumentService()->activate($document->getId());
		}
	}
	
	/**
	 * @return array
	 */
	protected function getDocumentProperties()
	{
		$properties = parent::getDocumentProperties();
		
		// Deprecated use the generic listvisual-refid attribute.
		if (isset($properties['listvisualid']))
		{
			$media = $this->script->getElementById($properties['listvisualid']);
			if ($media !== null)
			{
				$properties['listvisual'] = $media->getPersistentDocument();
			}
			unset($properties['listvisualid']);
		}
		
		// Deprecated use the generic detailvisual-refid attribute.
		if (isset($properties['detailvisualid']))
		{
			$media = $this->script->getElementById($properties['detailvisualid']);
			if ($media !== null)
			{
				$properties['detailvisual'] = $media->getPersistentDocument();
			}
			unset($properties['detailvisualid']);
		}
		
		// Deprecated use the generic accessmap-refid attribute.
		if (isset($properties['accessmapid']))
		{
			$media = $this->script->getElementById($properties['accessmapid']);
			if ($media !== null)
			{
				$properties['accessmap'] = $media->getPersistentDocument();
			}
			unset($properties['accessmapid']);
		}
		
		// Deprecated use the generic attachment-refid attribute.
		if (isset($properties['attachmentid']))
		{
			$media = $this->script->getElementById($properties['attachmentid']);
			if ($media !== null)
			{
				$properties['attachment'] = $media->getPersistentDocument();
			}
			unset($properties['attachmentid']);
		}
		
		return $properties;
	}
}