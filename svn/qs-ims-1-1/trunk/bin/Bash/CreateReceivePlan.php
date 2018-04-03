<?php
class Qss_Bin_Bash_CreateReceivePlan extends Qss_Lib_Bin
{
	// @todo: Cần refresh lại trang khi thêm
	// @todo: Chưa chọn được đúng thuộc tính của sản phẩm (ko thể dùng auto cho thuộc tính)
	public function __doExecute()
	{
		$insertReceivePlan = array(); // Mang cap nhat ke hoach nhan hang de them vao csdl
		$aiInsert          = 0;       // Chi so mang insert
		$deleteReceivePlan = array(); // Mang ke hoach nhan hang cu de xoa di khoi csdl
		
		
		if($this->_form->FormCode == 'M401')
		{
			$objectAlias = 'ODSDonMuaHang';
			$itemAlias   = 'MaSP';
                        $uomAlias    = 'DonViTinh';
			$attrAlias   = 'ThuocTinh';
			$qtyAlias    = 'SoLuong';
			$dateAlias   = 'NgayYCNH';
			$ifidAlias   = 'IFID_M401';
			$module      = 'M401';
		}
		elseif($this->_form->FormCode == 'M507')
		{
			$objectAlias = 'ODanhSachHangTL';
			$itemAlias   = 'MaSP';
                        $uomAlias    = 'DonViTinh';
			$attrAlias   = 'ThuocTinh';
			$qtyAlias    = 'SoLuong';
			$dateAlias   = 'NgayNhan';		
			$ifidAlias   = 'IFID_M507';	
			$module      = 'M507';
		} 
		
		
		// Lay mang ke hoach nhan hang cu de xoa
		foreach ($this->_params->OKeHoachNhanHang as $item)
		{
			$deleteReceivePlan['OKeHoachNhanHang'][] = $item->IOID; 
		}
		 
		// Lay mang ke hoach nhan hang
		foreach ($this->_params->$objectAlias as $item)
		{
			
			$insertReceivePlan['OKeHoachNhanHang'][$aiInsert]['MaSP']         = $item->$itemAlias;
                        $insertReceivePlan['OKeHoachNhanHang'][$aiInsert]['DonViTinh']    = $item->$uomAlias;
			$insertReceivePlan['OKeHoachNhanHang'][$aiInsert]['ThuocTinh']    = $item->$attrAlias;
			$insertReceivePlan['OKeHoachNhanHang'][$aiInsert]['SoLuong']      = $item->$qtyAlias;
			$insertReceivePlan['OKeHoachNhanHang'][$aiInsert]['NgayNhanHang'] = $this->_params->$dateAlias;
			$aiInsert++;
		}
		
		// Xoa ke hoach nhan hang cu
		if(count($deleteReceivePlan))
		{
			$remove =  $this->services->Form->Remove($module, $this->_params->$ifidAlias, $deleteReceivePlan);
			if($remove->isError())
			{
				$this->setError();
				$this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
		
		// Cap nhat vao co so du lieu
		if(count($insertReceivePlan))
		{
			$insert =  $this->services->Form->Manual($module, $this->_params->$ifidAlias, $insertReceivePlan,false);
			if($insert->isError())
			{
				$this->setError();
				$this->setMessage($insert->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
		
	}
	
}