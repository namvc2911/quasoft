<?php
class Static_M829Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js'); //lay js
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri(); //lay crurl (giong data) de su dung trong html
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mMaintain      = new Qss_Model_Extra_Maintain();
        $start          = $this->params->requests->getParam('start', '');
        $end            = $this->params->requests->getParam('end', '');
        $location       = $this->params->requests->getParam('location', '');
        $group          = $this->params->requests->getParam('group', '');
        $type           = $this->params->requests->getParam('type', '');
        $equip          = $this->params->requests->getParam('equip', '');

        $report         = $mMaintain->getSoLuongNangLucHoatDongThietBi(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $location
            , $group
            , $type
            , $equip
        );

        $numOfDate      = Qss_Lib_Date::diffTime($start, $end, 'D');


        $this->html->time   = $numOfDate * 24;
        $this->html->month  = date('m', strtotime($start));
        $this->html->year   = date('Y', strtotime($start));
        $this->html->report = $report;

    }
}
