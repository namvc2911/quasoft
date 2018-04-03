<?php
class Qss_View_Object_OGiamSatBaoTri extends Qss_View_Abstract
{

    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
        $model              = new Qss_Model_Maintenance_Equip_Monitor();
        $form               = new Qss_Model_Form();
        $user               = Qss_Register::get('userinfo');
        $form->initData ($ifid, $user->user_dept_id);

        $this->html->ifid   = $ifid;
        $this->html->deptid = $user->user_dept_id;
        $this->html->status = $form->i_Status;
        $this->html->safe   = ($form->i_Status == 1 || $form->i_Status == 4 || $form->i_Status == 5)?false:true;
        $this->html->data   = $model->getMonitorByWorkorder($ifid);

    }
}
?>