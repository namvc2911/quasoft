<?php
/**
 * Create filter form
 * 
 * @author HuyBD
 *
 */
class Qss_View_Instance_Form_Popup extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_Form $form, Qss_Model_UserInfo $user)
	{
		$objects = $form->o_fGetMainObjects();
		//$object instanceof Qss_Model_Object;
		$this->html->objects = $objects;
		$keys = array_keys($objects);
		$this->html->object = $objects[$keys[0]];
		if ( !is_dir(QSS_DATA_DIR . '/views/popup/') )
		{
			mkdir(QSS_DATA_DIR . '/views/popup/');
		}
		$sz_FileName = QSS_DATA_DIR . '/views/popup/' . $form->FormCode . '.phtml';
		$sz_FileName = Qss_Lib_Template::b_fGenerateFormEdit($form, $sz_FileName,true);
		if ( file_exists($sz_FileName) )
		{
			$this->html->setHtml($sz_FileName);
		}
	}
 }

?>