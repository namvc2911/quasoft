<?php
class Qss_Bin_Validation_M509_Step2 extends Qss_Lib_WValidation
{
	/**
	 * onNext
	 * 1. Báo giá còn trống ?
	 * 2. Ngày báo giá ?
	 * 3. Thuộc tính yêu cầu bắt buộc?
	 */
	public function onNext()
	{
			parent::init();
		$danhSach 		= $this->_params->ODSBGBanHang;
		$deliveryDate   = $this->_params->NgayBaoGia;//NgayBaoGia
		$product  		= new Qss_Model_Extra_Products();
		
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage('- Danh sách báo giá còn trống.');
		}
		
		// Kiểm tra ngày báo giá còn trống
		if(!$deliveryDate) 
		{
			$this->setError();
			$this->setMessage('- Ngày báo giá còn trống.');
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