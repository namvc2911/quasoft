<?php
/**
 * Class Static_M158Controller
 * Kết quả tiêu thụ điện năng hàng tháng
 */
class Static_M229Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri();
    }

    public function indexAction()
    {
        
    }

    public function showAction()
    {
        $pstart      = $this->params->requests->getParam('start', '');
        $pend        = $this->params->requests->getParam('end', date('d-m-Y'));
        $project    = $this->params->requests->getParam('project', 0);
        $m220      = new Qss_Model_M220_Main();
        $m221      = new Qss_Model_M221_Main();
        //$this->html->report = $model->getTransaction($employee, $asset, $nhaMay, $boPhan);
        $start     = Qss_Lib_Date::displaytomysql($pstart);
        $end       = Qss_Lib_Date::displaytomysql($pend);

        $this->html->tongthu  = $m221->getPaymentIn($start, $end,$project);
        $this->html->tongchi  = $m220->getPaymentOut($start, $end,$project);
        $this->html->thuduan   = $m221->getPaymentInByProject($start, $end,$project);
        $this->html->chiduan   = $m220->getPaymentOutByProject($start, $end,$project);
        $this->html->chitrungtam   = $m220->getPaymentOutByCostCenter($start, $end,$project);
        $this->html->start   = $pstart;
        $this->html->end     = $pend;
    }
}