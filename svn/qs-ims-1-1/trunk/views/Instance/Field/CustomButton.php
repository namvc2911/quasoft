<?php
class Qss_View_Instance_Field_CustomButton extends Qss_View_Abstract
{
	public function __doExecute (Qss_Model_Object $object,Qss_Model_Field $field)
	{
		$classname = 'Qss_Bin_Calculate_'.$object->ObjectCode.'_'.$field->FieldCode;
		if(class_exists($classname))
		{
			$autocal = new $classname($object);
			$this->html->value = $autocal->__doExecute();
		}
	}
}
?>