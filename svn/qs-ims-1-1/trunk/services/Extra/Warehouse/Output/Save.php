<?php

class Qss_Service_Extra_Warehouse_Output_Save extends Qss_Service_Abstract
{
	
	public function __doExecute ($params)
	{		
		if($this->validate($params))
		{
			// code
			$model  = new Qss_Model_Extra_Warehouse();
			$insert = array();
			$keepItemDuplicate = array();
			$ai     = 0; // Chi so mang insert
			$line   = isset($params['itemCode'])?$params['itemCode']:array();
			// Lay trang thai luu tru theo mang ioid cua hang doi
			$ssIndex = 0; // Chi so mang thuoc tinh chi tiet insert
			$ioidStackArr   = isset($params['stackLine'])?$params['stackLine']:array();
			$stackInfo      = array('module'=>'M611', 'object'=>'OHangDoiXuat', 'stockStatusObject'=>'OThuocTinhChiTiet');
			$getStockStatus = $model->getStockStatusOfStack($ioidStackArr, $stackInfo);
			$stackStockStatus = array();
			
			// Gan trang thai luu tru cua hang doi vao mot mang co key la hd IFID
			$i = 0;
			foreach ($getStockStatus as $item)
			{
				//MaSanPham, MaThuocTinh
				$stackStockStatus[$item->IFID][$i]['SoLo'] = $item->SoLo;
				$stackStockStatus[$item->IFID][$i]['SoSerial'] = $item->SoSerial;
				$stackStockStatus[$item->IFID][$i]['SoLuong'] = $item->SoLuong;
				$stackStockStatus[$item->IFID][$i]['Zone'] = $item->Zone;
				$stackStockStatus[$item->IFID][$i]['Bin'] = $item->Bin;
				$i++;
			}

			foreach ($line as $itemCode)
			{
				$info = $model->getOrderToComingInfo($params['module'][$ai], $params['orderLine'][$ai]);
				$insert['ODanhSachXuatKho'][$ai]['MaSP']        = $itemCode;
				$insert['ODanhSachXuatKho'][$ai]['SoLuong']     = $params['qty'][$ai];
                                $insert['ODanhSachXuatKho'][$ai]['DonViTinh']   = $params['itemUOM'][$ai];
				$insert['ODanhSachXuatKho'][$ai]['DongDonHang'] = isset($info->docNo)?$info->docNo:'';
				$insert['ODanhSachXuatKho'][$ai]['ThuocTinh']   = $params['attributeCode'][$ai];
				$insert['ODanhSachXuatKho'][$ai]['DonGia']      = $params['price'][$ai];
				$insert['ODanhSachXuatKho'][$ai]['ioidlink']    = $params['stackLine'][$ai];
				
				if(isset($stackStockStatus[$params['stackIFID'][$ai]]))
				{
					$keepCode = array();
					foreach ($stackStockStatus[$params['stackIFID'][$ai]] as $ss)
					{
						$code = $itemCode.'_'.$params['attributeCode'][$ai].'_'.$ss['SoLo'].'_'.$ss['SoSerial'].'_'.$ss['Zone'].'_'.$ss['Bin'];
						$code = trim($code);
						//$code = str_replace('_', '', $code);
						//$code = str_replace(' ', '_', $code);
						
						if(!key_exists($code, $keepCode))
						{
							$insert['OThuocTinhChiTiet'][$ssIndex]['MaSanPham']   = $itemCode;
							$insert['OThuocTinhChiTiet'][$ssIndex]['MaThuocTinh'] = $params['attributeCode'][$ai];
                                                        $insert['OThuocTinhChiTiet'][$ssIndex]['DonViTinh']   = $params['itemUOM'][$ai];
							$insert['OThuocTinhChiTiet'][$ssIndex]['SoLo']        = $ss['SoLo'];
							$insert['OThuocTinhChiTiet'][$ssIndex]['SoSerial']    = $ss['SoSerial'];
							$insert['OThuocTinhChiTiet'][$ssIndex]['SoLuong']     = $ss['SoLuong'];
							$insert['OThuocTinhChiTiet'][$ssIndex]['Zone']        = $ss['Zone'];
							$insert['OThuocTinhChiTiet'][$ssIndex]['Bin']         = $ss['Bin'];
							$keepCode[$code] = $ssIndex;
							$ssIndex++;
						}
						else 
						{
							$insert['OThuocTinhChiTiet'][$keepCode[$code]]['SoLuong'] += $ss['SoLuong'];
						}
					}
				}
				$ai++;
			}

			// Them tham chieu so don hang
			
			if(count($insert))
			{
				$service = $this->services->Form->Manual($params['insertModule'] ,$params['ifid'],$insert,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			if(!$this->isError())
			{
				$this->setMessage('Cập nhật thành công!');
			}
		}
		else 
		{
			$this->setError();
		}
	}

	private function validate($params)
	{
		$return = true;
		$extra  = new Qss_Model_Extra_Extra();
		$status = $extra->getStatusByIFID($params['insertModule'], $params['insertObject'], $params['ifid']);
		$lockStep = array(2);
		
		if(in_array($status, $lockStep))
		{
			$return = false;
			$this->setMessage('Không thể xóa do chuyển hàng đã được hoàn thành!');
		}
		return $return;
	}
}
?>