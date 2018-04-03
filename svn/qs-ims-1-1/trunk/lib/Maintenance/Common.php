<?php

class Qss_Lib_Maintenance_Common
{

	/**
	 *
	 * @return unknown_type
	 */
	public $_model;
	public $_common;

	public function __construct()
	{
		$this->_model = new Qss_Model_Extra_Maintenance();
		$this->_common = new Qss_Model_Extra_Extra();

	}

	// ***********************************************************
	// === Get downtime
	// ***********************************************************
	public function getTotalDowntime($start, $end, $period = '', $refEquipArr = array(), $return = 'M')
	{
		$downtime = $this->getDowntime($start, $end, $period, $refEquipArr, true);
		$retval = 0;

		foreach ($downtime as $eq => $date)
		{
			foreach ($date as $d => $dt)
			{
				$retval += $dt ? $dt : 0;
			}
		}

		if ($return == 'D') // tra ve ngay
		{
			return $retval / 1440;
		} elseif ($return == 'H') // tra ve gio
		{
			return $retval / 60;
		} elseif ($return == 'M')// tra ve phut
		{
			return $retval;
		} elseif ($return == 'S')// tra ve giay
		{
			return $retval * 60;
		}

	}

	public function getDowntime($start, $end, $period = '', $refEquipArr = array(), $add = false)
	{
		$refEquipArr = Qss_Lib_Extra::changeToArray($refEquipArr);
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

	private function getDowntimeFromPriorityMaintainPlan($start, $end, $period, $refEquipArr = array())
	{
		$startFormat = date_create($start);
		$endFormat = date_create($end);
		$data = $this->_model->getMaintenancePlanFull($refEquipArr);
		//$shift = $this->_modelQuery->getViewData('M701', 'OCa');

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
								case 'D': $key = $startSpe->format('Y-m-d');
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
								case 'D': $key = $start->format('Y-m-d');
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
								(($item->MaKy == 'D') || ($item->MaKy == 'W' && $week == $item->GiaTri) || ($item->MaKy == 'M' && $day == $item->Ngay)
								//|| ($item->MaKy == 'Q'
								//&& $monthNo == $item->ThangThu)
								|| ($item->MaKy == 'Y' && $day == $item->Ngay && $month == $item->Thang)
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

	private function getDowntimeFromWorkOrder($start, $end, $period, $refEquipArr = array())
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
						$key = Qss_Lib_Date::displaytomysql($item->NgayDungMay);
						break;
					case 'W':
						$key = (int) $item->Tuan . '.' . (int) $item->Nam;
						break;
					case 'M':
						$key = (int) $item->Thang . '.' . (int) $item->Nam;
						break;
					/* case 'Q':
					  $key = (int) $item->Quy . '.' . (int) $item->Nam;
					  break; */
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

	// ***********************************************************
	// === End Get Downtime
	// ***********************************************************
}
