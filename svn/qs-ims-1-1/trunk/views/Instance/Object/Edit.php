<?php
/**
 * Create filter form
 * 
 * @author HuyBD
 *
 */
class Qss_View_Instance_Object_Edit extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_Object $object, Qss_Model_UserInfo $user,$dialog = false)
	{
		/*$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
		if(!class_exists($classname))
		{
			$classname = 'Qss_Lib_Onload';
		}
		$onload = new $classname(null,$object);
		$onload->__doExecute();
		$fields = $object->loadFields();
		foreach ($fields as $key => $f)
		{
			$onload->{$f->FieldCode}();
		}*/
		$object->autoCalculate();//@todo: Để đây đúng ko ta
		$this->html->object = $object;
		if ( !is_dir(QSS_DATA_DIR . '/views/edit/object/') )
		{
			mkdir(QSS_DATA_DIR . '/views/edit/object/');
		}
		$sz_FileName = QSS_DATA_DIR . '/views/edit/object/' . $object->ObjectCode . '.phtml';
		$sz_FileName = Qss_Lib_Template::b_fGenerateObjectEdit($object, $sz_FileName,$dialog);
		if ( file_exists($sz_FileName) )
		{
			$this->html->_path = QSS_ROOT_DIR  . '/views/Instance/Object/Edit.phtml';
			$this->html->setHtml($sz_FileName);
		}
	}
}

?>