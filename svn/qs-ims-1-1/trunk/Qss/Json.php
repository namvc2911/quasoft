<?php

class Qss_Json
{
	public static function encode($value)
	{
		return json_encode($value);
	}
	
	public static function decode($value)
	{
		return json_decode($value,true);
	}
}
?>