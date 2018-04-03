<?php
/**
 * Create filter form
 * 
 * @author HuyBD
 *
 */
class Qss_View_Instance_Form_Search extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_Form $form, $search = array())
	{
		$object = $form->o_fGetMainObject();
		$object instanceof Qss_Model_Object;
		$this->html->object = $object;
		$this->html->filter = $search;
		if ( !is_dir(QSS_DATA_DIR . '/views/search/') )
		{
			mkdir(QSS_DATA_DIR . '/views/search/');
		}
		$sz_FileName = QSS_DATA_DIR . '/views/search/' . $form->FormCode . '.phtml';
		$sz_FileName = Qss_Lib_Template::b_fGenerateSearch($form, $sz_FileName);
		if ( file_exists($sz_FileName) )
		{
			$this->html->setHtml($sz_FileName);
		}
	}
}

?>