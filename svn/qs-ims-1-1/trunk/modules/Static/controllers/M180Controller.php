<?php
/**
 * Class Static_M158Controller
 * Kết quả tiêu thụ điện năng hàng tháng
 */
class Static_M180Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $month     = $this->params->requests->getParam('month', 0);
        $year      = $this->params->requests->getParam('year', 0);
        $model     = new Qss_Model_Maintenance_Electric();

        $this->html->month  = @(int)$month;
        $this->html->year   = @(int)$year;
        $this->html->report = $model->getSoSanhDienNang($month, $year);
    }



}