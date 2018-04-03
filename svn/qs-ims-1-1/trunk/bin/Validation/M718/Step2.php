<?php
class Qss_Bin_Validation_M718_Step2 extends Qss_Lib_WValidation
{
	/**
	 * 1. Danh sách còn trống ?
	 * 2. Ngày chuyển hàn còn trống ?
	 * 3. Đã cập nhật hết lot và serial chưa ?
	 * 4. Có thuộc tính nào bắt buộc mà chưa cập nhật hay không?
	 * 5. Thuộc tính trong bảng lot & serial có giống thuộc tính trên line hay không ?
	 * 6. Kiểm tra xem có thể đáp ứng được không ?
	 */
	public function onNext()
	{
		parent::init();
		$deliveryDate   = $this->_params->Ngay;
		$serialLot      = $this->_params->OThuocTinhChiTiet;
		$model 			= new Qss_Model_Extra_Warehouse();
		$product        = new Qss_Model_Extra_Products();
				
		// Kiểm tra ngày trả hàng
		if(!$deliveryDate || $deliveryDate == '0000-00-00') 
		{
			$this->setError();
			$this->setMessage("{$this->_translate(2)}");
		}
		
		$total              = $model->getTotalReceiveHasAttr($this->_params->IFID_M718, 'M718');
		$totalStockStatus   = $this->_params->SoLuong;
		
		if($total && $total != $totalStockStatus)
		{
			$this->setError();
			$this->setMessage("{$this->_translate(3)}");
		}
		
		// Kiểm tra xem có thuộc tính bắt buộc hay ko ?
		$checkRequires = $product->checkAttributeRequires($this->_params->MaSP);
		if(!$this->_params->ThuocTinh && $checkRequires)
		{
			$this->setError();
			$this->setMessage("- {$this->_translate(4)} {$this->_params->MaSP} {$this->_translate(5)}");
		}
	}
	
	/**
	 * Số lượng sản phẩm trong kho đủ để đáp ứng ?
	 * Nếu đủ thì cập nhật vào kho !
	 */
	public function next()
	{
		parent::init();
		//$this->updateWarehouse();
	}
}