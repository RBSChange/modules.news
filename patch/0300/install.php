<?php
/**
 * news_patch_0300
 * @package modules.news
 */
class news_patch_0300 extends patch_BasePatch
{
//  by default, isCodePatch() returns false.
//  decomment the following if your patch modify code instead of the database structure or content.
    /**
     * Returns true if the patch modify code that is versionned.
     * If your patch modify code that is versionned AND database structure or content,
     * you must split it into two different patches.
     * @return Boolean true if the patch modify code that is versionned.
     */
//	public function isCodePatch()
//	{
//		return true;
//	}
 
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		parent::execute();
		
		$this->executeSQLQuery("ALTER TABLE `m_news_doc_news` ADD `document_s18s` MEDIUMTEXT NULL ;");
		
		$stmt = $this->executeSQLSelect("select * from m_news_doc_news");
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			try 
			{
				$news = DocumentHelper::getDocumentInstance(intval($row['document_id']));
				$frontpageduration = null;
				if ($row['publicationyear'] !== null)
				{
					$frontpageduration = $row['publicationyear'] . 'y';
				}
				if ($row['publicationmonth'] !== null)
				{
					$frontpageduration = $row['publicationmonth'] . 'm';
				}
				if ($row['publicationweek'] !== null)
				{
					$frontpageduration = $row['publicationweek'] . 'w';
				}
				$news->setFrontpageduration($frontpageduration);
				$archiveduration = null;
				if ($row['archiveyear'] !== null)
				{
					$archiveduration = $row['archiveyear'] . 'y';
				}
				if ($row['archivemonth'] !== null)
				{
					$archiveduration = $row['archivemonth'] . 'm';
				}
				if ($row['archiveweek'] !== null)
				{
					$archiveduration = $row['archiveweek'] . 'w';
				}
				$news->setArchiveduration($archiveduration);
				if ($news->getLinkedpage() != null)
				{
					$news->setUselinkedpage("true");
				}
				$news->save();
			}
			catch (Exception $e)
			{
				// 
			}
		}
		
		// Implement your patch here.
	}

	/**
	 * Returns the name of the module the patch belongs to.
	 *
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'news';
	}

	/**
	 * Returns the number of the current patch.
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0300';
	}
}