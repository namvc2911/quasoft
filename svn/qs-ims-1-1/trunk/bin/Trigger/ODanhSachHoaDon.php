<?php
class Qss_Bin_Trigger_ODanhSachHoaDon extends Qss_Lib_Trigger
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
	/**
	 * onInsert
	 */
	public function onInsert($object)
	{
		parent::init();
	}
	/**
	 * onUpdate
	 */
        public function onUpdate($object)
	{
		parent::init();
	}
    
	
	
	private function updateTax($object) 
	{
		$model = new Qss_Model_Extra_Purchase();
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
		$service = $this->services->Form->Manual('M404',$object->i_IFID,$params,false);
		//var_dump($params); die;
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
	}
}
?>