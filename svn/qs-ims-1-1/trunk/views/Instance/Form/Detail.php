<?php
/**
 * Create filter form
 *
 * @author HuyBD
 *
 */
class Qss_View_Instance_Form_Detail extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_Form $form, Qss_Model_UserInfo $user)
	{
		$objects = $form->o_fGetMainObjects();
		//$object instanceof Qss_Model_Object;
		foreach ($objects as $object)
		{
			$object->initData($form->i_IFID, $form->i_DepartmentID, $object->i_IOID);
		}
		$this->html->objects = $objects;
		$keys = array_keys($objects);
		$this->html->object = $objects[$keys[0]];
		if ( !is_dir(QSS_DATA_DIR . '/views/detail/') )
		{
			mkdir(QSS_DATA_DIR . '/views/detail/');
		}
		$sz_FileName = QSS_DATA_DIR . '/views/detail/' . $form->FormCode . '.phtml';
		$sz_FileName = Qss_Lib_Template::sz_fGenerateFormDetail($form, $sz_FileName);
		if ( file_exists($sz_FileName) )
		{
			$this->html->setHtml($sz_FileName);
		}
	}
}

?>