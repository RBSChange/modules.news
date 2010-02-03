<?php
class news_CancelNewsWorkflowaction extends workflow_CancelContentWorkflowaction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	function execute()
	{		
		return parent::execute();
	}
}