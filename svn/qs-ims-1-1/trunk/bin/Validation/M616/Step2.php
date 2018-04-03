<?php
/**
 * @author Thinh Tuan <thinh.tuan@quasoft.vn>
 * @module Sap xep kho - Step 2
 * @date 17/10/2014
 */

class Qss_Bin_Validation_M616_Step2 extends Qss_Lib_Warehouse_WValidation
{		
	public function onNext() 
	{
		parent::init();
		if(!count((array)$this->_params->OChiTietSapXepKho))
		{
			$this->setMessage('Danh sách sắp xếp kho còn trống!');
			$this->setError();
		}
	}
	
	
	/**
	 * next: Cap nhat ket qua sap xep kho
	 * + Kiểm tra
	 * - Kiểm tra kho đang sắp xếp có thực hiện kiểm kê hay không?
	 * - Kiểm tra số lượng có đủ để chuyển
	 * B1: Chuyển số lượng so sanh về số lượng theo đơn vị tính cơ sở
	 * B2: Lấy ra số lượng tồn trong kho theo đơn vị tính cơ sở
	 *	IF Nếu có bin, lot hoặc serial lấy số lượng so sanh từ 
	 *  IF Nếu chỉ có bin đến lấy số lượng = số lượng hiện có trừ đi tổng số 
	 *	lượng trong "Trạng thái lưu trữ"
	 * B3: So sanh hai số lượng trên để thông báo có đủ số lượng lấy ra hay ko?
	 * - Kiểm tra bin chuyển đến có chưa mặt hàng và đủ sức chứa số lượng chuyển
	 * đến hay không?
	 * + Cập nhật lại thông tin "Trạng thái lưu trữ"
	 */
	public function next()
	{
		parent::init();
		
		$insert       = array();
		$insertIFID   = 0; // Mang IFID
		$insertNum    = 0;
		$err          = false; // Bao loi
		$notExistsErr = false;
		$model        = new Qss_Model_Extra_Warehouse();
		$commonModel  = new Qss_Model_Extra_Extra();
		$productModel = new Qss_Model_Extra_Products();
			
		
		// KIEM TRA KHO CO BI KHOA HAY KHONG?
		$warehouseLock = $this->_checkWarehouseLocked(
			$this->_params->Ref_Kho
			, $this->_params->OChiTietSapXepKho
			, 'M612'); // Lay thong tin kho hien tai co dang kiem ke kho hay ko?(Bị khóa)
		
		
		if(!$this->isError())
		{
			foreach ($this->_params->OChiTietSapXepKho as $item)
			{
				
				// KIEM TRA SO LUONG CHUYEN DEN 
				// Quy doi so luong ra so luong yeu cau
				$updateQty = $productModel->changeQtyBaseOnBaseUOM(
					$item->Ref_MaSP
					, $item->Ref_DonViTinh
					, $item->SoLuong);
				
				if($item->TuBin || $item->Lot || $item->Serial)
				{
					$currentBin = $model->getCurrentDetailInventoryStatus(
						$item->Ref_MaSP
						, $this->_params->Ref_Kho, 0
						, $item->Lot, $item->Serial, $item->Ref_TuBin);
					$currentBin->SoLuongQuyDoi = $currentBin?$currentBin->SoLuongQuyDoi:0;
					$updateQtyToBin  = $currentBin->SoLuongQuyDoi - $updateQty;
				}
				else
				{
					$currentBin = $productModel->getTotalItemQty($this->_params->Ref_Kho, $item->Ref_MaSP);
					
					$availableUpdateQty = @(double)$currentBin->SoLuongHCQuyDoi - @(double)$currentBin->TongSoLuongQuyDoi;
					$updateQtyToBin  = $availableUpdateQty - $updateQty;
				}
				
				
				if($updateQtyToBin < 0 ) //|| $updateQtyToMain < 0
				{
					if($item->TuBin)
					{
						$this->setMessage('Mặt hàng '.$item->MaSP.' trong bin '.$item->TuBin
							.' không đủ số lượng cập nhật!' );
					}
					else
					{
						$this->setMessage('Mặt hàng '.$item->MaSP.' không đủ số lượng cập nhật!' );
					}
					$this->setError();
				}
				
				// KIEM TRA BIN CHUYEN DEN CO CHUA SAN PHAM VA CO DU SUC CHUA HAY KHONG?
				// * Kiểm tra sức chứa của kho (Ưu tiên BIN trước)
//				$capacity = $model->checkCapacityForSortWarehouseModule($this->_params->IFID_M616, $this->_params->Ref_Kho);
//				foreach ($capacity as $val)
//				{
//					$newQty = $val->NewQty + $val->HasQty;
//					switch ($val->Condition)
//					{
//						case 1: // Bin co san pham
//							if($val->BinCapacity && ($newQty > $val->BinCapacity) )
//							{
//								$this->setError();
//								$this->setMessage('Bin '.$val->BinCode.' không đủ sức chứa.');
//							}
//							break;
//
//						case 2: // Bin co don vi tinh
//							if($val->BinCapacity && ($newQty > $val->BinCapacity) )
//							{
//								$this->setError();
//								$this->setMessage('Bin '.$val->BinCode.' không đủ sức chứa.');
//							}
//						break;
//					}
//				}
				
				// GAN MANG DU LIEU
				if(!$this->isError())
				{
					if($item->Lot || $item->Serial || $item->TuBin)
					{
						$insert['OThuocTinhChiTiet'][$insertNum]['MaSanPham']   = $item->MaSP;
						$insert['OThuocTinhChiTiet'][$insertNum]['DonViTinh']   = $item->DonViTinh;
						$insert['OThuocTinhChiTiet'][$insertNum]['SoLo']        = $item->Lot;
						$insert['OThuocTinhChiTiet'][$insertNum]['SoSerial']    = $item->Serial;
						$insert['OThuocTinhChiTiet'][$insertNum]['MaThuocTinh'] = @(string)$item->ThuocTinh;
						$insert['OThuocTinhChiTiet'][$insertNum]['Kho']         = $this->_params->Kho;
						$insert['OThuocTinhChiTiet'][$insertNum]['Bin']         = $item->TuBin;			
						$insert['OThuocTinhChiTiet'][$insertNum]['SoLuong']     = $updateQtyToBin;
						$insertNum++;
					}
					$insertIFID = @(int)$currentBin->IFID_M602;
					
					$currentEBin = $model->getCurrentDetailInventoryStatus($item->Ref_MaSP, $this->_params->Ref_Kho, 0, $item->Lot, $item->Serial, $item->Ref_DenBin);
					$updateQtyToEBin  = @(double)$currentEBin->SoLuong + @(double)$item->SoLuong;
					

					$insert['OThuocTinhChiTiet'][$insertNum]['MaSanPham']   = $item->MaSP;
					$insert['OThuocTinhChiTiet'][$insertNum]['DonViTinh']   = $item->DonViTinh;
					$insert['OThuocTinhChiTiet'][$insertNum]['SoLo']        = $item->Lot;
					$insert['OThuocTinhChiTiet'][$insertNum]['SoSerial']    = $item->Serial;
					$insert['OThuocTinhChiTiet'][$insertNum]['MaThuocTinh'] = @(string)$item->ThuocTinh;
					$insert['OThuocTinhChiTiet'][$insertNum]['Kho']         = $this->_params->Kho;
					$insert['OThuocTinhChiTiet'][$insertNum]['Bin']         = $item->DenBin;			
					$insert['OThuocTinhChiTiet'][$insertNum]['SoLuong']     = $updateQtyToEBin;
					$insertNum++;
				}
			}
			
//			if($notExistsErr)
//			{
//				$this->setMessage('Chưa có số liệu tồn kho của mặt hàng "'.$item->MaSP. '" trong kho "'.$this->_params->Kho. '"' );
//			}
			// LUU VAO CSDL
			if(!$this->isError() && count($insert))
			{
				$service = $this->services->Form->Manual('M602' , $insertIFID, $insert, false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			if($err)
			{
				$this->setError();
			}
		}
	}
}