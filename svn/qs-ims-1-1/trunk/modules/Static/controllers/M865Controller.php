<?php

class Static_M865Controller extends Qss_Lib_Controller {

    public function init() {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction() {

    }

    public function showAction() {

        $mM759      = new Qss_Model_M759_Workorder();
        $mCommon    = new Qss_Model_Extra_Extra();
        $employee   = $this->params->requests->getParam('employee', 0);
        $workcenter = $this->params->requests->getParam('workcenter', 0);
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $mStart     = Qss_Lib_Date::displaytomysql($start);
        $mEnd       = Qss_Lib_Date::displaytomysql($end);

        $this->html->arrCountByStatus = $mM759->countWorkOrdersByStatus($mStart, $mEnd, $workcenter, $employee);
        $this->html->objEmp           = $mCommon->getTableFetchOne('ODanhSachNhanVien', array('IOID'=>$employee));
        $this->html->objWc            = $mCommon->getTableFetchOne('ODonViSanXuat', array('IOID'=>$workcenter));
        $this->html->start            = $start;
        $this->html->end              = $end;
    }
}