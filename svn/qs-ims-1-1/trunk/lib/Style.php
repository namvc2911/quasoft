<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Lib_Style
{
	protected $_data;
	
	protected $_db;
	
	/**
	 *
	 * @return void
	 */
	public function __construct($data)
	{ 
		$this->_data = $data;
		$this->_db = Qss_Db::getAdapter('main');
		$this->_params = new stdClass();
	}
}
?>