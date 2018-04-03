<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M792Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Equip_Operation();
	}
    /**
	 * Tong hop ke hoach bao tri theo nam
	 */
	public function indexAction()
	{

	}

	public function showAction()
	{

		$year         = $this->params->requests->getParam('year', 0);
		if($year)
		{
			$equipIOID    = $this->params->requests->getParam('equipment', 0);
			$locationIOID = $this->params->requests->getParam('location', 0);
			$eqGroupIOID  = $this->params->requests->getParam('group', 0);
			$eqTypeIOID   = $this->params->requests->getParam('type', 0);
			$start        = $this->params->requests->getParam('start', '');
			$end          = $this->params->requests->getParam('end', '');

			$this->html->report           = $this->getMaintainPlanByYear(
				$year
				, $locationIOID
				, $equipIOID
				, $eqTypeIOID
				, $eqGroupIOID
				, Qss_Lib_Date::mysqltodisplay($start)
				, Qss_Lib_Date::mysqltodisplay($end)
			);
			$hieuchuanModel = new Qss_Model_Maintenance_Calibration();
			$hieuchuan = $hieuchuanModel->getCalibrationOrderByDateRange(Qss_Lib_Date::mysqltodisplay($start)
								, Qss_Lib_Date::mysqltodisplay($end));
			$arrHieuChuan = array();
			foreach ($arrHieuChuan as $item)
			{
				$arrHieuChuan[$item->EIOID] = $item;
			}
			$this->html->hieuchuan = $arrHieuChuan;
			$this->html->equipIOID        = $equipIOID;
			$this->html->equip            = $this->params->requests->getParam('equipmentStr', '');
			$this->html->locationIOID     = $locationIOID;
			$this->html->location         = $this->params->requests->getParam('locationStr', '');
			$this->html->eqGroupIOID      = $eqGroupIOID;
			$this->html->eqGroup          = $this->params->requests->getParam('groupStr', '');
			$this->html->eqTypeIOID       = $eqTypeIOID;
			$this->html->eqType           = $this->params->requests->getParam('typeStr', '');
			$this->html->year             = $year;
		}

	}

	public function getMaintainPlanByYear($year, $locIOID = 0, $eqIOID = 0, $eqTypeIOID = 0, $eqGroupIOID = 0 , $start = '', $end = '')
	{
		$retval      = array();
		$retval2     = array();
		$planIFIDArr = array();

		if($year)
		{
			$planModel = new Qss_Model_Maintenance_Plan();
			$oldPlanIOID = '';
			$eqIOIDArr = $eqIOID?array($eqIOID):array();
			$i         = 0;
			$lastDay   = $year.'-12-31';
			$lastDayTmp = date_create($lastDay);
			//$markCode   = array(); // danh dau cac loai thiet bi co cay lon hon bang 2 (tu level 2)

			// $appovedPeriod: Cac ky xet den trong (khong tinh chi so)
			$appovedPeriod = array(
				Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY
			, Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY
			);

			$plans = $planModel->getPlansForYearReportDataOfTopEquips(Qss_Lib_Date::displaytomysql($start)
									, Qss_Lib_Date::displaytomysql($end)
									,$eqIOIDArr
									,$locIOID
									,$eqTypeIOID
									,$eqGroupIOID);
			$sPlans = $planModel->getPlansForYearReportDataOfBelowTopEquips();

            // echo '<pre>'; print_r($plans); die;

			//$plansBelowLevelOne = $planModel->getPlansOfEquipBelowLevelOneForYearReportData();

			foreach($plans as $pl)
			{
				if($pl->IFID && !in_array($pl->IFID, $planIFIDArr))
				{
					$planIFIDArr[] = $pl->IFID;
				}
			}

			foreach($sPlans as $pl)
			{
				if($pl->IFID && !in_array($pl->IFID, $planIFIDArr))
				{
					$planIFIDArr[] = $pl->IFID;
				}
			}

			if(count($planIFIDArr))
			{
				$planTasks = $planModel->getTasksByPlanIFID($planIFIDArr);

				foreach($planTasks as $item)
				{
					$retval2[$item->IFID][] = array('Des'=>$item->MoTaCongViec, 'WorkCode'=>$item->CongViec);
				}
			}

			$oldEqIOID = '';
			foreach($plans as $pl)
			{
				$code = (int)$pl->EqGroupIOID.'-'.(int)$pl->EqTypeIOID.'-'.(int)$pl->LIOID.'-'.(int)$pl->CKIOID;

				$temp = $this->getElementForPlanYearReprot($year, $pl, $retval2);

				if(isset($temp[$code]))
				{
					$retval[$code] = $temp[$code];
				}
//				if($oldEqIOID != $pl->LIOID)
//				{
					foreach($sPlans as $pl2)
					{
						if($pl2->EqLft > $pl->EqLft && $pl2->EqRgt < $pl->EqRgt )
						{
							$code2 = (int)$pl2->EqGroupIOID.'-'.(int)$pl2->EqTypeIOID.'-'.(int)$pl2->LIOID.'-'.(int)$pl2->CKIOID;
							$temp2 = $this->getElementForPlanYearReprot($year, $pl2, $retval2);
							if(isset($temp2[$code2]) && isset($retval[$code]))
							{
								foreach($temp2[$code2] as $key=>$item)
								{
									$retval[$code][$pl->MTIOID]['Sub'][$code2][$key] = $temp2[$code2][$key];
								}
							}
						}
					}
//				}

				$oldEqIOID = $pl->LIOID;

			} // End foreach loop plans
		}
		return $retval;
	}


	public function getElementForPlanYearReprot($year, $pl, $retval2)
	{
		$lastDay   = $year.'-12-31';
		$lastDayTmp = date_create($lastDay);
		$retval     = array();

		$appovedPeriod = array(
			Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY
		, Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY
		);
		$code = (int)$pl->EqGroupIOID.'-'.(int)$pl->EqTypeIOID.'-'.(int)$pl->LIOID.'-'.(int)$pl->CKIOID;

		if( in_array($pl->MaKy, $appovedPeriod))
		{
			$ngay  = ((int)$pl->Ngay < 10)?'0'.(int)$pl->Ngay:(int)$pl->Ngay;
			$thang = ((int)$pl->Thang < 10)?'0'.(int)$pl->Thang:(int)$pl->Thang;
			$lap   = ((int)$pl->LapLai > 0)? (int)$pl->LapLai:1;
			// find first day: tim ngay bao tri dau tien
			// todo: Neu ngay bat dau ko co va khong bat buoc can lay ngay bat dau la ngay bao tri dau tien cua tb
			if($pl->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY)
			{
				$tempFirst = $year.'-01-'.$ngay;
				$first = $pl->NgayBatDau?$pl->NgayBatDau:$tempFirst;
				$yearOfFirst = date('Y', strtotime($first));
				$monthOfFirst = date('m', strtotime($first));

			}
			elseif($pl->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY)
			{
				$tempFirst = $year.'-'.$thang.'-'.$ngay;
				$first = $pl->NgayBatDau?$pl->NgayBatDau:$tempFirst;
				$yearOfFirst = date('Y', strtotime($first));
				$monthOfFirst = date('m', strtotime($first));
			}

//            if($yearOfFirst > $year)
//            {
//                continue;
//            }
			if($yearOfFirst <= $year)
			{

				if(!isset($retval[$code]) || !isset($retval[$code][$pl->MTIOID]))
				{
					$retval[$code][$pl->MTIOID]['EqGroupIOID'] = (int)$pl->EqGroupIOID;
					$retval[$code][$pl->MTIOID]['EqTypeIOID']  = (int)$pl->EqTypeIOID;
					$retval[$code][$pl->MTIOID]['CurrentEqTypeIOID']  = @(int)$pl->CurrentEqTypeIOID;
					$retval[$code][$pl->MTIOID]['EqTypeLevel'] = @$pl->EqTypeLevel;
					$retval[$code][$pl->MTIOID]['EqGroup']     = @$pl->EqGroup;
					$retval[$code][$pl->MTIOID]['EqType']      = @$pl->EqType;
					$retval[$code][$pl->MTIOID]['EqIOID']      = $pl->LIOID;
					$retval[$code][$pl->MTIOID]['EqCode']     = $pl->MaThietBi;
					$retval[$code][$pl->MTIOID]['EqName']     = $pl->TenThietBi;
					$retval[$code][$pl->MTIOID]['FACode']     = $pl->MaTaiSan;
					$retval[$code][$pl->MTIOID]['Serial']     = $pl->Serial;
					$retval[$code][$pl->MTIOID]['WorkCenter'] = $pl->TenDVBT;
					$retval[$code][$pl->MTIOID]['MaintCode']  = $pl->MaLoaiBaoTri;
					$retval[$code][$pl->MTIOID]['Lft']        = $pl->EqLft;
					$retval[$code][$pl->MTIOID]['Rgt']        = $pl->EqRgt;
					$retval[$code][$pl->MTIOID]['Des']        = array();//$pl->PlanTasks;
					$retval[$code][$pl->MTIOID]['Rowspan']    = 1;

					if(isset($retval2[$pl->IFID]))
					{
						$retval[$code][$pl->MTIOID]['Rowspan'] = count($retval2[$pl->IFID]);
						foreach($retval2[$pl->IFID] as $item2)
						{
							$retval[$code][$pl->MTIOID]['Des'][] = $item2['Des'];
							$retval[$code][$pl->MTIOID]['WorkCode'][] = $item2['WorkCode'];
						}
					}
					$retval[$code][$pl->MTIOID]['Interval']   = $lap;
					$retval[$code][$pl->MTIOID]['Period']     = $pl->MaKy;
					$retval[$code][$pl->MTIOID]['Start']      = $first;
					$retval[$code][$pl->MTIOID]['Month']      = array();
				}

				$first = $tempFirst;
				$tempStart = date_create($first);
				$next      = $first; // init
				$k         = 0;
				if((!$pl->NgayBatDau
						|| Qss_Lib_Date::compareTwoDate($next,$pl->NgayBatDau ) >= 0 )
					&& (!$pl->NgayKetThuc
						|| Qss_Lib_Date::compareTwoDate($next,$pl->NgayKetThuc) <= 0))
				{
					$retval[$code][$pl->MTIOID]['Month'][(int)date('m', strtotime($next))] = (Qss_Lib_Date::compareTwoDate(date('Y-m-d'), date('Y-m-d', strtotime($next))) == 1)?'O':'X';
				}

				while ($tempStart <= $lastDayTmp)
				{
					$k++;
					if($k > 365)
					{
						break;
					}

					if($pl->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY)
					{
						$next = date("Y-m-d", strtotime("+".$lap." months", strtotime($next)));
					}
					elseif($pl->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY)
					{
						$next = date("Y-m-d", strtotime("+".$lap." years", strtotime($next)));
					}

					$yearOfNext = date('Y', strtotime($next));

					if($yearOfNext == $yearOfFirst)
					{
						if((!$pl->NgayBatDau
								|| Qss_Lib_Date::compareTwoDate($next,$pl->NgayBatDau ) >= 0 )
							&& (!$pl->NgayKetThuc
								|| Qss_Lib_Date::compareTwoDate($next,$pl->NgayKetThuc) <= 0)) {
							//$retval[$code][$pl->MTIOID]['Month'][(int)date('m', strtotime($next))] = date('d-m-Y', strtotime($next));

							$retval[$code][$pl->MTIOID]['Month'][(int)date('m', strtotime($next))] = (Qss_Lib_Date::compareTwoDate(date('Y-m-d'),date('Y-m-d', strtotime($next))) > 0)?'O':'X';
							$tempStart = date_create($next);
						}
					}
					else
					{
						break;
					}
				}
			}
		}
		return $retval;
	}
}

?>