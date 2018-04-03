<?php
class Qss_View_Button_M308_Calendar extends Qss_View_Abstract
{

	public function __doExecute ($user,$m,$y,$department)
	{
		$arrWeek = array(0=>'ChuNhat',1=>'ThuHai',2=>'ThuBa',3=>'ThuTu',4=>'ThuNam',5=>'ThuSau',6=>'ThuBay');
        $deptmodel = new Qss_Model_M319_Main();
        $empmodel = new Qss_Model_M316_Main();
        $model = new Qss_Model_M308_Main();
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
        $arrNguoiThucHien = array();
		
        $data = $model->getDataByPeriod($startdate,$enddate,$department);
		//cho vào mảng từ ngày đến ngày
		foreach($data as $item)
		{
			$start = date_create($item->TuNgay);
			$end = date_create($item->DenNgay);
			while ($start <= $end)
			{
				//xem là thứ mấy để lấy đúng giá trị
				$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')] = $item->{$arrWeek[$start->format('w')]};	
				$start = Qss_Lib_Date::add_date($start, 1);
			}
		}
		$this->html->data = $arrData;
		
        $this->html->nguoithuchien = $empmodel->getEmployee($department);
        //check là ngày nghỉ thì cho vào array
        $modelnghi = new Qss_Model_M077_Main();
        $dataNghi = $modelnghi->getDataByPeriod($startdate, $enddate,$department);
        $arrNghi = array();
		foreach($dataNghi as $item)
		{
			$start = date_create($item->NgayBatDau);
			$end = date_create($item->NgayKetThuc);
			while ($start <= $end)
			{
				//xem là thứ mấy để lấy đúng giá trị
				$arrNghi[$item->Ref_MaNhanVien][$start->format('Y-m-d')] = $item->LoaiNgayNghi;	
				$start = Qss_Lib_Date::add_date($start, 1);
			}
		}
		$this->html->nghi = $arrNghi;
        //check là ngày ưu tiên thì cho vào array
        //check là ngày OT thì cho vào array
        //lát hiện ra tổng thể 
	}
}
?>