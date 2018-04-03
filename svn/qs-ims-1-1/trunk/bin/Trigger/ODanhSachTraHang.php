<?php
class Qss_Bin_Trigger_ODanhSachTraHang extends Qss_Lib_Trigger
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
	 * delete lot, serial 
	 */
    public function onUpdate($object)
	{
		parent::init();
		if(!$this->isError())
		{
			$this->deleteSerialLot($object);
		}
	}
	/**
	 * delete lot, serial
	 */
	public function onDelete($object)
	{
		parent::init();
		$this->deleteSerialLot($object);
	}
    
	private function deleteSerialLot($object)
	{
		$model = new Qss_Model_Extra_Products();
		
		// Lấy mã sản phẩm trên dòng đang xét
		$itemCode = '';		
		foreach ($this->_params->ODanhSachTraHang as $value) {
			if($value->IOID == $object->i_IOID)
			{
				$itemCode = $value->MaSP;
			}
		}
		
		// So sánh mã sản phẩm được thay đổi với giá trị trước đó.
		// Nếu có sự khác biệt ta tiến hành xóa lot & serial và IOIDLink
		$old = $itemCode;
		$new = $object->getFieldByCode('MaSP')->getValue();
		
		if($old != $new)
		{
			// IOIDArray sẽ giúp lưu những giá trị IOID cần xóa trong bảng lot & serial vả qsioidlink
			// param mảng tham số lưu các giá trị IOID này.
			$IOIDArray  = $model->getOldStockStatus($object->FormCode, $object->i_IOID, $object->i_IFID);
		   	$params     = array();
			foreach ($IOIDArray as $val)
			{
				$params[] = $val->IOID;
			}

			if(count($params))
			{
				/*
				// Tiến hành xóa IOIDLink
				foreach ($params as $val) {
					$this->_form->deleteIOIDLink($object->i_IOID, $val);
				}
				*/
				
				// Tiến hành xóa các dòng trong bảng lot & serial tương ứng với IOID của params truyền vào
				$params = array('OThuocTinhChiTiet'=>$params);
				$service = $this->services->Form->Remove('M403',$object->i_IFID,$params);
				
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
		}
	}
	private function updateTax($object) 
	{
		$model = new Qss_Model_Extra_Purchase();
		$taxs = $model->getTaxsByReturn($object->i_IFID);
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
		$service = $this->services->Form->Manual('M403',$object->i_IFID,$params,false);
		//var_dump($params); die;
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
	}
}
?>