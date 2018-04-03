<?php

class Static_M864Controller extends Qss_Lib_Controller {

    public function init() {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction() {

    }

    public function showAction() {
        $mCommon        = new Qss_Model_Extra_Extra();
        $mM765          = new Qss_Model_M765_Statistic();
        $locationIOID   = $this->params->requests->getParam('location', 0);
        $eqGroupIOID    = $this->params->requests->getParam('group', 0);
        $eqTypeIOID     = $this->params->requests->getParam('type', 0);
        $eqIOID         = $this->params->requests->getParam('equipment', 0);
        $nestedSet      = Qss_Lib_System::fieldActive('OPhanLoaiBaoTri', 'TrucThuoc');

        if(Qss_Lib_System::fieldActive('OPhanLoaiBaoTri', 'TrucThuoc')) {
            $this->html->objLoaiBaoTri = $mCommon->getTableFetchAll('OPhanLoaiBaoTri', array(), array('*'), array('lft'));
        }
        else {
            $this->html->objLoaiBaoTri = $mCommon->getTableFetchAll('OPhanLoaiBaoTri', array(), array('*'), array('Loai ASC'));
        }

        $this->html->report        = $mM765->thongKeThongSoThietBi($locationIOID, $eqGroupIOID, $eqTypeIOID, $eqIOID);
    }
}