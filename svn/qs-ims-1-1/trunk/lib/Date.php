<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Date
{

	public static function i_fString2Time ($sz_Date)
	{
		$arr = explode('-', $sz_Date);
		if ( sizeof($arr) == 3 )
		{
			return mktime(0, 0, 0, $arr[1], $arr[0], $arr[2]);
		}
		else
		{
			return null;
		}

	}

	public static function i_fMysql2Time ($sz_Date)
	{
		$arr1 = explode(' ', $sz_Date);
		$arr = explode('-', $arr1[0]);
		if ( sizeof($arr) >= 3 )
		{
			if(isset($arr1[1]))
			{
				$arr2 = explode(':', $arr1[1]);
			}
			return mktime(@$arr2[0],@$arr2[1], @$arr2[2], $arr[1], $arr[2], $arr[0]);
		}
		else
		{
			return null;
		}

	}

	public static function f_fString2Float ($sz_Value)
	{
		return doubleval(str_replace(",", ".", $sz_Value));
	}

	public static function f_fString2Money ($sz_Value)
	{
		return floatval(str_replace(',', '', $sz_Value));
	}
	public static function mysqltodisplay ($mysqldate,$format=Qss_Lib_Const::DATE_FORMAT)
	{
		return ($mysqldate && $mysqldate != '0000-00-00')?date($format,strtotime( $mysqldate )):'';
	}
	public static function displaytomysql($sz_Date,$format=Qss_Lib_Const::DATE_FORMAT)
	{
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $sz_Date)) 
		{
		 	return $sz_Date;
		} 
		else 
		{
			$sz_Date = trim($sz_Date,"'");
		  	return $sz_Date?date('Y-m-d',self::i_fString2Time($sz_Date)):$sz_Date;
		}
	}
	public static function add_date($original_date,$interval,$type = 'day')
	{
		$time = mktime(0,0,0,$original_date->format('m'),$original_date->format('d'),$original_date->format('Y'));
		$date = date('d-m-Y',$time)  . sprintf('%1$d %2$s',$interval,$type);
		return date_create($date);
	}
    
	public static function diff_date($original_date,$day_interval)
	{
		$time = mktime(0,0,0,$original_date->format('m'),$original_date->format('d'),$original_date->format('Y'));
		$date = date('d-m-Y',$time)  . sprintf('-%1$d day',$day_interval);
		return date_create($date);
	}    
    
	public static function getDateByWeek($w,$y)
	{
		$retval = new DateTime();
		$retval->setISODate($y,$w);
		return $retval;
	}
	public static function compareTwoDate($dateOne, $dateTwo)
	{
		$microDateOne = is_int($dateOne)?$dateOne:strtotime($dateOne);
		$microDateTwo = is_int($dateTwo)?$dateTwo:strtotime($dateTwo);
		
		if($microDateOne > $microDateTwo)
		{
			return 1;
		}
		elseif($microDateOne == $microDateTwo)
		{
			return 0;
		}
		elseif($microDateOne < $microDateTwo)
		{
			return -1;
		}
	}
	
	public static function checkInRangeTime($date, $start, $end)
	{
		$date  = strtotime($date);
		$start = strtotime($start);
		$end   = strtotime($end);
		
		if($date >= $start && $date <= $end)
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
        
        public static function formatTime($time)
        {
        	if($time !== '')
        	{
                $arrtmp = explode(':', $time);
                foreach ($arrtmp as $k=>$v)
                {
                        $arrtmp[$k] = substr(str_pad($v,2,'0',STR_PAD_LEFT),0,2);
                }
                $time = implode(':',$arrtmp);
                $time = str_pad($time, 8 , '0');
                $time = substr($time,0,8);
                $time = substr_replace($time,':',2,1);
                $time = substr_replace($time,':',5,1);
                return $time;
        	}
        	else 
        	{
        		return '';
        	}
        }
        
        /**
         * Tinh ra thoi gian tu ngay bat dau den ngay ket thuc
		 * (Khong tinh vao gio:phut:giay)
         * @param date $start YYYY-mm-dd - ngay bat dau - so tru
         * @param date $end  YYYY-mm-dd  - ngay ket thuc - so bi tru
         * @param string $return tra ve kieu ngay, gio, phut, giay
         * @return int
         */
        public static function divDate($start, $end, $return = 'D')
        {
                $solar  = new Qss_Model_Calendar_Solar();
                $div    = 0;
                $mStart = strtotime($start);
                $mEnd   = strtotime($end);
                $div    = abs($mEnd - $mStart);
                //$div   = ceil($div/86400) + 1;
                
                switch ($return)
                {
                    case 'Y':
                    case 'MO':
                    case 'W':
                    case 'D':
                        
                    $div = ceil($div/86400);
                        
                    break;
                
                    case 'H':
                    case 'M':
                    case 'S':
                        
                    $div = $div/86400;
                        
                    break;
                }
                
                if($return == 'Y')
                {
                    return $solar->countYear($start, $end)-1;
                }
                elseif($return == 'MO') 
                {
                    return $solar->countMonth($start, $end)-1;
                }     
                elseif($return == 'W')
                {
                    return $solar->countWeek($start, $end)-1;
                }                
                elseif($return == 'D') // tra ve ngay
                {
                    return $div; 
                }
                elseif($return == 'H') // tra ve gio
                {
                    return $div * 24;
                }
                elseif($return == 'M')// tra ve phut
                {
                    return $div * 1440 ;
                }
                elseif($return == 'S')// tra ve giay
                {
                    return $div * 86400;
                }
        }
		/**
		 * Tinh ra thoi gian tu ngay bat dau den ngay ket thuc
		 * (Có tính bao gồm cả giờ)
         * @param date $start YYYY-mm-dd or YYYY-mm-dd hh:mm:ss - ngay bat dau - so tru
         * @param date $end  YYYY-mm-dd or YYYY-mm-dd hh:mm:ss  - ngay ket thuc - so bi tru
         * @param string $return tra ve kieu gio, phut, giay
         * @param boolean $addFirst 11-01-2017 11-03-2017 true thì ra 3 tháng false thì ra 2 tháng (Áp dụng cho trả về năm tháng tuần ngày)
		 * @return float
		 */
		public static function diffTime($start, $end, $return = 'H', $addFirst = true)
		{
		    if($start == '' || $end == '')
            {
                return 0;
            }
            
                $solar = new Qss_Model_Calendar_Solar();
                $div   = 0;
                $mStart = strtotime($start);
                $mEnd   = strtotime($end);
                $div    = abs($mEnd - $mStart);                
                //$div    = abs($end - $start);
                //$div   = ceil($div/86400) + 1;
                
                switch ($return)
                {
                    case 'Y':
                    case 'MO':
                    case 'W':
                    case 'D':
                        
                    $div = ceil($div/86400);
                        
                    break;
                
                    case 'H':
                    case 'M':
                    case 'S':
                        
                    $div = $div/86400;
                        
                    break;
                }
                
                if($return == 'Y')
                {
                    return $addFirst?$solar->countYear($start, $end):($solar->countYear($start, $end)-1);
                }
                elseif($return == 'MO') 
                {
                    return $addFirst?$solar->countMonth($start, $end):($solar->countMonth($start, $end)-1);
                }     
                elseif($return == 'W')
                {
                    return $addFirst?$solar->countWeek($start, $end):($solar->countWeek($start, $end)-1);
                }                
                elseif($return == 'D') // tra ve ngay
                {
                    return $addFirst?($div + 1):$div;
                }
                elseif($return == 'H') // tra ve gio
                {
                    return $div * 24;
                }
                elseif($return == 'M')// tra ve phut
                {
                    return $div * 1440 ;
                }
                elseif($return == 'S')// tra ve giay
                {
                    return $div * 86400;
                }		
		}
	
	
}
?>