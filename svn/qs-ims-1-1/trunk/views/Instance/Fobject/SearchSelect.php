<?php
/**
 * Create filter form
 * 
 * @author HuyBD
 *
 */
class Qss_View_Instance_Form_SearchSelect extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_Form $form,Qss_Model_Object $object, $search = array())
	{
		//$object = $form->o_fGetMainObject();
		//$object instanceof Qss_Model_Object;
		$this->html->object = $object;
		$this->html->filter = $search;
		if ( !is_dir(QSS_DATA_DIR . '/views/search/select/') )
		{
			mkdir(QSS_DATA_DIR . '/views/search/select/');
		}
		$sz_FileName = QSS_DATA_DIR . '/views/search/select/' . $object->FormCode. '.phtml';
		$sz_FileName = Qss_Lib_Template::b_fGenerateSelectSearch($object, $sz_FileName);
		if ( file_exists($sz_FileName) )
		{
			$this->html->setHtml($sz_FileName);
		}
	}
}

?>