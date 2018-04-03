<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Array
{
	public static function countDistinctSQL($array,$field)
	{
		$retval = array();
		foreach ($array as $item)
		{
			if(!isset($retval[$item->{$field}]))
			{
				$retval[$item->{$field}] = 0;
			}
			$retval[$item->{$field}]++;
		}
		return $retval;
	}
}
?>