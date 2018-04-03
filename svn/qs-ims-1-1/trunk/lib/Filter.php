<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Lib_Filter
{
	protected $_user;
	
	protected $_db;
	
	protected $_params;
	
	/**
	 *
	 * @return void
	 */
	public function __construct($user,$params = array())
	{ 
		$this->_user = $user;
		$this->_db = Qss_Db::getAdapter('main');
		$this->_params = $params;
	}
	public function getWhere(){return '';}
	
	public function getJoin(){return '';}
	
	public function getRights($ifid){return 63;}
}
?>