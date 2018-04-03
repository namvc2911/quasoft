<?php
class Qss_Model_Calendar_Solar extends Qss_Model_Abstract
{
	/*
	 The start day of the week. This is the day that appears in the first column
	 of the calendar. Sunday = 0.
	 */
	var $startDay = 0;

	/*
	 The start month of the year. This is the month that appears in the first slot
	 of the calendar in the year view. January = 1.
	 */
	var $startMonth = 1;

	/*
	 The labels to display for the days of the week. The first entry in this array
	 represents Sunday.
	 */
	var $dayNames = array("S", "M", "T", "W", "T", "F", "S");

	/*
	 The labels to display for the months of the year. The first entry in this array
	 represents January.
	 */
	var $monthNames = array("January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December");
	
	/*
	 Month no of quarter, month=>month no.
	 */
	var $montnhNo    = array(1=>1,2=>2,3=>3,4=>1,5=>2,6=>3,7=>1,8=>2,9=>3,10=>1,11=>2,12=>3);
	
	/*
	 Quarter, month=>quarter
	 */
	var $quarter     = array(1=>1,2=>1,3=>1,4=>2,5=>2,6=>2,7=>3,8=>3,9=>3,10=>4,11=>4,12=>4);
	
	var $quarterFull = array(1=>array(1=>1,2=>2,3=>3)
		, 2=>array(1=>4,2=>5,3=>6)
		, 3=>array(1=>7,2=>8,3=>9)
		, 4=>array(1=>10,2=>11,3=>12));

	/*
	 The number of days in each month. You're unlikely to want to change this...
	 The first entry in this array represents January.
	 */
	var $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	/*
	 Constructor for the Calendar class
	 */
	function __construct()
	{
	}

	/*
	 * @return: Month no of quarter
	 */
	function getMonthNo($month)
	{
		return $this->montnhNo[$month];
	}
	
	/*
	 * @return : Quarter
	 */
	function getQuarter($month)
	{
		return $this->quarter[$month];
	}

	/*
	 Get the array of strings used to label the days of the week. This array contains seven
	 elements, one for each day of the week. The first entry in this array represents Sunday.
	 */
	function getDayNames()
	{
		return $this->dayNames;
	}


	/*
	 Set the array of strings used to label the days of the week. This array must contain seven
	 elements, one for each day of the week. The first entry in this array represents Sunday.
	 */
	function setDayNames($names)
	{
		$this->dayNames = $names;
	}

	/*
	 Get the array of strings used to label the months of the year. This array contains twelve
	 elements, one for each month of the year. The first entry in this array represents January.
	 */
	function getMonthNames()
	{
		return $this->monthNames;
	}

	/*
	 Set the array of strings used to label the months of the year. This array must contain twelve
	 elements, one for each month of the year. The first entry in this array represents January.
	 */
	function setMonthNames($names)
	{
		$this->monthNames = $names;
	}



	/*
	 Gets the start day of the week. This is the day that appears in the first column
	 of the calendar. Sunday = 0.
	 */
	function getStartDay()
	{
		return $this->startDay;
	}

	/*
	 Sets the start day of the week. This is the day that appears in the first column
	 of the calendar. Sunday = 0.
	 */
	function setStartDay($day)
	{
		$this->startDay = $day;
	}


	/*
	 Gets the start month of the year. This is the month that appears first in the year
	 view. January = 1.
	 */
	function getStartMonth()
	{
		return $this->startMonth;
	}

	/*
	 Sets the start month of the year. This is the month that appears first in the year
	 view. January = 1.
	 */
	function setStartMonth($month)
	{
		$this->startMonth = $month;
	}


	


	/*
	 Calculate the number of days in a month, taking into account leap years.
	 */
	function getDaysInMonth($month, $year)
	{
		if ($month < 1 || $month > 12)
		{
			return 0;
		}
        
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
		
        /**
         * 
        // @reason: Khong biet thang nao ra 28 ngay va php co ho tro san ham nay
		$d = $this->daysInMonth[$month - 1];
			
		if ($month == 2)
		{
			// Check for leap year
			// Forget the 4000 rule, I doubt I'll be around then...

			if ($year%4 == 0)
			{
				if ($year%100 == 0)
				{
					if ($year%400 == 0)
					{
						$d = 29;
					}
				}
				else
				{
					$d = 29;
				}
			}
		}

		return $d;
         * 
         */
	}


