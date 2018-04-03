<?php

class Qss_Service_Extra_Warehouse_Receive_Stockstatus_Save extends Qss_Service_Abstract
{

	public function __doExecute($params)
	{
		if ($this->validate($params))
		{
			//$this->updateLot($params);
			$this->updateStockStatus($params);
			//$this->setMessage('Cập nhật thành công!');
		} else
		{
			$this->setError();
		}

	}

	/*
	 * Cập nhật vào bảng Trạng thái lưu trữ
	 */

	private function updateStockStatus($params)
	{
		// Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724
		//'$vattuObject = (_QSS_DATA_FOLDER_ == 'data_hoaphat')?'OVatTu':'OVatTuPBT';

		$model = new Qss_Model_Extra_Products();
		$IOIDExist = array();
		$dataTmp = array();
		$delZero = array();
		$delOld = array();
		$stockObj = 'OThuocTinhChiTiet';

		$ioidEnd = ($params['ioid']) ? $params['ioid'] : $model->getIOIDfromIFID($params['module'], $params['ifid']);
		$oldValueObj = $model->getOldStockStatus($params['module'], $ioidEnd, $params['ifid'], $params['obj']);
		$oldValue = $this->getOldValues($oldValueObj);
		$countOldValue = count((array) $oldValue);
		$countNewUpdate = count($params['qty']);

		$serialLot = $model->getSerialLot($params['module'], @(int) $params['ioid'], @(int) $params['ifid'], @(string) $params['obj']);
		$zoneIoidExists = isset($params['ioidExists']) ? $params['ioidExists'] : array();
		$zone = isset($params['zone']) ? $params['zone'] : array();
		$bin = isset($params['bin']) ? $params['bin'] : array();
		$lot = isset($params['lot']) ? $params['lot'] : array();
		$serial = isset($params['serial']) ? $params['serial'] : array();
		$qty = $params['qty'];
		$attributes = $params['attributes'];
		$receiveDate = isset($params['receiveDate']) ? $params['receiveDate'] : array();
		$productDate = isset($params['productionDate']) ? $params['productionDate'] : array();
		$expiryDate = isset($params['expiryDate']) ? $params['expiryDate'] : array();
		
		$lines = 0;
		
		if($serialLot->serial)
		{
			$lines = count(@(array)$params['serial']);
		}
		elseif($serialLot->lot)
		{
			$lines = count(@(array)$params['lot']);
		}
		elseif(@$params['bin'])
		{
			$lines = count(@(array)$params['bin']);
		}

		// Neu co object trung nhau co the dung module de phan dinh tiep
		switch ($params['obj'])
		{
			case 'OSanLuong': // Output statistics
				$stockObj = 'OTrangThaiLuuTruTP';
				break;
			case 'ONVLDauVao': // Material consumption
				$stockObj = 'OTrangThaiLuuTruNVL';
				break;
			case 'OSanPhamLoi':// Defect statistics
				$stockObj = 'OTrangThaiLuuTruPL';
				break;
			case 'OCongCuCauThanh'://toolset statistics
				$stockObj = 'OTrangThaiLuuTruCCDC';
				break;
			case 'OPheLieu':
				$stockObj = 'OTrangThaiLuuTruPLBT';
				break;
			case 'OVatTuPBT':
				$stockObj = 'OTrangThaiLuuTruVT';
			break;
			case 'OVatTu':
				$stockObj = 'OTrangThaiLuuTruVT';
			break;		
		}

		// Exception: Xu ly voi doi tuong chinh cua thong ke san xuat
		if ($params['module'] == 'M717' && !$params['obj'])
		{
			$stockObj = 'OThuocTinhChiTiet';
		}

		// Neu so ban ghi cu lon hon so ban ghi moi can xoa ban ghi cu di moi cap nhat duoc
		if ($countOldValue > $countNewUpdate)
		{
			foreach ($oldValue as $val)
			{
				$delOld[] = $val['IOID'];
			}
		}
		
//		// Xoa ban ghi khi chi co bin
//		if(!$serialLot->serial && !$serialLot->lot && isset($params['bin']))
//		{
//			foreach ($oldValue as $val)
//			{
//				$delOld[] = $val['IOID'];
//			}
//		}

		if ($zoneIoidExists)
		{
			$IOIDExist = $zoneIoidExists;
		} else
		{
			foreach ($oldValue as $val)
			{
				$IOIDExist[] = $val['IOID'];
			}
		}
		



		for ($i = 0; $i < $lines; $i++)
		{
			if (isset($IOIDExist[$i]) && $IOIDExist[$i] && $qty[$i] <= 0)
			{
				$delZero[] = $IOIDExist[$i];
			}
			// Lặp qua các dòng của bảng thuộc tính 
			if ((isset($IOIDExist[$i]) && $IOIDExist[$i]) || $qty[$i])
			{
				// nếu tồn tại ioid hoặc số lượng khác không 
				// Lấy mã sản phẩm chung
				$dataTmp[$i]['MaSanPham'] = $serialLot->itemCode;
				$dataTmp[$i]['DonViTinh'] = $serialLot->itemUOM;

				// Lấy lô theo dòng
				if ($lot)
				{
					// nếu có serial
					$dataTmp[$i]['SoLo'] = $lot[$i];
				}

				// Update kho
				if ($serialLot->warehouse)
				{
					$dataTmp[$i]['Kho'] = $serialLot->warehouse;
				}

				// Lấy serial theo dòng
				if ($serial)
				{
					$dataTmp[$i]['SoSerial'] = $serial[$i];
				}

				// Ngay nhan hang
				if ($receiveDate)
				{
					$dataTmp[$i]['NgayNhan'] = $receiveDate[$i];
				}

				// Ngay san xuat
				if ($productDate)
				{
					$dataTmp[$i]['NgaySX'] = $productDate[$i];
				}


				// Ngay han
				if ($expiryDate)
				{
					$dataTmp[$i]['NgayHan'] = $expiryDate[$i];
				}

				// Lấy thuộc tính theo dòng
				if ($attributes)
				{
					$dataTmp[$i]['MaThuocTinh'] = $attributes;
				}

				if ($zone)
				{
					$dataTmp[$i]['Zone'] = $zone[$i];
				}

				if ($bin)
				{
					$dataTmp[$i]['Bin'] = $bin[$i];
				}

				// Lấy số lượng trên mỗi dòng
				$dataTmp[$i]['SoLuong'] = $qty[$i];

				if (isset($IOIDExist[$i]) && $IOIDExist[$i])
				{
					// Chi trong truong hop so dong moi cap nhat lon hon so dong 
					if (!count($delOld))
					{
						$dataTmp[$i]['ioid'] = $IOIDExist[$i];
					}
				} else
				{
					if ($params['ioid'])
					{
						$dataTmp[$i]['ioidlink'] = $params['ioid'];
					} else
					{
						$dataTmp[$i]['ioidlink'] = $model->getIOIDfromIFID($params['module'], $params['ifid']);
					}
				}
			}
		}


		// Xoa tat ca cac dong cu neu xuat hien dong thua, tuc la lan nhap sau it dong hon lan nhap truoc
		if (count($delOld))
		{

			$delOld = array($stockObj => $delOld);
			$removeService1 = $this->services->Form->Remove($params['module'], $params['ifid'], $delOld);
			if ($removeService1->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}

		// Cap nhat lai cac dong
		//print_r($dataTmp);die;
		if (count($dataTmp))
		{
			$data = array($stockObj => $dataTmp);
			$service = $this->services->Form->Manual($params['module'], $params['ifid'], $data, false);
			if ($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}

		// Neu trong cac dong da cap nhat co dong nao khong co so luong thi xoa no di
		if (count($delZero))
		{

			$delZero = array($stockObj => $delZero);
			$removeService2 = $this->services->Form->Remove($params['module'], $params['ifid'], $delZero);
			if ($removeService2->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}

	}

//	/**
//	 * Cập nhật vào bảng lô trong nhóm module kho
//	 * Enter description here ...
//	 * @param unknown_type $lot : array
//	 * @param unknown_type $serial : array
//	 * @param unknown_type $qty : array , số lượng trên dòng
//	 * @param unknown_type $itemCode : mã sản phẩm
//	 */
//	private function updateLot($params)
//	{
//		$lot      = isset($params['lot'])?$params['lot']:array();
//		$serial   = isset($params['serial'])?$params['serial']:array();
//		$qty      = $params['qty'];
//		$itemCode = unserialize($params['serialLot'])->itemCode;
//		
//		if($lot)
//		{
//			$model    = new Qss_Model_Extra_Products();
//                        $common   = new Qss_Model_Extra_Extra();
//			$lotExist = array();
//			foreach($common->getTable(array('*'), 'OLo', array('MaSanPham'=>$itemCode), array(), 'NO_LIMIT') as $val)
//			{
//				$lotExist[] = $val->SoLo;
//			}
//	
//			/**
//			 * Neu co serial hoac thuoc tinh thi can phai loai bo cac lo trung lap
//			 */
//			if($serial)
//			{
//				$lot = array_unique($lot);
//			}
//			/**
//			 * Chi cap nhat cac lo co gia tri va chua ton tai
//			 */
//			$i    = 0;
//			$data = array();
//
//			foreach($lot as $val)
//			{
//                            if($val && $qty[$i] && !in_array($val, $lotExist))
//                            {
//                                $data[] = array(
//                                        'MaSanPham'=>$itemCode
//                                        ,'SoLo'=>$val
//                                );
//                            }
//                            $i++;
//			}
//			
//			/**
//			 * Neu co du lieu thi cap nhat vao bang lo
//			 */
//			if(count($data))
//			{
//				$data = array('OLo'=>$data);
//				$service = $this->services->Form->Manual('M120',0,$data,false);
//				
//				if($service->isError())
//				{
//					$this->setError();
//					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//				}
//			}
//		}
//	}

	private function validate($params)
	{
		$model = new Qss_Model_Extra_Products();
		$common = new Qss_Model_Extra_Extra();
		$result = true; // Kết quả trả về
		$breakCondition = true; // Ngắt báo lỗi
		$zoneAndBin = $model->getBinByWarehouse(
			$params['refWarehouse']
			, $params['refItem']
			, $params['itemUOM']);
		$serialLot = $model->getSerialLot($params['module'], @(int) $params['ioid'], @(int) $params['ifid'], @(string) $params['obj']);
		$itemCode = @$serialLot->itemCode;

		/**
		 * Kiểm tra số lượng sản phẩm !
		 */
		for ($i = 0; $i < count((array) $params['qty']); $i++)
		{
			/* Không phải số hoặc nhỏ hơn 0 báo lỗi */
			if (!is_numeric($params['qty'][$i]) || ($params['qty'][$i] < 0))
			{
				$this->setMessage('Dòng ' . ($i + 1) . ' có số lượng không phải là số dương lớn hơn 0!');
				$result = false;
				$breakCondition = false;
			}
		}

		if (isset($params['bin']))
		{
			for ($i = 0; $i < count((array) $params['qty']); $i++)
			{
				if (!$params['bin'][$i])
				{
					$this->setMessage('Dòng ' . ($i + 1) . ' thiếu bin !');
					$breakCondition = false;
					$result = false;
				}
			}
		}
		
		/**
		 * Kiem tra hai bin trung lap trong truong hop ko co lot va serial
		 */
		$filterBinArr = array();
		if(!$serialLot->serial && !$serialLot->lot && isset($params['bin']))
		{
			foreach ($params['bin'] as $bin)
			{
				if(in_array($bin, $filterBinArr))
				{
					$filterBinArr[] = $bin;
					$this->setMessage('Bin ' . $bin . ' bị trùng lặp! Kiểm tra để cập nhật lại!' );
					$breakCondition = false;
					$result = false;
				}
				else 
				{
					$filterBinArr[] = $bin;
				}
			}
		}


		/* Nếu tổng số lượng lớn hơn số lượng sản phẩm báo lỗi */
		$total = array_sum($params['qty']) ? array_sum($params['qty']) : 0;
		if ($total > $params['itemQty'])
		{
			$this->setMessage('Tổng số lượng được chọn lớn hơn tổng số lượng sản phẩm!');
			$result = false;
			$breakCondition = false;
		}


		/* Kiểm tra lô */
		if (isset($params['lot']))
		{
			$countLot = count($params['lot']);
			$uniqueLot = array_unique($params['lot']);
			$countUniqueLot = count($uniqueLot);
			// Kiểm tra xem có lô nào để trống
			for ($j = 0; $j < $countLot; $j++)
			{
				if (!$params['lot'][$j])
				{
					$result = false;
					$breakCondition = false;
					$this->setMessage('Lot tại dòng ' . ($j + 1) . ' để trống, đề nghị kiểm tra lại !');
				}
			}

			// Kiểm tra xem có lô nào trùng lặp khi chỉ có lô mà không có serial
			if (!isset($params['serial']) && $countUniqueLot < $countLot)
			{
				for ($k = 0; $k < $countLot; $k++)
				{
					if (!isset($uniqueLot[$k]))
					{
						$mistake = array_search($params['lot'][$k], $params['lot']) + 1;
						$this->setMessage('Lot trùng lặp tại dòng ' . ($k + 1) . ', trùng lặp với giá trị "'
							. $params['lot'][$k] . '" của dòng ' . $mistake . ' , đề nghị kiểm tra lại !');
					}
				}
				$result = false;
				$breakCondition = false;
			}

			// Kiem tra xem co lo nao da ton tai trong kho khong
			// Neu lo da ton tai bao loi

			$lotExistsArr = $model->checkLotExists($params['lot']);

			if (count((array) $lotExistsArr))
			{
				// Lay lo thuoc dong nao
				$i = 0; //here
				$keepOrderIndexForLot = array();
				foreach ($params['lot'] as $val)
				{
					$keepOrderIndexForLot[$val][] = $params['no'][$i];
					$i++;
				}

				foreach ($lotExistsArr as $val)
				{
					if ($val->SoLo)
					{
						$lineNoHasInvalidLot = isset($keepOrderIndexForLot[$val->SoLo]) ? ' trên dòng ' . implode(',', $keepOrderIndexForLot[$val->SoLo]) : '';
						$this->setMessage("Lot {$val->SoLo} {$lineNoHasInvalidLot} đã được sử dụng trước đó, bạn cần cập lại số lô khác.");
					}
				}
				$result = false;
			}

			// Kiem tra xem lo co danh cho cung mot san pham khong
			// Truong hop nay khong can kiem tra do lot chi duoc danh mot lan
		}

		/* Kiểm tra serial */
		if (isset($params['serial']))
		{
			$countSerial = count($params['serial']);
			$uniqueSerial = array_unique($params['serial']);
			$countUniqueSerial = count($uniqueSerial);

			// Kiểm tra xem có serial nào để trống
			$j = 0;
			foreach ($params['serial'] as $val)
			{
				if (!$val)
				{
					$result = false;
					$breakCondition = false;
					$this->setMessage('Serial tại dòng ' . ($j + 1) . ' để trống, đề nghị kiểm tra lại !');
				}
				$j++;
			}

			// Kiểm tra xem có serial trùng lặp
			if (($countUniqueSerial < $countSerial))
			{
				for ($k = 0; $k < $countSerial; $k++)
				{
					if (!isset($uniqueSerial[$k]))
					{
						$mistake = array_search($params['serial'][$k], $params['serial']) + 1;
						$this->setMessage('Serial trùng lặp tại dòng ' . ($k + 1) . ', trùng lặp với giá trị "'
							. $params['serial'][$k] . '" của dòng ' . $mistake . ' , đề nghị kiểm tra lại !');
					}
				}
				$result = false;
				$breakCondition = false;
			}

			// Kiem tra serial da duoc dung chua
			$serialArr = $model->checkSerialExists($params['serial']);

			if (count((array) $serialArr))
			{
				// Lay serial thuoc dong nao
				$i = 0; //here
				$keepOrderIndexForSerial = array();
				foreach ($params['serial'] as $val)
				{
					$keepOrderIndexForSerial[$val][] = $params['no'][$i];
					$i++;
				}

				foreach ($serialArr as $val)
				{
					$lineNoHasInvalidSerial = isset($keepOrderIndexForSerial[$val->SoSerial]) ? ' trên dòng '
						. implode(',', $keepOrderIndexForSerial[$val->SoSerial]) : '';
					$this->setMessage("Serial {$val->SoSerial} {$lineNoHasInvalidSerial}" .
						" đã được sử dụng trước đó, bạn cần cập lại số serial khác.");
				}
				$result = false;
			}
		}

		/**
		 * Kiểm tra lỗi nhưng vẫn cho cập nhật này
		 * 
		 */
		if ($breakCondition && $total < $params['itemQty']) // Nếu nhỏ hơn cảnh báo
		{
			$itemUOM = $params['itemUOM'];
			$div = (($params['itemQty'] - $total) < $params['itemQty']) ? ($params['itemQty'] - $total) : $params['itemQty'];
			$this->setMessage('Tổng số lượng được cập nhật : ' . $total . ' ' . $itemUOM);
			$this->setMessage('Số lượng chưa cập nhật  : ' . $div . ' ' . $itemUOM);
			$this->setStatus(1);
		}
		
		return $result;

	}

	private function getOldValues($oldValue)
	{
		$arrAttrTmp = array(); // mảng lưu giá trị
		if ($oldValue)
		{
			$old = 0;
			$i = -1;
			foreach ($oldValue as $val)
			{
				if ($old != $val->IOID)
				{
					// Nếu giá trị IOID khác nhau ta sang một dòng mới
					$i++;
				}
				if (!isset($arrAttrTmp[$i]))
				{
					$arrAttrTmp[$i] = array();
					// Khởi tạo mảng lưu giá trị cho từng dòng thứ i
				}

				$arrAttrTmp[$i]['SoLuong'] = $val->SoLuong;
				$arrAttrTmp[$i]['SoLo'] = isset($val->SoLo) ? $val->SoLo : '';
				$arrAttrTmp[$i]['SoSerial'] = isset($val->SoSerial) ? $val->SoSerial : '';
				$arrAttrTmp[$i]['MaThuocTinh'] = isset($val->MaThuocTinh) ? $val->MaThuocTinh : '';
				$arrAttrTmp[$i]['Zone'] = isset($val->Zone) ? $val->Zone : '';
				$arrAttrTmp[$i]['Bin'] = isset($val->Bin) ? $val->Bin : '';
				$arrAttrTmp[$i]['IOID'] = $val->IOID;
				$arrAttrTmp[$i]['ReceiveDate'] = $val->NgayNhan;
				$arrAttrTmp[$i]['ProductDate'] = $val->NgaySX;
				$arrAttrTmp[$i]['ExpiryDate'] = $val->NgayHan;
				$old = $val->IOID;
			}
		}
		return $arrAttrTmp;

	}

}

?>