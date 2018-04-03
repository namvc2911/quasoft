<?php
class Qss_View_Object_ODonViApDungQuyDinh extends Qss_View_Abstract
{

    private $_user;

    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
        $this->_user = Qss_Register::get('userinfo');
        $model = new Qss_Model_Extra_News();
        $this->html->data = $model->getDepartmentByPolicyIFID($ifid);
        $this->html->deptid = $this->_user->user_dept_id;
    }
}
?>