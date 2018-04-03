<?php
// @todo: sua lai class nay
class Qss_Lib_Extra_WCalendar
{
	const EQUAL_START = 'EQUAL_START';
	const EQUAL_END   = 'EQUAL_END';
	const IN_RANGE    = 'IN_RANGE';
	
	public $wCalendars        = array(); // Lich lam viec array(int, int)
	public $eqCalendarsByDate = array(); // Lich theo ngay, array[eqIOID][start_YYYY-mm-dd][end_]
	public $locIOIDArr        = array();
	public $eqIOIDArr         = array(); // IOID cua thiet bi array(int, int)
	public $countWeekday      = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0); 
	public $startDate         = ''; // Ngay bat dau YYYY-mm-dd
	public $endDate           = ''; // Ngay ket thuc YYYY-mm-dd
	public $weekdayWorkTime; // Thời gian làm việc theo từng ngày từng ca
	public $specialWorkTime; // Thời gian làm việc theo lịch làm việc từng ngày từng ca
	public $wCalendarModel;  // Model về thời gian làm việc
	
	public function __construct() 
	{
		$this->wCalendarModel = new Qss_Model_Extra_WCalendar();
	}
	
	/**
	 * Init data cho lịch làm việc của thiết bị
	 * @param type $equip
	 */
	public function initEquipCals($eqIOIDArr = array(), $locIOIDArr = array(), $startDate = '', $endDate = '')
	{
		$this->setEqIOIDArr($eqIOIDArr);
		$this->setLocIOIDArr($locIOIDArr);
		$this->setStartDate($startDate);
		$this->setEndDate($endDate);		
		$this->setEquipWCals();
		$this->countWeekdays();
		$this->setCals();
	}
	
	public function setLocIOIDArr($locIOIDArr)
	{
		$this->locIOIDArr = $locIOIDArr;
	}
	
	public function setEqIOIDArr($eqIOIDArr)
	{
		$this->eqIOIDArr = $eqIOIDArr;
	}	
	
	public function setLocIOIDArrFromOne($locIOID)
	{
		$this->locIOIDArr = array($locIOID);
	}
	
	public function setEqIOIDArrFromOne($eqIOID)
	{
		$this->eqIOIDArr = array($locIOID);
	}		
	
	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
	}
	
	public function setEndDate($endDate)
	{
		$this->endDate = $endDate;
	}
	
	public function setCals()
	{
		$this->weekdayWorkTime = $this->wCalendarModel->getWorkingHoursPerWeekdays($this->wCalendars);
		$this->specialWorkTime = $this->wCalendarModel->getSpecialCalendars($this->wCalendars, $this->startDate);
	}

	
	/**
	 * Lấy lịch làm việc trong một khoảng thời gian của thiết bị
	 * @return array equip calendars array[eqIOID][start_YYYY-mm-dd][end_]
	 * @return2 array calendars array array(ref_lich)
	 */
	public function setEquipWCals()
	{
		$wCals = $this->wCalendarModel->getWCalOfEquipByTimeByEqList($this->eqIOIDArr
			, $this->startDate, $this->endDate);
		
		foreach ($wCals as $w)
		{
			$this->eqCalendarsByDate[$w->Ref_MaThietBi]
				[$w->NgayBatDau][$w->NgayKetThuc] = $w->Ref_LichLamViec;
			$this->wCalendars[] = $w->Ref_LichLamViec;
		}
	}

	/**
	 * Đếm tổng số ngày theo các ngày thứ trong tuần.
	 * @param date $this->startDate 'YYYY-mm-dd'
	 * @param type $this->endDate 'YYYY-mm-dd'
	 * @return array array[weekday] = count_that_weekday
	 */
	public function countWeekdays()
	{
		$solar   = new Qss_Model_Calendar_Solar;
		$days    = $solar->createDateRangeArray($this->startDate, $this->endDate);
		foreach ($days as $day) { $this->countWeekday[date("w", strtotime($day))] += 1; }
	}


	/**
	 * Thời gian làm việc của từng ca theo ngày theo lịch làm việc
	 * @param array $this->wCalendars
	 * @return array 
	 */
	public  function getWorkingHoursPerShiftByCal()
	{
		$retval   = array();
		foreach ($this->weekdayWorkTime as $item) {
			for ($i = 0; $i < 7; $i++) {
				for ($j = 1; $j <= 4; $j++) {
					$code   = "Ngay{$i}_Ca{$j}";
					$code2  = "Ngay{$i}_RefCa{$j}";
					$retval[$item->LLVIOID][$i][@(int)$item->$code2] = abs($item->$code);
				}
			}
		}
		return $retval;
	}
	
	/**
	 * Thời gian làm việc của từng ca theo ngày theo lịch làm việc
	 * @param array $this->wCalendars
	 * @return array 
	 */
	public  function getWorkingHoursPerShiftByCalWithShiftDetail()
	{
		$retval   = array();
		foreach ($this->weekdayWorkTime as $item) {
			for ($i = 0; $i < 7; $i++) {
				for ($j = 1; $j <= 4; $j++) {
					$code   = "Ngay{$i}_Ca{$j}";
					$start  = "Ngay{$i}_Ca{$j}_Start";
					$end    = "Ngay{$i}_Ca{$j}_End";
					$code2  = "Ngay{$i}_RefCa{$j}";
					$retval[$item->LLVIOID][$i][@(int)$item->$code2]['Time']  = $item->$code;
					$retval[$item->LLVIOID][$i][@(int)$item->$code2]['Start'] = $item->$start;
					$retval[$item->LLVIOID][$i][@(int)$item->$code2]['End']   = $item->$end;
				}
			}
		}
		return $retval;
	}	

	/**
	 * Lịch làm việc đặc biệt theo ngày
	 * @return array array[id_lich][YYYY-mm-dd][ref_ca] = thời gian
	 */
	public  function getSpecialCaldendars()
	{
		$retval           = array();
		foreach ($this->specialWorkTime as $item)
		{
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa1] = abs($item->Ca1);
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa2] = abs($item->Ca2);
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa3] = abs($item->Ca3);
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa4] = abs($item->Ca4);
		}
		return $retval;
	}
	
	/**
	 * Lịch làm việc đặc biệt theo ngày
	 * @return array array[id_lich][YYYY-mm-dd][ref_ca] = thời gian
	 */
	public  function getSpecialCaldendarsWithShiftDetail()
	{
		$retval           = array();
		foreach ($this->specialWorkTime as $item)
		{
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa1]['Time']  = $item->Ca1;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa1]['Start'] = $item->Ca1_Start;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa1]['End']   = $item->Ca1_End;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa2]['Time']  = $item->Ca2;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa2]['Start'] = $item->Ca2_Start;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa2]['End']   = $item->Ca2_End;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa3]['Time']  = $item->Ca3;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa3]['Start'] = $item->Ca3_Start;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa3]['End']   = $item->Ca3_End;	
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa4]['Time']  = $item->Ca4;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa4]['Start'] = $item->Ca4_Start;
			$retval[$item->LLVIOID][$item->Ngay][@(int)$item->RefCa4]['End']   = $item->Ca4_End;
		}
		return $retval;
	}	

	/**
	 * Lấy tổng thời gian không bao gồm lịch đặc biệt khi truyền duy nhất một lịch lv
	 * @param  $this->wCalendars
	 * @param type $this->startDate
	 * @param type $this->endDate
	 * @return type
	 */
	public  function getWorkingHours() 
	{
		$total         = 0;

		// Chỉ lặp một lần
		foreach ($this->weekdayWorkTime as $val) {
			for ($i = 0; $i < 7; $i++) {
				$code = 'Ngay' . $i;
				$total += $val->$code * $this->countWeekday[$i];
			}
		}
		return $total;
	}

	/**
	 * Lấy tổng thời gian làm việc của từng lịch không bao gồm lịch đặc biệt
	 * @param  $this->wCalendars
	 * @param type $this->startDate
	 * @param type $this->endDate
	 * @return type
	 */
	public  function getWorkingHoursOfManyCalendars()
	{
		$total         = array();

		foreach ($this->weekdayWorkTime as $val) {
			for ($i = 0; $i < 7; $i++)  {
				$code = 'Ngay' . $i;
				if (isset($total[$val->LLVIOID])) {
					$total[$val->LLVIOID] += $val->$code * $this->countWeekday[$i];
				}
				else {
					$total[$val->LLVIOID] = $val->$code * $this->countWeekday[$i];
				}
			}
		}
		return $total;
	}

	/**
	 * Lấy thời gian làm việc của một lịch trong một tuần
	 * @return type
	 */
	public  function getWorkingHoursOfOneWeek()
	{
		$retval = array();

		// Only one loop
		foreach ($this->weekdayWorkTime as $val)
		{
			for ($i = 0; $i < 7; $i++)
			{
				$retval[$i] = $val->{'Ngay' . $i};
			}
		}
		return $retval;
	}

	/**
	 * Lấy tổng thời gian làm việc trong một khoảng ngày của thiết bị (Đã tính lịch đặc biệt)
	 * @return array array[Ref_Cal] = Total time
	 */
	public  function getTotalWCal()
	{
		$wCalEnd = array();
		$wCal = $this->getWorkingHoursPerShiftByCal(); // Lich lam viec thong thuong, @todo: chuyen thanh 
		$swCal = $this->getSpecialCaldendars(); // Lich lam viec dac biet, @todo: chuyen thanh 
		$total = $this->getWorkingHoursOfManyCalendars(); // Tong thoi gian
		$start = date_create($this->startDate);
		$end   = date_create($this->endDate);

		while ($start <= $end)
		{
			$weekday     = $start->format('w'); // Ngay trong tuan
			$startToDate = $start->format('Y-m-d'); // Ngay


			foreach ($this->wCalendars as $rWCal)
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

		foreach ($this->wCalendars as $rWCal)
		{
			$wCalEnd[$rWCal] = ($total[$rWCal] - $wTotal[$rWCal]) + $swTotal[$rWCal];
		}
		return $wCalEnd;
	}

	/**
	 * Lấy tổng thời gian làm việc trong một khoảng ngày của thiết bị (Đã tính lịch đặc biệt)
	 * theo từng ngày
	 * @return array $countTimeByDay[ref_Cal][YYYY-mm-dd] = Total
	 */	
	public  function getWCalByDay()
	{
		$wCalEnd = array();
		$wCal    = $this->getWorkingHoursPerShiftByCal(); // Lich lam viec thong thuong
		$swCal   = $this->getSpecialCaldendars(); // Lich lam viec dac biet
		$total   = $this->getWorkingHoursOfManyCalendars(); // Tong thoi gian
		$start   = date_create($this->startDate);
		$end     = date_create($this->endDate);
		$countTimeByDay = array();

		while ($start <= $end)
		{
			$weekday     = $start->format('w'); // Ngay trong tuan
			$startToDate = $start->format('Y-m-d'); // Ngay


			foreach ($this->wCalendars as $rWCal)
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
	 * Lấy tổng thời gian làm việc trong một khoảng ngày của thiết bị (Đã tính lịch đặc biệt)
	 * theo từng ngày
	 * @return array $countTimeByDay[ref_Cal][YYYY-mm-dd] = Total
	 */	
	public  function getWCalByDayWithShiftDetail()
	{
		$wCalEnd = array();
		$wCal    = $this->getWorkingHoursPerShiftByCalWithShiftDetail(); // Lich lam viec thong thuong
		$swCal   = $this->getSpecialCaldendarsWithShiftDetail(); // Lich lam viec dac biet
		$total   = $this->getWorkingHoursOfManyCalendars(); // Tong thoi gian
		$start   = date_create($this->startDate);
		$end     = date_create($this->endDate);
		$countTimeByDay = array();

		while ($start <= $end)
		{
			$weekday     = $start->format('w'); // Ngay trong tuan
			$startToDate = $start->format('Y-m-d'); // Ngay


			foreach ($this->wCalendars as $rWCal)
			{
			    if($rWCal)
			    {
    				$countTimeByDay[$rWCal][$startToDate] = isset($swCal[$rWCal][$startToDate])?
    					$swCal[$rWCal][$startToDate]:
    					$wCal[$rWCal][$weekday];
			    }
			}
			$start = Qss_Lib_Date::add_date($start, 1);
		}
		return $countTimeByDay;
	}	
	
	public  function getDownTimePlansByDate($date)
	{
		$downtime = $this->wCalendarModel->getDowntimePlans($date, $this->eqIOIDArr);
		$retval   = array();
		
		foreach($downtime AS $d){
			if(!isset($max[$d->EquipIOID])) { $max[$d->EquipIOID] = 0;}
			
			if($d->Time > $max[$d->EquipIOID]) {
				$max[$d->EquipIOID]                        = $d->Time;
				$retval[$d->EquipIOID][$date]['Time']      = $d->Time;
				$retval[$d->EquipIOID][$date]['Type']      = $d->Type;
				$retval[$d->EquipIOID][$date]['StartTime'] = $d->StopTime;
				$retval[$d->EquipIOID][$date]['EndTime']   = $d->RunTime;
			}
		}
		return $retval;
	}
	
	/**
	 * 
	 * @param type $date YYYY-mm-dd $this->startDate
	 * @param type $time hh:mm:ss
	 * @param type $eqIOIDArr $this->eqIOIDArr
	 */
	public  function checkEquipPauseOrRun($time)
	{
//		$this->setStartDate($date);
//		$this->setEndDate($date);
//		$this->setEqIOIDArr(array($eqIOID));
//		$this->setEquipWCals();
//		$this->countWeekday();
//		$this->setCals();	
		$date      = $this->startDate;
		$calForDay = $this->getWCalByDayWithShiftDetail();
		$downtime  = $this->getDownTimePlansByDate($this->startDate);
		$pause     = array();
		$inShift   = array();
		$timeMic   = strtotime($time);
		
		// Kiem tra xem thoi diem do lich co lam viec hay ko
		// Neu co kiem tra tiep xem co dung may hay khong
		foreach ($this->wCalendars as $refCal)
		{
			if(isset($calForDay[$refCal][$date]))
			{
				$start     = (isset($calForDay[$refCal][$date]['Start']) && $calForDay[$refCal][$date]['Start'])
					?$calForDay[$refCal][$date]['Start']:'00:00:00';
				$end       = (isset($calForDay[$refCal][$date]['End']) && $calForDay[$refCal][$date]['End'])
					?$calForDay[$refCal][$date]['End']:'00:00:00';	
				
				$tempStart = strtotime($start);
				$tempEnd   = strtotime($end);
				
				if($end == '00:00:00' || $end == '00:00') // Can nang nen mot ngay
				{
					$tempEnd   = strtotime(date('Y-m-d 00:00:00'). " +1 day");
				}

				foreach ($this->eqIOIDArr as $eqIOID)
				{
					$pause[$eqIOID]   = false;
					$inShift[$eqIOID] = false;					

					if($timeMic >= $tempStart && $timeMic <= $tempEnd)
					{
						$inShift[$eqIOID] = true;
						$pause[$eqIOID]   = false; // Dang lam viec can kiem tra tiep co dung may hay ko

						if(isset($downtime[$eqIOID][$date]))
						{
							if($downtime[$eqIOID][$date]['Type'] == self::EQUAL_START)
							{
								$temp1 = strtotime($downtime[$eqIOID][$date]['StartTime']);
								if($temp1 < $timeMic)
								{
									$pause[$eqIOID] = true;
								}
							}
							elseif($downtime[$eqIOID][$date]['Type'] == self::EQUAL_END)
							{
								$temp1 = strtotime($downtime[$eqIOID][$date]['EndTime']);
								if($temp1 > $timeMic)
								{
									$pause[$eqIOID] = true;
								}							
							}
							else
							{
								$pause[$eqIOID] = true;
							}
						}
					}
					else
					{
						continue; // Khong kiem tra khi thoi gian ko nam trong tg ca
					}

					// Neu ko thuoc ve ca nao tuc la dang nghi
					if(!$inShift[$eqIOID])
					{
						$pause[$eqIOID] = true;
					}						
				}
			}
		}
		return $pause;
	}
	
	public  function checkEquipStopOrRun()
	{
		$stop       = array();
		$stopObj    = $this->wCalendarModel->getEquipsStopInfo($this->eqIOIDArr);
		$oldEqIOID  = '';
		
		foreach($stopObj AS $d){
			$stop[$d->EQIOID] = $d->Stop;
			if($oldEqIOID != $d->EQIOID)
			{
				$stop[$d->EQIOID] = 0;// init
			}
			
			if($stop[$d->EQIOID] == 0)// find stop = 1
			{
				$stop[$d->EQIOID] = $d->Stop;
			}	
			
			$oldEqIOID = $d->EQIOID;
		}
		return $stop;		
	}	
	
	public function checkLocationStopOrRun()
	{
		$stop     = array();
		$stopObj  = $this->wCalendarModel->getLocationsStopInfo($this->locIOIDArr);
		$oldIOID  = '';
		
		foreach($stopObj AS $d){
			if($oldIOID != $d->IOID)
			{
				$stop[$d->IOID] = 0;// init
			}
			
			if($stop[$d->IOID] == 0)// find stop = 1
			{
				$stop[$d->IOID] = $d->Stop;
			}
			
			$oldIOID = $d->IOID;
		}
		return $stop;			
	}

}
