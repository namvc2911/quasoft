<?php
class Qss_View_Object_ODacTinhThietBi extends Qss_View_Abstract
{

	public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
		$model = new Qss_Model_Maintenance_Equipment();
		$this->html->loai = $model->getLoaiThietBi($ifid);
		$this->html->data = $model->getDacTinhKyThuat($ifid);
		$this->html->ifid = $ifid;
	}
}
?>