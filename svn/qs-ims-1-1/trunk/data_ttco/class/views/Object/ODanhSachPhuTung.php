<?php
class Qss_View_Object_ODanhSachPhuTung extends Qss_View_Abstract
{

    private $_user;

    public function __doExecute ($ifid,$reloadjs = '')
    {
        $this->_user = Qss_Register::get('userinfo');
        $model = new Qss_Model_Maintenance_Equipment();
        $this->html->data = $model->getCauTrucPhuTung($ifid);
        $this->html->deptid = $this->_user->user_dept_id;
    }
}
?>