<?php
/**
 * @uthor: Tuyền
 * Class Static_M817Controller
 * Báo cáo tiêu thụ nước
 */
class Static_M817Controller extends Qss_Lib_Controller
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
    /*Viết trước 22-01-2016*/
    public function showAction(){
    	$monthStart = $this->params->requests->getParam('monthStart');
    	$monthEnd = $this->params->requests->getParam('monthEnd');
    	$year = $this->params->requests->getParam('year');
    	if($monthStart || $monthEnd || $year)
    	{
    		$model = new Qss_Model_Maintenance_Water();
    		$this->html->monthStart = $monthStart;
    		$this->html->monthEnd = $monthEnd;
    		$this->html->year = $year;
    		//$this->html->metter = $model->getDataMetter($monthStart, $monthEnd, $year);//lấy dữ liệu theo tháng ở công tơ con;
    		$this->html->sumMetter = $model->getDataSumMetter($monthStart, $monthEnd, $year);//lấy dữ liệu theo tháng ở công tơ tổng;
    	}
    }
}