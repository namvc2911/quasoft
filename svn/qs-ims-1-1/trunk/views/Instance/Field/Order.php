<?php
class Qss_View_Instance_Field_Order extends Qss_View_Abstract
{
	public function __doExecute ($object, $FieldCode)
	{
		$this->html->field = $object->getFieldByCode($FieldCode);
		$this->html->up = $object->getFieldByCode($FieldCode)->getUpper();
	}
}
?>