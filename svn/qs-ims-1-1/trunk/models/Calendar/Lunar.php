<?php
class Qss_Model_Calendar_Lunar extends Qss_Model_Abstract
{

	function __construct()
	{

	}
	function alertDayInfo($dd, $mm, $yy, $leap, $jd, $sday, $smonth, $syear) {
		$lunar = $this->LunarDate($dd, $mm, $yy, $leap, $jd);
		$s = $this->getDayString($lunar, $sday, $smonth, $syear);
		$s .= " âm lịch";
		$s .= $this->getDayName($lunar);
		$s .= "Giờ đầu ngày: " . $this->getCanHour0($jd) . " " . Qss_Lib_Const::$CHI[0];
		$s = $s . "Tiết: " . Qss_Lib_Const::$TIETKHI[0];
		$s .= "Giờ hoàng đạo: " . $this->getGioHoangDao($jd);
		return $s;
	}
	function getDayString($lunar, $solarDay, $solarMonth, $solarYear) {
		$ret;
		$dayOfWeek = Qss_Lib_Const::$TUAN[($lunar->jd + 1) % 7];
		$ret = $dayOfWeek . " " . $solarDay . "/" . $solarMonth . "/" . $solarYear;
		$ret .= " -+- ";
		$ret = $ret . "Ngày " . $lunar->day . " tháng " . $lunar->month;
		if ($lunar->leap == 1) {
			$ret = $ret . " nhuận";
		}
		return $ret;
	}
	function getDayName($lunarDate) {
		if ($lunarDate->day == 0) {
			return "";
		}
		$cc = $this->getCanChi($lunarDate);
		$s = "Ngày " . $cc[0] . ", tháng " . $cc[1] . ", năm " . $cc[2];
		return $s;
	}
	function getCanChi($lunar) {
		//var dayName, monthName, yearName;
		$dayName = Qss_Lib_Const::$CAN[($lunar->jd + 9) % 10] . " " . Qss_Lib_Const::$CHI[($lunar->jd+1)%12];
		$monthName = Qss_Lib_Const::$CAN[($lunar->year*12+$lunar->month+3) % 10] . " " . Qss_Lib_Const::$CHI[($lunar->month+1)%12];
		if ($lunar->leap == 1) {
			$monthName .= " (nhuận)";
		}
		$yearName = $this->getYearCanChi($lunar->year);
		return array($dayName, $monthName, $yearName);
	}
	function getYearCanChi($year) {
		return Qss_Lib_Const::$CAN[($year+6) % 10] . " " . Qss_Lib_Const::$CHI[($year+8) % 12];
	}

	/*
	 * Can cua gio Chinh Ty (00:00) cua ngay voi JDN nay
	 */
	function getCanHour0($jdn) {
		return Qss_Lib_Const::$CAN[($jdn-1)*2 % 10];
	}

	function LunarDate($dd, $mm, $yy, $leap, $jd) {
		$ret = new stdClass();
		$ret->day = $dd;
		$ret->month = $mm;
		$ret->year = $yy;
		$ret->leap = $leap;
		$ret->jd = $jd;
		return $ret;
	}


	function getGioHoangDao($jd) {
		$chiOfDay = ($jd+1) % 12 ;
		$gioHD = Qss_Lib_Const::$GIO_HD[$chiOfDay % 6]; // same values for Ty' (1) and Ngo. (6), for Suu and Mui etc.
		$ret = "";
		$count = 0;
		for ($i = 0; $i < 12; $i++) {
			if ($gioHD[$i] == '1') {
				$ret .= Qss_Lib_Const::$CHI[$i];
				$ret .= ' (' . ($i*2+23)%24 . '-' . ($i*2+1)%24 . ')';
				if ($count++ < 5) $ret .= ', ';
			}
		}
		return $ret;
	}

	function INT($d) {
		return floor($d);
	}

	function jdFromDate($dd, $mm, $yy) {
		$a = $this->INT((14 - $mm) / 12);
		$y = $yy + 4800 - $a;
		$m = $mm + 12 * $a - 3;
		$jd = $dd + $this->INT((153 * $m + 2) / 5) + 365 * $y + $this->INT($y / 4) - $this->INT($y / 100) + $this->INT($y / 400) - 32045;
		if ($jd < 2299161) {
			$jd = $dd + $this->INT((153* $m + 2)/5) + 365 * $y + $this->INT($y / 4) - 32083;
		}
		return $jd;
	}

