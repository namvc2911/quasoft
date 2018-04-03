<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Mobile_Controller extends Qss_Lib_Controller
{
	/**
	 *
	 * @return void
	 */
	public function init ()
	{
		parent::init();
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mobile.php';
	}
	
}
?>