	/*
	 Adjust dates to allow months > 12 and < 0. Just adjust the years appropriately.
	 e.g. Month 14 of the year 2001 is actually month 2 of year 2002.
	 */
	function adjustDate($month, $year)
	{
		$a = array();
		$a[0] = $month;
		$a[1] = $year;

		while ($a[0] > 12)
		{
			$a[0] -= 12;
			$a[1]++;
		}

		while ($a[0] <= 0)
		{
			$a[0] += 12;
			$a[1]--;
		}

		return $a;
	}
	function adjustDay($day, $month, $year)
	{
		$a = array();
		$a[0] = $day;
		$a[1] = $month;
		$a[2] = $year;
		$dayinmonth = $this->getDaysInMonth($month, $year);
		if($a[0] < 1)
		{
			$newmonth = $this->adjustDate($month-1, $year);
			$a[0] = $this->getDaysInMonth($newmonth[0], $newmonth[1]);
			$a[1] = $newmonth[0];
			$a[2] = $newmonth[1];
			
		}
		elseif($a[0] > $dayinmonth)
		{
			$newmonth = $this->adjustDate($month+1, $year);
			$a[0] = 1;
			$a[1] = $newmonth[0];
			$a[2] = $newmonth[1];
		}
		return $a;
	}
	function adjustWeek($week, $month, $year)
	{
		$a = array();
		$a[0] = $week;
		$a[1] = $month;
		$a[2] = $year;
		if($a[0] < 1)
		{
			$date = date_create($year."-01-01");
			if($date->format('W') == 53)
				$a[0] = 53;
			else
				$a[0] = 52;
			$a[2] = $year - 1;
		}
		elseif($a[0] > 53)
		{
			$a[0] = 1;
			$a[2] = $year + 1;
		}
		elseif($a[0] == 53)
		{
			$date = date_create($year."-12-31");
			if($date->format('W') == 53)
			{
				$a[0] = 53;
				$a[2] = $year;
			}
			else
			{
				$a[0] = 1;
				$a[2] = $year + 1;
			}
		}
		$a[1] = Qss_Lib_Date::getDateByWeek($a[0],$a[2])->format('m');
		return $a;
	}
	function getDateRangeByWeek($weekno,$year)
	{
		$week_start = new DateTime();
		$week_start->setISODate($year,$weekno);
		$start = date_create($week_start->format('Y-m-d'));
		$end = Qss_Lib_Date::add_date($start, 6);
		return array($start->format('d-m-Y'),$end->format('d-m-Y'));
	}
	
	
	//thang thu 3 quy 1
	function getMonthByQuarter($monthNo, $quarterNo)
	{
		return $this->quarterFull[$quarterNo][$monthNo];
	}
    
