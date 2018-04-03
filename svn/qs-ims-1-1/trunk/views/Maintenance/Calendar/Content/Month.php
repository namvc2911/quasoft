<?php
class Qss_View_Maintenance_Calendar_Content_Month extends Qss_View_Abstract
{

	public function __doExecute ($user,$m,$y,$workcenter,$responseid)
	{
        $steps  = Qss_Lib_System::getStepsByForm('M759');
        $aSteps = array();
        foreach ($steps as $step)
        {
            $aSteps[(int)$step->StepNo] = $step;
        }


        $model = new Qss_Model_Maintenance_Calendar();
		$sonar = new Qss_Model_Calendar_Solar();
		$a = $sonar->adjustDate($m, $y);
		$month = $a[0];
		$year = $a[1];
		$daysInMonth = $sonar->getDaysInMonth($month, $year);
		$startdate = sprintf('%3$s-%2$s-%1$s','01',str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$enddate = sprintf('%3$s-%2$s-%1$s',$daysInMonth,str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$data = $model->getCalendar($user,$startdate,$enddate,$workcenter,$responseid);
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

		foreach($data as $item)
		{
			$start    = strtotime($item->SDate);
			$end      = strtotime($item->EDate);
			$mleft    = 1;
			$mright   = $this->daysInMonth;
			$mright   = (int)date('d',$endtime);
			
			$item->Ref_MaDVBT = $item->Ref_MaDVBT?$item->Ref_MaDVBT:0;
            $item->Ref_NguoiThucHien = $item->Ref_NguoiThucHien?$item->Ref_NguoiThucHien:0;


			if(!isset($arrData[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien]))
			{
				$arrData[$item->Ref_MaDVBT][$item->Ref_NguoiThucHien] = array();
				$arrDVBT[$item->Ref_MaDVBT] = $item->TenDVBT;
                $arrNguoiThucHien[$item->Ref_NguoiThucHien] = $item->NguoiThucHien;
			}
			if($start > $starttime)
			{
				$mleft = (int)date('d',$start);
			}

			if($end < $endtime)
			{
				$mright = (int)date('d',$end);
			}

			// Set width and margin of box
			// Thiết lập độ rộng, khoảng cách của một khối
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

		$this->html->data = $arrData;
		$this->html->donvibaotri = $arrDVBT;
        $this->html->nguoithuchien = $arrNguoiThucHien;
        $this->html->steps = $aSteps;
	}
}
?>