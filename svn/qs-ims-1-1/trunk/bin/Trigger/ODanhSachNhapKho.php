<?php
class Qss_Bin_Trigger_ODanhSachNhapKho extends Qss_Lib_Trigger
{

	/**
	 * onDeleted
	 * - Xoa trang thai luu tru
	 */
	public function onDeleted($object)
	{
		parent::init();
		$this->deleteStockStatus($object);
	}
	
	/**
	 * onUpdated
	 * - Xoa trang thai luu tru
	 */
	public function onUpdated($object)
	{
		parent::init();
		$this->deleteStockStatus($object);
	}

		
	private function deleteStockStatus($object)
	{
		$params     = array();
		$mInv       = new Qss_Model_Inventory_Inventory();
		$IOIDArray  = $mInv->getStockStatusOfInput($object->i_IFID);

		foreach ($IOIDArray as $val)
		{
			if($val->InputLineIOID == 0)
			{
				$params['OThuocTinhChiTiet'][] = $val->IOID;
			}
		}

		//echo '<pre>'; print_r($params); die;

		if(isset($params['OThuocTinhChiTiet']) && count($params['OThuocTinhChiTiet']))
		{
			// Tiến hành xóa các dòng trong bảng lot & serial tương ứng với IOID của params truyền vào
			$service = $this->services->Form->Remove('M402',$object->i_IFID,$params);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
	
}
?>