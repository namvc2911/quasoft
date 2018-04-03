<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Qss_Lib_Maintenance_Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */
	protected $_params; /* Remove */
	protected $_common; /* Remove */

	public function init()
	{
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_params    = $this->params->requests->getParams();
		$this->_common    = new Qss_Model_Extra_Extra();
		$this->_model     = new Qss_Model_Extra_Maintenance();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}

	
	/**
	 *
	 * @param unknown $resultEqIOIDArr
	 * @param unknown $maintypeStrArr array('SUCO', 'DINHKY')
	 * @return Ambigous <multitype:, number>
	 */
	protected function getNextPrevMaintainOfEquipByMaintainType(
	$resultEqIOIDArr = array()
	, $maintypeStrArr = array()
	, $maintypeIOIDArr = array())
	{
		$workorderModel = new Qss_Model_Maintenance_Workorder();
		$planModel      = new Qss_Model_Maintenance_Plan();
		$plan           = $planModel->getMaintainPlansByMaintainType($resultEqIOIDArr, $maintypeStrArr, $maintypeIOIDArr);
		$workorder      = $workorderModel->getOrderByMaintainType($resultEqIOIDArr, $maintypeStrArr, $maintypeIOIDArr);
		$today          = date_create(date('Y-m-d'));
		$retval         = array();
		$planArr        = array();
		$excludeStatus  = array(1, 2);
		$dueDays        = 15;

		//echo '<pre>'; print_r($plan); die;

		// [Ref-Thietbi][status] + [past] + [next]

		foreach($plan as $item)
		{
			$require      = date('Y-m-d');
			$requireMicro = Qss_Lib_Date::i_fMysql2Time(date('Y-m-d'));
			$next         = '';
			$today        = date('Y-m-d');
			$interval     = $item->LapLai?(int)$item->LapLai:1;
			$start        = $item->NgayBatDau?$item->NgayBatDau:$today;
			$end          = $item->NgayKetThuc?$item->NgayKetThuc:'';

			// Kiem dinh da ket thuc khong lay kiem dinh nay
			if($end && Qss_Lib_Date::compareTwoDate($today, $end) == 1)
			{
				continue;
			}
			else
			{
				$next = '';
				$past = '';

				$item->Thang = $item->Thang?( ((int)$item->Thang > 9) ? (int)$item->Thang : '0'.(int)$item->Thang ):'';
				$item->Ngay  = $item->Ngay?( ((int)$item->Ngay > 9) ? (int)$item->Ngay : '0'.(int)$item->Ngay ):'';

				switch ($item->MaKy)
				{
					case 'D':
						// Tính số ngày từ ngày bắt đầu
						$diffDate = Qss_Lib_Date::divDate($start, $today);
						$sodu     = $diffDate%$interval;
						$next     = ($sodu)?date('Y-m-d',strtotime($today . ' + '.$sodu.' days')):$today;
						//$past     = date('Y-m-d',strtotime($next . ' - '.$interval.' days'));
						break;

					case 'W':
						$diffWeek = Qss_Lib_Date::divDate($start, $today, 'W');
						$sodu     = $diffWeek%$interval;
						$next     = ($sodu)?date('W',strtotime($today . ' + '.$sodu.' weeks')):$today;
						$timetamp = $this->change_date($next, (int)$item->ThuMay);
						$next     = date('Y-m-d', $timetamp);
						$next     = (Qss_Lib_Date::compareTwoDate($next, $today) == -1)? date('Y-m-d',strtotime($next . ' + '.$interval.' weeks')): $next;
						//$past     = date('Y-m-d',strtotime($next . ' - '.$interval.' weeks'));
						break;

					case 'M':
						$diffMonth = Qss_Lib_Date::divDate($start, $today, 'MO');
						$sodu      = $diffMonth%$interval;
						$sodu      = (Qss_Lib_Date::compareTwoDate(date('Y-m-'.$item->Ngay), $today) == -1)? ($sodu + $interval): $sodu;
						$next      = ($sodu)?date('Y-m-'.$item->Ngay,strtotime($today . ' + '.$sodu.' months')):date('Y-m-'.$item->Ngay);
						//$past      = date('Y-m-'.$item->Ngay,strtotime($next . ' - '.$interval.' months'));
						break;

					case 'Y':
						$diffYear  = Qss_Lib_Date::divDate($start, $today, 'Y');
						$sodu      = $diffYear%$interval;
						$sodu      = (Qss_Lib_Date::compareTwoDate(date('Y-'.$item->Thang.'-'.$item->Ngay), $today) == -1)? ($sodu + $interval): $sodu;
						$next      = ($sodu)?date('Y-'.$item->Thang.'-'.$item->Ngay,strtotime($today . ' + '.$sodu.' years')):date('Y-'.$item->Thang.'-'.$item->Ngay);
						//$past      = date('Y-'.$item->Thang.'-'.$item->Ngay,strtotime($next . ' - '.$interval.' years'));
						break;
				}

				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['name']   = $item->LoaiBaoTri;
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['next']   = $next;
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['past']   = '';
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 0; // chua co tinh trang
			}
		}

		// Kiem tra xem da co phieu bao tri cho ke hoach hay chua
		foreach($workorder AS $item)
		{
			/**
			 * + N/A : thiết bị không cần hiệu chuẩn
			 + Còn hạn : còn hiệu lực kiểm định đến > 15 ngày kiểm định kế tiếp theo thời hạn
			 + Hết hạn : Quá hạn kiểm định
			 + Sắp hết hạn : 15 ngày đổ lại tới hạn kiểm định kế tiếo
			 */
			// co phieu bao tri
			if(isset($planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]))
			{
				// Phieu bao tri da hoan thanh hoac dong
				if(!in_array($item->Status, $excludeStatus) )
				{
					$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 1; // dung han
				}
				else // Phieu bao tri dang thuc hien
				{
					$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['past']   = $item->NgaySoSanh;
					$diff = Qss_Lib_Date::divDate($item->NgaySoSanh, $planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['next']);
					if($diff > $dueDays)
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 2; // con han
					}
					elseif($diff < $dueDays && $diff > 0)
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 3; // sap het han
					}
					else
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 4; // qua han
					}
				}
			}
		}
		//echo '<pre>'; print_r($planArr); die;
		return $planArr;
	}



	protected function getCalibration($resultEqIOIDArr)
	{
		$workorderModel = new Qss_Model_Maintenance_Workorder();
		$planModel      = new Qss_Model_Maintenance_Plan();
		$plan           = $planModel->getCalibrationPlan($resultEqIOIDArr);
		$workorder      = $workorderModel->getCalibrationOrder($resultEqIOIDArr);
		$today          = date_create(date('Y-m-d'));
		$retval         = array();
		$planArr        = array();
		$excludeStatus  = array(1, 2);
		$dueDays        = 15;
	  
		//echo '<pre>'; print_r($plan); die;
	  
		// [Ref-Thietbi][status] + [past] + [next]
	  
		foreach($plan as $item)
		{
			$require      = date('Y-m-d');
			$requireMicro = Qss_Lib_Date::i_fMysql2Time(date('Y-m-d'));
			$next         = '';
			$today        = date('Y-m-d');
			$interval     = $item->LapLai?(int)$item->LapLai:1;
			$start        = $item->NgayBatDau?$item->NgayBatDau:$today;
			$end          = $item->NgayKetThuc?$item->NgayKetThuc:'';
			 
			// Kiem dinh da ket thuc khong lay kiem dinh nay
			if($end && Qss_Lib_Date::compareTwoDate($today, $end) == 1)
			{
				continue;
			}
			else
			{
				$next = '';
				$past = '';
				 
				$item->Thang = $item->Thang?( ((int)$item->Thang > 9) ? (int)$item->Thang : '0'.(int)$item->Thang ):'';
				$item->Ngay  = $item->Ngay?( ((int)$item->Ngay > 9) ? (int)$item->Ngay : '0'.(int)$item->Ngay ):'';
				 
				switch ($item->MaKy)
				{
					case 'D':
						// Tính số ngày từ ngày bắt đầu
						$diffDate = Qss_Lib_Date::divDate($start, $today);
						$sodu     = $diffDate%$interval;
						$next     = ($sodu)?date('Y-m-d',strtotime($today . ' + '.$sodu.' days')):$today;
						//$past     = date('Y-m-d',strtotime($next . ' - '.$interval.' days'));
						break;
	      
					case 'W':
						$diffWeek = Qss_Lib_Date::divDate($start, $today, 'W');
						$sodu     = $diffWeek%$interval;
						$next     = ($sodu)?date('W',strtotime($today . ' + '.$sodu.' weeks')):$today;
						$timetamp = $this->change_date($next, (int)$item->ThuMay);
						$next     = date('Y-m-d', $timetamp);
						$next     = (Qss_Lib_Date::compareTwoDate($next, $today) == -1)? date('Y-m-d',strtotime($next . ' + '.$interval.' weeks')): $next;
						//$past     = date('Y-m-d',strtotime($next . ' - '.$interval.' weeks'));
						break;

					case 'M':
						$diffMonth = Qss_Lib_Date::divDate($start, $today, 'MO');
						$sodu      = $diffMonth%$interval;
						$sodu      = (Qss_Lib_Date::compareTwoDate(date('Y-m-'.$item->Ngay), $today) == -1)? ($sodu + $interval): $sodu;
						$next      = ($sodu)?date('Y-m-'.$item->Ngay,strtotime($today . ' + '.$sodu.' months')):date('Y-m-'.$item->Ngay);
						//$past      = date('Y-m-'.$item->Ngay,strtotime($next . ' - '.$interval.' months'));
						break;

					case 'Y':
						$diffYear  = Qss_Lib_Date::divDate($start, $today, 'Y');
						$sodu      = $diffYear%$interval;
						$sodu      = (Qss_Lib_Date::compareTwoDate(date('Y-'.$item->Thang.'-'.$item->Ngay), $today) == -1)? ($sodu + $interval): $sodu;
						$next      = ($sodu)?date('Y-'.$item->Thang.'-'.$item->Ngay,strtotime($today . ' + '.$sodu.' years')):date('Y-'.$item->Thang.'-'.$item->Ngay);
						//$past      = date('Y-'.$item->Thang.'-'.$item->Ngay,strtotime($next . ' - '.$interval.' years'));
						break;
				}

				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['name']   = $item->LoaiBaoTri;
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['next']   = $next;
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['past']   = '';
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 0; // chua co tinh trang
			}
		}
	  
		// Kiem tra xem da co phieu bao tri cho ke hoach hay chua
		foreach($workorder AS $item)
		{
			/**
			 * + N/A : thiết bị không cần hiệu chuẩn
			 + Còn hạn : còn hiệu lực kiểm định đến > 15 ngày kiểm định kế tiếp theo thời hạn
			 + Hết hạn : Quá hạn kiểm định
			 + Sắp hết hạn : 15 ngày đổ lại tới hạn kiểm định kế tiếo
			 */
			// co phieu bao tri
			if(isset($planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]))
			{
				// Phieu bao tri da hoan thanh hoac dong
				if(!in_array($item->Status, $excludeStatus) )
				{
					$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 1; // dung han
				}
				else // Phieu bao tri dang thuc hien
				{
					$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['past']   = $item->NgaySoSanh;
					$diff = Qss_Lib_Date::divDate($item->NgaySoSanh, $planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['next']);
					if($diff > $dueDays)
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 2; // con han
					}
					elseif($diff < $dueDays && $diff > 0)
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 3; // sap het han
					}
					else
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 4; // qua han
					}
				}
			}
		}
		//echo '<pre>'; print_r($planArr); die;
		return $planArr;
	}

	function change_date($week_num, $day) {
		$timestamp    = strtotime(date('Y') . '-W' . $week_num . '-' . $day);
		return $timestamp;
	}


	/**
	 * @module: M757 - Tình trạng bảo hành thiết bị - params
	 */
	public function equipmentWarrantyAction()
	{

	}

	/**
	 * @module: M757 - Tình trạng bảo hành thiết bị - report
	 */
	public function equipmentWarranty1Action()
	{
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', 0);
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);
			
		$eqModel        = new Qss_Model_Extra_Equip();
		$retData        = array(); // mang bao hanh
		$wIndex         = 0;
		$nowMicro       = Qss_Lib_Date::i_fString2Time(date('d-m-Y'));
		$data           = $eqModel->getEquipments(
		array(),
		$locationIOID,
		0,
		$eqGroupIOID,
		$eqTypeIOID
		);


		// Lay mang du lieu bao hanh
		foreach ($data as $d)
		{
			$retData[$wIndex]['ECode']  = $d->MaThietBi;
			$retData[$wIndex]['EType']  = $d->LoaiThietBi;
			$retData[$wIndex]['EGroup'] = $d->NhomThietBi;
			$retData[$wIndex]['EDate']  = $d->HanBaoHanh?Qss_Lib_Date::mysqltodisplay($d->HanBaoHanh):'';
			$retData[$wIndex]['PCode']  = $d->HangBaoHanh;
			$retData[$wIndex]['PName']  = $d->HangBaoHanh;

			// Kiem tra ngay thang
			if ($retData[$wIndex]['EDate'])
			{
				$dueMicro = Qss_Lib_Date::i_fString2Time($retData[$wIndex]['EDate']);

				if ($nowMicro == $dueMicro)
				{
					$retData[$wIndex]['EStatus'] = 1; // Chính hôm nay đây
					$retData[$wIndex]['EClass'] = 'now_16';
				} elseif ($nowMicro < $dueMicro)
				{
					$interval = floor(($dueMicro - $nowMicro) / (60 * 60 * 24));
					if ($interval <= 7)
					{
						$retData[$wIndex]['EStatus'] = 2; // due soon
						$retData[$wIndex]['EClass'] = 'duesoon_16';
					} else
					{
						$retData[$wIndex]['EStatus'] = 3; // normal
						$retData[$wIndex]['EClass'] = 'normal_16';
					}
				} elseif ($nowMicro > $dueMicro)
				{
					$retData[$wIndex]['EStatus'] = 4; // over due
					$retData[$wIndex]['EClass'] = 'overdue_16';
				}
			} else // Neu ko co han bao hanh o dong chinh
			{
				$retData[$wIndex]['EStatus'] = 0; // không có hạn bảo hành
				$retData[$wIndex]['EClass'] = 'question_16';
			}
			$wIndex++;
		}

		$this->html->report       = $retData;
		// LOC THEO KHU VUC
		$this->html->locationIOID = $locationIOID;
		$this->html->location     = $location;
		// LOC THEO NHOM THIET BI
		$this->html->eqGroupIOID  = $eqGroupIOID;
		$this->html->eqGroup      = $eqGroup;
		// LOC THEO LOAI THIET BI
		$this->html->eqTypeIOID   = $eqTypeIOID;
		$this->html->eqType       = $eqType;
	}

	/**
	 * @module Bao cao tong hop theo doi tuong
	 * @path report/maintenance/cost/object
	 */
	public function costObjectAction()
	{
        $eqs         = $this->_common->getTable(array('*'), 'ODanhSachThietBi', array(), array('MaThietBi'), 100000);
		$eqsDialbox  = array();
		$eqIndex     =   0;

		foreach($eqs as $eq)
		{
			$eqsDialbox[0]['Dat'][$eqIndex]['ID']       = $eq->IOID;
			$eqsDialbox[0]['Dat'][$eqIndex]['Display']  = $eq->MaThietBi
			.' - '. $eq->TenThietBi;
			$eqIndex++;
		}

		$this->html->eqsDialbox = $eqsDialbox;
        $this->html->limit      = Qss_Lib_Extra_Const::$DATE_LIMIT;
	}

	// @todo: chua tinh chi phi sua chua
	public function costObject1Action()
	{
		$report = array();
		$start  = $this->params->requests->getParam('start', '');
		$end    = $this->params->requests->getParam('end', '');
		$eqs    = $this->params->requests->getParam('eqs', array());
		$floc   = $this->params->requests->getParam('flocation', 0);
		$fwc    = $this->params->requests->getParam('fworkcenter', 0);
		$feq    = $this->params->requests->getParam('fequip', 0);

//		$end    = Qss_Lib_Extra::getEndDate(Qss_Lib_Date::displaytomysql($start)
//		, Qss_Lib_Date::displaytomysql($end)
//		, 'D'
//		, Qss_Lib_Extra_Const::$DATE_LIMIT);

		$filter          = array();
		$filter['start'] = Qss_Lib_Date::displaytomysql($start);
		$filter['end']   = $end;
		$filter['eqs']   = $eqs;

		// Tong hop chi phi theo don vi thuc hien
		if($fwc)
		{
			$fwcData = $this->_model->getCostFromOutputGroupByWorkcenter($filter);
			$report['GroupWC'] = 'Đơn vị';
			$report['DatWC']     = array();
			$fwcDataIndex        = 0;
				
			foreach ($fwcData as $dat)
			{
				$report['DatWC'][$fwcDataIndex]['Name']          = $dat->WCCode
				.' - '. $dat->WCName;
				$report['DatWC'][$fwcDataIndex]['MaterialCost']  = $dat->MaterialCost;
				$fwcDataIndex++;
			}
			if(!count($report['DatWC'])) unset($report['GroupWC']);
		}

		// Tong hop chi phi theo cac khu vuc
		if($floc)
		{
			$flocData = $this->_model->getCostFromOutputGroupByLocation($filter);
			$report['GroupLoc'] = 'Khu vực';
			$report['DatLoc']    = array();
			$retval              = array();
				
			foreach($flocData as $index=>$dat)
			{
				$devideRL = $dat->LocRight - $dat->LocLeft;


				// Neu ko co chi phi va ko co cay con thi bo qua
				if($devideRL == 1 && $dat->MaterialCost == 0)
				{
					continue;
				}


				$retval['loc'][$index] = $dat;
				if(!isset($retval['MaterialCost'][$dat->LocID]))
				{
					$retval['MaterialCost'][$dat->LocID] = $dat->MaterialCost;

				}
				else
				{
					$retval['MaterialCost'][$dat->LocID] += $dat->MaterialCost;
				}

				unset($flocData->{$index}); // loai bo phan tu hien tai ra khoi cay

				if($devideRL > 1)
				{
					foreach ($flocData as $index2 => $a2)
					{
						// giam thieu so vong lap
						if(($retval['loc'][$index]->LocRight - $a2->LocRight) < 0)
						{
							break;
						}

						if($retval['loc'][$index]->LocLeft < $a2->LocLeft
						&& $retval['loc'][$index]->LocRight >  $a2->LocRight)
						{
							$retval['loc'][$index]->MaterialCost += $a2->MaterialCost;
						}
					}
						
					// Neu co cay con nhung chi phi bang 0 cung bo di
					if($retval['loc'][$index]->MaterialCost == 0)
					{
						unset($retval['loc'][$index]);
					}
				}


			}
				
			$report['DatLoc'] = isset($retval['loc'])?$retval['loc']:array();
			if(!count($report['DatLoc'])) unset($report['GroupLoc']);
		}

		// Tong hop chi phi theo tung thiet bi
		if($feq)
		{
			$feqData = $this->_model->getCostFromOutputGroupByEquip($filter);
			$report['GroupEq'] = 'Thiết bị';
			$report['DatEq']  = array();
			$oldEqID = '';
			$eqGroupIDIndex = -1;
			$feqDataIndex = 0;
				
			foreach($feqData as $dat)
			{
				if($oldEqID != $dat->EqGroupID)
				{
					$eqGroupIDIndex++;
					$report['DatEq'][$eqGroupIDIndex]['Type'] = $dat->EqType;
				}

				$report['DatEq'][$eqGroupIDIndex]['Dat'][$feqDataIndex]['Name'] = $dat->EqCode;
				$report['DatEq'][$eqGroupIDIndex]['Dat'][$feqDataIndex]['MaterialCost'] = $dat->MaterialCost;
				$feqDataIndex++;
				$oldEqID = $dat->EqGroupID;
			}
				
			if(!count($report['DatEq'])) unset($report['GroupEq']);
				
		}
		//echo '<pre>'; print_r($report); die;
		$this->html->report = $report;
		$this->html->start  = $start;
		$this->html->end    = Qss_Lib_Date::mysqltodisplay($end);
	}

	public function costAnalysisAction()
	{

	}

	public function costAnalysis1Action()
	{
		$start = Qss_Lib_Date::displaytomysql($this->_params['start']);
		$end = Qss_Lib_Date::displaytomysql($this->_params['end']);
		$end = Qss_Lib_Extra::getEndDate($start, $end, $this->_params['period']);
		$period = $this->params->requests->getParam('period');
		$aTime = Qss_Lib_Extra::displayRangeDate($start, $end, $this->_params['period']);


		$this->html->time = $aTime;
		$this->html->period = $period;
		$this->html->startDate = $this->_params['start'];
		$this->html->endDate = Qss_Lib_Date::mysqltodisplay($end);
		$this->html->groupBy = $this->_params['groupBy'];
		$this->html->cost = $this->_model->getCostAnalysis($start, $end
		, $period, $this->_params['groupBy']);

	}

	/* cost service hiện tại không được dùng */

	public function costServiceAction()
	{
		$suppliers = $this->_common->getTable(array('*'), 'ODoiTac', array('NhaCungCap' => 1)
		, array(), 'NO_LIMIT');
		$this->html->suppliers = $suppliers;

	}

	public function costService1Action()
	{
		$startDate = $this->params->requests->getParam('start');
		$endDate = $this->params->requests->getParam('end');
		$supplier = $this->params->requests->getParam('suppl');
		$this->html->start = $startDate;
		$this->html->end = $endDate;
		$this->html->cost = $this->_model->getServiceCosts(Qss_Lib_Date::displaytomysql($startDate)
		, Qss_Lib_Date::displaytomysql($endDate), $supplier);

	}

	public function costPeriodAction()
	{

	}

	public function costPeriod1Action()
	{
		$start = Qss_Lib_Date::displaytomysql($this->_params['start']);
		$end = Qss_Lib_Date::displaytomysql($this->_params['end']);
		$end = Qss_Lib_Extra::getEndDate($start, $end, $this->_params['period']);
		$aTime = Qss_Lib_Extra::displayRangeDate($start, $end, $this->_params['period']); // Range time


		if (Qss_Lib_System::fieldActive('ODanhSachThietBi', 'Ref_LoaiThietBi'))
		{
			$eqTypeIOID = $this->_params['type'];
		} else
		{
			$eqTypeIOID = 0;
		}


		$this->_params['end'] = Qss_Lib_Date::mysqltodisplay($end);
		$this->html->params = $this->_params;
		$this->html->time = $aTime;
		$this->html->cost = $this->_model->getCostByPeriod(
		$this->_params['period']
		, $start
		, $end
		, $eqTypeIOID
		, $this->_params['group']
		, $this->_params['equipment']
		, $this->_params['location']);
		$this->html->currency = $this->_common->getDefaultCurrency();

	}


	public function oeeAnalysisAction()
	{

	}

	public function oeeAnalysis1Action()
	{
		$eqIOID   = $this->params->requests->getParam('line', 0);
		$itemIOID = $this->params->requests->getParam('item', 0);
		$sDate    = $this->params->requests->getParam('start', 0);
		$eDate    = $this->params->requests->getParam('end', 0);
		$mSDate   = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate   = Qss_Lib_Date::displaytomysql($eDate);

		$this->html->report = $this->getOeeAnalysisData($mSDate, $mEDate, $eqIOID, $itemIOID);

	}

	public function oeeAnalysis2Action()
	{
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$eqIOID     = $this->params->requests->getParam('line', 0);
		$sDate      = $this->params->requests->getParam('start', 0);
		$eDate      = $this->params->requests->getParam('end', 0);

		$this->html->items = $this->_model->getItemsFromOperationBOM(
		$eqIOID,
		Qss_Lib_Date::displaytomysql($sDate),
		Qss_Lib_Date::displaytomysql($eDate)
		);
	}

	public function oeeGeneralAction()
	{

	}

	public function oeeGeneral1Action()
	{
		$eqIOID = $this->params->requests->getParam('line', 0);
		$sDate  = $this->params->requests->getParam('start', 0);
		$eDate  = $this->params->requests->getParam('end', 0);
		$mSDate = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate = Qss_Lib_Date::displaytomysql($eDate);
		$this->html->report = $this->getOeeGeneralData($mSDate, $mEDate, $eqIOID);
		$this->html->startDate = $sDate;
		$this->html->endDate = $eDate;
	}

	/**
	 * Lấy số lượng trên giờ chỉ với loại chỉ số là theo giờ
	 * @return array mảng theo thời gian
	 */
	protected function getProductsFromOperationBom($startDate, $endDate, $equipIOID = 0, $itemIOID = 0)
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();
		$retval      = array();
		$i           = 0;

		// Lay san pham cua thiet bi tu dinh muc thiet bi
		$dinhMuc = $equipModel->getProductsFromOperationBom($startDate, $endDate, $equipIOID, $itemIOID);

		foreach($dinhMuc as $dm)
		{
			if(isset($tempEquipIOID) && $dm->ItemIOID == $tempItemIOID && $dm->EqIOID == $tempEquipIOID)
			{
				$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndDate']    = $dm->StartDate;
			}

			if(isset($tempEquipIOID) && ($dm->ItemIOID != $tempItemIOID || $dm->EqIOID != $tempEquipIOID))
			{
				$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndDate']    = $endDate;
			}

			$tempEquipIOID = $dm->EqIOID;
			$tempItemIOID  = $dm->ItemIOID;
			$tempIndex     = $i;

			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EquipIOID']  = $dm->EqIOID;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EquipCode']  = $dm->EqCode;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EquipName']  = $dm->EqName;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemIOID']   = $dm->ItemIOID;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemCode']   = $dm->ItemCode;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemName']   = $dm->ItemName;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemUOM']    = $dm->ItemUOM;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['QtyPerHour'] = $dm->ItemBOMQty;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['StartDate']  = $dm->StartDate;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EndDate']    = '';
			$i++;
		}

		if(isset($tempEquipIOID))
		{
			$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndDate']    = $endDate;
		}

		return $retval;
	}

	protected function getActiveTimeFromDailyRecord($mSDate, $mEDate, $eqIOID = 0)
	{
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$arr        = $equipModel->getActiveTimeFromDailyRecord($mSDate, $mEDate, $eqIOID);
		$retval     = array();

		foreach ($arr as $item)
		{
			$retval[$item->RefEq] = $item->Time;
		}
		return $retval;
	}

	protected function getWorkingTime($wCalendar, $startDate, $endDate)
	{
		$retval = array();
		$retval2 = array();
		$temp   = $wCalendar; // Khong lam thay doi gia tri cua mang lich lam viec

		// Thiet bi
		foreach($temp as $item)
		{
			// Lich thiet bi
			foreach($item as $w)
			{
				// s <= s moc va e >= e moc, hai khoang thoi gian giao nhau
				if(Qss_Lib_Date::compareTwoDate($w['Start'], $endDate) != 1
				&& Qss_Lib_Date::compareTwoDate($w['End'], $startDate) != -1)
				{
					// Thay doi khoang thoi gian bat dau va ket thuc de luon
					if(Qss_Lib_Date::compareTwoDate($w['Start'], $startDate) == -1)
					{
						$w['Start'] = $startDate;
					}

					if(Qss_Lib_Date::compareTwoDate($w['End'], $endDate) == 1)
					{
						$w['End'] = $endDate;
					}

					$retval[] = $w;
				}
			}
		}

		foreach($retval AS $item)
		{
			if(!isset($retval2[$item['Ref_Eq']])) $retval2[$item['Ref_Eq']] = 0;

			$time = Qss_Lib_Extra::getTotalWCal($item['Ref_Cal'], $item['Start'], $item['End']);
			$retval2[$item['Ref_Eq']] += @$time[$item['Ref_Cal']];
		}
		return $retval2;
	}


	protected function getProductionStatistics($startDate, $endDate, $equipIOID = 0, $itemIOID = 0)
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();
		$retval      = array();
		$i           = 0;

		// Lay san pham cua thiet bi tu dinh muc thiet bi
		$thongke = $equipModel->getSumOfProductStatisticsFromDailyRecords($startDate, $endDate, $equipIOID, $itemIOID);

		foreach($thongke as $dm)
		{
			//             $retval[$dm->EqIOID][$dm->ItemIOID]['Date']       = $dm->Date;
			//             $retval[$dm->EqIOID][$dm->ItemIOID]['StartTime']  = $dm->StartTime;
			//             $retval[$dm->EqIOID][$dm->ItemIOID]['EndTime']    = $dm->EndTime;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemIOID']   = $dm->ItemIOID;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemCode']   = $dm->ItemCode;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemName']   = $dm->ItemName;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemUOM']    = $dm->ItemUOM;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ProduceQty'] = $dm->SanLuong;
			$retval[$dm->EqIOID][$dm->ItemIOID]['DefectQty']  = $dm->SoLuongLoi;
			$i++;
		}
		return $retval;
	}

	protected function getOeeGeneralData($mSDate, $mEDate, $eqIOID = 0)
	{
		$retval       = array();
		$operationBom = $this->getProductsFromOperationBom($mSDate, $mEDate, $eqIOID);
		$wCals        = Qss_Lib_Extra::getWorkingCals($mSDate, $mEDate, $eqIOID);
		$dailyRec     = $this->getActiveTimeFromDailyRecord($mSDate, $mEDate, $eqIOID);
		$downTime     = Qss_Lib_Maintenance_Downtime::getDowntimeFromPlanAndOrder($mSDate, $mEDate, $eqIOID);

		// Thiet bi
		foreach($operationBom as $eqIOIDKey=>$eq)
		{
			// San pham cua thiet bi
			foreach ($eq as $itemIOIDKey=>$items)
			{
				foreach($items as $item)
				{
					$code  = @(int)$item['EquipIOID'].'_'.@(int)$item['ItemIOID'];
					$wTime = $this->getWorkingTime($wCals, $item['StartDate'], $item['EndDate']);
					$pQty  = $this->getProductionStatistics($item['StartDate'], $item['EndDate'], $item['EquipIOID']);
					$produceQty = isset($pQty[$eqIOIDKey][$itemIOIDKey])?$pQty[$eqIOIDKey][$itemIOIDKey]['ProduceQty']:0;
					$defectQty = isset($pQty[$eqIOIDKey][$itemIOIDKey])?$pQty[$eqIOIDKey][$itemIOIDKey]['DefectQty']:0;

					if(!isset($retval[$code]))
					{
						$retval[$code]['LineCode'] = $item['EquipCode'];
						$retval[$code]['LineName'] = $item['EquipName'];
						$retval[$code]['ItemCode'] = $item['ItemCode'];
						$retval[$code]['ItemName'] = $item['ItemName'];
						$retval[$code]['Availability'] = 0;
						$retval[$code]['Performance'] = 0;
						$retval[$code]['Quality'] = 0;
						$retval[$code]['OEE'] = 0;

					}

					$retval[$code]['Availability'] += (isset($dailyRec[$item['EquipIOID']]) && isset($wTime[$eqIOIDKey]) && $wTime[$eqIOIDKey])?($dailyRec[$item['EquipIOID']]/ $wTime[$eqIOIDKey]) * 100:0;

					$retval[$code]['Performance'] += ($item['QtyPerHour'] && isset($wTime[$eqIOIDKey]) && $wTime[$eqIOIDKey])?($produceQty / ($item['QtyPerHour'] * $wTime[$eqIOIDKey])) * 100:0;

					$retval[$code]['Quality'] += ($produceQty + $defectQty)?($produceQty / ($produceQty + $defectQty)) * 100:0;

					$retval[$code]['OEE'] += ($item['QtyPerHour'] && isset($wTime[$eqIOIDKey]) && $wTime[$eqIOIDKey])?($produceQty / ($item['QtyPerHour'] * $wTime[$eqIOIDKey])) * 100:0;


				}
			}
		}
		return $retval;
	}


	protected function getOeeAnalysisData($mSDate, $mEDate, $eqIOID = 0, $itemIOID = 0)
	{
		$retval       = array();
		$operationBom = $this->getProductsFromOperationBom($mSDate, $mEDate, $eqIOID, $itemIOID);
		$wCals        = Qss_Lib_Extra::getWorkingCals($mSDate, $mEDate, $eqIOID);
		$dailyRec     = $this->getActiveTimeFromDailyRecord($mSDate, $mEDate, $eqIOID);
		$downTime     = Qss_Lib_Maintenance_Downtime::getDowntimeFromPlanAndOrder($mSDate, $mEDate, $eqIOID);
		$cDownTime    = Qss_Lib_Maintenance_Downtime::countDowntimeFromPlanAndOrder($mSDate, $mEDate, $eqIOID);

		// Thiet bi
		foreach($operationBom as $eqIOIDKey=>$eq)
		{
			// San pham cua thiet bi
			foreach ($eq as $itemIOIDKey=>$items)
			{
				foreach($items as $item)
				{


					$code  = @(int)$item['EquipIOID'].'_'.@(int)$item['ItemIOID'];
					$wTime = $this->getWorkingTime($wCals, $item['StartDate'], $item['EndDate']);
					$pQty  = $this->getProductionStatistics($item['StartDate'], $item['EndDate'], $item['EquipIOID']);
					$produceQty = isset($pQty[$eqIOIDKey][$itemIOIDKey])?$pQty[$eqIOIDKey][$itemIOIDKey]['ProduceQty']:0;
					$defectQty = isset($pQty[$eqIOIDKey][$itemIOIDKey])?$pQty[$eqIOIDKey][$itemIOIDKey]['DefectQty']:0;

					if(!isset($retval['ThoiGianDayChuyen']))
					{
						$retval['ThoiGianDayChuyen'] = 0;
					}
					$retval['ThoiGianDayChuyen'] += isset($wTime[$eqIOIDKey])?$wTime[$eqIOIDKey]:0;



					if(!isset($retval['SanLuongLyThuyet']))
					{
						$retval['SanLuongLyThuyet'] = 0;
					}
					$retval['SanLuongLyThuyet'] = @(double)$wTime[$eqIOIDKey] * $item['QtyPerHour'];

					if(!isset($retval['ThoiGianKeHoach']))
					{
						$retval['ThoiGianKeHoach'] = 0;
					}
					$retval['ThoiGianKeHoach'] += isset($wTime[$eqIOIDKey])?$wTime[$eqIOIDKey]:0;;
					 

					if(!isset($retval['SanLuongKeHoach']))
					{
						$retval['SanLuongKeHoach'] = 0;
					}
					$retval['SanLuongKeHoach'] += @(double)$wTime[$eqIOIDKey] * $item['QtyPerHour'];

					if(!isset($retval['ThoiGianDungMay']))
					{
						$retval['ThoiGianDungMay'] = 0;
					}
					$retval['ThoiGianDungMay'] += isset($downTime[$item['EquipIOID']]) ? $downTime[$item['EquipIOID']]: 0;

					if(!isset($retval['SoLuongBiLoi']))
					{
						$retval['SoLuongBiLoi'] = 0;
					}
					$retval['SoLuongBiLoi'] += $defectQty;

					if(!isset($retval['ThoiGianBiLoi']))
					{
						$retval['ThoiGianBiLoi'] = 0;
					}
					$retval['ThoiGianBiLoi'] += $item['QtyPerHour'] ? ($defectQty / $item['QtyPerHour']) : 0;;

					if(!isset($retval['SoLanDungMay']))
					{
						$retval['SoLanDungMay'] = 0;
					}
					$retval['SoLanDungMay'] += isset($cDownTime[$item['EquipIOID']]) ? $cDownTime[$item['EquipIOID']]: 0;

					if(!isset($retval['SanLuongThucTe']))
					{
						$retval['SanLuongThucTe'] = 0;
					}
					$retval['SanLuongThucTe'] += $produceQty;

					if(!isset($retval['ThoiGianHoatDong']))
					{
						$retval['ThoiGianHoatDong'] = 0;
					}
					$retval['ThoiGianHoatDong'] = (isset($dailyRec[@(int)$item['EquipIOID']]))?$dailyRec[@(int)$item['EquipIOID']]:0;

					if(!isset($retval['SoGioYeuCau']))
					{
						$retval['SoGioYeuCau'] = 0;
					}
					$retval['SoGioYeuCau'] = $item['QtyPerHour'] ? $produceQty / $item['QtyPerHour'] : 0;

					if(!isset($retval['KhaNangDapUng']))
					{
						$retval['KhaNangDapUng'] = 0;
					}
					$retval['KhaNangDapUng'] += (isset($dailyRec[@(int)$item['EquipIOID']]) && isset($wTime[$eqIOIDKey]) && $wTime[$eqIOIDKey])?($dailyRec[@(int)$item['EquipIOID']]/ $wTime[$eqIOIDKey]) * 100:0;

					if(!isset($retval['HieuSuatSanLuong']))
					{
						$retval['HieuSuatSanLuong'] = 0;
					}
					$retval['HieuSuatSanLuong'] += ($item['QtyPerHour']&& isset($wTime[$eqIOIDKey]) && $wTime[$eqIOIDKey])?($produceQty / ($item['QtyPerHour'] * $wTime[$eqIOIDKey])) * 100:0;

					if(!isset($retval['ChatLuong']))
					{
						$retval['ChatLuong'] = 0;
					}
					$retval['ChatLuong'] += ($produceQty + $defectQty)?($produceQty / ($produceQty + $defectQty)) * 100:0;

					if(!isset($retval['HieuSuatDayChuyen']))
					{
						$retval['HieuSuatDayChuyen'] = 0;
					}
					$retval['HieuSuatDayChuyen'] += ($item['QtyPerHour']&& isset($wTime[$eqIOIDKey])&& $wTime[$eqIOIDKey])?($produceQty / ($item['QtyPerHour'] * $wTime[$eqIOIDKey])) * 100:0;

					if(!isset($retval['Productive']))
					{
						$retval['Productive'] = 0;
					}
					$retval['Productive'] = $retval['SoGioYeuCau'] - $retval['ThoiGianBiLoi'];
				}
			}
		}
		return $retval;
	}


	public function remove_oeeAnalysisAction()
	{

	}

	public function remove_oeeAnalysis1Action()
	{
		$downtimeModel = new Qss_Model_Maintenance_Breakdown();
		$equipModel    = new Qss_Model_Maintenance_Equipment();
		$commonModel   = new Qss_Model_Extra_Extra();
		$params = $this->params->requests->getParams();
		$start = Qss_Lib_Date::displaytomysql($params['start']);
		$end = Qss_Lib_Date::displaytomysql($params['end']);
		$retval = array();

		$thietbi = $commonModel->getTable(array('*'), 'ODanhSachThietBi', array('IOID'=>@(int)$params['line']), array(), 'NO_LIMIT', 1);
		$params['workingCalendar'] = $thietbi?$thietbi->Ref_LichLamViec:0;

		$totalShiftDuration = Qss_Lib_Extra::getWorkingHours($params['workingCalendar'], $start, $end);

		// do chi co 1 day chuyen va 1 sp len ko can lay dc va sp lam key
		$tmp = array();
		$scheduleDowntime = $this->getScheduleDowntimeOfLine($params['line'], $start, $end);
		$thongKeSanXuat = $equipModel->getSumOfProductStatisticsFromDailyRecords(@(int)$params['line'], $start, $end);
		$dungDayChuyen = $downtimeModel->getDowntimeOfLines($params['line'], $start, $end);

		//$lineArr = $params['line']?array($params['line']):array();
		//$dungDayChuyen = $downtimeModel->getDowntimeFromWorkOrder($start, $end, 'D', $lineArr);


		foreach ($scheduleDowntime as $key => $val)
		{
			$tmp['KeHoachDungMay'] = round($val, 2);
		}


		foreach ($thongKeSanXuat as $tk)
		{
			// Ham tinh gio cho ca @todo: viet ham tinh so gio cua ca
			$start = Qss_Lib_Date::formatTime($tk->GioBatDau);
			$end = Qss_Lib_Date::formatTime($tk->GioKetThuc);
			$mcStart = strtotime($start);
			$mcEnd = strtotime($end);
			$mcMinus = $mcEnd - $mcStart;

			if ($mcMinus <= 0) // bat dau tu ngay hom truoc sang ngay hom sau
			{
				$today = date('d-m-Y'); // ngay hien tai gia dinh
				$tomorrow = date("d-m-Y", time() + 86400); // ngay mai gia dinh
				$todayTime = $today . ' ' . $start;
				$tomorrowTime = $tomorrow . ' ' . $end;
				$mcMinus = strtotime($tomorrowTime) - strtotime($todayTime);
			}
			// celse // cung trong mot ngay, khong thay doi gi

			$soGio = abs(round(10 * ($mcMinus) / 3600) / 10);
			// End Ham tinh gio cho ca




			if (!isset($tmp['ThoiGianKeHoach']))
			{
				$tmp['ThoiGianKeHoach'] = 0;
			}

			if (!isset($tmp['SanLuongThucTe']))
			{
				$tmp['SanLuongThucTe'] = 0;
			}

			if (!isset($tmp['SoLuongBiLoi']))
			{
				$tmp['SoLuongBiLoi'] = 0;
			}

			$tmp['ThoiGianKeHoach'] += $soGio;
			$tmp['SanLuongThucTe'] += $tk->SanLuong;
			$tmp['SoLuongBiLoi'] += $tk->SoLuongLoi;
		}

		foreach ($dungDayChuyen as $ddc)
		{
			$tmp['SoLanDungMay'] = $ddc->SoLanDungMay;
			$tmp['ThoiGianDungMay'] = $ddc->ThoiGianDungMay;
		}


		// Mảng retval
		$retval['NgayBatDau'] = $params['start'];
		$retval['KeHoachDungMay'] = isset($scheduleDowntime[$params['line']]) ? round($scheduleDowntime[$params['line']] / 60, 2) : 0;
		$retval['NgayKetThuc'] = $params['end'];
		$retval['DayChuyen'] = $params['lineName'];
		$retval['MaSanPham'] = $params['itemCode'];
		$retval['ThoiGianDayChuyen'] = $totalShiftDuration;
		$retval['SanLuongLyThuyet'] = $totalShiftDuration * $params['qtyPerHour'];
		$retval['ThoiGianKeHoach'] = isset($tmp['ThoiGianKeHoach']) ? $tmp['ThoiGianKeHoach'] : 0;
		$retval['SanLuongKeHoach'] = $totalShiftDuration * $params['qtyPerHour'];
		$retval['ThoiGianDungMay'] = isset($tmp['ThoiGianDungMay']) ? ($tmp['ThoiGianDungMay']) / 60 : 0;
		$retval['SoLanDungMay'] = isset($tmp['SoLanDungMay']) ? $tmp['SoLanDungMay'] : 0;
		$retval['SoLuongBiLoi'] = isset($tmp['SoLuongBiLoi']) ? $tmp['SoLuongBiLoi'] : 0;
		$retval['ThoiGianBiLoi'] = $params['qtyPerHour'] ? ($retval['SoLuongBiLoi'] / $params['qtyPerHour']) : 0;
		$producedQty = isset($tmp['SanLuongThucTe']) ? $tmp['SanLuongThucTe'] : 0;
		$retval['SanLuongThucTe'] = isset($tmp['SanLuongThucTe']) ? ($tmp['SanLuongThucTe']) : 0;
		$retval['ThoiGianHoatDong'] = $producedQty ? $retval['ThoiGianKeHoach'] - $retval['ThoiGianDungMay'] : 0;
		$retval['KhaNangDapUng'] = $retval['ThoiGianKeHoach'] ? ($retval['ThoiGianHoatDong'] / $retval['ThoiGianKeHoach']) * 100 : 0; // Availability
		$retval['SoGioYeuCau'] = $params['qtyPerHour'] ? $retval['SanLuongThucTe'] / $params['qtyPerHour'] : 0;
		$retval['HieuSuatSanLuong'] = $retval['SanLuongLyThuyet'] ? ($retval['SanLuongThucTe'] / $retval['SanLuongLyThuyet']) * 100 : 0;
		$retval['ChatLuong'] = $retval['SanLuongThucTe'] ? ( $retval['SanLuongThucTe'] / ($retval['SanLuongThucTe'] + $retval['SoLuongBiLoi'])) * 100 : 0;
		$retval['Productive'] = $retval['SoGioYeuCau'] - $retval['ThoiGianBiLoi'];
		$retval['HieuSuatDayChuyen'] = $retval['ThoiGianKeHoach'] ? ( ($retval['SoGioYeuCau'] - $retval['ThoiGianBiLoi']) / $retval['ThoiGianKeHoach'] ) * 100 : 0;
		//echo '<pre>'; print_r($retval); die;

		$this->html->oee = $retval;

	}

	public function remove_oeeAnalysis2Action()
	{
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$eqIOID = $this->params->requests->getParam('line', 0);
		$sDate  = $this->params->requests->getParam('start', 0);
		$eDate  = $this->params->requests->getParam('end', 0);

		$this->html->items = $this->_model->getItemsFromOperationBOM(
		$eqIOID,
		Qss_Lib_Date::displaytomysql($sDate),
		Qss_Lib_Date::displaytomysql($eDate)
		);
	}

	public function remove_oeeAnalysis3Action()
	{
		$retval = array();
		$eqIOID = $this->params->requests->getParam('line', 0);
		$sDate  = $this->params->requests->getParam('start', 0);
		$eDate  = $this->params->requests->getParam('end', 0);


		$equipment = $this->_model->getItemFromOperationBOM($eqIOID, $sDate, $eDate);
		foreach ($equipment as $item)
		{
			$retval[] = array('id' => $item->Ref_MaSanPham, 'value' => $item->MaSanPham . '-' . $item->TenSanPham);
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	/************************************************************************************************************
	 * M749: Hiệu suất tổng thể
	 * - C1: Tính dựa trên lệnh sản xuất
	 * - C2: Tính dựa trên phần bảo trì
	 ************************************************************************************************************
	 */
	public function remove_oeeGeneralAction()
	{

	}

	public function remove_oeeGeneral1Action()
	{
		$start     = $this->params->requests->getParam('start', '');
		$end       = $this->params->requests->getParam('end', '');
		$equipIOID = $this->params->requests->getParam('equip_ioid', 0);

		$this->html->startDate = $start;
		$this->html->endDate   = $end;
		$this->html->retval    = $this->_oeeGeneralGetOEEDataReport(Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end), $equipIOID);
	}



	/**
	 * Lich dieu dong thiet bi theo thoi gian
	 * @param unknown $startDate
	 * @param unknown $endDate
	 * @param number $equipIOID
	 */
	protected function _oeeGeneralGetDieuDongThietBi($startDate, $endDate, $equipIOID = 0)
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();

		// Lấy lịch làm việc từ điều động thiết bị => điều động thiết bị sắp xếp theo ngày bắt đầu
		// Lưu ý: Nếu ko có điều động thiết bị lấy lịch làm việc trong thiết bị
		// Lưu ý: Có thể sử dụng hoặc không sử dụng lịch điều động
		return $equipModel->getEquipWorking($startDate, $endDate, $equipIOID); // Lich dieu dong thiet bi
	}

	/**
	 * Lay lich lam viec cua thiet bi theo thoi gian
	 * @param unknown $equipWorking ket qua tra ve cua ham  _oeeGeneralGetDieuDongThietBi
	 * @return array lịch làm việc của thiết bị theo thời gian
	 */
	protected function _oeeGeneralGetWorkingCalsByTime($equipWorking, $startDate, $endDate)
	{
		$workingCals = array(); // Lich lam viec
		$i           = 0; // Index lich lam viec

		// Từ lịch điều động thiết bị lấy ra thời gian làm việc theo ngày
		// Lưu ý: Yêu cầu phải có start và end time để kiểm tra có giao với thời gian dừng máy
		// $workingCals[ref_equip][$i][Ref_Cal] + [UseMove] (Điều động tb) + [Start] + [End] (Start & End có thể trống)
		foreach($equipWorking as $item)
		{
			$workingCals[$item->Ref_Equip][$i]['Ref_Eq']  = $item->Ref_Equip;
			$workingCals[$item->Ref_Equip][$i]['Ref_Cal'] = $item->Ref_Cal;
			$workingCals[$item->Ref_Equip][$i]['UseMove'] = $item->UseMove?true:false;
			$workingCals[$item->Ref_Equip][$i]['Start']   = $item->UseMove?$item->StartDate:$startDate;
			$workingCals[$item->Ref_Equip][$i]['End']     = $item->UseMove?$item->EndDate:$endDate;
			$i++;
		}

		return $workingCals;
	}

	/**
	 * Lấy ra thời gian dừng máy của từng thiết bị
	 */
	protected function _oeeGeneralGetDowntime($startDate, $endDate, $equipIOID = 0)
	{
		$downTime = array();
		$eqIOIDArr = $equipIOID?array($equipIOID):array();

		return $this->getDowntime($startDate, $endDate, 'D', $eqIOIDArr, true);

		// Lấy thời gian dừng máy từ phiếu bảo trì theo ngày
		// Lưu ý: Yêu cầu phải có start và end time để kiểm tra có giao với thời gian làm việc

		// Lấy thời gian dừng máy từ module dừng máy  theo ngày
		// Lưu ý: Yêu cầu phải có start và end time để kiểm tra có giao với thời gian làm việc

		//return array();

	}



	/**
	 * Lấy số lượng sản xuất thực tế và số lượng lỗi của sản phẩm từ nhật trình máy móc
	 * @param unknown $startDate
	 * @param unknown $endDate
	 * @param number $eqIOID
	 */
	protected function remove_oeeGeneralGetProductionStatistics($startDate, $endDate, $equipIOID = 0)
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();
		$retval      = array();
		$i           = 0;

		// Lay san pham cua thiet bi tu dinh muc thiet bi
		$thongke = $equipModel->getSumOfProductStatisticsFromDailyRecords($startDate, $endDate, $equipIOID);

		foreach($thongke as $dm)
		{
			//             $retval[$dm->EqIOID][$dm->ItemIOID]['Date']       = $dm->Date;
			//             $retval[$dm->EqIOID][$dm->ItemIOID]['StartTime']  = $dm->StartTime;
			//             $retval[$dm->EqIOID][$dm->ItemIOID]['EndTime']    = $dm->EndTime;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemIOID']   = $dm->ItemIOID;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemCode']   = $dm->ItemCode;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemName']   = $dm->ItemName;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ItemUOM']    = $dm->ItemUOM;
			$retval[$dm->EqIOID][$dm->ItemIOID]['ProduceQty'] = $dm->SanLuong;
			$retval[$dm->EqIOID][$dm->ItemIOID]['DefectQty']  = $dm->SoLuongLoi;
			$i++;
		}
		return $retval;
	}

	/**
	 * Lấy số lượng trên giờ chỉ với loại chỉ số là theo giờ
	 * @return array mảng theo thời gian
	 */
	protected function _oeeGeneralGetProductsOfOperationBom($startDate, $endDate, $equipIOID = 0, $itemIOID = 0)
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();
		$retval      = array();
		$i           = 0;

		// Lay san pham cua thiet bi tu dinh muc thiet bi
		$dinhMuc = $equipModel->getProductsFromOperationBom($startDate, $endDate, $equipIOID, $itemIOID);

		foreach($dinhMuc as $dm)
		{
			if(isset($tempEquipIOID) && $dm->ItemIOID == $tempItemIOID && $dm->EqIOID == $tempEquipIOID)
			{
				$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndDate']    = $dm->StartDate;
				$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndTime']    = '00:00:00';
			}

			if(isset($tempEquipIOID) && ($dm->ItemIOID != $tempItemIOID || $dm->EqIOID != $tempEquipIOID))
			{
				$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndDate']    = $endDate;
				$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndTime']    = '00:00:00';
			}

			$tempEquipIOID = $dm->EqIOID;
			$tempItemIOID  = $dm->ItemIOID;
			$tempIndex     = $i;

			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EquipIOID']  = $dm->EqIOID;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EquipCode']  = $dm->EqCode;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EquipName']  = $dm->EqName;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemIOID']   = $dm->ItemIOID;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemCode']   = $dm->ItemCode;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemName']   = $dm->ItemName;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['ItemUOM']    = $dm->ItemUOM;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['QtyPerHour'] = $dm->ItemBOMQty;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['StartDate']  = $dm->StartDate;
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['StartTime']  = '00:00:00';
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EndDate']    = '';
			$retval[$dm->EqIOID][$dm->ItemIOID][$i]['EndTime']    = '';
			$i++;
		}


		if(isset($tempEquipIOID))
		{
			$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndDate']    = $endDate;
			$retval[$tempEquipIOID][$tempItemIOID][$tempIndex]['EndTime']    = '00:00:00';
		}

		return $retval;
	}



	protected function reomve_getAllWCalendarInTime($wCalendar, $startDate, $endDate)
	{
		$retval = array();
		$temp   = $wCalendar; // Khong lam thay doi gia tri cua mang lich lam viec

		// Thiet bi
		foreach($temp as $item)
		{
			// Lich thiet bi
			foreach($item as $w)
			{
				// s <= s moc va e >= e moc, hai khoang thoi gian giao nhau
				if(Qss_Lib_Date::compareTwoDate($w['Start'], $endDate) != 1
				&& Qss_Lib_Date::compareTwoDate($w['End'], $startDate) != -1)
				{
					// Thay doi khoang thoi gian bat dau va ket thuc de luon
					if(Qss_Lib_Date::compareTwoDate($w['Start'], $startDate) == -1)
					{
						$w['Start'] = $startDate;
					}

					if(Qss_Lib_Date::compareTwoDate($w['End'], $endDate) == 1)
					{
						$w['End'] = $endDate;
					}

					$retval[] = $item;
				}
			}
		}

		return $retval;
	}



	/**
	 *;
	 */
	protected function _oeeGeneralGetOEEDataReport($startDate, $endDate, $eqIOID = 0)
	{
		$retval  = array();
		$i       = 0;

		$dinhmuc      = $this->_oeeGeneralGetProductsOfOperationBom($startDate, $endDate, $eqIOID);
		$equipWorking = $this->_oeeGeneralGetDieuDongThietBi($startDate, $endDate, $eqIOID);
		$wCalendar    = $this->_oeeGeneralGetWorkingCalsByTime($equipWorking, $startDate, $endDate);
		$productionStatistics = $this->_oeeGeneralGetProductionStatistics($startDate, $endDate, $eqIOID);
		$dungmay = $this->_oeeGeneralGetDowntime($startDate, $endDate, $eqIOID);

		$performance = array();
		$quality = array();
		$oee    = array();
		$availability = array();

		// Thiet bi
		foreach($dinhmuc as $eqIOIDKey=>$eq)
		{
			// San pham cua thiet bi
			foreach ($eq as $itemIOIDKey=>$items)
			{
				foreach($items as $item)
				{
					$getAllLichLamVienInTime     = $this->getAllWCalendarInTime($wCalendar, $startDate, $endDate);
					$getAllThongKeSanLuongInTime = $this->_oeeGeneralGetProductionStatistics($item['StartDate'], $item['EndDate']);
					$thoiGianLamViec             = isset($getAllLichLamVienInTime[$eqIOIDKey])?$getAllLichLamVienInTime[$eqIOIDKey]:array();
					$refCalArr                   = array();
					/*
					 * $retval[$dm->EqIOID][$dm->ItemIOID]['ProduceQty'] = $dm->SanLuong;
					 $retval[$dm->EqIOID][$dm->ItemIOID]['DefectQty']  = $dm->SoLuongLo
					 */

					foreach($thoiGianLamViec as $tglv)
					{
						$refCalArr[$tglv['Ref_Eq']] = $tglv['Ref_Cal'];
					}

					$thoiGianCuaLich = count($refCalArr)?Qss_Lib_Extra::getTotalWCal($refCalArr, $item['Start'], $item['End']):array();
					$code            = @(int)$item['EqIOID'].'_'.@(int)$item['ItemIOID'];
					$thoiGianLamViecTheoLich = (isset($refCalArr[$eqIOIDKey]) && isset($thoiGianCuaLich[$refCalArr[$eqIOIDKey]]))?$thoiGianCuaLich[$refCalArr[$eqIOIDKey]]:0;
					$sanLuongThucTe  = isset($getAllThongKeSanLuongInTime[$eqIOIDKey][$itemIOIDKey])?$getAllThongKeSanLuongInTime[$eqIOIDKey][$itemIOIDKey]['ProduceQty']:0;
					$sanLuongLoi     = isset($getAllThongKeSanLuongInTime[$eqIOIDKey][$itemIOIDKey])?$getAllThongKeSanLuongInTime[$eqIOIDKey][$itemIOIDKey]['DefectQty']:0;
					$thoiGianDungMay = isset($dungmay[$eqIOIDKey])?$dungmay[$eqIOIDKey]:0;

					// LineCode + LineName + ItemCode + ItemName
					if(!isset($retval[$code]))
					{
						$retval[$code]['LineCode'] = $item['EquipCode'];
						$retval[$code]['LineName'] = $item['EquipName'];
						$retval[$code]['ItemCode'] = $item['ItemCode'];
						$retval[$code]['ItemName'] = $item['ItemName'];
					}

					// AvailabilityKha nang = (thoi gian lam việc - thoi gian dừng máy trong thời gian làm việc)/thoi gian lam việc
					$availability[$code][] = $thoiGianLamViecTheoLich?(($thoiGianLamViecTheoLich - $thoiGianDungMay)/$thoiGianLamViecTheoLich)*100:0;

					// Performance(Hiệu suất) = (Sản lượng thực tế / (Số lượng trên giờ * Thời gian làm việc theo lịch)) * 100
					$performance[$code][] = ($item['QtyPerHour'] && $thoiGianLamViecTheoLich)?($sanLuongThucTe / ($item['QtyPerHour'] * $thoiGianLamViecTheoLich)) * 100:0;

					// Quality
					// Chất lượng = ( Sản lượng thực tế / (Sản lượng thực tế + Số lượng lỗi) ) * 100
					$quality[$code][]  = ($sanLuongThucTe + $sanLuongLoi)?($sanLuongThucTe / ($sanLuongThucTe + $sanLuongLoi)) * 100:0;

					// OEE (((Sản lượng thực/ số lượng trên giờ) - (Sản phẩm lỗi/số lượng trên giờ)) / Thời gian làm việc theo lịch) *100
					$oee[$code][] = ($item['QtyPerHour'] && $thoiGianLamViecTheoLich)?((($sanLuongThucTe / $item['QtyPerHour']) - ($sanLuongLoi/ $item['QtyPerHour']))/ $thoiGianLamViecTheoLich) *100:0;
					$i++;
				}
			}
		}


		foreach($availability as $key=>$items)
		{
			$retval[$key]['Availability'] = array_sum($items)/count($items);
		}

		foreach ($performance as $key=>$items)
		{
			$retval[$key]['Performance'] = array_sum($items)/count($items);
		}

		foreach ($quality as $key=>$items)
		{
			$retval[$key]['Quality'] = array_sum($items)/count($items);
		}

		foreach ($oee as $key=>$items)
		{
			$retval[$key]['OEE'] = array_sum($items)/count($items);
		}

		return $retval;
	}

	/* #Move  */
	public function resourceEmpAction()
	{

	}
	/* #Move  */
	public function resourceEmp1Action()
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$start = $this->params->requests->getParam('start', '');
		$end = $this->params->requests->getParam('end', '');
		$refWorkcenter = $this->params->requests->getParam('workcenter', 0);
		$emplTime = $maint->getWorkingHourByEmployee(Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end), $refWorkcenter);
		$empl = $maint->resourceEmpReportGetEmployee($refWorkcenter);
		$eTime = array();
		//$this->_common->showTableStucture('OCongViecBaoTri');
		foreach ($emplTime as $et)
		{
			if ($et->Ref_Worker && $et->Time)
			{
				$eTime[$et->Ref_Worker]['Work'][$et->Ref_Work] = $et->Time;
			}
		}

		foreach ($empl as $e)
		{
			$eTime[$e->IOID]['Group']['ID'] = @(int) $e->DVIOID;
			$eTime[$e->IOID]['Group']['Code'] = $e->Ma;
			$eTime[$e->IOID]['Group']['Name'] = $e->Ten;
			$eTime[$e->IOID]['Emp']['Code'] = $e->MaNhanVien;
			$eTime[$e->IOID]['Emp']['Name'] = $e->TenNhanVien;
		}

		$this->html->works = $this->getMaintWork();
		$this->html->report = $eTime;
		$this->html->start = $start;
		$this->html->end = $end;

	}

	/* #Move  */
	/* Move to Maintenancereport/EmployeeController */
	public function resourceTimeAction()
	{

	}
	/* #Move  */
	public function resourceTime1Action()
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$start = $this->params->requests->getParam('start', '');
		$end = $this->params->requests->getParam('end', '');
		$end = Qss_Lib_Extra::getEndDate($start, $end, 'D', Qss_Lib_Extra_Const::$DATE_LIMIT);
		$endDisplayDateFormat = Qss_Lib_Date::mysqltodisplay($end);

		$this->html->available = $this->getResouceTimeAvailableData($start, $endDisplayDateFormat);
		$this->html->plan = $this->getResouceTimePlanData($start, $endDisplayDateFormat);
		$this->html->works = $this->getMaintWork();
		$this->html->start = $start;
		$this->html->end = $endDisplayDateFormat;

	}

	/**
	 *
	 */
	/* #Move  */
	public function resourceWorkcenterAction()
	{

	}
	/* #Move  */
	public function resourceWorkcenter1Action()
	{
		// Lay cac tham so truyen len
		$start      = $this->params->requests->getParam('start', '');
		$end        = $this->params->requests->getParam('end', '');
		$workcenter = $this->params->requests->getParam('workcenter', '');
		$end        = Qss_Lib_Extra::getEndDate(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end)
		, 'D'
		, Qss_Lib_Extra_Const::$DATE_LIMIT);

		// Lay tong so gio yeu cau theo ke hoach
		$this->html->plan = $this->getResouceTimePlanDataForWorkcenterByDay(
		$start, $end, $workcenter);

		// Lay tong so gio theo so nhan vien lam viec
		$this->html->emp = $this->getResouceTimeAvailableDataForWorkcenterByDay(
		$start, $end, $workcenter);

		$this->html->date = Qss_Lib_Extra::displayRangeDate(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end), 'D');
	}

	/* #Move  */
	protected function getMaintWork()
	{
//		$filter['module'] = 'OCongViecBaoTri';
//		$works = $this->_common->getDataset($filter);
        $mTable = Qss_Model_Db::Table('OCongViecBaoTri');
		$retval = array();

		foreach ($mTable->fetchAll() as $w)
		{
			$retval[$w->IOID] = $w;
		}
		return $retval;

	}

	/* #Move  */
	// @todo: Khong can group theo don vi lam viec co the tinh tong luon
	protected function getResouceTimePlanDataForWorkcenterByDay($start, $end, $workcenter)
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$countDate = 0;

		$startTmp = date_create($start);
		$endTmp = date_create($end);
		$workArr = array();

		while ($startTmp <= $endTmp)
		{
			if ($countDate == Qss_Lib_Extra_Const::$DATE_LIMIT['D']) // gioi han 60 ngay
			{
				break;
			}
			$countDate++;
			$date = $startTmp->format('Y-m-d');
			$plan = $maint->getMaintPlanWorkTime($date
			, array('workcenter'=>$workcenter, 'group'=>'wc'));

			$workArr[$date] = 0;
				
			foreach ($plan as $item)
			{
				if ($item->Work)
				{
					$workArr[$date] += $item->ThoiGian;
				}
			}
			$startTmp = Qss_Lib_Date::add_date($startTmp, 1); // add start one day
		}
		return $workArr;

	}

	/* #Move  */
	protected function getResouceTimeAvailableDataForWorkcenterByDay($start, $end, $workcenter)
	{
		$RefWCal = array();
		$TimeByWC = array();
		$TimeByWCRet = array();

		$start = Qss_Lib_Date::displaytomysql($start);
		$end   = Qss_Lib_Date::displaytomysql($end);

		$maint = new Qss_Model_Extra_Maintenance();
		$empl  = $maint->getWorkingEmplFromWorkCenter($start, $end
		, $workcenter);
		//getWCalByDay

		// Lay ra lich lam viec cua nhan vien
		foreach($empl as $e)
		{
			if(!in_array($e->RefWCal, $RefWCal))
			{
				$RefWCal[] = $e->RefWCal;
			}
				
			// Tinh tong cac lich lam viec theo tung don vi
			// dung de xac dinh tung ngay don vi lam bao nhieu tg
			if(!isset($TimeByWC[$e->RefWC][$e->RefWCal]))
			{
				$TimeByWC[$e->RefWC][$e->RefWCal] = 1;
			}
			else
			{
				$TimeByWC[$e->RefWC][$e->RefWCal]++;
			}
		}

		// Lay thoi gian lam viec tung ngay theo lich lam viec
		$WCalByDay = Qss_Lib_Extra::getWCalByDay($RefWCal, $start, $end);
		//		echo '<pre>';
		//		print_r($TimeByWC);
		//		echo '<pre>';
		//		print_r($WCalByDay);
		//		die;

		// Lay tong thoi gian lam viec cua tung to bao tri
		$startTem = date_create($start);
		$endTem   = date_create($end);

		while ($startTem <= $endTem)
		{
			$startToDate = $startTem->format('Y-m-d'); // Ngay
			$TimeByWCRet[$startToDate] = 0;
				
			foreach($TimeByWC as $refWC1 => $Cal1)
			{

				foreach($Cal1 as $refWCal1 => $countWCal1)
				{
					if(isset($WCalByDay[$refWCal1][$startToDate]))
					{
						$TimeByWCRet[$startToDate] += $countWCal1
						* array_sum($WCalByDay[$refWCal1][$startToDate]);
					}
				}
				//$TimeByWCRet
			}
			$startTem = Qss_Lib_Date::add_date($startTem, 1);
		}
		return $TimeByWCRet;
	}

	/* #Move  */
	protected function getResouceTimePlanData($start, $end)
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$countDate = 0;

		$startTmp = date_create($start);
		$endTmp = date_create($end);
		$workArr = array();

		while ($startTmp <= $endTmp)
		{
			if ($countDate == Qss_Lib_Extra_Const::$DATE_LIMIT['D']) // gioi han 60 ngay
			{
				break;
			}
			$countDate++;

			$plan = $maint->getMaintPlanWorkTime($startTmp->format('Y-m-d'));
			foreach ($plan as $item)
			{
				if ($item->Work)
				{
					if (!isset($workArr[$item->Work]))
					$workArr[$item->Work] = 0;
					$workArr[$item->Work] += $item->ThoiGian;
				}
			}
			$startTmp = Qss_Lib_Date::add_date($startTmp, 1); // add start one day
		}
		return $workArr;

	}

	/* #Move  */
	protected function getResouceTimeAvailableData($start, $end)
	{

		$maint = new Qss_Model_Extra_Maintenance();
		$EmplWorkCal = array();
		$TotalByWorkCal = array();
		$totalTimeEmpl = 0;
		$totalTimeByWCal = array();
		$work = array(); // cong viec
		$maxTimeByWork = array(); // Thoi gian lon nhat theo lich lam viec(Cong tong lich lam viec cua cong viec)
		$minTimeByWork = array(); // Thoi gian nho nhat theo lich lam viec(lay lich lam viec nho nhat ung voi cong viec)
		//$timeByWork       = array(); // Cac lich lam viec cho tung ky nang
		$minTimeByWorkTemp = array();


		// ***********************************************************************************************
		$empl = $maint->getWorkingEmpl(); // Lay nhan vien va cong viec cua nhan vien con hoat dong
		$emplTemp = array();
		$oldEmp = '';
		$onlyOneWork = 0; // Dung de tinh min, neu lam mot duy nhat cong viec => min, lon hon => min =0
		// Lay mang lich lam viec khong lap lai cua nhan vien $EmplWorkCal
		// Dem so luong moi lich lam viec $TotalByWorkCal
		foreach ($empl as $ep)
		{
			if ($ep->Ref_LichLamViec)
			{
				$emplTemp[$ep->IFID] = $ep;
				if ($oldEmp == $ep->IFID)
				{
					unset($emplTemp[$ep->IFID]);
				}

				// dem theo lich lam viec
				if ($oldEmp != $ep->IFID)
				{
					if (isset($TotalByWorkCal[$ep->Ref_LichLamViec]))
					{
						$TotalByWorkCal[$ep->Ref_LichLamViec] += 1;
					} else
					{
						$TotalByWorkCal[$ep->Ref_LichLamViec] = 1;
					}
				}


				// Lay mang lich lam viec
				if (!in_array($ep->Ref_LichLamViec, $EmplWorkCal))
				{
					$EmplWorkCal[] = $ep->Ref_LichLamViec;
				}

				// Dem theo cong viec + lich lam viec
				if (isset($work[$ep->Ref_LichLamViec][$ep->Ref_KyNang]))
				{
					$work[$ep->Ref_LichLamViec][$ep->Ref_KyNang] += 1;
				} else
				{
					$work[$ep->Ref_LichLamViec][$ep->Ref_KyNang] = 1;
				}
			}
			$oldEmp = $ep->IFID;
		}

		// thoi gian theo lich lam viec $wCal
		$wCal = Qss_Lib_Extra::getTotalWCal($EmplWorkCal
		, Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end));

		// Tong thoi gian cua tat ca nhan vien co the lam $totalTimeEmpl
		foreach ($TotalByWorkCal as $refWCal => $count)
		{
			if (isset($wCal[$refWCal]))
			{
				// init tong thoi gian theo lich lam viec
				if (!isset($totalTimeByWCal[$refWCal]))
				{
					$totalTimeByWCal[$refWCal] = 0;
				}


				$totalTimeEmpl += $count * $wCal[$refWCal];
				$totalTimeByWCal[$refWCal] = $count * $wCal[$refWCal];


				if (isset($work[$refWCal]))
				{
					foreach ($work[$refWCal] as $refKyNang => $count)
					{
						// init tong thoi gian cho tung cong viec (cong tong khong xet cac nhan vien cung lam 1 cong viec)
						if (!isset($maxTimeByWork[$refKyNang]))
						{
							$maxTimeByWork[$refKyNang] = 0;
						}

						$maxTimeByWork[$refKyNang] += $count * $wCal[$refWCal];
						//$timeByWork[$refKyNang][] = $wCal[$refWCal];
					}
				}
			}
		}

		foreach ($emplTemp as $ep)
		{
			if (!isset($minTimeByWork[$ep->Ref_KyNang]))
			{
				$minTimeByWork[$ep->Ref_KyNang] = 0;
			}
			if (isset($wCal[$ep->Ref_LichLamViec]))
			{
				$minTimeByWork[$ep->Ref_KyNang] += $wCal[$ep->Ref_LichLamViec];
			}
		}

		// Lay thoi gian min cua tung cong viec
		//                foreach($timeByWork as $refKyNang=>$time)
		//                {
		//                        if(count($timeByWork[$refKyNang]) > 1)
		//                        {
		//                                $minTimeByWork[$refKyNang] = 0;
		//                        }
		//                        else
		//                        {
		//                                $minTimeByWork[$refKyNang] = $timeByWork[$refKyNang][0];
		//                        }
		//                }
		return array('total' => $totalTimeEmpl, 'max' => $maxTimeByWork, 'min' => $minTimeByWork);

	}

	// ***********************************************************
	// === Get downtime
	// ***********************************************************
	protected function getDowntime($start, $end, $period = '', $refEquipArr = array(), $add = false)
	{
		$retval = array();
		$now = date('Y-m-d');
		$compareEndWithNow = Qss_Lib_Date::compareTwoDate($end, $now);
		$compareStartWithNow = Qss_Lib_Date::compareTwoDate($start, $now);


		if ($compareStartWithNow == 1 || $compareStartWithNow == 0)
		{
			// Tinh hoan toan trong luong lai, dua vao bao tri dinh ki
			$past = array();
			$future = $this->getDowntimeFromPriorityMaintainPlan($start, $end, $period, $refEquipArr);
		} elseif (($compareStartWithNow == 0 || $compareStartWithNow == -1) && ($compareEndWithNow == 1 || $compareEndWithNow == 0))
		{
			// Tinh trong ca hien tai lan tuong lai
			// $now = date('Y-m-d');
			$nearNow = date('Y-m-d', strtotime($now) - 86400);
			$past = $this->getDowntimeFromWorkOrder($start, $nearNow, $period, $refEquipArr);
			$future = $this->getDowntimeFromPriorityMaintainPlan($now, $end, $period, $refEquipArr);
		} elseif ($compareEndWithNow == -1)
		{
			// Tinh trong qua khu, dua vao hai phieu bao tri
			$past = $this->getDowntimeFromWorkOrder($start, $end, $period, $refEquipArr);
			$future = array();
		}

		if ($add) // Cong don qua khu va tuong lai
		{
			if ($period)
			{
				foreach ($past as $refEq => $valByPeriod)
				{
					foreach ($valByPeriod as $period => $val)
					{
						if (!isset($retval[$refEq][$period]))
						{
							$retval[$refEq][$period] = 0;
						}
						$retval[$refEq][$period] += $val;
					}
				}

				foreach ($future as $refEq => $valByPeriod)
				{


					foreach ($valByPeriod as $period => $val)
					{
						if (!isset($retval[$refEq][$period]))
						{
							$retval[$refEq][$period] = 0;
						}
						$retval[$refEq][$period] += $val;
					}
				}
			} else
			{
				foreach ($past as $refEq => $val)
				{
					if (!isset($retval[$refEq]))
					{
						$retval[$refEq] = 0;
					}

					$retval[$refEq] += $val;
				}

				foreach ($future as $refEq => $val)
				{
					if (!isset($retval[$refEq]))
					{
						$retval[$refEq] = 0;
					}

					$retval[$refEq] += $val;
				}
			}
		} else // tra ve 2 mang rieng le ve qua khu va tuong lai
		{
			$retval = array('past' => $past, 'future' => $future);
		}
		return $retval;

	}

	protected function getDowntimeFromPriorityMaintainPlan($start, $end, $period, $refEquipArr = array())
	{
		$startFormat = date_create($start);
		$endFormat = date_create($end);
		$data = $this->_model->getMaintenancePlanFull($refEquipArr);
		$shift = $this->_common->getTable(array('*'), 'OCa');

		$solar = new Qss_Model_Calendar_Solar();
		$print = array();
		$i = 0;
		$oldPlanMainTainIOID = '';


		if ($period)
		{
			foreach ($data as $item)
			{
				// Them bao tri ko dinh ky
				// Them bao tri dinh ky

				if ($item->NgayBTKDK)
				{
					if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca]))
					{
						if (Qss_Lib_Date::checkInRangeTime($item->NgayBTKDK, $this->_params['start'], $this->_params['end']))
						{
							$startSpe = date_create($item->NgayBTKDK);
							$day = (int) $startSpe->format('d');
							$week = (int) $startSpe->format('w');
							$month = (int) $startSpe->format('m');
							$year = $startSpe->format('Y');
							$quarter = (int) $solar->getQuarter((int) $month);
							$monthNo = $solar->getMonthNo((int) $month);

							switch ($period)
							{
								case 'D': $key = $startSpe->format('d-m-Y');
								break;
								case 'W': $key = $week . '.' . $year;
								break;
								case 'M': $key = $month . '.' . $year;
								break;
								case 'Q': $key = $quarter . '.' . $year;
								break;
								case 'Y': $key = $year;
								break;
							}
							$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca] = 1;
							$print[$item->Ref_MaThietBi][$key] = $item->DungMay;
							$i++;
						}
					}
				} // End bao tri ko dinh ky

				if ($item->IOID != $oldPlanMainTainIOID)
				{
					$start = $startFormat;
					while ($start <= $endFormat)
					{
						$date = $start->format('Y-m-d');


						if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca]))
						{
							$day = (int) $start->format('d');
							$week = (int) $start->format('w');
							$month = (int) $start->format('m');
							$year = $start->format('Y');
							$quarter = (int) $solar->getQuarter((int) $month);
							$monthNo = $solar->getMonthNo((int) $month);

							switch ($period)
							{
								case 'D': $key = $start->format('d-m-Y');
								break;
								case 'W': $key = $week . '.' . $year;
								break;
								case 'M': $key = $month . '.' . $year;
								break;
								case 'Q': $key = $quarter . '.' . $year;
								break;
								case 'Y': $key = $year;
								break;
							}

							if (!isset($print[$item->Ref_MaThietBi][$key]))
							{
								$print[$item->Ref_MaThietBi][$key] = 0;
							}

							if (
							(($item->MaKy == 'D') || ($item->MaKy == 'W' && $week == $item->GiaTri) || ($item->MaKy == 'M' && $day == $item->Ngay) || ($item->MaKy == 'Q' && $monthNo == $item->ThangThu) || ($item->MaKy == 'Y' && $day == $item->Ngay && $month == $item->Thang)
							)
							)
							{
								$item->DungMay = $item->DungMay ? $item->DungMay : 0;
								$print[$item->Ref_MaThietBi][$key] += $item->DungMay;
								$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca] = 1;
							}
						}
						$start = Qss_Lib_Date::add_date($start, 1);
					}
				}
				$oldPlanMainTainIOID = $item->IOID;
			}
		} else
		{
			foreach ($data as $item)
			{
				// Them bao tri ko dinh ky
				// Them bao tri dinh ky

				if (!isset($print[$item->Ref_MaThietBi]))
				{
					$print[$item->Ref_MaThietBi] = 0;
				}

				if ($item->NgayBTKDK)
				{
					if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca]))
					{
						if (Qss_Lib_Date::checkInRangeTime($item->NgayBTKDK, $this->_params['start'], $this->_params['end']))
						{
							$item->DungMay = $item->DungMay ? $item->DungMay : 0;
							$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca] = 1;
							$print[$item->Ref_MaThietBi] += $item->DungMay;
							$i++;
						}
					}
				} // End bao tri ko dinh ky

				if ($item->IOID != $oldPlanMainTainIOID)
				{
					$start = $startFormat;
					while ($start <= $endFormat)
					{
						$date = $start->format('Y-m-d');


						if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca]))
						{
							$item->DungMay = $item->DungMay ? $item->DungMay : 0;
							$print[$item->Ref_MaThietBi] += $item->DungMay;
							$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca] = 1;
						}
						$start = Qss_Lib_Date::add_date($start, 1);
					}
				}
				$oldPlanMainTainIOID = $item->IOID;
			}
		}
		return $print;

	}

	protected function getDowntimeFromWorkOrder($start, $end, $period, $refEquipArr = array())
	{

		$ret = array();

		if ($period)
		{
			$data = $this->_model->getDowntimeFromWorkOrder($start, $end, $period, $refEquipArr);
			foreach ($data as $item)
			{
				$key = '';
				switch ($period)
				{
					case 'D':
						$key = (int) $item->NgayDungMay;
						break;
					case 'W':
						$key = (int) $item->Tuan . '.' . (int) $item->Nam;
						break;
					case 'M':
						$key = (int) $item->Thang . '.' . (int) $item->Nam;
						break;
					case 'Q':
						$key = (int) $item->Quy . '.' . (int) $item->Nam;
						break;
					case 'Y':
						$key = (int) $item->Nam;
						break;
				}
				$ret[$item->Ref_MaThietBi][$key] = $item->DungMay;
			}
		} else
		{
			$data = $this->_model->getDowntimeFromWorkOrder($start, $end, 'D', $refEquipArr);
			foreach ($data as $item)
			{
				if (!isset($ret[$item->Ref_MaThietBi]))
				{
					$ret[$item->Ref_MaThietBi] = 0;
				}
				$ret[$item->Ref_MaThietBi] += $item->DungMay;
			}
		}
		return $ret;

	}


	/**
	 * Plan Downtime by equip (or line)
	 * @param inte $eqIOID 1 eq = 1 manufacturing line
	 * @param date $start
	 * @param date $end
	 * @return array [itemIOID] => Number
	 */
	protected function getScheduleDowntimeOfLine($eqIOID, $start, $end, $totalWorkHoursByCal = array())
	{
		$maintPlanModel   = new Qss_Model_Maintenance_Plan();
		$maintPlanModel->setFilterByEqIOID(@(int)$eqIOID);

		$periodic         = $maintPlanModel->getPlans();
		$solar            = new Qss_Model_Calendar_Solar();
		$scheduleDowntime = array();
		$lineID           = '';
		$dateRange        = $solar->createDateRangeArray($start, $end);
		$interval         = 1;
		$keepSpecialDate  = array();
		$tongSoGio        = count($dateRange) * 24;


		// Lay theo ngay dac biet
		foreach($periodic as $p)
		{
			if(!isset($scheduleDowntime[$p->LIOID])) $scheduleDowntime[$p->LIOID]= 0;
			if($p->NgayDacBiet != '' && $p->NgayDacBiet != '0000-00-00' )
			{
				$keepSpecialDate[$p->IFID][] = $p->NgayDacBiet;
			}
			$scheduleDowntime[$p->LIOID] += $p->DungMay;
		}

		// Lay theo dinh ky co tru ngay dac biet neu trung lap
		foreach ($periodic as $p)
		{
			$p->LapLai = isset($p->LapLai)?$p->LapLai:1;
				
			// Dung may cong them thoi gian nghi
			if(isset($totalWorkHoursByCal[$p->Ref_MaThietBi]))
			{
				if(!isset($scheduleDowntime[$p->LIOID]))
				{
					$scheduleDowntime[$p->LIOID] = $tongSoGio - $totalWorkHoursByCal[$p->Ref_MaThietBi];
				}
				else
				{
					$scheduleDowntime[$p->LIOID] += $tongSoGio - $totalWorkHoursByCal[$p->Ref_MaThietBi];
				}
			}
				
			if(!isset($scheduleDowntime[$p->LIOID])) $scheduleDowntime[$p->LIOID]= 0;
			if ($lineID != $p->IFID && !isset($scheduleDowntime[$p->LIOID]))
			{
				$scheduleDowntime[$p->LIOID] = 0;
			}
			switch ($p->MaKy)
			{
				case 'D':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						$next++;
						if(( (($interval%$next)==0) || $next == 1)
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
				case 'W':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						if (date('w', strtotime($date)) == $p->ThuTrongTuan)
						{
							$next++;
						}

						if (date('w', strtotime($date)) == $p->ThuTrongTuan
						&& ( (($interval%$next)==0)  || $next == 1 )
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
				case 'M':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						if (date('d', strtotime($date)) == $p->NgayTrongThang)
						{
							$next++;
						}

						if (date('d', strtotime($date)) == $p->NgayTrongThang
						&& ( (($interval%$next)==0)  || $next == 1 )
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
				case 'Y':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						$dateMicro = strtotime($date);
						$monthOf = date('m', $dateMicro);
						$dateOf = date('d', $dateMicro);

						if (($monthOf == $p->ThangTrongNam) && ($dateOf == $p->NgayTrongThang))
						{
							$next++;
						}

						if ((($monthOf == $p->ThangTrongNam) && ($dateOf == $p->NgayTrongThang))
						&& ( (($interval%$next)==0)  || $next == 1 )
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
			}
			$lineID = $p->IFID;
		}
		return $scheduleDowntime;

	}


	protected function countTimeForDailyRecord($period, $start, $end)
	{
		$ret = 0;
		switch ($period)
		{
			case Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY:
				$ret = Qss_Lib_Date::divDate($start, $end);
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
				$range  = $solar->createWeekRangeArray($start, $end);
				$ret = count((array)$range);
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
				$range = $solar->createMonthRangeArray($start, $end);

				foreach ($range as $year=>$monthArr)
				{
					$ret += count($monthArr);
				}
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_QUARTERLY:
				$range = $solar->createQuarterRangeArray($start, $end);
				$ret = count((array)$range);
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
				$range = $solar->createYearRangeArray($start, $end);
				$ret = count((array)$range);
				break;
		}
		return $ret;
	}

	/**
	 * Get tasks, materials by plan (Note: materials group by ifid, eq position)
	 * @param date $date YYYY-mm-dd
	 * @param int $location IOID
	 * @param int $maintype IOID
	 * @return array
	 */
	protected function getMaintainDataForMaintainPlan($date, $location, $maintype,&$row = 0)
	{
		$planModel    = new Qss_Model_Maintenance_Plan();

		$planModel->setFilterByLocIOID($location);
		if(is_array($maintype))
		{
			$planModel->setFilterByMaintainTypeIOIDs($maintype);
		}
		else
		{
			$planModel->setFilterByMaintainTypeIOID($maintype);
		}
		$planModel->setFilterByDate($date);

		$plans        = $planModel->getPlans();
		$retval       = array();
		$planIFIDArr  = array();
		$oldIFID      = '';
		$oldPosition  = '';
		$mOldIFID     = ''; // Material
		$mOldPosition = ''; // Material
		$tIndex       = 0;
		$mIndex       = 0;

		// ===== INIT MAINT PLAN ARRAY =====
		foreach ($plans as $item)
		{
			$row++;
			$planIFIDArr[]               = $item->IFID;
			$tempInfo                    = array();
			$tempInfo['IFID']            = $item->IFID;
			$tempInfo['DocNo']           = @$item->SoPhieu;
			$tempInfo['Code']            = $item->MaThietBi;
			$tempInfo['Name']            = $item->TenThietBi;
			$tempInfo['Type']            = $item->LoaiBaoTri;
			$tempInfo['Shift']           = $item->Ca;
			$tempInfo['WorkCenter']      = $item->TenDVBT;
			$tempInfo['Employee']        = $item->NguoiThucHien;
			$tempInfo['Line']            = 0;
			$retval[$item->IFID]['Info'] = $tempInfo;
			$retval[$item->IFID]['Component'] = array();
		}
		// ===== GET TASKS AND MATERIALS =====
		$tasks        = $planModel->getTasksByPlanIFID($planIFIDArr);
		$materials    = $planModel->getMaterialsByPlanIFIDGroupByPosition($planIFIDArr);

		// ===== ADD TASKS MAINT TO PLAN ARRAY  =====
		foreach($tasks as $item)
		{
			$row++;
			if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri)
			{
				$tIndex = 0;
			}
			$oldIFID     = $item->IFID;
			$oldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom            = array();
				$tempCom['BoPhan']  = $item->BoPhan;
				$tempCom['ViTri']   = $item->ViTri;
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp            = array();
			$temp['MoTa']    = $item->MoTaCongViec;
			$temp['GhiChu']  = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
			$temp['NguoiThucHien'] = $item->NguoiThucHien;
			$temp['Dat']     = $item->Dat;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $temp;
			$tIndex++;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan']       = $tIndex;
		}

		// ===== ADD MATERIALS TO MAINT PLAN ARRAY  =====
		foreach($materials as $item)
		{
			if($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri)
			{
				$mIndex = 0;
			}
			$mOldIFID     = $item->IFID;
			$mOldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$row++;
				$tempCom = array();
				$tempCom['BoPhan']    = $item->BoPhan;
				$tempCom['ViTri']     = $item->ViTri;
				$tempCom['RowSpan']   = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp           = array();
			$temp['VatTu']  = $item->VatTu;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
			$mIndex++;
		}
		return $retval;
	}

	protected function getMaintainDataForMaintainOrder($date, $location, $maintype,&$row = 0)
	{
		$orderModel   = new Qss_Model_Maintenance_Workorder();
		$orderModel->setFilterTimeForGetWorkOrdersFunc($date, $date);
		$orderModel->setFilterLocIOIDForGetWorkOrdersFunc($location);
		$orderModel->setFilterMaintTypeIOIDForGetWorkOrdersFunc($maintype);
		$orderModel->setOrderByEquipForGetWorkOrdersFunc();
		$orders       = $orderModel->getWorkOrders();
		//$date, $date, $location, $maintype, 0, 'vn', false, 0, 1, '', true, false

		$retval       = array();
		$ordersIFIDArr = array();
		$oldIFID      = '';
		$oldPosition  = '';
		$mOldIFID     = ''; // Material
		$mOldPosition = ''; // Material
		$oOldIFID     = '';
		$oOldPosition = '';
		$tIndex       = 0;
		$oIndex       = 0;
		$mIndex       = 0;

		// ===== INIT MAINT ORDER ARRAY =====
		foreach ($orders as $item)
		{
			$row++;
			$ordersIFIDArr[]             = $item->IFID_M759;
			$tempInfo                    = array();
			$tempInfo['IFID']            = $item->IFID_M759;
			$tempInfo['DocNo']           = @$item->SoPhieu;
			$tempInfo['Code']            = $item->MaThietBi;
			$tempInfo['Name']            = $item->TenThietBi;
			$tempInfo['Type']            = $item->LoaiBaoTri;
			$tempInfo['Shift']           = $item->Ca;
			$tempInfo['WorkCenter']      = $item->TenDVBT;
			$tempInfo['Employee']        = $item->NguoiThucHien;
			$tempInfo['Line']            = 0;
			$tempInfo['Status']          = $item->Name;
			$tempInfo['Review']          = $item->DanhGia;
			$retval[$item->IFID_M759]['Info'] = $tempInfo;
			$retval[$item->IFID_M759]['Component'] = array();
		}
		// ===== GET TASKS AND MATERIALS =====
		$tasks        = $orderModel->getTasksByIFID($ordersIFIDArr);
		$materials    = $orderModel->getMaterialsByIFIDGroupByIFID($ordersIFIDArr);
		// 		$outsources   = $orderModel->getOutsourcesByIFID($ordersIFIDArr);

		// ===== ADD TASKS TO MAINT ARRAY  =====
		
		foreach($tasks as $item)
		{
			$row++;
			if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri)
			{
				$tIndex = 0;
			}
			$oldIFID     = $item->IFID;
			$oldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom            = array();
				$tempCom['BoPhan']  = $item->BoPhan;
				$tempCom['ViTri']   = $item->ViTri;
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp            = array();
			$temp['MoTa']    = $item->MoTaCongViec;
			$temp['GhiChu']  = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
			$temp['NguoiThucHien'] = $item->NguoiThucHien;
			$temp['Dat']     = $item->Dat;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $temp;
			$tIndex++;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']  = $tIndex;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan'] = $tIndex;
		}

		// ===== ADD OUTSOURCES TO MAINT ARRAY  =====
		// 		foreach($outsources as $item)
		// 		{
		// 			if($oOldIFID != $item->IFID || $oOldPosition != @(int)$item->Ref_ViTri)
		// 			{
		// 				if(isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']))
		// 				{
		// 					$oIndex = $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex'];
		// 				}
		// 				else
		// 				{
		// 					$oIndex = 0;
		// 				}
		// 			}

		// 			$oOldIFID     = $item->IFID;
		// 			$oOldPosition = @(int)$item->Ref_ViTri;

		// 			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
		// 			{
		// 				$tempCom            = array();
		// 				$tempCom['BoPhan']  = @(string)$item->BoPhan;
		// 				$tempCom['ViTri']   = @(string)$item->ViTri;
		// 				$tempCom['RowSpan'] = 0;
		// 				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
		// 			}

		// 			$temp           = array();
		// 			$temp['MoTa']   = $item->MoTaCongViec;
		// 			$temp['GhiChu'] = '';
		// 			$temp['Dat']    = $item->HoanThanh;

		// 			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$oIndex] = $temp;
		// 			$oIndex++;

		// 			// Cộng dồn với phần công việc bảo trì
		// 			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan']  = $oIndex;
		// 		}
		// ===== ADD MATERIALS TO MAINT ORDER ARRAY  =====
		foreach($materials as $item)
		{
			if($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri)
			{
				$mIndex = 0;
			}
			$mOldIFID     = $item->IFID;
			$mOldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$row++;
				$tempCom = array();
				$tempCom['BoPhan']    = $item->BoPhan;
				$tempCom['ViTri']     = $item->ViTri;
				$tempCom['RowSpan']   = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp           = array();
			$temp['VatTu']  = $item->VatTu;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
			$mIndex++;
		}
		return $retval;
	}

	protected function getWorkOrderHistoryDetailOfEquipment($refEq)
	{
		$orderModel = new Qss_Model_Maintenance_Workorder();
		$orders     = $orderModel->getClosedWorkOrderByEquipment($refEq);

		$retval       = array();
		$ordersIFIDArr = array();
		$oldIFID      = '';
		$oldPosition  = '';
		$mOldIFID     = ''; // Material
		$mOldPosition = ''; // Material
		$oOldIFID     = '';
		$oOldPosition = '';
		$tIndex       = 0;
		$oIndex       = 0;
		$mIndex       = 0;

		// ===== INIT MAINT ORDER ARRAY =====
		foreach ($orders as $item)
		{
			$ordersIFIDArr[]             = $item->IFID_M759;
			$tempInfo                    = array();
			$tempInfo['IFID']            = $item->IFID_M759;
			$tempInfo['DocNo']           = @$item->SoPhieu;
			$tempInfo['Code']            = $item->MaThietBi;
			$tempInfo['Name']            = $item->TenThietBi;
			$tempInfo['Type']            = $item->LoaiBaoTri;
			$tempInfo['BoPhan']          = $item->BoPhan;
			$tempInfo['TypeCode']        = $item->Loai;
			$tempInfo['Shift']           = $item->Ca;
			$tempInfo['WorkCenter']      = $item->TenDVBT;
			$tempInfo['Employee']        = $item->NguoiThucHien;
			$tempInfo['Line']            = 0;
			$tempInfo['Status']          = $item->Name;
            $tempInfo['StatusNo']        = ($item->StepNo > 0)?$item->StepNo:1;
			$tempInfo['Review']          = $item->DanhGia;
			$tempInfo['ReqDate']         = $item->NgayYeuCau;
			$tempInfo['SDate']           = $item->NgayBatDau;
			$tempInfo['EDate']           = $item->Ngay;
			$tempInfo['Des']             = $item->MoTa;
			$tempInfo['Intervention']    = $item->XuLy;
			$tempInfo['MIndex']          = 0;
			$tempInfo['TIndex']          = 0;
			$tempInfo['NotMat']          = 0;
			$retval[$item->IFID_M759]['Info'] = $tempInfo;
			$retval[$item->IFID_M759]['Component'] = array();
		}

		// ===== GET TASKS AND MATERIALS =====
		$tasks        = $orderModel->getTasksByIFID($ordersIFIDArr);
		$materials    = $orderModel->getMaterialsByIFID($ordersIFIDArr);
		// 		$outsources   = $orderModel->getOutsourcesByIFID($ordersIFIDArr);

		// ===== ADD TASKS TO MAINT ARRAY  =====
		foreach($tasks as $item)
		{
			if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri)
			{
				if(isset($tIndex) && $tIndex && $oldIFID)
				{
					if(!isset($retval[$oldIFID]['Info']['TIndex']))
					{
						$retval[$oldIFID]['Info']['TIndex'] = $tIndex;
					}
					else
					{
						$retval[$oldIFID]['Info']['TIndex'] += $tIndex;
					}
				}
				$tIndex = 0;
			}

			$oldIFID     = $item->IFID;
			$oldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom            = array();
				$tempCom['BoPhan']  = $item->BoPhan;
				$tempCom['ViTri']   = $item->ViTri;
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp            = array();
			$temp['BoPhan']  = $item->BoPhan . ($item->ViTri?(' - '.$item->ViTri):'');
			$temp['MoTa']    = $item->MoTaCongViec;
			$temp['Ten']     = $item->Ten;
            $temp['NguoiThucHien']= @$item->NguoiThucHien;
			$temp['GhiChu']  = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
			$temp['Dat']     = $item->Dat;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $temp;
			$tIndex++;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']  = $tIndex;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan'] = $tIndex;
		}

		if(isset($tIndex) && $tIndex && $oldIFID)
		{
			if(!isset($retval[$oldIFID]['Info']['TIndex']))
			{
				$retval[$oldIFID]['Info']['TIndex'] = $tIndex;
			}
			else
			{
				$retval[$oldIFID]['Info']['TIndex'] += $tIndex;
			}
		}


		// ===== ADD MATERIALS TO MAINT ORDER ARRAY  =====
		foreach($materials as $item)
		{
			if($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri)
			{
				if(isset($mIndex) && $mIndex && $mOldIFID)
				{
					if(!isset($retval[$mOldIFID]['Info']['MIndex']))
					{
						$retval[$mOldIFID]['Info']['MIndex'] = $mIndex;
					}
					else
					{
						$retval[$mOldIFID]['Info']['MIndex'] += $mIndex;
					}
				}
				$mIndex = 0;
			}
			$mOldIFID     = $item->IFID;
			$mOldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom = array();
				$tempCom['BoPhan']    = $item->BoPhan;
				$tempCom['ViTri']     = $item->ViTri;
				$tempCom['RowSpan']   = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp                   = array();
			$temp['MaVatTu']        = $item->MaVatTu;
			$temp['TenVatTu']       = $item->TenVatTu;
			$temp['DonViTinh']      = $item->DonViTinh;
			$temp['SoLuong']        = $item->SoLuong;
			$temp['GhiChu']         = $item->GhiChu;
			$temp['DacTinhKyThuat'] = $item->DacTinhKyThuat;
			$temp['GhiChu']         = $item->GhiChu;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
			$mIndex++;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['MIndex']  = $mIndex;
		}

		if(isset($mIndex) && $mIndex && $mOldIFID)
		{
			if(!isset($retval[$mOldIFID]['Info']['MIndex']))
			{
				$retval[$mOldIFID]['Info']['MIndex'] = $mIndex;
			}
			else
			{
				$retval[$mOldIFID]['Info']['MIndex'] += $mIndex;
			}
		}

		return $retval;
	}

	protected function getWorkOrderHistoryOfEquipment($refEq)
	{
		$eqHistoryTemp = array();
		$eqHistory = $this->_model->getWorkOrderHistoryOfEquipment($refEq);

		$profile = '';
		if (Qss_Lib_System::fieldActive('OPhieuBaoTri', 'GhiVaoLyLich'))
		{
			$profile = ' and ifnull(GhiVaoLyLich,0) = 1 ';
		}

		/* // dung xoa code nay
		 $materialTemp = array();
		 $material = $this->_common->getObjectByIDArr($refEq,
		 array('code' => 'M759'
		 , 'table' => 'OVatTu'
		 , 'main' => 'OPhieuBaoTri'), 3, 'Ref_MaThietBi',
		 $profile);

		 $workTemp = array();
		 $work = $this->_common->getObjectByIDArr($refEq,
		 array('code' => 'M759'
		 , 'table' => 'OCongViecBTPBT'
		 , 'main' => 'OPhieuBaoTri'), 3, 'Ref_MaThietBi',
		 $profile);


		 // Create material array with ifid key
		 foreach ($material as $ma)
		 {
		 if ($ma->IFID_M759)
		 {
		 $materialTemp[$ma->IFID_M759][] = $ma;
		 }
		 }

		 // Create work array with ifid key
		 foreach ($work as $ma)
		 {
		 if ($ma->IFID_M759)
		 {
		 $workTemp[$ma->IFID_M759][] = $ma;
		 }
		 }
		 */


		foreach ($eqHistory as $item)
		{
			$eqHistoryTemp[$item->IFID_M759]['General'] = $item;
			/*  // dung xoa code nay
			 $eqHistoryTemp[$item->IFID_M759]['Material'] = isset($materialTemp[$item->IFID_M759]) ? $materialTemp[$item->IFID_M759] : array();
			 $eqHistoryTemp[$item->IFID_M759]['Work'] = isset($workTemp[$item->IFID_M759]) ? $workTemp[$item->IFID_M759] : array();
			 */
		}

		return $eqHistoryTemp;

	}

	protected function getEquipmentHistoryReportData($start, $end, $locID = 0, $eqGroupID = 0, $eqTypeID = 0)
	{
		$retval = array();
		$mainHistory = $this->_model->getWorkOrderHistory(
		$start, $end, $locID, $eqGroupID, $eqTypeID);

		foreach ($mainHistory as $item)
		{
			if ($item->IOID)
			{
				if (!isset($retval[$item->Ref_MaThietBi]))
				{
					$retval[$item->Ref_MaThietBi]['Code'] = $item->MaThietBi;
					$retval[$item->Ref_MaThietBi]['Name'] = $item->TenThietBi;
					$retval[$item->Ref_MaThietBi]['Group1'] = 0;
					$retval[$item->Ref_MaThietBi]['Total'] = 0;
				}


				$temp = array(
	                'IOID' => $item->IOID
				, 'No' => $item->DocNo
				, 'Date' => $item->DocDate
				, 'Type' => $item->Type
				, 'Class' => $item->Class
				, 'Step' => $item->Step
				, 'Class' => $item->Class
				, 'MType' => $item->LoaiBaoTri
				, 'Priority' => $item->MucDoUuTien
				, 'MaterialCost' => $item->GiaVatTu
				, 'ServiceCost' => $item->GiaDichVu
				, 'EmployeeCost' => $item->GiaNhanCong
				);
				$retval[$item->Ref_MaThietBi]['Detail'][$item->IOID] = $temp;
				$retval[$item->Ref_MaThietBi]['Detail'][$item->IOID]['Group2'] = 0;
				$retval[$item->Ref_MaThietBi]['Group1'] += 1;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->GiaVatTu;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->GiaDichVu;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->GiaNhanCong;
			}
		}
		return $retval;

	}

	/**
	 * @note: Dung rieng cho in lich su cai dat thiet bi
	 * @param object $equipments
	 * @param date $start Ngay bat dau tim kiem lich su cai dat (dd-mm-YYYY)
	 * @param date $end Ngay ket thuc tim kiem lich su cai dat (dd-mm-YYYY)
	 * @retun array tra ve mang cai dat di chuyen thiet bi da duoc sap xep ngay thang
	 * @use-in: equipmentLocation1Action()
	 */
	protected function getEquipmentForIntallHistoryReport($equipments, $start, $end)
	{

		if(!count((array)$equipments)) return array();
		$eqArray = array(); // Mang thiet bi da sap xep
		$i       = 0;

		foreach($equipments as $item)
		{
			$item->NgayBatDau  = $item->NgayBatDau?$item->NgayBatDau:$start;
			$item->NgayKetThuc = $item->NgayKetThuc?$item->NgayKetThuc:$end;
			$eqArray[$i]['MaThietBi']        = $item->MaThietBi;
			$eqArray[$i]['TenThietBi']        = $item->TenThietBi;
			$eqArray[$i]['Ref_MaThietBi']    = $item->Ref_MaThietBi;
			$eqArray[$i]['LoaiThietBi']      = $item->LoaiThietBi;
			$eqArray[$i]['LichLamViec']      = $item->CDLich;
			$eqArray[$i]['KhuVuc']           = $item->MaKVMoi;
			$eqArray[$i]['TuNgay']           = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
			$eqArray[$i]['DenNgay']          = Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc);
			//$eqArray[$i]['ThoiGian']         = $item->ThoiGian;
			$eqArray[$i]['Model']            = $item->Model;
			$eqArray[$i]['NhomThietBi']      = $item->NhomThietBi;
			$eqArray[$i]['HanBaoHanh']       = Qss_Lib_Date::mysqltodisplay($item->HanBaoHanh);
			$eqArray[$i]['HangBaoHanh']      = $item->HangBaoHanh;
			$eqArray[$i]['NgayDuaVaoSuDung'] = $NgayDuaVaoSuDung;
			$eqArray[$i]['NgayMua']          = $item->NgayMua;
			$eqArray[$i]['XuatXu']           = $item->XuatXu;
			$eqArray[$i]['NamSanXuat']       = $item->NamSanXuat;
			$eqArray[$i]['TBIOID']           = $item->TBIOID;
			$eqArray[$i]['ThoVanHanh']       = $item->ThoVanHanh;
			$eqArray[$i]['DuAn']             = $item->DuAn;
			$eqArray[$i]['SoGioHoatDong']    = $item->SoGioHoatDong;
			$i++;
		}
		return $eqArray;
	}

	/**
	 * @note: Dung rieng cho in lich su cai dat thiet bi
	 * @param object $equipments Danh sach thiet bi
	 * @return array Thong so ky thuat cua thiet bi
	 * @use-in: equipmentLocation1Action()
	 */
	protected function getParamOfEquip($equipments)
	{
		if(!count((array)$equipments)) return array();

		$equipModel     = new Qss_Model_Maintenance_Equipment();
		$parameterArray = array();
		$TBIOID         = array();

		foreach ($equipments as $item)
		{
			$TBIOID[] = $item->TBIOID;
		}

		// Thong so thiet bi
		$parametersSql  = (count($TBIOID))?$equipModel->getTechnicalParameterValues($TBIOID) : array();

		// Lay mang thong so theo thiet bi
		foreach ($parametersSql as $p)
		{
			$paramsPrintFormat = sprintf('- %1$s: %2$s %3$s (%4$s - %5$s) <br/>', $p->Ten, $p->GiaTri, $p->ChiSo
			, $p->GiaTriNN, $p->GiaTriLN);

			if (isset($parameterArray[$p->TBIOID]))
			{
				$parameterArray[$p->TBIOID] .= $paramsPrintFormat;
			} else
			{
				$parameterArray[$p->TBIOID]  = $paramsPrintFormat;
			}
		}

		return $parameterArray;
	}
}

?>