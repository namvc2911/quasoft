<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M781Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_model     = new Qss_Model_Maintenance_Breakdown();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	
	public function indexAction()
	{

	}
	public function showAction()
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
	private function getMaintWork()
	{
//		$common = new Qss_Model_Extra_Extra();
//		$filter['module'] = 'OCongViecBaoTri';
//		$works = $common->getDataset($filter);
        $mTable = Qss_Model_Db::Table('OCongViecBaoTri');
		$retval = array();

		foreach ($mTable->fetchAll() as $w)
		{
			$retval[$w->IOID] = $w;
		}
		return $retval;

	}


	/**
	 * VD:
	 * NVA: KN1 + KN2 (8h)
	 * NVB: KN1 (8h)
	 * KN1: Lớn nhất 16h, nhỏ nhất 8h (Tất cả nhân viên có một kỹ năng và là KN1)
	 * KN2: Lớn nhất 8h, nhỏ nhất 0h (Không có nhân viên cho kỹ năng)
	 *
	 * @param $start
	 * @param $end
	 * @return array
	 */
	private function getResouceTimeAvailableData($start, $end)
	{
		$maint             = new Qss_Model_Extra_Maintenance();
		$EmplWorkCal       = array(); // Mang luu tru lich lam viec nhan vien <Cac lich khong lap lai>
		$TotalByWorkCal    = array(); // Dem xem moi lich lam viec xuat hien bao nhieu lan
		$totalTimeEmpl     = 0;       // Tong thoi gian = tong cua tong thoi gian cho tung lich nhan voi so lan xuat hien cua lich ay
		$totalTimeByWCal   = array(); // Tong thoi gian cua tung lich = Thoi gian cho lich * so lan xuat hien lich
		$work              = array(); // cong viec
		$maxTimeByWork     = array(); // Thoi gian lon nhat theo lich lam viec(Cong tong lich lam viec cua cong viec)
		$minTimeByWork     = array(); // Thoi gian nho nhat theo lich lam viec(lay lich lam viec nho nhat ung voi cong viec)
		//$timeByWork      = array(); // Cac lich lam viec cho tung ky nang
		$minTimeByWorkTemp = array();
		$empl              = $maint->getWorkingEmpl(); // Lay nhan vien va cong viec cua nhan vien con hoat dong
		//$emplTemp          = array(); // Mang cac nhan vien chi co mot ky nang
		$oldEmp            = '';      // Giup loai bo cac nhan vien co tu 2 ky nang tro nen
		$onlyOneWork       = 0;       // Dung de tinh min, neu lam mot duy nhat cong viec => min, lon hon => min =0

		foreach ($empl as $ep)
		{
			if ($ep->Ref_LichLamViec)
			{
				// Lay mang nhan vien voi key là ifid
				if($ep->CongViecChinh)
				{
					$emplTemp[$ep->IFID] = $ep;
				}

				// Loai nhan vien ra neu nhan vien da ton tai
				// Tuong ưng voi viec lay nhan vien chi co mot ky nang
				//if ($oldEmp == $ep->IFID)
				//{
				//	unset($emplTemp[$ep->IFID]);
				//}

				// Dem so lan xuat hieen tung lịch làm việc
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


				// Lay danh sach lich lam viec
				if (!in_array($ep->Ref_LichLamViec, $EmplWorkCal))
				{
					$EmplWorkCal[] = $ep->Ref_LichLamViec;
				}

				// Dem theo cong viec + lich lam viec
				if (isset($work[$ep->Ref_LichLamViec][$ep->Ref_KyNang]))
				{
					$work[$ep->Ref_LichLamViec][$ep->Ref_KyNang] += 1;
				}
				else
				{
					$work[$ep->Ref_LichLamViec][$ep->Ref_KyNang] = 1;
				}
			}
			$oldEmp = $ep->IFID;
		}

		// Tong thoi gian lam viec trong mot khoang thoi gian theo lich
		$wCal = Qss_Lib_Extra::getTotalWCal(
			$EmplWorkCal
			, Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end)
		);

		// Tong thoi gian cua tat ca nhan vien co the lam $totalTimeEmpl
		foreach ($TotalByWorkCal as $refWCal => $count)
		{
			if (isset($wCal[$refWCal]))
			{
				// Init bien tổng thời gian của từng lịch làm việc
				if (!isset($totalTimeByWCal[$refWCal]))
				{
					$totalTimeByWCal[$refWCal] = 0;
				}

				$totalTimeEmpl            += $count * $wCal[$refWCal]; // Tong thoi gian lam viec
				$totalTimeByWCal[$refWCal] = $count * $wCal[$refWCal]; // Tong thoi gian lam viec theo tung lich


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
private function getResouceTimePlanData($start, $end)
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
}
?>