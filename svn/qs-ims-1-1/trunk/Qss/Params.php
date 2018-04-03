<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Params
{

	protected static $_instance;

	public $sessions;

	public $requests;

	public $responses;

	public $cookies;

	public $configs;

	public $registers;

	/**
	 * Build constructor 
	 * @return void
	 */
	public function __construct ()
	{
		$this->registers = new Qss_Register();
		$this->sessions = new Qss_Session();
		$this->requests = new Qss_Request();
		$this->responses = new Qss_Response();
		$this->cookies = new Qss_Cookie();
		$this->configs = new Qss_Config();
	}

	/**
	 * 
	 * @return Qss_Params
	 */
	public static function getInstance ()
	{
		if ( null === self::$_instance )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
?>