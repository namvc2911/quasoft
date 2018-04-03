<?php
class Qss_View_Button_M759_Employee_Plan_Workorder extends Qss_View_Abstract
{

	public function __doExecute ($user,$m,$y,$workcenter)
	{
        $model = new Qss_Model_M759_Employee();
		$sonar = new Qss_Model_Calendar_Solar();
		$a = $sonar->adjustDate($m, $y);
		$month = $a[0];
		$year = $a[1];
		$daysInMonth = $sonar->getDaysInMonth($month, $year);
		$startdate = sprintf('%3$s-%2$s-%1$s','01',str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$enddate = sprintf('%3$s-%2$s-%1$s',$daysInMonth,str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$this->html->prev = $sonar->adjustDate($m - 1, $y);
		$this->html->next = $sonar->adjustDate($m + 1, $y);
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
		$this->html->daysInMonth = $daysInMonth;
		$arrData = array();//$a[DVTH][ROW]
		$matrix = array();
		$starttime = strtotime($startdate);
		$endtime   = strtotime($enddate);
		$arrDVBT = array();
        $arrNguoiThucHien = array();
		
        $dvbt = $model->getWorkCenter($startdate, $enddate);
        
        foreach($dvbt as $item)
        {
        	$arrDVBT[$item->IOID][$item->NgayBatDauDuKien] = array('TongSo'=>$item->TongSo,'DaGiao'=>$item->DaGiao);
        }
        $data = $model->getCountWOByEmployee($startdate,$enddate,$workcenter);
		
		foreach($data as $item)
		{
			$arrData[$item->Ref_NguoiThucHien][$item->NgayBatDauDuKien] = $item;
		}

		$this->html->data = $arrData;
		$this->html->donvibaotri = $arrDVBT;
        $this->html->nguoithuchien = $model->getEmployee($workcenter);
	}
}
?>