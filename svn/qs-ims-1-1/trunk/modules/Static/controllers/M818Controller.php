<?php
/**
 * @uthor: Tuyền
 * Class Static_M818Controller
 * Báo cáo tiêu thụ nước từng tháng
 */
class Static_M818Controller extends Qss_Lib_Controller
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
    		$data = array();
    		$dataSum = array();
    		$dataSumByGroup = array();
    		$detail = $model->getInfomationMetter($monthStart, $monthEnd, $year);
    		foreach ($detail as  $item)
    		{
    			$DonVi = $item->Ref_DonVi;
    			$IOID = $item->IOID;
    			$Thang = $item->Thang;
    			$data[$DonVi][$IOID][$Thang] = $item;
    			if(!isset($dataSumByGroup[$DonVi][$Thang]['SoLuong']))
    			{
    				$dataSumByGroup[$DonVi][$Thang]['SoLuong'] = 0;
    			}
    			if(!isset($dataSumByGroup[$DonVi][$Thang]['ThanhTien']))
    			{
    				$dataSumByGroup[$DonVi][$Thang]['ThanhTien'] = 0;
    			}
    			$dataSumByGroup[$DonVi][$Thang]['SoLuong'] 	+= $item->SoLuong;
    			$dataSumByGroup[$DonVi][$Thang]['ThanhTien'] += $item->ThanhTien * 1.25;
    			if(!isset($dataSum[$DonVi]['SoLuong']))
    			{
    				$dataSum[$DonVi]['SoLuong'] = 0;
    			}
    			$dataSum[$DonVi]['SoLuong'] += $item->SoLuong;
    			if(!isset($dataSum[$DonVi]['ThanhTien']))
    			{
    				$dataSum[$DonVi]['ThanhTien'] = 0;
    			}
    			$dataSum[$DonVi]['ThanhTien'] += $item->ThanhTien * 1.25;
    		}
    		//echo '<pre>';print_r($dataSum);die;
    		$this->html->data = $data;
    		$this->html->dataSumByGroup = $dataSumByGroup;
    		$this->html->dataSum = $dataSum;
    		$this->html->list = $model->getMeters();
    		//$this->html->dataGroup =$model->getDataByRangeAndGroup($monthStart, $monthEnd, $year);
    		//echo "<pre>";    		print_r ($data);    		die;
    	}
    }
}