<?php
/**
 * @uthor: Tuyền
 * Class Static_M822Controller
 * Báo cáo Đăng kiểm (Theo dõi kiểm định đầu máy và thùng gió)
 */
class Static_M822Controller extends Qss_Lib_Controller
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
    	$start = $this->params->requests->getParam('start');
    	$end = $this->params->requests->getParam('end');
    	$IOID  = $this->params->requests->getParam('IOID');
    	if(isset($start) && isset($end ))
    	{
    		$model = new Qss_Model_Maintenance_Water();
    		$this->html->start = $start;
    		$this->html->end = $end;
    		$startDayToMysql = Qss_Lib_Date::displaytomysql($start);//chuyển ngày bắt đầu sang dạng ngày chuẩn của mysql
    		$endDayToMysql = Qss_Lib_Date::displaytomysql($end);//chuyển ngày kết thúc sang dạng ngày chuẩn của mysql
    		$startDate = date_create($startDayToMysql);
    		$this->html->yearStart = $startDate->format('Y');
    		$endDate = date_create($endDayToMysql);
    		$this->html->yearEnd = $endDate->format('Y');
    		/**/
    		$this->html->data = $model->getListToInsurrance($startDayToMysql, $endDayToMysql,$IOID);
    		/*Tính số thùng gió con*/
    		$maxDevice = 1;
    		$detail= $model->getCountMaxDevice($startDayToMysql, $endDayToMysql, $IOID);
    		foreach ($detail as $item){
    			$maxDevice = $item->Max;
    		}
    		$this->html->maxDevice = $maxDevice;
    		
    	}
    }
}