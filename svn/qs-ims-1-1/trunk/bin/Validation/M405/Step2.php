<?php
class Qss_Bin_Validation_M405_Step2 extends Qss_Lib_WValidation
{
	/**
	 * onNext
	 * 1. Kiểm tra danh sách báo giá mua hàng còn trống hay ko?
	 * 2. Kiểm tra xem thuộc tính chi tiết
	 * của sản phẩm đã được cập nhật hết chưa
	 */
	public function onNext()
	{
		parent::init();
		$danhSach = $this->_params->OChiTietYeuCau;
		$product  = new Qss_Model_Extra_Products();
		
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage('- Danh sách yêu cầu mua hàng còn trống.');
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