	function jdToDate($jd) {
		if ($jd > 2299160) { // After 5/10/1582, Gregorian calendar
			$a = $jd + 32044;
			$b = $this->INT((4*$a+3)/146097);
			$c = $a - $this->INT(($b*146097)/4);
		} else {
			$b = 0;
			$c = $jd + 32082;
		}
		$d = $this->INT((4*$c+3)/1461);
		$e = $c - $this->INT((1461*$d)/4);
		$m = $this->INT((5*$e+2)/153);
		$day = $e - $this->INT((153*$m+2)/5) + 1;
		$month = $m + 3 - 12*$this->INT($m/10);
		$year = $b*100 + $d - 4800 + $this->INT($m/10);
		//echo "day = $day, month = $month, year = $year\n";
		return array($day, $month, $year);
	}

	function getNewMoonDay($k, $timeZone) {
		$T = $k/1236.85; // Time in Julian centuries from 1900 January 0.5
		$T2 = $T * $T;
		$T3 = $T2 * $T;
		$dr = M_PI/180;
		$Jd1 = 2415020.75933 + 29.53058868*$k + 0.0001178*$T2 - 0.000000155*$T3;
		$Jd1 = $Jd1 + 0.00033*sin((166.56 + 132.87*$T - 0.009173*$T2)*$dr); // Mean new moon
		$M = 359.2242 + 29.10535608*$k - 0.0000333*$T2 - 0.00000347*$T3; // Sun's mean anomaly
		$Mpr = 306.0253 + 385.81691806*$k + 0.0107306*$T2 + 0.00001236*$T3; // Moon's mean anomaly
		$F = 21.2964 + 390.67050646*$k - 0.0016528*$T2 - 0.00000239*$T3; // Moon's argument of latitude
		$C1=(0.1734 - 0.000393*$T)*sin($M*$dr) + 0.0021*sin(2*$dr*$M);
		$C1 = $C1 - 0.4068*sin($Mpr*$dr) + 0.0161*sin($dr*2*$Mpr);
		$C1 = $C1 - 0.0004*sin($dr*3*$Mpr);
		$C1 = $C1 + 0.0104*sin($dr*2*$F) - 0.0051*sin($dr*($M+$Mpr));
		$C1 = $C1 - 0.0074*sin($dr*($M-$Mpr)) + 0.0004*sin($dr*(2*$F+$M));
		$C1 = $C1 - 0.0004*sin($dr*(2*$F-$M)) - 0.0006*sin($dr*(2*$F+$Mpr));
		$C1 = $C1 + 0.0010*sin($dr*(2*$F-$Mpr)) + 0.0005*sin($dr*(2*$Mpr+$M));
		if ($T < -11) {
			$deltat= 0.001 + 0.000839*$T + 0.0002261*$T2 - 0.00000845*$T3 - 0.000000081*$T*$T3;
		} else {
			$deltat= -0.000278 + 0.000265*$T + 0.000262*$T2;
		};
		$JdNew = $Jd1 + $C1 - $deltat;
		//echo "JdNew = $JdNew\n";
		return $this->INT($JdNew + 0.5 + $timeZone/24);
	}

	function getSunLongitude($jdn, $timeZone) {
		$T = ($jdn - 2451545.5 - $timeZone/24) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
		$T2 = $T * $T;
		$dr = M_PI/180; // degree to radian
		$M = 357.52910 + 35999.05030*$T - 0.0001559*$T2 - 0.00000048*$T*$T2; // mean anomaly, degree
		$L0 = 280.46645 + 36000.76983*$T + 0.0003032*$T2; // mean longitude, degree
		$DL = (1.914600 - 0.004817*$T - 0.000014*$T2)*sin($dr*$M);
		$DL = $DL + (0.019993 - 0.000101*$T)*sin($dr*2*$M) + 0.000290*sin($dr*3*$M);
		$L = $L0 + $DL; // true longitude, degree
		//echo "\ndr = $dr, M = $M, T = $T, DL = $DL, L = $L, L0 = $L0\n";
		// obtain apparent longitude by correcting for nutation and aberration
		$omega = 125.04 - 1934.136 * $T;
		$L = $L - 0.00569 - 0.00478 * sin($omega * $dr);
		$L = $L*$dr;
		$L = $L - M_PI*2*($this->INT($L/(M_PI*2))); // Normalize to (0, 2*PI)
		return $this->INT($L/M_PI*6);
	}

