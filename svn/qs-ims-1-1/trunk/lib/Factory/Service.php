<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Factory_Service extends Qss_Service_Abstract
{

	/**
	 *
	 * @param Qss_Model_Object $object
	 * @param $data
	 * @return boolean
	 */
	public static function createInstance()
	{
		return new self();
	}
}
?>