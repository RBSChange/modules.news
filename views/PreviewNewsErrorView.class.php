<?php
/**
 * @date Tue May 15 10:57:07 CEST 2007
 * @author intbonjf
 */
class news_PreviewNewsErrorView extends f_view_BaseView
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$this->setMimeContentType('html');
		$this->setTemplateName('PreviewNews-Error', K::HTML);
	}
}