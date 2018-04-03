<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Memcache
{

	protected $iTtl = 600; // Time To Live

	protected $bEnabled = false; // Memcache enabled?

	protected $oCache = null;

	protected static $_instance;
	// constructor
	public function __construct() {
		if (class_exists('Memcache')) {
			$this->oCache = new Memcache();
			$this->bEnabled = true;
			if (! $this->oCache->connect('localhost', 11211))  { // Instead 'localhost' here can be IP
				$this->oCache = null;
				$this->bEnabled = true;
			}
		}
	}
	public static function getInstance ()
	{
		if ( null === self::$_instance )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	// get data from cache server
	function get($sKey,$defaul = null) {
		$vData = $this->oCache->get($sKey);
		return false === $vData ? $defaul : $vData;
	}

	// save data to cache server
	function set($sKey, $vData) {
		//Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib).
		return $this->oCache->set($sKey, $vData, 0, $this->iTtl);
	}

	// delete data from cache server
	function delete($sKey) {
		return $this->oCache->delete($sKey);
	}
	// delete all data from cache server
	function flush() {
		return $this->oCache->flush();
	}
	function isEnabled() {
		return $this->bEnabled;
	}
}
?>