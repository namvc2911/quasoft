<?php
class Qss_Bin_Validation_M403_Step2 extends Qss_Lib_Warehouse_WValidation
{
	/**
	 * 1. Kiểm tra đơn hàng rổng ?
	 * 2. Kiểm tra ngày trả hàng?
	 * 3. Thuộc tính bắt buộc?
	 */
	public function onNext()
	{
		
		parent::init();
		$danhSach       = $this->_params->ODanhSachTraHang;
		$deliveryDate   = $this->_params->NgayTraHang;
		$product        = new Qss_Model_Extra_Products();
		
		// Kiem tra don hang rong ?
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
		
		// Kiểm tra ngày trả hàng
		if(!$deliveryDate || $deliveryDate == '0000-00-00') 
		{
			$this->setError();
			$this->setMessage($this->_translate(2));
		}		
		
		// Kiểm tra xem có thuộc tính bắt buộc hay ko ?
		foreach ($danhSach as $value) {
			$checkRequires = $product->checkAttributeRequires($value->MaSP);
			if(!$value->ThuocTinh && $checkRequires)
			{
				$this->setError();
				$this->setMessage("{$this->_translate(4)} {$value->MaSP} {$this->_translate(5)}");
			}
		}
		
		// Check order match with plan 
		$orderField = array('Item'=>'MaSP', 'Attr'=>'ThuocTinh', 'Qty'=>'SoLuong');
		$planField  = array('Item'=>'MaSP', 'Attr'=>'ThuocTinh', 'Qty'=>'SoLuong');
		if($this->compareOrderWithDeliveryPlan($this->_params->ODanhSachTraHang, $orderField, 
											$this->_params->OKeHoachGiaoHang, $planField))
		{
			$this->setError();
			$this->setMessage($this->_translate(18));
		}
	}
	
	/**
	 * create outgoing shipments
	 */
	public function next()
	{
		parent::init();
		$field = array('MaSP', 'ThuocTinh', 'SoLuong', 'NgayGiaoHang', 'DonGia', 'MaSP', 'ThuocTinh', '', 'SoChungTu', 'DonViTinh') ;
		$insertFieldLabel = array('Partner'=>'MaDoiTac', 'Date'=>'Ngay', 
								  'Item'=>'MaSP', 'Attr'=>'ThuocTinh', 
								  'Qty'=>'SoLuong', 'Price'=>'DonGia',
								  'Module'=>'Module', 'Des'=>'MoTa'
                                                                  ,'UOM'=>'DonViTinh');
		$this->insertIncomingOutgoing($field, $insertFieldLabel, 'OHangDoiXuat', 'ODanhSachTraHang', 'OKeHoachGiaoHang' , 
									  'M403', 'M611', $this->_params->MaNCC, true, 'OTraHang');
		//$this->setError();
	}
	

}