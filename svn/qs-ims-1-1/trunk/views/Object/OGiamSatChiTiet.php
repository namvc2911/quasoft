<?php
class Qss_View_Object_OGiamSatChiTiet extends Qss_View_Abstract
{

    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
        $model              = new Qss_Model_Maintenance_Equip_Monitor();
        $user               = Qss_Register::get('userinfo');
        $this->html->deptid = $user->user_dept_id;
        $this->html->data   = $model->getMonitorByDetailPlan($ifid);
    }
}
?>