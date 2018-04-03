<?php
class Qss_Bin_Bash_CreateDeliveryPlan extends Qss_Lib_Bin
{
	// @todo: Cần refresh lại trang khi thêm
	// @todo: Chưa chọn được đúng thuộc tính của sản phẩm (ko thể dùng auto cho thuộc tính)
	public function __doExecute()
	{
		$model               = new Qss_Model_Extra_Sale();
		$deleteDeliveryPlan  = array(); // Mang ke hoach giao cu dung de xoa 
		$aInsertDeliveryPlan = array(); // Mang ke hoach giao mac dinh dung de them moi
		$aiInsert            = 0;       // Tu tang cua mang mac dinh
		
		if($this->_form->FormCode == 'M403')
		{
			$mainView  = 'OTraHang'; 
			$subView   = 'ODanhSachTraHang';
			$dateAlias = 'NgayTraHang';
			$ifidAlias = 'M403';
			$ifid      = $this->_params->IFID_M403;
			$itemAlias = 'MaSP';
			$attrAlias = 'ThuocTinh';
			$qtyAlias  = 'SoLuong';
                        $uomAlias  = 'DonViTinh';
		}
		elseif($this->_form->FormCode == 'M505')
		{
			$mainView  = 'ODonBanHang'; 
			$subView   = 'ODSDonBanHang';
			$dateAlias = 'NgayYCNH';
			$ifidAlias = 'M505';
			$ifid      = $this->_params->IFID_M505;
			$itemAlias = 'MaSP';
			$attrAlias = 'ThuocTinh';
			$qtyAlias  = 'SoLuong';
                        $uomAlias  = 'DonViTinh';
		} 
		
		$oIssueDate          = $model->getIssueDateForDeliveryPlan($mainView, $subView, $dateAlias, 
																   $ifidAlias, $itemAlias, $ifid);
		// Lay mang ke hoach giao hang cu de xoa di
		foreach ($this->_params->OKeHoachGiaoHang as $item)
		{
			$deleteDeliveryPlan['OKeHoachGiaoHang'][] = $item->IOID; 
		}
		
		// Lay mang ke hoach giao hang can insert
		foreach ($this->_params->$subView as $item)
		{
			$aInsertDeliveryPlan['OKeHoachGiaoHang'][$aiInsert]['MaSP']      = $item->$itemAlias;
                        $aInsertDeliveryPlan['OKeHoachGiaoHang'][$aiInsert]['DonViTinh'] = $item->$uomAlias;
			$aInsertDeliveryPlan['OKeHoachGiaoHang'][$aiInsert]['ThuocTinh'] = $item->$attrAlias;
			$aInsertDeliveryPlan['OKeHoachGiaoHang'][$aiInsert]['SoLuong']   = $item->$qtyAlias;
			$aInsertDeliveryPlan['OKeHoachGiaoHang'][$aiInsert]['NgayXuatHang'] = $oIssueDate->NgayXuatHang;
			$aInsertDeliveryPlan['OKeHoachGiaoHang'][$aiInsert]['NgayGiaoHang'] = $this->_params->$dateAlias;
			$aiInsert++;
		}
		// echo 'Test: <pre>'; print_r($oInsertDeliveryPlan); die;
		
		// Thuc hien xoa mang giao hang cu
		if(count($deleteDeliveryPlan))
		{
			$remove =  $this->services->Form->Remove($ifidAlias, $ifid, $deleteDeliveryPlan);
			if($remove->isError())
			{
				$this->setError();
				$this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
		
		// Thuc hien them ke hoach giao hang mac dinh
		if(count($aInsertDeliveryPlan))
		{
			$insert =  $this->services->Form->Manual($ifidAlias, $ifid, $aInsertDeliveryPlan,false);
			if($insert->isError())
			{
				$this->setError();
				$this->setMessage($insert->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
	}
	
}