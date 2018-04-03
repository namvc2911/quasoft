<?php
class Qss_View_Object_OBangNhatTrinhTB extends Qss_View_Abstract
{

	public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
		$model = new Qss_Model_Maintenance_Equip_Operation();
		$this->html->data = $model->getEquipmentIndicators($ifid);
	}
}
?>