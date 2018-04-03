<?php
class Qss_Bin_Validation_M406_Step2 extends Qss_Lib_WValidation
{
	/**
	 * onNext
	 * 1. Kiểm tra xem danh sách báo giá còn trống hay không?
	 * 2. Kiểm tra xem thuộc tính của sản phẩm có bắt buộc?
	 * 3. Ngày báo giá còn trống?
	 * của sản phẩm đã được cập nhật hết chưa
	 */
	public function onNext()
	{
		parent::init();
		$danhSach       = $this->_params->ODSBGMuaHang;
		$deliveryDate   = $this->_params->NgayBaoGia;//NgayBaoGia
		$product  = new Qss_Model_Extra_Products();
		
		// Kiểm tra ngày báo giá còn trống
		if(!$deliveryDate) 
		{
			$this->setError();
			$this->setMessage('- Ngày báo giá còn trống.');
		}
		
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage('- Danh sách báo giá mua hàng còn trống.');
		}
		
		foreach ($danhSach as $value) {
			if(!$value->ThuocTinh && $product->checkAttributeRequires($value->MaSP))
			{
				$this->setError();
				$this->setMessage('- Sản phẩm '.$value->MaSP.' có thuộc tính bắt buộc chưa được cập nhật.');
			}
		}
	}
}