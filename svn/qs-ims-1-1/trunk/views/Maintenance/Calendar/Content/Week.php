<?php
class Qss_View_Maintenance_Calendar_Content_Week extends Qss_View_Abstract
{

	public function __doExecute ($user,$w,$m,$y,$workcenter,$responseid)
	{
        $steps  = Qss_Lib_System::getStepsByForm('M759');
        $aSteps = array();
        foreach ($steps as $step)
        {
            $aSteps[(int)$step->StepNo] = $step;
        }

		if($w == 53 && $m == 1)
		{
			$y--;
		}
		$model = new Qss_Model_Maintenance_Calendar();
		$sonar = new Qss_Model_Calendar_Solar();
		$startd = Qss_Lib_Date::getDateByWeek($w,$y);
		$startdate = $startd->format('Y-m-d');
		$enddate = Qss_Lib_Date::add_date($startd,6)->format('Y-m-d');
		
		$data = $model->getCalendar($user,$startdate,$enddate,$workcenter,$responseid);
		$this->html->prev = $sonar->adjustWeek($w - 1, $m, $y);
		$this->html->next = $sonar->adjustWeek($w + 1, $m, $y);
		$this->html->startdate1 = $startd;
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
		
		
		$arrData = array();//$a[DVTH][ROW]
		$starttime=strtotime($startdate);
		$endtime=strtotime($enddate);
		$matrix = array();
		$arrDVBT = array();
        $arrNguoiThucHien = array();
		
		foreach($data as $item)
		{
			$start=strtotime($item->SDate);
			$end= $item->EDate?strtotime($item->EDate):0;
			$item->Ref_MaDVBT        = $item->Ref_MaDVBT?$item->Ref_MaDVBT:0;
            $item->Ref_NguoiThucHien = $item->Ref_NguoiThucHien?$item->Ref_NguoiThucHien:0;

			if(!isset($arrData[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien]))
			{
				$arrData[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien] = array();
				$arrDVBT[$item->Ref_MaDVBT] = $item->TenDVBT;
                $arrNguoiThucHien[$item->Ref_NguoiThucHien] = $item->NguoiThucHien;
			}
			$mleft=1;
			$mright=7;
			if($start > $starttime)
			{
				$mleft 	=	date('w',$start);
				$mleft 	=	$mleft?$mleft:7;
			}
			if($end < $endtime)
			{
				$mright=date('w',$end);
				$mright=$mright?$mright:7;
			}
			$item->mleft = $mleft;
			$item->mright = $mright;
			$item->left = false;
			$item->right = false;
			if($start < $starttime)
			{
				$item->left = true;
			}
			if($end > $endtime)
			{
				$item->right = true;
			}
			//kiểm tra xem dòng hiện tại có trống các ô của event này ko, nếu ko thì cộng thêm 1 dòng
			$row = 1;
			while(true)
			{
				$avail = true;
				for ($i = $mleft; $i <= $mright; $i++)
				{
					if(@$matrix[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien][$row][$i])
					{
						$avail = false;
						break;
					}
				}
				if(!$avail)
				{
					$row++;
				}
				else 
				{
					break;
				}
			}
			for ($i = $mleft; $i <= $mright; $i++)
			{
				$matrix[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien][$row][$i] = true;
			}
			if(!isset($arrData[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien][$row]))
			{
				$arrData[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien][$row] = array();
			}
			$arrData[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien][$row][] = $item;
		}

        //echo '<pre>'; print_r($arrData); die;

		$this->html->data = $arrData;
		$this->html->donvibaotri = $arrDVBT;
        $this->html->nguoithuchien = $arrNguoiThucHien;
        $this->html->steps = $aSteps;
	}
}
?>