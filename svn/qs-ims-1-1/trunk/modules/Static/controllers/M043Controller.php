<?php

class Static_M043Controller extends Qss_Lib_Controller
{
    public function init() {
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
        $this->headLink($this->params->requests->getBasePath() . '/css/print.css');
    }

    /**
     *
     */
    public function indexAction() {
        $object             = Qss_Lib_System::getFieldsByObject('M077', 'ODangKyNghi');
        $this->html->object = $object;
        $this->html->fields = $object->loadFields();
        $this->html->user   = $this->_user;
        $this->html->deptID = $this->_user->user_dept_id;
    }

    public function dangkynghiFormAction() {
    	$form = new Qss_Model_Form();
    	$form->init('M077');
        $object = $form->o_fGetMainObject();
        $service = new Qss_Lib_Service();
        $service->b_fValidate($object,array());
        $this->html->object = $object;
        $this->html->fields = $object->loadFields();
        $this->html->user   = $this->_user;
        $this->html->deptID = $this->_user->user_dept_id;
        $model              = new Qss_Model_M043_Main();
        $this->html->empl   = $model->getEmployeeByUser($this->_user->user_id);
        $this->html->disabled = $this->html->empl?0:1;
        $this->html->quyNghi  = $model->getNghiCuaNhanVien2(@(int)$this->html->empl->IOID, date('Y'));
        $this->html->mobile   = $this->_user->user_mobile;
    }

    public function dangkynghiDetailAction() {

        $nam                = $this->params->requests->getParam('year', date('Y'));
        $model              = new Qss_Model_M043_Main();
        $empl               = $model->getEmployeeByUser($this->_user->user_id);
        $this->html->data   = $model->getDangKyNghiByUser(@(int)$empl->IOID, $nam);
        $this->html->nam    = $nam;
        $this->html->mobile = $this->_user->user_mobile;

    }

    public function dangkynghiSaveAction() {
    	$params = $this->params->requests->getParams();
		$service = $this->services->Form->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
        /*$params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())  {
            $service = $this->services->Static->M043->CreateLeave($params);
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);*/
    }

    public function dangkynghiCancelAction() {
        $ifids   = $this->params->requests->getParam('ifids', '');
        $deptids = $this->params->requests->getParam('deptids', '');
        $stepno  = 3; // Yeu cau huy
        $comment = '';
        $ifids   = explode(',',$ifids);
        $deptids = explode(',',$deptids);
        $retval  = '';

        foreach ($ifids as $key=>$ifid) {
            $deptid  = $deptids[$key];
            $form    = new Qss_Model_Form();
            $form->initData($ifid, $deptid);
            $service = $this->services->Form->Request($form, $stepno, $this->_user, $comment);

            if($service->isError()) {
                $retval = $service->getMessage();
            }
        }

        if($retval) {
            echo $retval;
        }
        else {
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function dangkylamthemFormAction() {
        $form = new Qss_Model_Form();
    	$form->init('M078');
        $object = $form->o_fGetMainObject();
        $service = new Qss_Lib_Service();
        $service->b_fValidate($object,array());
        $this->html->object = $object;
        $this->html->fields = $object->loadFields();
        $this->html->user   = $this->_user;
        $this->html->deptID = $this->_user->user_dept_id;
        $model              = new Qss_Model_M043_Main();
        $this->html->empl   = $model->getEmployeeByUser($this->_user->user_id);
        $this->html->disabled = $this->html->empl?0:1;
        $this->html->mobile   = $this->_user->user_mobile;
    }

    public function dangkylamthemDetailAction() {
        $nam                = $this->params->requests->getParam('year', date('Y'));
        $model              = new Qss_Model_M043_Main();
        $empl               = $model->getEmployeeByUser($this->_user->user_id);
        $this->html->data   = $model->getDangKyLamThemByUser(@(int)$empl->IOID, $nam);
        $this->html->nam    = $nam;
        $this->html->mobile = $this->_user->user_mobile;
    }

    public function dangkylamthemSaveAction() {
    	$params = $this->params->requests->getParams();
		$service = $this->services->Form->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
        /*$params = $this->params->requests->getParams();
        if ($this->params->requests->isAjax())  {
            $service = $this->services->Static->M043->CreateOT($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
        */
    }

    public function dangkylamthemCancelAction() {
        $ifids   = $this->params->requests->getParam('ifids', '');
        $deptids = $this->params->requests->getParam('deptids', '');
        $stepno  = 3; // Yeu cau huy
        $comment = '';
        $ifids   = explode(',',$ifids);
        $deptids = explode(',',$deptids);
        $retval  = '';

        foreach ($ifids as $key=>$ifid) {
            $deptid  = $deptids[$key];
            $form    = new Qss_Model_Form();
            $form->initData($ifid, $deptid);
            $service = $this->services->Form->Request($form, $stepno, $this->_user, $comment);

            if($service->isError()) {
                $retval = $service->getMessage();
            }
        }

        if($retval) {
            echo $retval;
        }
        else {
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function mytimecardIndexAction() {

    }

    public function mytimecardFormAction() {
        $model                = new Qss_Model_M043_Main();
        $this->html->empl     = $model->getEmployeeByUser($this->_user->user_id);
        $this->html->disabled = $this->html->empl?0:1;
        $this->html->mobile = $this->_user->user_mobile;

    }

    public function mytimecardDetailAction() {
        $model       = new Qss_Model_M043_Main();
        $kyCong      = $this->params->requests->getParam('kycong', 0);
        $strKyCong   = $this->params->requests->getParam('kycong_tag', '');

        // Lọc theo kỳ công mới nhất của nhân viên
        if($kyCong == 0) {
            $empl      = $model->getEmployeeByUser($this->_user->user_id);
            $objKyCong = $model->getKyCongMoiNhatByUser(@(int)$empl->IOID);

            if($objKyCong && $objKyCong->IOID) {
                $kyCong    = $objKyCong->IOID;
                $strKyCong = $objKyCong->MaKyCong .' - '. $objKyCong->TenKyCong;
            }
        }

        $this->html->kyCong    = $kyCong;
        $this->html->strKyCong = $strKyCong;

        $empl                  = $model->getEmployeeByUser($this->_user->user_id);
        $this->html->data      = $model->getCongNgayByUser(@(int)$empl->IOID, $kyCong);
        $this->html->mobile = $this->_user->user_mobile;
    }

    public function mytimecardLeaveAction() {
        $nam               = $this->params->requests->getParam('year', date('Y'));
        $model             = new Qss_Model_M043_Main();
        $empl              = $model->getEmployeeByUser($this->_user->user_id);
        $total             = $model->consumeNghiTheoNhanVien(@(int)$empl->IOID, $nam, true);
        $this->html->data  = $model->consumeNghiTheoNhanVien(@(int)$empl->IOID, $nam);
        $this->html->total = (@$total && @$total[0])?$total[0]:array();
        $this->html->year  = $nam;
    }

    public function mytimecardOtAction() {
        $nam               = $this->params->requests->getParam('year', date('Y'));
        $model             = new Qss_Model_M043_Main();
        $empl              = $model->getEmployeeByUser($this->_user->user_id);
        $total             = $model->consumeLamThemTheoNhanVien(@(int)$empl->IOID, $nam, true);
        $this->html->data  = $model->consumeLamThemTheoNhanVien(@(int)$empl->IOID, $nam);
        $this->html->total = (@$total && @$total[0])?$total[0]:array();
        $this->html->year  = $nam;
    }
}