<?php
class Qss_View_Object_OChiPhiPBT extends Qss_View_Abstract
{

	public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
		$model = new Qss_Model_Maintenance_Workorder();
		$this->html->data = $model->getCostByID($ifid);
	}
}
?>