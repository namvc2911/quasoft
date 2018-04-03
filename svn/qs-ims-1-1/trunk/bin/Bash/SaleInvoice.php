<?php
class Qss_Bin_Bash_SaleInvoice extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$aLines = array();
		$aTaxs = array();
		/*$grossTotal = $this->_params->TongTien - $this->_params->GiamTru;*/
		$orderNo = $this->_params->SoDonHang;
		
		foreach($this->_params->ODSDonBanHang as $val)
		{
			$aLines[$val->IOID] = array(
							   'MaSP'=>$val->MaSP
							  ,'TenSP'=>$val->TenSP
                                                          ,'DonViTinh'=>$val->DonViTinh
							  ,'ThuocTinh'=>$val->ThuocTinh
							  ,'SoDonHang'=>$orderNo
							  /*,'SoDonChuyenHang'=>$val->MaNCC
							  ,'SoDonNhanTra'=>$val->MaNCC*/
							  ,'DonGia'=>$val->DonGia
							  ,'NhomThue'=>$val->NhomThue
							  ,'DonViTinh'=>$val->DonViTinh
							  ,'SoLuong'=>$val->SoLuong
							  ,'ThanhTien'=>$val->ThanhTien
							  );
		}	
		
		
		foreach($this->_params->OThueDonHang as $val)
		{
			$aTaxs[$val->IOID] = array(
							'MaThue'=>$val->MaThue,
							'SoTienChiuThue'=>$val->SoTienChiuThue/1000,
							'SoTienThue'=>($val->SoTienThue/100000)
			);
		}		
								
		
		$invoice = array('OHoaDonBanHang'=>array($this->_params->IOID=>
											array(
											   'TenHD'=>'Tạo tự động từ đơn hàng '.$orderNo,
											   'KhachHang'=>$this->_params->TenKH
											/*  ,'SoChungTu'=>''
											  ,'NgayChungTu'=>''
											  ,'SoHoaDon'=>''
											  ,'NgayHoaDon'=>''*/
											  ,'LoaiHoaDon'=>'Thông thường'
											  ,'LoaiTien'=>$this->_params->LoaiTien
											  /*,'GiaTri'=>$this->_params->GiaTri*/
											 /* ,'Thue'=>$this->_params->Thue*/
											 /* ,'TongTien'=>$this->_params->TongTien*/
											  ,'GiamTru'=>$this->_params->GiamTru
											  /*,'TongTienDH'=>$grossTotal*/
											  /*,'SoTienDaTT'=>''*/
											  /*,'ChiPhiVanChuyen'=>$this->_params->ChiPhiVanChuyen*/
											  )),
						  'ODSHDBanHang'=>$aLines,
						  'OThueDonHang'=>$aTaxs
		);
		/*echo '<pre>';
		print_r($invoice);*/
		if(!$this->_form->getIOIDLink($this->_params->IOID)){
			$service = $this->services->Form->Manual('M508',0,$invoice,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			//$this->_form->saveIOIDLink ($this->_params->IFID_M401,$service->getData());
		}
		else 
		{
			$this->setError();
			$this->setMessage("{$this->_translate(1)}");/*Hóa đơn đã được tạo từ trước.*/
		}
	/*	echo "<pre>";
		print_r($service);*/


	}
}