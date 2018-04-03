<?php
/**
 *
 * @author HuyBD
 *
 */
class Ws_MaintenanceController extends Qss_Controller
{

	public function init ()
	{
		parent::init();
		ini_set("soap.wsdl_cache_enabled", "0");
	//	$this->params->responses->setHeader('', 'Content-Type: text/html; charset=utf-8');
	}

	/**
	 *
	 * @return void
	 */
	public function hoursAction ()
	{
		$soap = new SoapServer(QSS_BASE_URL .'/ws/maintenance/hours/wsdl');
		$soap->setClass("Qss_Model_Maintenance_Ws_Hours");
		$soap->handle();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function hoursWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/hours.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function productionAction ()
	{
		$soap = new SoapServer(QSS_BASE_URL .'/ws/maintenance/production/wsdl');
		$soap->setClass("Qss_Model_Maintenance_Ws_Production");
		$soap->handle();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function electricWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/electric.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
/**
	 *
	 * @return void
	 */
	public function electricAction ()
	{
		$soap = new SoapServer(QSS_BASE_URL .'/ws/maintenance/electric/wsdl');
		$soap->setClass("Qss_Model_Maintenance_Ws_Production");
		$soap->handle();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function productionWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/production.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>