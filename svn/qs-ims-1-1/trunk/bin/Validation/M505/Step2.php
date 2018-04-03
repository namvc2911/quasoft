<?php
class Qss_Bin_Validation_M505_Step2 extends Qss_Lib_Warehouse_WValidation
{
	public function onNext()
	{
		parent::init();
		$danhSach = $this->_params->ODSDonBanHang;
		$product  = new Qss_Model_Extra_Products();
		
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
		
		// Kiểm tra xem có thuộc tính bắt buộc hay ko ?
		foreach ($danhSach as $value) {
			if(!$value->ThuocTinh && $product->checkAttributeRequires($value->MaSP))
			{
				$this->setError();
				$this->setMessage($this->_translate(2).$value->MaSP.$this->_translate(3));
			}
		}
		
		// Check order match with plan 
		$orderField = array('Item'=>'MaSP', 'Attr'=>'ThuocTinh', 'Qty'=>'SoLuong');
		$planField  = array('Item'=>'MaSP', 'Attr'=>'ThuocTinh', 'Qty'=>'SoLuong');
		if($this->compareOrderWithDeliveryPlan($this->_params->ODSDonBanHang, $orderField, 
											$this->_params->OKeHoachGiaoHang, $planField))
		{
			$this->setError();
			$this->setMessage($this->_translate(6));
		}
	}
	
	public function next()
	{
		parent::init();
		$field = array('MaSP', 'ThuocTinh', 'SoLuong', 'NgayXuatHang', 'DonGia', 'MaSP', 'ThuocTinh', '', 'SoDonHang', 'DonViTinh') ;
		$insertFieldLabel = array('Partner'=>'MaDoiTac', 'Date'=>'Ngay', 
								  'Item'=>'MaSP', 'Attr'=>'ThuocTinh', 
								  'Qty'=>'SoLuong', 'Price'=>'DonGia',
								  'Module'=>'Module', 'Des'=>'MoTa',
                                                                    'UOM'=>'DonViTinh');
		$this->insertIncomingOutgoing($field, $insertFieldLabel, 'OHangDoiXuat', 'ODSDonBanHang', 
									  'OKeHoachGiaoHang' ,'M505', 'M611', $this->_params->MaKhachHang
									  , true, 'ODonBanHang');
	}
	
	public function onAlert()
	{
		parent::init();
		$deliveryDate       = $this->_params->NgayYCNH;
		$orderDate          = $this->_params->NgayDatHang;
		$now = time();
		if($now > Qss_Lib_Date::i_fMysql2Time($deliveryDate)) 
		{
			$this->setError();
			$this->setMessage($this->_translate(4));
		}
		
		if(Qss_Lib_Date::i_fMysql2Time($deliveryDate) < Qss_Lib_Date::i_fMysql2Time($orderDate))
		{
			$this->setError();
			$this->setMessage($this->_translate(5));
		}
	}
}