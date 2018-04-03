<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Util
{

	/**
	 *	Convert array to object
	 *
	 * @param $array
	 * @return object
	 */
	public static function arrayToObject ($array)
	{
		if ( !is_array($array) )
		{
			return $array;
		}
		$object = new stdClass();
		if ( is_array($array) && count($array) > 0 )
		{
			foreach ($array as $name => $value)
			{
				$name = trim($name);
				if ( $name !== '' )
				{
					$object->$name = self::arrayToObject($value);
				}
			}
			return $object;
		}
		else
		{
			return false;
		}
	}

	public static function hmac_md5 ($data, $key)
	{
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		

		$b = 64; // byte length for md5
		if ( strlen($key) > $b )
		{
			$key = pack("H*", md5($key));
		}
		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;
		
		return md5($k_opad . pack("H*", md5($k_ipad . $data)));
	}

}
?>