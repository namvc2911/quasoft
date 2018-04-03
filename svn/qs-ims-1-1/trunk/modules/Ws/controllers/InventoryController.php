<?php
/**
 *
 * @author HuyBD
 *
 */
class Ws_InventoryController extends Qss_Controller
{

	public function init ()
	{
		parent::init();
		ini_set("soap.wsdl_cache_enabled", "0");
	//	$this->params->responses->setHeader('', 'Content-Type: text/html; charset=utf-8');
	}

	/**
	 * Tồn kho
	 * @return void
	 */
	public function syncAction ()
	{
		$soap = new SoapServer(QSS_BASE_URL .'/ws/inventory/sync/wsdl');
		$soap->setClass("Qss_Model_Inventory_Ws_Sync");
		$soap->handle();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function syncWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/inventory/sync.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 * Phiếu nhập
	 * @return void
	 */
	public function receiptAction ()
	{
		$soap = new SoapServer(QSS_BASE_URL .'/ws/inventory/receipt/wsdl');
		$soap->setClass("Qss_Model_Inventory_Ws_Receipt");
		$soap->handle();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function receiptWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/inventory/receipt.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>