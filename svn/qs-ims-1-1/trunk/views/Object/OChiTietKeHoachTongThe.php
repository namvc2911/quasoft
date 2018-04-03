<?php
class Qss_View_Object_OChiTietKeHoachTongThe extends Qss_View_Abstract
{

    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
        $mPlan = new Qss_Model_Maintenance_GeneralPlans();
        $ifid  = $form->i_IFID;
        $user  = Qss_Register::get('userinfo');
        $obj   = Qss_Lib_System::getFieldsByObject('M837', 'OKeHoachBaoTri');

        $this->html->generalPlan = $mPlan->getGeneralPlanByIFID($ifid);
        $this->html->detailPlans = $mPlan->getDetailPlanByGeneralIFID($ifid);
        $this->html->ifid        = $ifid;
        $this->html->deptid      = $user->user_dept_id;
        $this->html->fields      = $obj;

    }
}
?>