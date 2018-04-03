<?php
class Qss_View_Object_OThucHienDinhKy extends Qss_View_Abstract
{

	public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
		$model = new Qss_Model_Maintenance_Plan();
		$this->html->data = $model->getPreventiveByWorkOrder($ifid);
	}
}
?>