    function createDayRangeArray($strDateFrom,$strDateTo, $limit = 0)
	{
	    $aryRange=array();
	
	    $iDateFrom = mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
	    $iDateTo   = mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));
	
	    if ($iDateTo >= $iDateFrom)
	    {
	    	$i = 1;
	        array_push($aryRange,date('d',$iDateFrom)); 
	        while ($iDateFrom<$iDateTo)
	        {
	        	if($limit && ($i == $limit))
	            {
	            	break;
	            }
	            $iDateFrom+=86400; 
	            array_push($aryRange,date('d',$iDateFrom));
	            $i++;

	        }
	    }
	    return $aryRange;
	}    
	
	
	function createDateRangeArray($strDateFrom,$strDateTo, $limit = 0)
	{
	    $aryRange=array();
	
	    $iDateFrom = mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
	    $iDateTo   = mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));
	
	    if ($iDateTo >= $iDateFrom)
	    {
	    	$i = 1;
	        array_push($aryRange,date('Y-m-d',$iDateFrom)); 
	        while ($iDateFrom<$iDateTo)
	        {
	        	if($limit && ($i == $limit))
	            {
	            	break;
	            }
	            $iDateFrom+=86400; 
	            array_push($aryRange,date('Y-m-d',$iDateFrom));
	            $i++;

	        }
	    }
	    return $aryRange;
	}
	
	function createYearRangeArray($strDateFrom,$strDateTo)
	{
		$startYear = (int)date('Y',strtotime($strDateFrom));
		$endYear   = (int)date('Y',strtotime($strDateTo));
		$range     = ($endYear - $startYear) + 1;
		
		for ($i = 0; $i < $range; $i++)
		{
			$years[$i] = ($i==0)?$startYear:++$startYear;
		}
		return $years;
	}
	
	function countYear($strDateFrom,$strDateTo)
	{
	    $startYear = (int)date('Y',strtotime($strDateFrom));
	    $endYear   = (int)date('Y',strtotime($strDateTo));
	    return ($endYear - $startYear) + 1;
	}	
	
	function createMonthRangeArray($strDateFrom, $strDateTo, $limit = 0)
	{
		$time1  = strtotime($strDateFrom);
		$time2  = strtotime($strDateTo);
		$my1    = date('mY', $time1); 
		$my2    = date('mY', $time2);
		$year1  = date('Y', $time1);
		$year2  = date('Y', $time2);
		$years  = range($year1, $year2);
		 
		$i = 0;
		foreach($years as $year)
		{
			if($limit && ($i == $limit))
            {
            	break;
            }
			$months[$year] = array();
			while($time1 < $time2)
			{
				if($limit && ($i == $limit))
	            {
	            	break;
	            }
				if(date('Y',$time1) == $year)
				{
					$months[$year][] = date('m', $time1);
					$time1 = strtotime(date('Y-m-d', $time1).' +1 month');
				}
				else
				{
					break;
				}
				$i++;
			}

			continue;
		}
		 
		return $months;
	}
	
	function countMonth($strDateFrom, $strDateTo)
	{
	    $start = date_create($strDateFrom);
	    $end   = date_create($strDateTo);

	    $numYears  = ((int)$end->format('Y') - (int)$start->format('Y')) + 1;
        $numMonths = 0;

	    if($numYears == 1) {
            $numMonths = ((int)$end->format('m') - (int)$start->format('m')) + 1;
        }
        else {
            $numMonths  = (12 - (int)$start->format('m')) + 1;
            $numMonths += (($numYears - 2) > 0)?($numYears - 2)*12:0;
            $numMonths += (int)$end->format('m');
        }

        return $numMonths;
	}	
	
	function createWeekRangeArray($strDateFrom, $strDateTo, $limit = 0)
	{
	 	$startDateUnix   = date_create($strDateFrom);
	    $endDateUnix     = date_create($strDateTo);
	    
	    //$startWeek       = date('w', $startDateUnix); 
	   // $currentDateUnix = $startWeek?strtotime('- '.((int)$startWeek).' day', $startDateUnix):$startDateUnix;
	    $weekNumbers     = array();
	    $i               = 0;
	    
	    while ($startDateUnix <= $endDateUnix) {
	    	if($limit && $limit == $i)
	    	{
	    		break;
	    	}
	    	$w = $startDateUnix->format('W');
	    	$y = $startDateUnix->format('Y');
	    	$m = $startDateUnix->format('m');
		    if($w == 53 && $m == 1)
			{
				$y--;
			}	
	        $weekNumbers[$i]['week'] = $w;
	        $weekNumbers[$i]['year'] = $y;
	        $startDateUnix = Qss_Lib_Date::add_date($startDateUnix, 1,'WEEK');
	        //$currentDateUnix = strtotime('+1 week', $currentDateUnix);
	        $i++;
	    }
	    return $weekNumbers;
	    
	}
	
	function countWeek($strDateFrom, $strDateTo)
	{
	    $startDateUnix   = strtotime($strDateFrom);
	    $endDateUnix     = strtotime($strDateTo);
        
	    $startWeek       = date('w', $startDateUnix); 
	    $currentDateUnix = $startWeek?strtotime('- '.((int)$startWeek).' day', $startDateUnix):$startDateUnix;
	    $i               = 0;
	     
	    while ($currentDateUnix <= $endDateUnix) {
	        $i++;
            $currentDateUnix = strtotime('+1 week', $currentDateUnix);
	    }
	    return $i;
	}	
	
	/* Get start date and end date of week of year */
	function getWeek($year, $week) 
	{ 
	    $time = strtotime("1 January $year", time());
	    $day = date('w', $time);
	    $time += ((7*$week)+1-$day)*24*3600;
	    $return[0] = date('Y-m-d', $time);
	    $time += 6*24*3600;
	    $return[1] = date('Y-m-d', $time);
	    return $return;
	} 
	
	function createQuarterRangeArray($startDate, $endDate, $limit = 0)
	{
		$startDate    = strtotime($startDate);
		$endDate      = strtotime($endDate);
		$retval       = array();
		$startQuarter = ceil(date('n', $startDate)/3);
		$startYear    = date('Y', $startDate);
		$endQuarter   = ceil(date('n', $endDate)/3);
		$endYear      = date('Y', $endDate);
		$yearDiff     = $endYear - $startYear;
		$numOfStartQua= (4 - $startQuarter) + 1;
		$numOfEndQua  = $endQuarter;
		$numOfQua     = 0;
		
		if($yearDiff > 1)
		{
			$numOfQua = $numOfStartQua + $numOfEndQua + 4 * ($yearDiff - 1);
		}
		elseif($yearDiff == 1)
		{
			$numOfQua = $numOfStartQua + $numOfEndQua;
		}
		elseif($yearDiff == 0) 
		{
			if($startQuarter < $endQuarter)
			{
				$numOfQua = $numOfStartQua;
			}
			else 
			{
				$numOfQua = 1;
			}
		}
		
		$retval[0]['quarter'] = $startQuarter;
		$retval[0]['year']    = $startYear;
		for($i = 1; $i < $numOfQua; $i ++)
		{
		
			if($limit && $limit == $i )
			{
				break;
			}
			$retval[$i]['quarter'] = ++$startQuarter;
			$retval[$i]['year']    = $startYear;
			
			if($startQuarter > 4)
			{
				$startQuarter = 1;
				$startYear++;
				$retval[$i]['quarter']   = 1;
				$retval[$i]['year']      = $startYear;
			}
			
		}
		return  $retval;
	}
}

?>