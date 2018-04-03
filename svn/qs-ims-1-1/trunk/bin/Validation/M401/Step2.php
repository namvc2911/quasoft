<?php
class Qss_Bin_Validation_M401_Step2 extends Qss_Lib_Warehouse_WValidation
{
	/**
     *  onNext():
	 *	1. Kiểm tra danh sách đơn hàng còn trống hay ko?
	 *  2. Kiểm tra xem sản phẩm có thuộc tính bắt buộc hay không?
	 */
	public function onNext()
	{
		parent::init();
		$product = new Qss_Model_Extra_Products();
				
		// Yeu cau danh sach don hang phai co it nhat mot dong don hang
		if(count((array)$this->_params->ODSDonMuaHang) == 0)
        {
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
		
		// Kiểm tra xem có thuộc tính bắt buộc nào chưa được cập nhật.
        if(Qss_Lib_System::fieldActive('ODSDonMuaHang', 'ThuocTinh'))
        {
            foreach ($this->_params->ODSDonMuaHang as $value)
            {
                if(!$value->ThuocTinh && $product->checkAttributeRequires($value->MaSP))
                {
                    $this->setError();
                    $this->setMessage("{$this->_translate(2)} {$value->MaSP} {$this->_translate(3)}");
                }
            }
        }
	}
	
	/**
     * onAlert(): Step2
	 * 1. Kiểm tra quá hạn yêu cầu ?	
	 * 2. So sánh ngày yêu cầu với ngày đặt hàng.
	 */
	public function onAlert()
	{
		parent::init();
		$deliveryDate = $this->_params->NgayYCNH;
		$orderDate    = $this->_params->NgayDatHang;
		$now 	      = Qss_Lib_Date::i_fMysql2Time(date('d-m-Y'));
		
		// Kiem tra don hang da qua han hay chua
		if($now > Qss_Lib_Date::i_fMysql2Time($deliveryDate)) 
		{
			$this->setError();
			$this->setMessage($this->_translate(4));
		}
		
		// Ngay yeu cau phai la ngay tiep theo hoac la ngay dat hang
		if(Qss_Lib_Date::i_fMysql2Time($deliveryDate) < Qss_Lib_Date::i_fMysql2Time($orderDate))
		{
			$this->setError();
			$this->setMessage($this->_translate(5));
		}
	}
}