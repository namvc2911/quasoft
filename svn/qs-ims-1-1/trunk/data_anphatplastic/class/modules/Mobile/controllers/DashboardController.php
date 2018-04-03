<?php
/**
 *
 * @author HuyBD
 *
 */
class Mobile_DashboardController extends Qss_Lib_Mobile_Controller
{

    /**
     *
     * @return unknown_type
     */
    public function init()
    {
        parent::init();

    }

    public function indexAction ()
    {
        $this->headLink($this->params->requests->getBasePath() . '/css/dashboad.css');
        $mWorkorder  = new Qss_Model_Maintenance_Workorder();
        $mInventory  = new Qss_Model_Inventory_Inventory();
        $mPurRequest = new Qss_Model_Purchase_Request();

        $formM759  = new Qss_Model_Form();
        $formM759->init('M759', $this->_user->user_dept_id, $this->_user->user_id);

        $formM506  = new Qss_Model_Form();
        $formM506->init('M506', $this->_user->user_dept_id, $this->_user->user_id);

        $formM412  = new Qss_Model_Form();
        $formM412->init('M412', $this->_user->user_dept_id, $this->_user->user_id);

        $rightM759 = $this->getFormRights($formM759);
        $rightM506 = $this->getFormRights($formM506);
        $rightM412 = $this->getFormRights($formM412);


        $this->html->countWO          = $mWorkorder->countIssueWorkordersByUser($this->_user->user_id);
        $this->html->rightM759        = $rightM759&1 || $rightM759&4;
        $this->html->countOut         = $mInventory->countOutputNeedApprove();
        $this->html->countOutByUser   = $mInventory->countOutputsByUser($this->_user->user_id);
        $this->html->rightApproveM506 = $this->checkApproveRights($formM506, 4);
        $this->html->rightCreateM506  = $rightM506&1 || $rightM506&4;
        $this->html->countReq         = $mPurRequest->countRequestsNeedApprove();
        $this->html->rightM412        = $this->checkApproveRights($formM412, 4);
        $this->html->userinfo         = $this->_user;
        $this->html->mobile           = 1;
    }
}