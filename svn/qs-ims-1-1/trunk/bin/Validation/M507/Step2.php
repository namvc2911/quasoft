<?php
class Qss_Bin_Validation_M507_Step2 extends Qss_Lib_Warehouse_WValidation
{
	/*
	 * - Danh sách trả hàng còn trống?
	 * - Ngày nhận hàng còn trống ?
	 * - Thuộc tính bắt buộc
	 */
	public function onNext()
	{
		parent::init();
		$danhSach       = $this->_params->ODanhSachHangTL;
		$deliveryDate   = $this->_params->NgayNhan;
		$product        = new Qss_Model_Extra_Products();
		
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
		
		// Kiểm tra ngày nhận lại
		if(!$deliveryDate || $deliveryDate == '0000-00-00') 
		{
			$this->setError();
			$this->setMessage($this->_translate(2));
		}

		// Kiểm tra xem có thuộc tính bắt buộc hay ko ?
		foreach ($danhSach as $value) 
		{
			$checkRequires = $product->checkAttributeRequires($value->MaSP);
			if(!$value->ThuocTinh && $checkRequires)
			{
				$this->setError();
				$this->setMessage("{$this->_translate(4)} $value->MaSP {$this->_translate(5)}");
			}
		}

		// Check order match with plan 
		$orderField = array('Item'=>'MaSP', 'Attr'=>'ThuocTinh', 'Qty'=>'SoLuong');
		$planField  = array('Item'=>'MaSP', 'Attr'=>'ThuocTinh', 'Qty'=>'SoLuong');
		if($this->compareOrderWithDeliveryPlan($this->_params->ODanhSachHangTL, $orderField, 
											$this->_params->OKeHoachNhanHang, $planField))
		{
			$this->setError();
			$this->setMessage($this->_translate(8));
		}
	}
	
	/**
	 * create incoming shipments
	 */
	public function next()
	{
		parent::init();
		$field = array('MaSP', 'ThuocTinh', 'SoLuong', 'NgayNhanHang', 'DonGia', 'MaSP', 'ThuocTinh','','SoChungTu','DonViTinh') ;
		$insertFieldLabel = array('Partner'=>'MaDoiTac', 'Date'=>'Ngay', 
								  'Item'=>'MaSP', 'Attr'=>'ThuocTinh', 
								  'Qty'=>'SoLuong', 'Price'=>'DonGia',
								  'Module'=>'Module', 'Des'=>'MoTa'
                                                                   ,'UOM'=>'DonViTinh');
		$this->insertIncomingOutgoing($field, $insertFieldLabel,'OHangDoiNhap', 'ODanhSachHangTL', 'OKeHoachNhanHang' , 
									  'M507', 'M610', $this->_params->MaKH, 'ONhanHangTL');
	}
}