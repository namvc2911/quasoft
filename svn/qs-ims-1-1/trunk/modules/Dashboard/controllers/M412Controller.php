<?php

class Dashboard_M412Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function approveAction()
    {
        $mPurRequest = new Qss_Model_Purchase_Request();

        $formM412  = new Qss_Model_Form();
        $formM412->init('M412', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM412 = $this->getFormRights($formM412);

        $this->html->countReq         = $mPurRequest->countRequestsNeedApprove();
        $this->html->rightM412        = $this->checkApproveRights($formM412, 4);
    }
}