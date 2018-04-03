<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Util
{
	public static $arrFormat;
	
	public static function numberToRome($N)
	{
		$c='IVXLCDM';
		for($a=5,$b=$s='';$N;$b++,$a^=7)
		for($o=$N%$a,$N=$N/$a^0;$o--;$s=@$c[$o>2?$b+$N-($N&=-2)+$o=1:$b].$s);
		return $s;
	}
	public static function scanFile($filename,&$virusname)
	{
		return 0;
		$ret = -1;
		if(function_exists('cl_scanfile'))
		{
			$retcode = cl_scanfile($filename, $virusname);
			if ($retcode == CL_VIRUS)
			{
				$ret = 1;
			}
			else
			{
				$ret = 0;
			}

		}
		return $ret;
	}
	public static function moneyToString($money)
	{
		$retval = '';
		$donvi = array(' đồng ',' nghìn ', ' triệu ',' tỷ ',' nghìn tỷ ', ' triệu tỷ ', ' tỷ tỷ ');
		$songuyen = array('không','một','hai', 'ba','bốn','năm', 'sáu', 'bảy','tám','chín');
		$dv = 1;
		$money = str_pad($money, 9,'0',STR_PAD_LEFT);
		$len = strlen($money);
		$sodonvi = (int) (($len % 3 > 0) ? ($len / 3 + 1) : ($len / 3));
		while ($dv <= $sodonvi)
		{
			$substr = substr($money, $len-($dv*3),3);		
			if($dv == $sodonvi)
			{
				$substr = (int) $substr;
			}
			$tmp = '';
			$i = 1;
			$j = strlen($substr);
			while($i <= $j)
			{
				$char = substr($substr,$j-$i,1);
				if ($i == 1)
				{
					$tmp = $songuyen[$char]. $tmp;
				}
				elseif ($i == 2)
				{
					$tmp = $songuyen[$char] . ' mươi ' . $tmp;
				}
				elseif ($i == 3)
				{
					$tmp = $songuyen[$char] . ' trăm ' . $tmp;
				}
				$i++;
			}
			$retval = $tmp . $donvi[$dv-1] . $retval;
			$dv++;
		}
		$retval = str_replace('không mươi không ', '',$retval);
		$retval = str_replace('không mươi', 'lẻ',$retval);
		$retval = str_replace('i không', 'i',$retval);
		$retval = str_replace('i năm', 'i lăm',$retval);
		$retval = str_replace('một mươi', 'mười',$retval);
		$retval = str_replace('mươi một', 'mươi mốt',$retval);
		return $retval;
	}
    
    public static function VndText($amount) {

        $Text = array("không", "một", "hai", "ba", "bốn", "năm", "sáu", "bảy", "tám", "chín");
        $TextLuythua = array("", "nghìn", "triệu", "tỷ", "ngàn tỷ", "triệu tỷ", "tỷ tỷ");
        $textnumber = "";
        $negative = false;
        
        if ($amount <= 0) {
            $negative = true;
            $amount = abs($amount);
        }        
        
        $length = strlen($amount);

        for ($i = 0; $i < $length; $i++)
            $unread[$i] = 0;

        for ($i = 0; $i < $length; $i++) {
            $so = substr($amount, $length - $i - 1, 1);

            if (($so == 0) && ($i % 3 == 0) && ($unread[$i] == 0)) {
                for ($j = $i + 1; $j < $length; $j ++) {
                    $so1 = substr($amount, $length - $j - 1, 1);
                    if ($so1 != 0)
                        break;
                }

                if (intval(($j - $i ) / 3) > 0) {
                    for ($k = $i; $k < intval(($j - $i) / 3) * 3 + $i; $k++)
                        $unread[$k] = 1;
                }
            }
        }

        for ($i = 0; $i < $length; $i++) {
            $so = substr($amount, $length - $i - 1, 1);
            if ($unread[$i] == 1)
                continue;

            if (($i % 3 == 0) && ($i > 0))
                $textnumber = $TextLuythua[$i / 3] . " " . $textnumber;

            if ($i % 3 == 2)
                $textnumber = 'trăm ' . $textnumber;

            if ($i % 3 == 1)
                $textnumber = 'mươi ' . $textnumber;


            $textnumber = $Text[$so] . " " . $textnumber;
        }

        //Phai de cac ham replace theo dung thu tu nhu the nay
        $textnumber = str_replace("không mươi", "lẻ", $textnumber);
        $textnumber = str_replace("lẻ không", "", $textnumber);
        $textnumber = str_replace("mươi không", "mươi", $textnumber);
        $textnumber = str_replace("một mươi", "mười", $textnumber);
        $textnumber = str_replace("mươi năm", "mươi lăm", $textnumber);
        $textnumber = str_replace("mươi một", "mươi mốt", $textnumber);
        $textnumber = str_replace("mười năm", "mười lăm", $textnumber);
        $textnumber = ($negative)?"Âm ".$textnumber:$textnumber;

        return ucfirst($textnumber . " đồng chẵn");
    }

    public static function formatNumber($number)
	{
		return $number?rtrim(rtrim(number_format($number, 2, ".", ","), '0'), '.'):$number;
	}

	public static function formatInteger($number)
	{
		return $number?number_format($number, 0, ",", "."):$number;
	}
	
	public static function cloneData($mix)
	{
		if(is_object($mix))
		{
			$retval = clone $mix;
			foreach($retval as $key=>$value)
			{
				$retval->$key = self::cloneData($value);
			}
		}
		elseif(is_array($mix))
		{
			$retval = $mix;
			foreach($retval as $key=>$value)
			{
				$retval[$key] = self::cloneData($value);
			}
		}
		else
		{
			$retval = $mix;
		}
		return $retval;
	}
	public static function copyObject($mix,&$retval)
	{
		if(is_object($mix))
		{
			$retval = clone $mix;
			foreach($retval as $key=>$value)
			{
				$retval->$key = self::cloneData($value);
			}
		}
		elseif(is_array($mix))
		{
			$retval = $mix;
			foreach($retval as $key=>$value)
			{
				$retval[$key] = self::cloneData($value);
			}
		}
		else
		{
			$retval = $mix;
		}
		//return $retval;
	}
	public static function formatMoney($number,$code = 'VND')
	{
	    $number = round($number, 0);
		$retval = $number;
		if(!self::$arrFormat || !is_array(self::$arrFormat) || !isset(self::$arrFormat[$code]))
		{
			$model = new Qss_Model_Currency();
			self::$arrFormat[$code] = $model->getByCode($code);
		}
		$format = self::$arrFormat[$code];
		if($format)
		{
			$retval = number_format($number/1000, (int)$format->Precision, $format->DecPoint, $format->ThousandsSep);
		}
		return $retval;
	}
        
	public static function textToHtml($retval)
    {
		$order = array("\r\n", "\n", "\r", "\n\r", "<br>");
        $replace = array("<br/>", "<br/>", "<br/>", "<br/>", "<br/>");
        $retval = str_replace($order, $replace, $retval);
        return $retval;
	}

	public static function htmlToText($html)
	{
		$order = array("<br>", "<\br>", "<br/>","<br />", "<p>", "</p>", "<p/>");
		return str_replace($order,"\n", $html);
	}
	
	public static function tsf($array)
	{
		$Tx = 0;
		$Ty = 0;
		$Txy = 0;
		$Tx2 = 0;
		
		foreach ($array as $key=>$val)
		{
			$i = $key + 1;
			$Tx += $i;
			$Ty += $val;
			$Txy += $i*$val;
			$Tx2 += $i*$i;
		}
		$n = count($array);
		$m = (($n*$Txy) - ($Tx * $Ty)) / ($n*$Tx2 - ($Tx* $Tx));
		$b = ($Ty - ($m * $Tx)) / $n;
		return ($m * ($n + 1)) + $b;	
	}
	
}
?>