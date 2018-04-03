<?php
/**
 * Create filter form
 * 
 * @author HuyBD
 *
 */
class Qss_View_Instance_Object_Search extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_Form $form, Qss_Model_Object $object, $search = array())
	{
		$this->html->form = $form;
		$this->html->object = $object;
		$this->html->filter = $search;
		if ( !is_dir(QSS_DATA_DIR . '/views/search/object/') )
		{
			mkdir(QSS_DATA_DIR . '/views/search/object/');
		}
		$sz_FileName = QSS_DATA_DIR . '/views/search/object/' . $object->ObjectCode . '.phtml';
		$sz_FileName = Qss_Lib_Template::b_fGenerateObjectSearch($object, $sz_FileName);
		if ( file_exists($sz_FileName) )
		{
			$this->html->setHtml($sz_FileName);
		}
	}
}

?>