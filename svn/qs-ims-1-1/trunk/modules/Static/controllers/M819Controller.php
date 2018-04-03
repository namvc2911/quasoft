<?php
/**
 * @uthor: Tuyá»�n
 * Class Static_M819Controller
 * BÃ¡o cÃ¡o thanh toÃ¡n tiá»�n nÆ°á»›c theo thÃ¡ng (Chi tiáº¿t)
 */
class Static_M819Controller extends Qss_Lib_Controller
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

    /*public function showAction(){
    	$monthStart = $this->params->requests->getParam('monthStart');
    	$monthEnd = $this->params->requests->getParam('monthEnd');
    	$year = $this->params->requests->getParam('year');
    	if($monthStart || $monthEnd || $year)
    	{
    		$model = new Qss_Model_Maintenance_Water();
    		$this->html->monthStart = $monthStart;
    		$this->html->monthEnd = $monthEnd;
    		$this->html->year = $year;
    		$this->html->data = $model->getDataSumByRange($monthStart, $monthEnd, $year);
    	}
    }*/
    
    /*NgÃ y 22012016: show m819*/
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
    		$dataMember = $model->getDataMember($monthStart, $monthEnd, $year);
    		$list = $model->getListMember();
    		foreach ($dataMember as $item)
    		{
    			$IOID = $item->IOID;
    			$member[$IOID] = $item;
    		}
    		foreach ($list as $item)
    		{
    			if(!$item->Ref_TrucThuoc)
    			{
    				$member[$item->IOID]->ChiSoCongToNuoc = 0;
    				$member[$item->IOID]->TrungBinhDonGia = 0;
    				$member[$item->IOID]->TongTienCongToNuoc = 0;
    				$member[$item->IOID]->SoHoaDon = isset($member[$item->IOID]->SoHoaDon)?$member[$item->IOID]->SoHoaDon:0;
    			}
    			else
    			{
    				//echo '---';print_r($member[$item->Ref_TrucThuoc]->ChiSoCongToNuoc);die;
    				$member[$item->Ref_TrucThuoc]->ChiSoCongToNuoc 		+= $member[$item->IOID]->ChiSoCongToNuoc;
    				$member[$item->Ref_TrucThuoc]->TrungBinhDonGia 		+= $member[$item->IOID]->TrungBinhDonGia;
    				$member[$item->Ref_TrucThuoc]->TongTienCongToNuoc 	+= $member[$item->IOID]->TongTienCongToNuoc;
    			}
    		}
    		$this->html->data = $member;//láº¥y dá»¯ liá»‡u tá»«ng CapDen
    		$this->html->list = $list;//Láº¥y thÃ´ng tin tá»«ng CapDen
    	}
    }
}