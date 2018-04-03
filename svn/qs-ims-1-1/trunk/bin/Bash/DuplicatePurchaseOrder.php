<?php
/**
 * @author Thinh Tuan
 * @date 20/10/2014
 * @module Nhan doi thiet bi
 */
class Qss_Bin_Bash_DuplicatePurchaseOrder extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $modelPurchase  = new Qss_Model_Purchase_Order();
        $modelDuplicate = new Qss_Model_Duplicate();
        $custom         = array();
        $user           = Qss_Register::get('userinfo');

        $custom['ODonMuaHang']['SoDonHang'] = $modelPurchase->getDocNo();

        $modelDuplicate->init($this->_params->IFID_M401, 'M401', array(), $custom);
        $modelDuplicate->duplicate();

        if($modelDuplicate->getError())
        {
            $this->setMessage('Có '.$modelDuplicate->getError().' dòng lỗi!');
            $this->setError();
        }
        else
        {
             Qss_Service_Abstract::$_redirect = "/user/form/edit?ifid={$modelDuplicate->getNewIFID()}&deptid={$user->user_dept_id}";
        }
    }
}