	function getLunarMonth11($yy, $timeZone) {
		$off = $this->jdFromDate(31, 12, $yy) - 2415021;
		$k = $this->INT($off / 29.530588853);
		$nm = $this->getNewMoonDay($k, $timeZone);
		$sunLong = $this->getSunLongitude($nm, $timeZone); // sun longitude at local midnight
		if ($sunLong >= 9) {
			$nm = $this->getNewMoonDay($k-1, $timeZone);
		}
		return $nm;
	}

	function getLeapMonthOffset($a11, $timeZone) {
		$k = $this->INT(($a11 - 2415021.076998695) / 29.530588853 + 0.5);
		$last = 0;
		$i = 1; // We start with the month following lunar month 11
		$arc = $this->getSunLongitude($this->getNewMoonDay($k + $i, $timeZone), $timeZone);
		do {
			$last = $arc;
			$i = $i + 1;
			$arc = $this->getSunLongitude($this->getNewMoonDay($k + $i, $timeZone), $timeZone);
		} while ($arc != $last && $i < 14);
		return $i - 1;
	}

	/* Comvert solar date dd/mm/yyyy to the corresponding lunar date */
	function convertSolar2Lunar($dd, $mm, $yy, $timeZone) {
		$dayNumber = $this->jdFromDate($dd, $mm, $yy);
		$k = $this->INT(($dayNumber - 2415021.076998695) / 29.530588853);
		$monthStart = $this->getNewMoonDay($k+1, $timeZone);
		if ($monthStart > $dayNumber) {
			$monthStart = $this->getNewMoonDay($k, $timeZone);
		}
		$a11 = $this->getLunarMonth11($yy, $timeZone);
		$b11 = $a11;
		if ($a11 >= $monthStart) {
			$lunarYear = $yy;
			$a11 = $this->getLunarMonth11($yy-1, $timeZone);
		} else {
			$lunarYear = $yy+1;
			$b11 = $this->getLunarMonth11($yy+1, $timeZone);
		}
		$lunarDay = $dayNumber - $monthStart + 1;
		$diff = $this->INT(($monthStart - $a11)/29);
		$lunarLeap = 0;
		$lunarMonth = $diff + 11;
		if ($b11 - $a11 > 365) {
			$leapMonthDiff = $this->getLeapMonthOffset($a11, $timeZone);
			if ($diff >= $leapMonthDiff) {
				$lunarMonth = $diff + 10;
				if ($diff == $leapMonthDiff) {
					$lunarLeap = 1;
				}
			}
		}
		if ($lunarMonth > 12) {
			$lunarMonth = $lunarMonth - 12;
		}
		if ($lunarMonth >= 11 && $diff < 4) {
			$lunarYear -= 1;
		}
		return array($lunarDay, $lunarMonth, $lunarYear, $lunarLeap);
	}
	public function convertLunar2Solar($lunarDay, $lunarMonth, $lunarYear, $lunarLeap, $timeZone) {
		//var k, a11, b11, off, leapOff, leapMonth, monthStart;
		if ($lunarMonth < 11) {
			$a11 = $this->getLunarMonth11($lunarYear-1, $timeZone);
			$b11 = $this->getLunarMonth11($lunarYear, $timeZone);
		} else {
			$a11 = $this->getLunarMonth11($lunarYear, $timeZone);
			$b11 = $this->getLunarMonth11($lunarYear+1, $timeZone);
		}
		$k = $this->INT(0.5 + ($a11 - 2415021.076998695) / 29.530588853);
		$off = $lunarMonth - 11;
		if ($off < 0) {
			$off += 12;
		}
		if ($b11 - $a11 > 365) {
			$leapOff = $this->getLeapMonthOffset($a11, $timeZone);
			$leapMonth = $leapOff - 2;
			if ($leapMonth < 0) {
				$leapMonth += 12;
			}
			if ($lunarLeap != 0 && $lunarMonth != $leapMonth) {
				return array(0, 0, 0);
			} else if ($lunarLeap != 0 || $off >= $leapOff) {
				$off += 1;
			}
		}
		$monthStart = $this->getNewMoonDay($k+$off, $timeZone);
		return $this->jdToDate($monthStart+$lunarDay-1);
	}
	//	/* Convert a lunar date to the corresponding solar date */
	//	function convertLunar2Solar1($lunarDay, $lunarMonth, $lunarYear, $lunarLeap, $timeZone) {
	//		if ($lunarMonth < 11) {
	//			$a11 = $this->getLunarMonth11($lunarYear-1, $timeZone);
	//			$b11 = $this->getLunarMonth11($lunarYear, $timeZone);
	//		} else {
	//			$a11 = $this->getLunarMonth11($lunarYear, $timeZone);
	//			$b11 = $this->getLunarMonth11($lunarYear+1, $timeZone);
	//		}
	//		$k = (0.5 + ($a11 - 2415021.076998695) / 29.530588853);
	//		$off = $lunarMonth - 11;
	//		if ($off < 0) {
	//			$off += 12;
	//		}
	//		if ($b11 - $a11 > 365) {
	//			$leapOff = $this->getLeapMonthOffset($a11, $timeZone);
	//			$leapMonth = $leapOff - 2;
	//			if ($leapMonth < 0) {
	//				$leapMonth += 12;
	//			}
	//			if ($lunarLeap != 0 && $lunarMonth != $leapMonth) {
	//				return array(0, 0, 0);
	//			} else if ($lunarLeap != 0 || $off >= $leapOff) {
	//				$off += 1;
	//			}
	//		}
	//		$monthStart = $this->getNewMoonDay($k + $off, $timeZone);
	//		return $this->jdToDate($monthStart + $lunarDay - 1);
	//	}
	function getMonth($mm, $yy) {
		if ($mm < 12) {
			$mm1 = $mm + 1;
			$yy1 = $yy;
		} else {
			$mm1 = 1;
			$yy1 = $yy + 1;
		}
		$jd1 = $this->jdFromDate(1, $mm, $yy);
		$jd2 = $this->jdFromDate(1, $mm1, $yy1);
		$ly1 = $this->getYearInfo($yy);
		//alert('1/'+$mm+'/'+yy+' = '+jd1+'; 1/'+mm1+'/'+yy1+' = '+jd2);
		$tet1 = $ly1[0]->jd;
		$result = array();
		if ($tet1 <= $jd1) { /* tet(yy) = tet1 < jd1 < jd2 <= 1.1.(yy+1) < tet(yy+1) */
			for ($i = $jd1; $i < $jd2; $i++) {
				$result[] = $this->findLunarDate($i, $ly1);
			}
		} else if ($jd1 < $tet1 && $jd2 < $tet1) { /* tet(yy-1) < jd1 < jd2 < tet1 = tet(yy) */
			$ly1 = $this->getYearInfo($yy - 1);
			for ($i = $jd1; $i < $jd2; $i++) {
				$result[] = $this->findLunarDate($i, $ly1);
			}
		} else if ($jd1 < $tet1 && $tet1 <= $jd2) { /* tet(yy-1) < jd1 < tet1 <= jd2 < tet(yy+1) */
			$ly2 = $this->getYearInfo($yy - 1);
			for ($i = $jd1; $i < $tet1; $i++) {
				$result[] = $this->findLunarDate($i, $ly2);
			}
			for ($i = $tet1; $i < $jd2; $i++) {
				$result[] = $this->findLunarDate($i, $ly1);
			}
		}
		return $result;
	}
	function getYearInfo($yyyy) {
		if ($yyyy < 1900) {
			$yearCode = Qss_Lib_Const::$TK19[$yyyy - 1800];
		} else if ($yyyy < 2000) {
			$yearCode = Qss_Lib_Const::$TK20[$yyyy - 1900];
		} else if ($yyyy < 2100) {
			$yearCode = Qss_Lib_Const::$TK21[$yyyy - 2000];
		} else {
			$yearCode = Qss_Lib_Const::$TK22[$yyyy - 2100];
		}
		return $this->decodeLunarYear($yyyy, $yearCode);
	}
	function decodeLunarYear($yy, $k) {
		//var monthLengths, regularMonths, offsetOfTet, leapMonth, leapMonthLength, solarNY, currentJD, j, $mm;
		$ly = array();
		$monthLengths = array(29, 30);
		$regularMonths = array(12);
		$offsetOfTet = $k >> 17;
		$leapMonth = $k & 0xf;
		$leapMonthLength = $monthLengths[$k >> 16 & 0x1];
		$solarNY = $this->jdFromDate(1, 1, $yy);
		$currentJD = $solarNY+$offsetOfTet;
		$j = $k >> 4;
		for($i = 0; $i < 12; $i++) {
			$regularMonths[12 - $i - 1] = $monthLengths[$j & 0x1];
			$j >>= 1;
		}
		if ($leapMonth == 0) {
			for($mm = 1; $mm <= 12; $mm++) {
				$ly[] = $this->LunarDate(1, $mm, $yy, 0, $currentJD);
				$currentJD += $regularMonths[$mm-1];
			}
		} else {
			for($mm = 1; $mm <= $leapMonth; $mm++) {
				$ly[] = $this->LunarDate(1, $mm, $yy, 0, $currentJD);
				$currentJD += $regularMonths[$mm-1];
			}
			$ly[] = $this->LunarDate(1, $leapMonth, $yy, 1, $currentJD);
			$currentJD += $leapMonthLength;
			for($mm = $leapMonth+1; $mm <= 12; $mm++) {
				$ly[] = $this->LunarDate(1, $mm, $yy, 0, $currentJD);
				$currentJD += $regularMonths[$mm-1];
			}
		}
		return $ly;
	}
	function findLunarDate($jd, $ly) {
		if (0 || $ly[0]->jd > $jd) {
			return $this->LunarDate(0, 0, 0, 0, $jd);
		}
		$i = count($ly)-1;
		while ($jd < $ly[$i]->jd) {
			$i--;
		}
		$off = $jd - $ly[$i]->jd;
		$ret = $this->LunarDate($ly[$i]->day+$off, $ly[$i]->month, $ly[$i]->year, $ly[$i]->leap, $jd);
		return $ret;
	}
	function getSolarTerm($dayNumber, $timeZone) {
		return $this->INT($this->SunLongitude($dayNumber - 0.5 - $timeZone/24.0) / pi() * 12);
	}
	function SunLongitude($jdn) {
		//var T, T2, dr, M, L0, DL, lambda, theta, omega;
		$T = ($jdn - 2451545.0 ) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
		$T2 = $T*$T;
		$dr = pi()/180; // degree to radian
		$M = 357.52910 + 35999.05030*$T - 0.0001559*$T2 - 0.00000048*$T*$T2; // mean anomaly, degree
		$L0 = 280.46645 + 36000.76983*$T + 0.0003032*$T2; // mean longitude, degree
		$DL = (1.914600 - 0.004817*$T - 0.000014*$T2)*sin($dr*$M);
		$DL = $DL + (0.019993 - 0.000101*$T)*sin($dr*2*$M) + 0.000290*sin($dr*3*$M);
		$theta = $L0 + $DL; // true longitude, degree
		// obtain apparent longitude by correcting for nutation and aberration
		$omega = 125.04 - 1934.136 * $T;
		$lambda = $theta - 0.00569 - 0.00478 * sin($omega * $dr);
		// Convert to radians
		$lambda = $lambda*$dr;
		$lambda = $lambda - pi()*2*($this->INT($lambda/(pi()*2))); // Normalize to (0, 2*PI)
		return $lambda;
	}
	function mysql2lunar($date)
	{
		$pdate = date_parse($date);
		$lunar = $this->convertSolar2Lunar($pdate['day'], $pdate['month'], $pdate['year'], 7);
		return $lunar[0].'/'.$lunar[1].'/'.$lunar[2];
	}

}
?>