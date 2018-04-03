<?php

class Dashboard_M506Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/form-list.js');
    }

    public function approveAction()
    {
        $mInventory  = new Qss_Model_Inventory_Inventory();

        $formM506  = new Qss_Model_Form();
        $formM506->init('M506', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM506 = $this->getFormRights($formM506);

        $this->html->countOut         = $mInventory->countOutputNeedApprove();
        $this->html->rightApproveM506 = $this->checkApproveRights($formM506, 4);
        $this->html->rightCreateM506  = $rightM506&1 || $rightM506&4;
        $this->html->userinfo         = $this->_user;

    }

    public function myoutputsAction()
    {
        $mInventory  = new Qss_Model_Inventory_Inventory();

        $formM506  = new Qss_Model_Form();
        $formM506->init('M506', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM506 = $this->getFormRights($formM506);

        $this->html->countOutByUser   = $mInventory->countOutputsByUser($this->_user->user_id);
        $this->html->rightApproveM506 = $this->checkApproveRights($formM506, 4);
        $this->html->rightCreateM506  = $rightM506&1 || $rightM506&4;
        $this->html->userinfo         = $this->_user;
    }
}