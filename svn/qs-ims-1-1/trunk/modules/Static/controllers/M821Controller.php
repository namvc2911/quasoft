<?php
/**
 * @uthor: Tuyền
 * Class Static_M821Controller
 * Báo cáo Danh sách đề nghị mua thiết bị vận tải năm 2015
 */
class Static_M821Controller extends Qss_Lib_Controller
{  
	
   public function init()
   {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js'); //lay js
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri();
   }

    public function indexAction()
    {
      
    }
    
    public function showAction()
    {
    	$start = $this->params->requests->getParam('start');
    	$end = $this->params->requests->getParam('end');
    	$IOID  = $this->params->requests->getParam('IOID');
    	if($start && $end )
    	{
    		$model = new Qss_Model_Maintenance_Water();
    		$this->html->start = $start;
    		$this->html->end = $end;
    		$startDayToMysql = Qss_Lib_Date::displaytomysql($start);
    		$endDayToMysql = Qss_Lib_Date::displaytomysql($end);
    		$startDate = date_create($startDayToMysql);
    		$this->html->yearStart = $startDate->format('Y');
    		$endDate = date_create($endDayToMysql);
    		$this->html->yearEnd = $endDate->format('Y');
    		$this->html->data = $model->getListToBuyInsurrance($startDayToMysql, $endDayToMysql,$IOID);
    	}
    }
}