<?php

/**
 *
 * @author ThinhTuan
 *
 */
class Qss_Lib_Extra
{
	public static $averageCost;
	public static $averageCostByWarehouse;
	public static $limit = array('D' => 31, 'W' => 24, 'M' => 24, 'Q' => 24, 'Y' => 5);// sau bo
	
	public static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
	    $sort_col = array();
	    foreach ($arr as $key=> $row) {
	        $sort_col[$key] = $row[$col];
	    }
	
	    array_multisort($sort_col, $dir, $arr);
	}

	public static function getFieldDisplay($fieldVal, $fieldType)
    {
        $ret    = '';
        $common = new Qss_Model_Extra_Extra();
        
        // Luu y: truong radio luon tra ve string (chon 0)
        switch ( $fieldType )
        {
            case 1: // Short Description
            case 2: // Medium Description
            case 3: // Long Description
            case 12: // Mail
                $ret = Qss_Lib_Util::textToHtml($fieldVal);
            break;
            case 7: // Boolean
                $ret = $fieldVal?'TRUE':'FALSE';
            break;
            case 5: // Int
                $ret = @(int)$fieldVal;
            break;
            case 6: // Float
                $ret = @(double)$fieldVal;
            break;
            case 10: // Date
                $ret = Qss_Lib_Date::mysqltodisplay($fieldVal);
            break;
            case 11: // Money
                $cfield = new Qss_Model_CField();
                $currency = $cfield->getDataByCode($common->getDefaultCurrency());
                if ( $currency)
                {
                    $ret = is_numeric($fieldVal)?number_format($fieldVal / 1000, (int)$currency->Precision, $currency->DecPoint, $currency->ThousandsSep):$fieldVal;
                    //$ret .= $currency->Symbol;
                }
            break;
            default :
                $ret = $fieldVal;
            break;
        }       
        return $ret;
    }

    /**
     * Đếm số năm hoạt động du tren nam cua ngay bat dau va hien
     * Vd: 12/05/2012 den 12/05/2013 la hai nam
     * @param date $start Ngay bắt đầu <YYYY-mm-dd>
     */
    public static function countActiveYearsBaseOnYear($start)
    {
        $today   = date('Y-m-d');
        $compare = Qss_Lib_Date::compareTwoDate($today, $start);
        return ($compare == 1 && $start)?Qss_Lib_Date::divDate($start, $today, 'Y'):0;
    }
    
    /**
     * Đếm số năm hoạt động dua tren so ngay
     * Vd: 12/05/2012 den 12/05/2013 la tron mot nam
     * @param date $start Ngay bắt đầu <YYYY-mm-dd>
     */    
    public static function countActiveYearsBaseOnDate($start)
    {
        $today   = date('Y-m-d');
        $compare = Qss_Lib_Date::compareTwoDate($today, $start);
        return ($compare == 1 && $start)?Qss_Lib_Date::divDate($start, $today)/365:0;
    }

	// ***********************************************************************************************
	// *** Nhom ham xu ly array
	// ***********************************************************************************************
	/**
	 * Chuyen doi thanh mang
	 * @param mix $oneOrArray la mot mang hoac mot doan ky tu
	 * @return array
	 */
	public static function changeToArray($oneOrArray)
	{
		if (!is_array($oneOrArray))
		{
			$temp = $oneOrArray;
			$oneOrArray = array();
			$oneOrArray[] = $temp;
		}
		return $oneOrArray;
	}

	// ***********************************************************************************************
	// *** Nhom ham xu ly cac loi co ban: tra ve sai, lan lon giua cac ky tu dac biet
	// ***********************************************************************************************

	/**
	 * tra ve mot so
	 * @param mix $numberOrNot
	 * @param mix $default
	 * @return mix
	 */
	public static function returnNumber($numberOrNot, $default = 0)
	{
		return (is_numeric($numberOrNot) && $numberOrNot) ? $numberOrNot : $default;
	}

	public static function numberFormat($number, $decimals = 2,
	$dec_point = '.', $thousands_sep = ',')
	{
		if (is_double($number))
		{
			$retval = number_format($number, $decimals, $dec_point,
			$thousands_sep);
		}
		else
		{
			$retval = number_format($number, 0, $dec_point,
			$thousands_sep);
		}
		return $retval;
	}	

	// ***********************************************************************************************
	// *** Nhom ham xu thoi gian lich lam viec
	// ***********************************************************************************************
	/**
	 * Lay lich lam viec theo id lich
	 * @param mix $calendar
	 * @return array
	 */
	public static function getWorkingHoursPerWeekdays($calendar)
	{
		$model = new Qss_Model_Extra_WCalendar();
		$calendar = self::changeToArray($calendar);
		return $model->getWorkingHoursPerWeekdays($calendar);
	}

	/**
	 * Dem theo ngay trong tuan trong mot khoang thoi gian
	 * @param date $startDate 'YYYY-mm-dd'
	 * @param type $endDate 'YYYY-mm-dd'
	 * @return array array[weekday] = count_that_weekday
	 */
	public static function countWeekdays($startDate, $endDate)
	{
		$solar = new Qss_Model_Calendar_Solar;
		$days = $solar->createDateRangeArray($startDate, $endDate);
		$weekday = array();
		$weekday[0] = 0; $weekday[7]  = '';
		$weekday[1] = 0; $weekday[8]  = '';
		$weekday[2] = 0; $weekday[9]  = '';
		$weekday[3] = 0; $weekday[10] = '';
		$weekday[4] = 0; $weekday[11] = '';
		$weekday[5] = 0; $weekday[12] = '';
		$weekday[6] = 0; $weekday[13] = '';

		foreach ($days as $day)
		{
			$dw = date("w", strtotime($day));
			$weekday[$dw]     += 1;
            $weekday[($dw+7)] .= $day;
		}
		return $weekday;
	}

	/// Get working hours per shift of production lines
	/// Many calendar
	public static function getWorkingHoursPerShiftByCal($calendar)
	{
		$model = new Qss_Model_Extra_Extra();
		$retval = array();
		$weekdays = self::getWorkingHoursPerWeekdays($calendar);

		foreach ($weekdays as $item)
		{
			for ($i = 0; $i < 7; $i++)
			{
				for ($j = 1; $j <= 4; $j++)
				{
					$code = "Ngay{$i}_Ca{$j}";
					$code2 = "Ngay{$i}_RefCa{$j}";

					$item->$code2 = $item->$code2 ? $item->$code2 : 0;
					$retval[$item->LLVIOID][$i][$item->$code2] = abs($item->$code);
				}
			}
		}
		return $retval;
	}

	/// Lay lich dac biet
	public static function getLichDacBiet($refLichLamViecArray,
	$startDate = '')
	{
		$model = new Qss_Model_Extra_Extra();
		$retval = array();
		$refLichLamViecArray = self::changeToArray($refLichLamViecArray);

		foreach ($model->getLichDacBiet($refLichLamViecArray, $startDate) as
		$item)
		{
			$item->RefCa1 = $item->RefCa1 ? $item->RefCa1 : 0;
			$item->RefCa2 = $item->RefCa2 ? $item->RefCa2 : 0;
			$item->RefCa3 = $item->RefCa3 ? $item->RefCa3 : 0;
			$item->RefCa4 = $item->RefCa4 ? $item->RefCa4 : 0;

			$retval[$item->LLVIOID][$item->Ngay][$item->RefCa1] = abs($item->Ca1);
			$retval[$item->LLVIOID][$item->Ngay][$item->RefCa2] = abs($item->Ca2);
			$retval[$item->LLVIOID][$item->Ngay][$item->RefCa3] = abs($item->Ca3);
			$retval[$item->LLVIOID][$item->Ngay][$item->RefCa4] = abs($item->Ca4);
		}
		return $retval;
	}

	/// Get total working hours of one working calendar
	public static function getWorkingHours($calendar, $startDate, $endDate)
	{
		$weekdays = self::getWorkingHoursPerWeekdays($calendar);
		$countWeekdays = self::countWeekdays($startDate, $endDate);
		$total = 0;

		// Chỉ lặp một lần
		foreach ($weekdays as $val)
		{
			for ($i = 0; $i < 7; $i++)
			{
				$code = 'Ngay' . $i;
				$total += $val->$code * $countWeekdays[$i];
			}
		}
		return $total;
	}

	/// Get many total working hours of many calendar
	public static function getWorkingHoursOfManyCalendar($calendar,
	$startDate, $endDate)
	{
		$weekdays = self::getWorkingHoursPerWeekdays($calendar);
		$countWeekdays = self::countWeekdays($startDate, $endDate);
		$total = array();

		// Chỉ lặp một lần
		foreach ($weekdays as $val)
		{
			for ($i = 0; $i < 7; $i++)
			{
				$code = 'Ngay' . $i;
				if (isset($total[$val->LLVIOID]))
				{
					$total[$val->LLVIOID] += $val->$code * $countWeekdays[$i];
				}
				else
				{
					$total[$val->LLVIOID] = $val->$code * $countWeekdays[$i];
				}
			}
		}
		return $total;
	}

	/// Get total working hours of weekedays of working calendar
	public static function getWorkingHoursOfOneWeek($calendar)
	{
		$weekdays = self::getWorkingHoursPerWeekdays($calendar);
		$retval = array();

		// Only one loop
		foreach ($weekdays as $val)
		{
			for ($i = 0; $i < 7; $i++)
			{
				$retval[$i] = $val->{'Ngay' . $i};
			}
		}
		return $retval;
	}

	/// End getWorkingHoursOfOneWeek
	// Tinh tong thoi gian lam viec trong mot khoang thoi gian da tru di lich dac biet
	public static function getTotalWCal($refWCals, $startDate, $endDate)
	{
		$wCalEnd = array();
		$refWCals = self::changeToArray($refWCals);
		$wCal = self::getWorkingHoursPerShiftByCal($refWCals); // Lich lam viec thong thuong, @todo: chuyen thanh static
		$swCal = self::getLichDacBiet($refWCals, $startDate); // Lich lam viec dac biet, @todo: chuyen thanh static
		$total = self::getWorkingHoursOfManyCalendar($refWCals,
		$startDate, $endDate); // Tong thoi gian
		$start = date_create($startDate);
		$end = date_create($endDate);

		while ($start <= $end)
		{
			$weekday = $start->format('w'); // Ngay trong tuan
			$startToDate = $start->format('Y-m-d'); // Ngay


			foreach ($refWCals as $rWCal)
			{
				// init tong lich thong thuong cua nhung ngay co lich dac biet
				if (!isset($swTotal[$rWCal]))
				{
					$swTotal[$rWCal] = 0;
				}

				// init tong lich dac biet cua nhung ngay co lich dac biet
				if (!isset($wTotal[$rWCal]))
				{
					$wTotal[$rWCal] = 0;
				}

				// Tinh tong thoi gian theo lich lam viec va theo lich dac biet cua ngay co lich dac biet

				$swTotal[$rWCal]+=isset($swCal[$rWCal][$startToDate])?
					(double)array_sum($swCal[$rWCal][$startToDate]):
					0;
				$wTotal[$rWCal] += isset($swCal[$rWCal][$startToDate])?
					(double)array_sum($wCal[$rWCal][$weekday]) : 
					0;
			}
			$start = Qss_Lib_Date::add_date($start, 1);
		}

		foreach ($refWCals as $rWCal)
		{
			$wCalEnd[$rWCal] = ($total[$rWCal] - $wTotal[$rWCal]) + $swTotal[$rWCal];
		}
		return $wCalEnd;
	}

	public static function getWCalByDay($refWCals, $startDate, $endDate)
	{
		$wCalEnd = array();
		$refWCals = self::changeToArray($refWCals);
		$wCal = self::getWorkingHoursPerShiftByCal($refWCals); // Lich lam viec thong thuong, @todo: chuyen thanh static
		$swCal = self::getLichDacBiet($refWCals, $startDate); // Lich lam viec dac biet, @todo: chuyen thanh static
		$total = self::getWorkingHoursOfManyCalendar($refWCals,
		$startDate, $endDate); // Tong thoi gian
		$start = date_create($startDate);
		$end = date_create($endDate);
		$countTimeByDay = array();
		
		while ($start <= $end)
		{
			$weekday = $start->format('w'); // Ngay trong tuan
			$startToDate = $start->format('Y-m-d'); // Ngay

			foreach ($refWCals as $rWCal)
			{
				$countTimeByDay[$rWCal][$startToDate] = isset($swCal[$rWCal][$startToDate])?
					(double)array_sum($swCal[$rWCal][$startToDate]):
					(double)array_sum($wCal[$rWCal][$weekday]);
			}
			$start = Qss_Lib_Date::add_date($start, 1);
		}
		
		return $countTimeByDay;
	}
    
        /**
     * @todo: chuyen sang mot cai khac de dung chung
     * Lay lich lam viec thiet bi theo thoi gian
     * @return array lich lam viec cua thiet bi theo thoi gian
     */
    public static function getWorkingCals($mSDate, $mEDate, $eqIOID = 0)
    {
        $equipMoveModel = new Qss_Model_Maintenance_Equipmentworking();
        $equipMovement  = $equipMoveModel->getEquipmentMovement($mSDate, $mEDate, $eqIOID);
        $i              = 0;
        $oldEq          = '';
        $oldI           = '';
        $oldEnd         = '';
        $retval         = array();
        
        foreach($equipMovement as $item)
        {
            $start = $item->UseMove?$item->StartDate:$mSDate;
            $end   = $item->UseMove?$item->EndDate:$mEDate;
            
            if(Qss_Lib_Date::compareTwoDate($start, $mSDate) == -1)
            {
                $start = $mSDate;
            }

            if(Qss_Lib_Date::compareTwoDate($end, $mEDate) == 1)
            {
                $end = $mEDate;
            }            
            
            if($oldEq == $item->Ref_Equip && $oldEnd && (Qss_Lib_Date::divDate($oldEnd, $start) > 1))
            {
                $retval[$oldEq][$oldI]['End'] = date('Y-m-d', strtotime($start . " -1 days"));;
            }
            
	        $retval[$item->Ref_Equip][$i]['Ref_Eq']  = $item->Ref_Equip;
	        $retval[$item->Ref_Equip][$i]['Ref_Cal'] = $item->Ref_Cal;
	        $retval[$item->Ref_Equip][$i]['UseMove'] = $item->UseMove?true:false;
	        $retval[$item->Ref_Equip][$i]['Start']   = $start;
	        $retval[$item->Ref_Equip][$i]['End']     = $end;
            
            $oldEq  = $item->Ref_Equip;
            $oldI   = $i;
            $oldEnd = $end;
	        $i++;                    
        }
        return $retval;
    }

    /**
     * @param $mSDate
     * @param $mEDate
     * @param $eqIOID
     */
    public function getWorkingTimeOfEquip($mSDate, $mEDate, $eqIOID)
    {
        $equipMoveModel = new Qss_Model_Maintenance_Equipmentworking();
        $equipMovement  = $equipMoveModel->getEquipmentMovement($mSDate, $mEDate, $eqIOID);
        $refCal         = array(0);
        $retval         = 0;

        if(count($equipMovement))
        {
            foreach($equipMovement as $val)
            {
                $refCal[] = $val->Ref_Cal;
            }

            $time = self::getWorkingHoursOfManyCalendar($refCal ,$mSDate, $mEDate);

            if(count($time))
            {
                foreach($time as $refCalKey=>$totalTimeOfCal)
                {
                    $retval += $totalTimeOfCal;
                }
            }
        }

        return $retval;
    }

	// ***********************************************************************************************
	// *** Nhom ham xu ly thoi gian
	// ***********************************************************************************************

	/**
	 * Get end date by limit <$limitByPeriod>
	 * @param date $start <YYYY-mm-dd or dd-mm-YYYY>
	 * @param date $end  <YYYY-mm-dd or dd-mm-YYYY>
	 * @param char $period <D || W || M || Y> 
	 * @param array $limitByPeriod array('D'=>int, 'W'=>int, 'M'=>int, 'Y'=>int)
	 * @return date YYYY-mm-dd
	 */
	public static function getEndDate($start, $end, $period = 'D',$limitByPeriod = array())
	{
		$limitByPeriod = count($limitByPeriod)?$limitByPeriod:Qss_Lib_Extra_Const::$DATE_LIMIT;
		$microEndDate  = strtotime($end);
		$endYYYYmmdd   = date('Y-m-d', $microEndDate);
		
		switch ($period)
		{
			case 'D':
				$limitDate = (isset($limitByPeriod['D']) && $limitByPeriod['D']) ? date('Y-m-d',
				strtotime($start . " +{$limitByPeriod['D']} days - 1 days")) : $endYYYYmmdd;
				break;
			case 'W':
				$limitDate = (isset($limitByPeriod['W']) && $limitByPeriod['W']) ? date('Y-m-d',
				strtotime($start . " +{$limitByPeriod['W']} weeks -1 days")) : $endYYYYmmdd;
				break;
			case 'M':
				$limitDate = (isset($limitByPeriod['M']) && $limitByPeriod['M']) ? date('Y-m-d',
				strtotime($start . " +{$limitByPeriod['M']} months -1 days")) : $endYYYYmmdd;
				break;
			case 'Q':
				$limit = (isset($limitByPeriod['Q']) && $limitByPeriod['Q']) ? $limitByPeriod['Q']
				* 3 : 0;
				$limitDate = $limit ? date('Y-m-d',
				strtotime($start . " +{$limit} months -1 days")) : $endYYYYmmdd;
				break;
			case 'Y':
				$limitDate = (isset($limitByPeriod['Y']) && $limitByPeriod['Y']) ? date('Y-m-d',
				strtotime($start . " +{$limitByPeriod['Y']} years -1 days")) : $endYYYYmmdd;
				break;
		}

		$microLimitDate = strtotime($limitDate);
		return ($microEndDate > $microLimitDate) ? $limitDate: $endYYYYmmdd;
	}

	/// Get display date array
	/// @Note: Use getEndDate function to limit number of records
	//  @Note: Week of mysql start to 0, Week of php start to 01
	public static function displayRangeDate($start, $end, $period,
	$glue = array('D' => '/', 'W' => ' - ', 'M' => '-', 'Q' => '-', 'Y' => ''),
	$defaultGlue = '/')
	{

		$retval = array();
		$solar = new Qss_Model_Calendar_Solar();
		$i = 0;
		$iStartDate = strtotime($start);
		$iEndDate = strtotime($end);
		$glue = isset($glue[$period]) ? $glue[$period] : $defaultGlue;
		//		$start      = Qss_Lib_Date::displaytomysql($start);
		//		$end        = Qss_Lib_Date::displaytomysql($end);

		switch ($period)
		{
			case 'D': /* Hiển thị ngày */
				$dateRange = $solar->createDateRangeArray($start,
				$end);

				foreach ($dateRange as $date)
				{
					$microTime = strtotime($date);
					$dayInMonth = date('d', $microTime);
					$month = date('m', $microTime);
					$year = date('Y', $microTime);
					$weekday = date('w', $microTime);
					$sDayInMonth = (string) $dayInMonth;
					$sMonth = (string) $month;
					$key = $date;

					/*
					 $retval[$i]['Day']       = $dayInMonth; // Comming soon
					 $retval[$i]['Month']     = $month;// Comming soon
					 $retval[$i]['Year']      = $year;// Comming soon
					 */
					$retval[$key]['Date'] = date('d-m-Y',
					$microTime);
					$retval[$key]['Weekday'] = $weekday;
					$retval[$key]['MicroTime'] = $microTime;
					$retval[$key]['Key'] = $key;
					$retval[$key]['FullDisplay'] = "{$dayInMonth}{$glue}{$sMonth}{$glue}{$year}";
					$retval[$key]['Period'] = 'D';
					$retval[$key]['Start'] = date('Y-m-d',
					$microTime);
					$retval[$key]['End'] = date('Y-m-d',
					$microTime);
					$retval[$key]['SimpleDisplay'] = $sDayInMonth;

					/* Hien thi ngay trong thang kieu gian luoc, vd ngay:01/08, 01 */
					if ($dayInMonth == 1)
					{
						if ($month == 1)
						{
							$retval[$key]['Display'] = "{$dayInMonth}{$glue}{$sMonth}{$glue}{$year}";
						}
						else
						{
							$retval[$key]['Display'] = "{$sDayInMonth}{$glue}{$sMonth}";
						}
					}
					else
					{
						$retval[$key]['Display'] = "{$sDayInMonth}";
					}

					if ($i == 0)
					{
						$retval[$key]['Display'] = $retval[$key]['FullDisplay'];
					}

					$i++;
				}
				break;
			case 'W':
				$dateRange = $solar->createWeekRangeArray($start,
				$end);
				$countWeek = count($dateRange);


				foreach ($dateRange as $week)
				{
					$rangeWeek = $solar->getDateRangeByWeek($week['week'],
					$week['year']);
					$startOfWeek = strtotime($rangeWeek[0]);
					$endOfWeek = strtotime($rangeWeek[1]);


					if ($i == 0 && ($startOfWeek < $iStartDate))
					{
						$rangeWeek[0] = date('d-m-Y',
						$iStartDate);
					}
					if ($i == ($countWeek - 1) && ($endOfWeek
					> $iEndDate))
					{
						$rangeWeek[1] = date('d-m-Y',
						$iEndDate);
					}

					$changePHPWeekToMySQLWeek = (int) $week['week'];
					$key = $changePHPWeekToMySQLWeek . '.' . (int) $week['year'];
					$retval[$key]['Display'] = "{$rangeWeek[0]}{$glue}{$rangeWeek[1]}";
					$retval[$key]['FullDisplay'] = "{$rangeWeek[0]}{$glue}{$rangeWeek[1]}";
					$retval[$key]['SimpleDisplay'] = $changePHPWeekToMySQLWeek;
					$retval[$key]['Key'] = $key;
					$retval[$key]['StartWeek'] = $rangeWeek[0];
					$retval[$key]['EndWeek'] = $rangeWeek[1];
					$retval[$key]['Period'] = 'W';
					$retval[$key]['Start'] = date('Y-m-d',
					strtotime($rangeWeek[0]));
					$retval[$key]['End'] = date('Y-m-d',
					strtotime($rangeWeek[1]));
					$i++;
				}
				break;
			case 'M':
				$dateRange = $solar->createMonthRangeArray($start,
				$end);

				foreach ($dateRange as $year => $month)
				{
					foreach ($month as $m)
					{
						$key = (int) $m . '.' . (int) $year;
						$retval[$key]['Display'] = ($m == '01'
						|| $m == 1) ? "{$m}{$glue}{$year}" : $m;
						$retval[$key]['FullDisplay'] = "{$m}{$glue}{$year}";
						$retval[$key]['SimpleDisplay'] = $m;
						$retval[$key]['Key'] = $key;
						$retval[$key]['Period'] = 'M';
						#NOW
						$m1 = ((int) $m < 10) ? '0' . (int) $m : $m;
						$retval[$key]['Start'] = $year . '-' . $m1 . '-01';
						$retval[$key]['End'] = date('Y-m-t',
						strtotime($retval[$key]['Start']));

						if ($i == 0)
						{
							$retval[$key]['Display'] = $retval[$key]['FullDisplay'];
						}

						$i++;
					}
				}
				break;
			case 'Q':
				$dateRange = $solar->createQuarterRangeArray($start,$end);
				foreach ($dateRange as $item)
				{
					
					$quater = $item['quarter'];
					$year = $item['year'];
					$key = (int) $quater . '.' . (int) $year;
					$retval[$key]['Display'] = ($quater == '01' || $quater == 1) ? "{$quater}{$glue}{$year}" : $quater;
					$retval[$key]['FullDisplay'] = "{$quater}{$glue}{$year}";
					$retval[$key]['SimpleDisplay'] = $year;
					$retval[$key]['Key'] = $key;
					$retval[$key]['Period'] = 'Q';
						#NOW
					$m1 = ((int) $quater < 10) ? '0' . (int) $quater : $quater;
					$retval[$key]['Start'] = $year . '-' . ((int)$quater + (((int)$quater)-1)*3) . '-01';
					$retval[$key]['End'] = date('Y-m-t',strtotime($retval[$key]['Start']));
					if ($i == 0)
					{
						$retval[$key]['Display'] = $retval[$key]['FullDisplay'];
					}
					$i++;
				}
				break;
			case 'Y':
				$dateRange = $solar->createYearRangeArray($start,
				$end);

				foreach ($dateRange as $year)
				{
					$key = (int) $year;
					$retval[$key]['Display'] = $year;
					$retval[$key]['FullDisplay'] = $year;
					$retval[$key]['Key'] = $key;
					$retval[$key]['Period'] = 'Y';
					$retval[$key]['Start'] = $year . '-01-01';
					$retval[$key]['End'] = $year . '-12-31';
					$i++;
				}
				break;
		}

		return $retval;
	}

	// ***********************************************************************************************
	// *** Nhom ham xu ly tien te
	// ***********************************************************************************************	
		/// Get currency exchange rate
	public static function getCurrencyExchangeRate($fromCurrency,
	$toCurrency)
	{
		$model = new Qss_Model_Extra_Extra();
		$rate = 1; /* Currencies default rate */

		if ($fromCurrency != $toCurrency)
		{
			$rate = $model->getTable(array('TyGia'),
                                'OBangTyGia',
			array('LoaiTien' => $fromCurrency,
                            'LoaiTienQuyDoi' => $toCurrency,
                            'HoatDong' => 1), array(' NgayBatDau DESC '), 1, 1);
			$rate = $rate ? $rate->TyGia : 1;
		}
		return $rate;
	}

	/// End getCurrencyExchangeRate
	/// Calculate average cost
	/// @var: inOrOut, 1 input, 0 output
	public static function calculateAverageCost($refItem, $refAttribute,
	$newPrice, $newQty, $moneyCode, $inOrOut = 1, $refWarehouse = 0)
	{
		$model = new Qss_Model_Extra_Extra();
		$defaultMoney = $model->getDefaultCurrency();
		$retval = 0; /* Return average cost */
		$i = 0;
		$common = new Qss_Model_Extra_Extra();

		if ($inOrOut === 1) /* Input */
		{

			/* Get currency exchange rate */
			$rate = self::getCurrencyExchangeRate($moneyCode,
			$defaultMoney);

			if (!$refWarehouse && isset(self::$averageCost[$refItem][$refAttribute])) /* Exists item */
			{
				/*  Use static variable value for calculate average cost */
				$oldPrice = self::$averageCost[$refItem][$refAttribute]['Gia'];
				$oldQty = self::$averageCost[$refItem][$refAttribute]['SoLuong'];
			}
			elseif ($refWarehouse && isset(self::$averageCostByWarehouse[$refItem][$refAttribute][$refWarehouse]))
			{
				$oldPrice = self::$averageCostByWarehouse[$refItem][$refAttribute][$refWarehouse]['Gia'];
				$oldQty = self::$averageCostByWarehouse[$refItem][$refAttribute][$refWarehouse]['SoLuong'];
			}
			else /* Not exists item */
			{
				/*  Set static variable if not exists */
				if ($refWarehouse)
				{
					if (!isset(self::$averageCostByWarehouse[$refItem][$refAttribute][$refWarehouse]))
					self::$averageCostByWarehouse[$refItem][$refAttribute][$refWarehouse] = array();
				}
				else
				{
					if (!isset(self::$averageCost[$refItem][$refAttribute]))
					self::$averageCost[$refItem][$refAttribute] = array();
				}

				$lastest = $model->getLastestAverageCostOfItem($refItem,
				$refAttribute, $refWarehouse); /* Lastest Average */

				if ($lastest)
				{
					$oldPrice = $lastest->GiaVon;
					$oldQty = $lastest->SoLuong;
				}
				else
				{
					$sp = $common->getTable(array('*'),
                                                'OSanPham',
					array('IOID' => $refItem),
					array(), 'NO_LIMIT', 1);
					$oldPrice = @$sp->GiaMua;
					$oldQty = 1;
				}
			}
			if ($newPrice == null)
			{
				$formula = $oldPrice;
			}
			else
			{
				$newPrice = $newPrice * $rate;
				$formula = ((($oldPrice * $oldQty) + ($newPrice * $newQty))
				/ ($oldQty + $newQty));
			}
			$retval = Qss_Lib_Util::formatMoney($formula,
			$defaultMoney);
			/* Set static variable value */
			if ($refWarehouse)
			{
				self::$averageCostByWarehouse[$refItem][$refAttribute][$refWarehouse]['Gia'] = $formula;
				self::$averageCostByWarehouse[$refItem][$refAttribute][$refWarehouse]['SoLuong'] = $oldQty
				+ $newQty;
			}
			else
			{
				self::$averageCost[$refItem][$refAttribute]['Gia'] = $formula;
				self::$averageCost[$refItem][$refAttribute]['SoLuong'] = $oldQty
				+ $newQty;
			}
		}
		elseif ($inOrOut === 0) /* Output */
		{
			/* Get currency exchange rate */
			$rate = self::getCurrencyExchangeRate($defaultMoney,
			$moneyCode);

			/* Get lastet average cost */
			$lastest = $model->getLastestAverageCostOfItem($refItem,
			$refAttribute, $refWarehouse); /* Lastest Average */
			$oldPrice = ($lastest) ? $lastest->GiaVon : 0;
			$retval = Qss_Lib_Util::formatMoney(($oldPrice * $rate),
			$moneyCode);
		}
		return $retval;
	}

	// ***********************************************************************************************
	// *** Nhom ham xu ly chung tu: Chuyen vao docment
	// ***********************************************************************************************
	/**
	 *
	 * @param mix $object OBJECT
	 * @return string so chung tu lon nhat hien tai
	 */
	public static function getDocumentNo($object,$condition='')
	{
		$model = new Qss_Model_Extra_Document($object);
		return $model->getDocumentNo($condition);
	}

	// ***********************************************************************************************
	// *** Nhom ham xu ly form
	// ***********************************************************************************************
	/**
	 * Kiem tra xem mot field co ton tai trong bang khong
	 * @param string $table
	 * @param string $field
	 * @return boolean true/false - exists/not exists
	 */
	public static function checkFieldExists($table, $field)
	{
		$model = new Qss_Model_Extra_Extra();
		return $model->checkFieldExists($table, $field) ? TRUE : FALSE;
	}

	// ***********************************************************************************************
	// *** Khong phan loai
	// ***********************************************************************************************

	/// Get line no
	public static function getLineNo($lastestLineNo)
	{
		$lastOf = (int) substr($lastestLineNo, -1); // return end positon value
		return (int) $lastestLineNo + (10 - $lastOf);
	}

	public static function loadHeaderIniFile()
	{
		$retval        = array();
		$url           = $_SERVER['REQUEST_URI'];
		$urlToFileName = $url?trim(str_replace(array('/','\\'), array('_', '_'), (string)$url), '_'):'';
		$pathToIniFile = $urlToFileName?
			'file://'.QSS_DATA_DIR.'/'
			.'template/'
			.$urlToFileName.'.ini':'';
		if(!file_exists($pathToIniFile))
		{
			$pathToIniFile = $urlToFileName?
				'file://'.QSS_DATA_BASE.'/'
				.'template/'
				.$urlToFileName.'.ini':'';
		}
		if($pathToIniFile && file_exists($pathToIniFile))
		{
			$ini_array = parse_ini_file($pathToIniFile);
			
			if(count($ini_array))
			{
				$retval = $ini_array;
			}
		}
		return $retval;
	}

	static public function getDefaultCurrency()
	{
		$model = new Qss_Model_Extra_Extra();
		return $model->getDefaultCurrency();
	}
}
?>