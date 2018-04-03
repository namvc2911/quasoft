<?php
class Qss_Bin_Trigger_ODSHDBanHang extends Qss_Lib_Trigger
{
	/**
	 * Tax
	 */
	public function onUpdated($object)
	{
		parent::init();
		$this->updateTax($object);
	}
	/**
	 * Tax
	 */
	public function onInserted($object)
	{
		parent::init();
		$this->updateTax($object);
	}
	/**
	 * Tax
	 */
	public function onDeleted($object)
	{
		parent::init();
		$this->updateTax($object);
	}
	
	private function updateTax($object)
	{
		$model = new Qss_Model_Extra_Sale();
		$taxs = $model->getTaxsByInvoice($object->i_IFID);
		$aTaxs = array();
		
		foreach($taxs as $val)
		{
			$aTaxs[] = array(
							'MaThue'=>$val->MaThue,
						  	'TenThue'=>$val->TenThue , 
						  	'SoTienChiuThue'=>$val->ThanhTien/1000,
						  	'SoTienThue'=>($val->ThanhTien * $val->PhanTramChiuThue/100000)
			);
			
		}
		$params = array('OThueDonHang'=>$aTaxs);
		$service = $this->services->Form->Manual('M508',$object->i_IFID,$params,false);
		//var_dump($params); die;
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
	}
}
?>