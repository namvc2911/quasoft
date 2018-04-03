<?php

/**
 *
 * @author ThinhTuan
 *
 */
class Qss_Lib_Warehouse_WValidation extends Qss_Lib_WValidation
{
        public $_common;
        public $_warehouse;

        public function __construct($form)
        {
                parent::__construct($form);
                $this->_common = new Qss_Model_Extra_Extra();
                $this->_warehouse = new Qss_Model_Extra_Warehouse();
        }

        public function init()
        {
                parent::init();
        }
        


        protected function insertIncomingOutgoing($field, $insertFieldLabel, $insertObject, $fromObject, $toObject, $fromModule, $toModule, $partner, $hasPlan = true, $hasReferenceObject = '')
        {
                /**
                 * $field: Trong field 4 truong dau la thuoc ke hoach nhan chuyen 2 truong sau thuoc order 
                 */
                $model = new Qss_Model_Extra_Warehouse();
                $ifidAlias = "IFID_{$fromModule}";
                $incoming = $model->getIncomingOutgoing($field, $fromObject, $toObject, $fromModule, $this->_params->$ifidAlias, $hasPlan, $hasReferenceObject);

                // Create incoming shipments;
                // Lay mang cap nhat csdl

                foreach ($incoming as $item)
                {
                        $insert = array();
                        $ai = 0;
                        $item->ItemPrice = $item->ItemPrice ? $item->ItemPrice : 0;
                        $insert[$insertObject][0][$insertFieldLabel['Partner']] = $partner;
                        $insert[$insertObject][0][$insertFieldLabel['Date']] = $item->ItemDate;
                        $insert[$insertObject][0][$insertFieldLabel['Item']] = $item->ItemCode;
                        $insert[$insertObject][0][$insertFieldLabel['UOM']] = $item->ItemUOM;
                        $insert[$insertObject][0][$insertFieldLabel['Attr']] = $item->ItemAttribute;
                        $insert[$insertObject][0][$insertFieldLabel['Qty']] = $item->ItemQty;
                        $insert[$insertObject][0][$insertFieldLabel['Price']] = $item->ItemPrice / 1000;
                        $insert[$insertObject][0][$insertFieldLabel['Des']] = $item->DocNo;
                        if (isset($insertFieldLabel['Warehouse']))
                        {
                                $insert[$insertObject][0][$insertFieldLabel['Warehouse']] = $item->ItemWarehouse;
                        }
                        $insert[$insertObject][0][$insertFieldLabel['Module']] = $fromModule;
                        $insert[$insertObject][0]['ioidlink']                  = $item->IOID;
                        $insert[$insertObject][0]['ifidlink']                  = $this->_params->$ifidAlias;
                        $code = '';

                        // Ngoại lệ
                        // Xử lý ngoại lệ với chuyển kho, insert thêm trạng thái lưu trữ
                        // Neu co trang thai luu tru moi thuc hien
                        if ($fromModule == 'M603')
                        {
                                $code = $item->RefItem . '_' . $item->RefAttribute . '_' . $item->RefFromWarehouse . '_' . $item->RefToWarehouse;
                        }


                        if ($fromModule == 'M603' && count((array) $this->_params->OTrangThaiLuuTruCK))
                        {
                                if ($insertObject == 'OHangDoiNhap')
                                {
                                        // Nhap kho
                                        foreach ($this->_params->OTrangThaiLuuTruCK as $ss)
                                        {
                                                /*
                                                 *  SoLo SoSerial  MaThuocTinh KhoLayHang TuZone TuBin SoLuong  
                                                 *  TuZone TuBin KhoNhanHang DenZone DenBin
                                                 */
                                                $compareCode = $ss->Ref_MaSanPham . '_' . $ss->Ref_MaThuocTinh . '_' . $ss->Ref_KhoLayHang . '_' . $ss->Ref_KhoNhanHang;


                                                if ($code == $compareCode)
                                                {
                                                        $insert['OThuocTinhChiTiet'][$ai]['MaSanPham'] = $ss->MaSanPham;
                                                        $insert['OThuocTinhChiTiet'][$ai]['DonViTinh'] = $ss->DonViTinh;
                                                        $insert['OThuocTinhChiTiet'][$ai]['SoLo'] = $ss->SoLo;
                                                        $insert['OThuocTinhChiTiet'][$ai]['SoSerial'] = $ss->SoSerial;
                                                        $insert['OThuocTinhChiTiet'][$ai]['MaThuocTinh'] = $ss->MaThuocTinh;
                                                        $insert['OThuocTinhChiTiet'][$ai]['Kho'] = $ss->KhoNhanHang;
                                                        //$insert['OThuocTinhChiTiet'][$ai]['Zone'] = $ss->DenZone;
                                                        $insert['OThuocTinhChiTiet'][$ai]['Bin'] = $ss->DenBin;
                                                        $insert['OThuocTinhChiTiet'][$ai]['SoLuong'] = $ss->SoLuong;
                                                        $ai++;
                                                }
                                        }
                                }
                                elseif ($insertObject == 'OHangDoiXuat')
                                {
                                        // Xuat kho
                                        foreach ($this->_params->OTrangThaiLuuTruCK as $ss)
                                        {
                                                $compareCode = $ss->Ref_MaSanPham . '_' . $ss->Ref_MaThuocTinh . '_' . $ss->Ref_KhoLayHang . '_' . $ss->Ref_KhoNhanHang;
                                                if ($code == $compareCode)
                                                {
                                                        $insert['OThuocTinhChiTiet'][$ai]['MaSanPham'] = $ss->MaSanPham;
                                                        $insert['OThuocTinhChiTiet'][$ai]['DonViTinh'] = $ss->DonViTinh;
                                                        $insert['OThuocTinhChiTiet'][$ai]['SoLo'] = $ss->SoLo;
                                                        $insert['OThuocTinhChiTiet'][$ai]['SoSerial'] = $ss->SoSerial;
                                                        $insert['OThuocTinhChiTiet'][$ai]['MaThuocTinh'] = $ss->MaThuocTinh;
                                                        $insert['OThuocTinhChiTiet'][$ai]['Kho'] = $ss->KhoLayHang;
                                                        //$insert['OThuocTinhChiTiet'][$ai]['Zone'] = $ss->TuZone;
                                                        $insert['OThuocTinhChiTiet'][$ai]['Bin'] = $ss->TuBin;
                                                        $insert['OThuocTinhChiTiet'][$ai]['SoLuong'] = $ss->SoLuong;
                                                        $ai++;
                                                }
                                        }
                                }
                        }

                        if (count($insert))
                        {
                                $service = $this->services->Form->Manual($toModule, 0, $insert, false);
                                if ($service->isError())
                                {
                                        $this->setError();
                                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                                }
                        }
                }

                //$this->setError();
        }

        // @todo: Do mot san pham co the co nhieu don vi tinh, len can quy doi ve don vi tinh co so de so sanh so luong giua don hang va ke hoach
        // So sanh danh sach don hang voi ke hoach chuyen hang
        protected function compareOrderWithDeliveryPlan($order, $orderField, $plan, $planField)
        {
                $aOrder = array(); // Mang chua danh sach don hang dung so sanh voi ke hoach nhan hang
                $aReceivePlan = array(); // Mang chua ke hoach nhan hang dung so sanh voi don hang thuc te
                $planNotMatchWithOrder = false;

                // Order alias
                $refOrderAttrAlias = 'Ref_' . $orderField['Attr'];
                $refOrderItemAlias = 'Ref_' . $orderField['Item'];
                $refOrderQtyAlias = $orderField['Qty'];
                // Plan alias
                $refPlanAttrAlias = 'Ref_' . $planField['Attr'];
                $refPlanItemAlias = 'Ref_' . $planField['Item'];
                $refPlanQtyAlias = $planField['Qty'];

                // Kiểm tra xem có thuộc tính bắt buộc nào chưa được cập nhật.
                foreach ($order as $value)
                {
                        // Gan mang don hang de dung so sanh voi ke hoach nhan
                        // key la refSanPham0refThuocTinh
                        $value->$refOrderAttrAlias = (int) $value->$refOrderAttrAlias;
                        $key = $value->$refOrderItemAlias . '-' . $value->$refOrderAttrAlias;
                        $aOrder[$key] = $value->$refOrderQtyAlias;
                }

                // So sanh so luong giua ds don hang va ke hoach nhan hang
                // Lay danh sach ke hoach nhan hang vao mang voi key la ref item va ref attribute
                // Cong don cac san pham giong nhau cung thuoc tinh 
                // Lay key dang ref0ref
                foreach ($plan as $item)
                {
                        $item->$refPlanAttrAlias = (int) $item->$refPlanAttrAlias;
                        $key = $item->$refPlanItemAlias . '-' . $item->$refPlanAttrAlias;

                        // Cong don so luong
                        if (isset($aReceivePlan[$key]))
                        {
                                $aReceivePlan[$key] += $item->$refPlanQtyAlias;
                        }
                        else
                        {
                                $aReceivePlan[$key] = $item->$refPlanQtyAlias;
                        }
                }

                //echo '<pre>'; print_r($aOrder);
                //echo '<pre>'; print_r($aReceivePlan);
                // So sanh hai mang don ban hang va ke hoach nhan hang
                // Loop qua mang don ban hang de lay cac sp can so sanh
                // Neu ton tai key cua don ban hang trong ke hoach nhan hang thi kiem tra so luong
                // Neu ko ton tai key cua don ban hang trong ke hoach thi bao loi 
                foreach ($aOrder as $item => $qty)
                {
                        if (isset($aReceivePlan[$item]))
                        {
                                // Kiem tra so luong trong don hang va ke hoach co bang nhau hay ko?
                                if ($qty != $aReceivePlan[$item])
                                {
                                        // Loi: Khong bang nhau roi bao loi thoi
                                        $planNotMatchWithOrder = true;
                                }
                        }
                        else
                        {
                                // Loi: khong co ke hoach nhan cho san pham trong don hang nay
                                $planNotMatchWithOrder = true;
                        }
                }
                return $planNotMatchWithOrder;
        }

        /**
         * Check lot and serial has existed in warehouse
         */
        public function _validateStock($stockStatus)
        {
                $mProduct = new Qss_Model_Extra_Products;
                //$stockStatus = $this->_params->OThuocTinhChiTiet;
                $lotArr = array();
                $seriArr = array();

                // put lots and serials to array
                foreach ($stockStatus as $val)
                {
                        if ($val->SoLo) $lotArr[] = $val->SoLo;
                        if ($val->SoSerial) $seriArr[] = $val->SoSerial;
                }

                // check lots exists
                $lotExists = $mProduct->checkLotExists($lotArr);

                // if lotExists has elment return error
                if (count((array) ($lotExists)))
                {
                        foreach ($lotExists as $lot)
                        {
                                $this->setMessage("{$this->_translate(7)} \"{$lot->SoLo}\" {$this->_translate(8)}");
                        }
                        $this->setError();
                }
                // if lotExists empty return null
                // Kiem tra serial da duoc dung chua
                $serialArr = $mProduct->checkSerialExists($seriArr);
                if (count((array) $serialArr))
                {
                        foreach ($serialArr as $val)
                        {
                                $this->setMessage("Serial {$val->SoSerial} " .
                                        " {$this->_translate(8)}");
                        }
                        $this->setError();
                }
        }
		
		/**
		 * @Note: co the co nhieu hon mot kho va co the khong co trang thai luu tru nen
		 * phai loop hai lan de lay gia tri
		 * @param type $refWarehouse
		 * @param type $stockStatus
		 * @param type $module
		 * @return type
		 */
		public function _checkWarehouseLocked($refWarehouse, $stockStatus, $module = '')
		{
			$ret = array();
			$warehouseModel  = new Qss_Model_Extra_Warehouse();
			$removeDuplicateWarehouse = array();
			$excludeModule = array('M612');
			
			$refBinAlias = 'Ref_Bin';
			$binAlias    = 'Bin';
			
			if($module == 'M612')
			{
				$refBinAlias = 'Ref_DenBin';
				$binAlias    = 'DenBin';
			}
			
			$removeDuplicateWarehouse[] = $refWarehouse;
			$warehouseControl = $warehouseModel->getWarehouseStatisticsNotComplete($refWarehouse);
			
			if(count((array)$warehouseControl))
			{
				foreach($warehouseControl as $control)
				{
					$ret[$control->Ref_MaKho]['Warehouse']  = $control->MaKho;
					$ret[$control->Ref_MaKho]['LockAll']    = true;
					
					if($control->$refBinAlias)
					{
						$ret[$control->Ref_MaKho]['LockAll'] = false;
					}
					
					foreach($stockStatus as $status)
					{
						if(($status->Ref_Kho == $control->Ref_MaKho) 
							&& ($control->$refBinAlias == $status->Ref_Bin))
						{
							$ret[$control->Ref_MaKho]['LockBin'][] = $control->$binAlias;
						}
					}
				}
			}
			
			
			if(!in_array($module, $excludeModule))
			{
			foreach($stockStatus as $status)
			{
				$warehouseControl = new stdClass();// reset
				if(!(in_array($status->Ref_Kho, $removeDuplicateWarehouse)))
				{
					$removeDuplicateWarehouse[] = $status->Ref_Kho;
					$warehouseControl = $warehouseModel->getWarehouseStatisticsNotComplete($status->Ref_Kho);
					if(count((array)$warehouseControl))
					{
						foreach($warehouseControl as $control)
						{
							
							$ret[$control->Ref_MaKho]['Warehouse']  = $control->MaKho;
							$ret[$control->Ref_MaKho]['LockAll']    = true;			
							
							if($control->$refBinAlias)
							{
								$ret[$control->Ref_MaKho]['LockAll'] = false;
							}

							foreach($stockStatus as $status)
							{
								if(($status->Ref_Kho == $control->Ref_MaKho) 
									&& ($control->$refBinAlias == $status->Ref_Bin))
								{
									$ret[$control->Ref_MaKho]['LockBin'][] = $control->$binAlias;
								}
							}
						}
					}
				}
			}
			}
			
			foreach($ret as $lock)
			{
				if($lock['LockAll'])
				{
					$this->setMessage('Kho '.$lock['Warehouse'].' đang thực hiện kiểm kê! Không thể thực hiện xuất nhập kho.');
					$this->setError();
				}	
				// Mot bin duoc kiem ke
				elseif(count($lock['LockBin']))
				{
					$this->setMessage('Bin '. implode(', ', $lock['LockBin']) .' trong kho '.$lock['Warehouse'].' đang thực hiện kiểm kê! Không thể thực hiện xuất nhập kho.');
					$this->setError();
				}				
			}
			return $ret;
		}		

        /**
         * 
         * @param type $formConfig
         *      module => 
         *      ifid   =>
         *      stockObj =>
         *      wModule =>
         *      wStockObj =>
         */
        protected function _getStockStatusSortedForUpdateWarehouse($formConfig, $alias)
        {
                $retval = array();
                $temp = array();
                $i = 0;

                // get stock status
                $stockStatus = $this->_warehouse->getStockStatusSortedForUpdateWarehouse($formConfig);

                // put stock status arr
                foreach ($stockStatus as $val)
                {
                        $val->$alias['RefAttr'] = isset($val->$alias['RefAttr'])?@(int)$val->$alias['RefAttr']:0;
                        $val->$alias['RefUOM'] = @(int)$val->$alias['RefUOM'];
						$val->$alias['wRefAttr'] = @(int)$val->$alias['wRefAttr'];
						
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['RefItem'] = $val->$alias['wRefItem'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['ItemCode'] = $val->$alias['wItemCode'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['ItemName'] = $val->$alias['wItemName'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['UOM'] = $val->$alias['UOM'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['RefUOM'] = $val->$alias['wRefUOM'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['RefAttr'] = $val->$alias['wRefAttr'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['Attr'] = $val->$alias['wAttr'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['Lot'] = $val->$alias['wLot'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['Serial'] = $val->$alias['wSerial'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['RefWarehouse'] = $val->$alias['wRefWarehouse'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['Warehouse'] = $val->$alias['wWarehouse'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['Bin'] = $val->$alias['wBin'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['Qty'] = $val->$alias['wQty'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['ReceiveDate'] = $val->$alias['wReceiveDate'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['ProductDate'] = $val->$alias['wProductDate'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['ExpiryDate'] = $val->$alias['wExpiryDate'];
                        $retval[$val->$alias['wRefItem']][$val->$alias['wRefAttr']][$val->$alias['wRefUOM']][$i]['Inventory'] = $val->$alias['wInventory'];

                        // increase index
                        $i++;
                }
                return $retval;
        }

        protected function _checkWarehouseInsertData($updateDB, $print = true)
        {
                $retval = false;
                // Not print error to normal user *** $retval = true
                if ($print && !is_array($updateDB))
                {
                        $retval = true;
                        switch ($updateDB)
                        {
                                case 1:
                                        // Error 1: $dataInsertConf has wrong element *** $updateDB return 1
                                        $this->setMessage('Error 1');
                                        //$retval = true;
                                        break;

                                case 2:
                                        // Error 2:(item Line not stockstaus) Lines insert empty
                                        $this->setMessage('Error 2');
                                        break;
                        }
                }


                return $retval;
        }

        protected function _getUpdateWarehouseDataAliasArr($moduleCode, $extAlias = 0)
        {
                $alias = array();
                // Specific Alias
                switch ($moduleCode)
                {
                        // Alias for receive arr
                        case 'M402':
                                // general
                                $alias['IFID'] = 'IFID_M402';
                                $alias['IOID'] = 'IOID';

                                // lines
                                $alias['ItemCode'] = 'MaSanPham';
                                $alias['ItemName'] = 'TenSanPham';
                                $alias['RefItem'] = 'Ref_MaSanPham';
                                $alias['UOM'] = 'DonViTinh';
                                $alias['RefUOM'] = 'Ref_DonViTinh';
                                $alias['RefAttr'] = 'Ref_ThuocTinh';
                                $alias['Attr'] = 'ThuocTinh';
                                $alias['Warehouse'] = 'Kho';
                                $alias['RefWarehouse'] = 'Ref_Kho';
                                $alias['UnitPrice'] = 'DonGia';
                                $alias['Qty'] = 'SoLuong';


                                // stock status
                                $alias['wItemCode'] = 'MaSanPham';
                                $alias['wItemName'] = 'TenSanPham';
                                $alias['wRefItem'] = 'Ref_MaSanPham';
                                $alias['wUOM'] = 'DonViTinh';
                                $alias['wRefUOM'] = 'Ref_DonViTinh';
                                $alias['wRefAttr'] = 'Ref_MaThuocTinh';
                                $alias['wAttr'] = 'MaThuocTinh';
                                $alias['wLot'] = 'SoLo';
                                $alias['wSerial'] = 'SoSerial';
                                $alias['wWarehouse'] = 'Kho';
                                $alias['wRefWarehouse'] = 'Ref_Kho';
                                $alias['wBin'] = 'Bin';
                                $alias['wQty'] = 'SoLuong';
                                $alias['wReceiveDate'] = 'NgayNhan';
                                $alias['wProductDate'] = 'NgaySX';
                                $alias['wExpiryDate'] = 'NgayHan';
                                $alias['wInventory'] = 'Inventory';

                                break;

                        case 'M506':
                                // general
                                $alias['IFID'] = 'IFID_M506';
                                $alias['IOID'] = 'IOID';

                                // lines
                                $alias['ItemCode'] = 'MaSP';
                                $alias['ItemName'] = 'TenSP';
                                $alias['RefItem'] = 'Ref_MaSP';
                                $alias['UOM'] = 'DonViTinh';
                                $alias['RefUOM'] = 'Ref_DonViTinh';
                                $alias['RefAttr'] = 'Ref_ThuocTinh';
                                $alias['Attr'] = 'ThuocTinh';
                                $alias['Warehouse'] = 'Kho';
                                $alias['RefWarehouse'] = 'Ref_Kho';
                                $alias['UnitPrice'] = 'DonGia';
                                $alias['Qty'] = 'SoLuong';


                                // stock status
                                $alias['wItemCode'] = 'MaSanPham';
                                $alias['wItemName'] = 'TenSanPham';
                                $alias['wRefItem'] = 'Ref_MaSanPham';
                                $alias['wUOM'] = 'DonViTinh';
                                $alias['wRefUOM'] = 'Ref_DonViTinh';
                                $alias['wRefAttr'] = 'Ref_MaThuocTinh';
                                $alias['wAttr'] = 'MaThuocTinh';
                                $alias['wLot'] = 'SoLo';
                                $alias['wSerial'] = 'SoSerial';
                                $alias['wWarehouse'] = 'Kho';
                                $alias['wRefWarehouse'] = 'Ref_Kho';
                                $alias['wBin'] = 'Bin';
                                $alias['wQty'] = 'SoLuong';
                                $alias['wReceiveDate'] = 'NgayNhan';
                                $alias['wProductDate'] = 'NgaySX';
                                $alias['wExpiryDate'] = 'NgayHan';
                                $alias['wInventory'] = 'Inventory';

                                break;

                        case 'M706':
                                if ($extAlias == 1)
                                {
                                        // general
                                        $alias['IFID'] = 'IFID_M706';
                                        $alias['IOID'] = 'IOID';

                                        // lines
                                        $alias['ItemCode'] = 'MaVatTu';
                                        $alias['ItemName'] = 'TenVatTu';
                                        $alias['RefItem'] = 'Ref_MaVatTu';
                                        $alias['UOM'] = 'DonViTinh';
                                        $alias['RefUOM'] = 'Ref_DonViTinh';
                                        $alias['RefAttr'] = 'Ref_ThuocTinh';
                                        $alias['Attr'] = 'ThuocTinh';
                                        $alias['Warehouse'] = 'KhoVatTu';
                                        $alias['RefWarehouse'] = 'Ref_KhoVatTu';
                                        $alias['UnitPrice'] = 'DonGia';
                                        $alias['Qty'] = 'SoLuong';


                                        // stock status
                                        $alias['wItemCode'] = 'MaSanPham';
                                        $alias['wItemName'] = 'TenSanPham';
                                        $alias['wRefItem'] = 'Ref_MaSanPham';
                                        $alias['wUOM'] = 'DonViTinh';
                                        $alias['wRefUOM'] = 'Ref_DonViTinh';
                                        $alias['wRefAttr'] = 'Ref_MaThuocTinh';
                                        $alias['wAttr'] = 'MaThuocTinh';
                                        $alias['wLot'] = 'SoLo';
                                        $alias['wSerial'] = 'SoSerial';
                                        $alias['wWarehouse'] = 'Kho';
                                        $alias['wRefWarehouse'] = 'Ref_Kho';
                                        $alias['wBin'] = 'Bin';
                                        $alias['wQty'] = 'SoLuong';
                                        $alias['wReceiveDate'] = 'NgayNhan';
                                        $alias['wProductDate'] = 'NgaySX';
                                        $alias['wExpiryDate'] = 'NgayHan';
                                        $alias['wInventory'] = 'Inventory';
                                }
                                elseif ($extAlias == 2)
                                {
                                        // general
                                        $alias['IFID'] = 'IFID_M706';
                                        $alias['IOID'] = 'IOID';

                                        // lines
                                        $alias['ItemCode'] = 'Ma';
                                        $alias['ItemName'] = 'Ten';
                                        $alias['RefItem'] = 'Ref_Ma';
                                        $alias['UOM'] = 'DonViTinh';
                                        $alias['RefUOM'] = 'Ref_DonViTinh';
                                        $alias['RefAttr'] = 'Ref_ThuocTinh';
                                        $alias['Attr'] = 'ThuocTinh';
                                        $alias['Warehouse'] = 'KhoPheLieu';
                                        $alias['RefWarehouse'] = 'Ref_KhoPheLieu';
                                        $alias['UnitPrice'] = 'DonGia';
                                        $alias['Qty'] = 'SoLuong';


                                        // stock status
                                        $alias['wItemCode'] = 'MaSanPham';
                                        $alias['wItemName'] = 'TenSanPham';
                                        $alias['wRefItem'] = 'Ref_MaSanPham';
                                        $alias['wUOM'] = 'DonViTinh';
                                        $alias['wRefUOM'] = 'Ref_DonViTinh';
                                        $alias['wRefAttr'] = 'Ref_MaThuocTinh';
                                        $alias['wAttr'] = 'MaThuocTinh';
                                        $alias['wLot'] = 'SoLo';
                                        $alias['wSerial'] = 'SoSerial';
                                        $alias['wWarehouse'] = 'Kho';
                                        $alias['wRefWarehouse'] = 'Ref_Kho';
                                        $alias['wBin'] = 'Bin';
                                        $alias['wQty'] = 'SoLuong';
                                        $alias['wReceiveDate'] = 'NgayNhan';
                                        $alias['wProductDate'] = 'NgaySX';
                                        $alias['wExpiryDate'] = 'NgayHan';
                                        $alias['wInventory'] = 'Inventory';
                                }
                                break;


                        case 'M759':
                                if ($extAlias == 1)
                                {
                                        // general
                                        $alias['IFID'] = 'IFID_M759';
                                        $alias['IOID'] = 'IOID';

                                        // lines
                                        $alias['ItemCode'] = 'MaVatTu';
                                        $alias['ItemName'] = 'TenVatTu';
                                        $alias['RefItem'] = 'Ref_MaVatTu';
                                        $alias['UOM'] = 'DonViTinh';
                                        $alias['RefUOM'] = 'Ref_DonViTinh';
                                        $alias['RefAttr'] = 'Ref_ThuocTinh';
                                        $alias['Attr'] = 'ThuocTinh';
                                        $alias['Warehouse'] = 'KhoVatTu';
                                        $alias['RefWarehouse'] = 'Ref_KhoVatTu';
                                        $alias['UnitPrice'] = 'DonGia';
                                        $alias['Qty'] = 'SoLuong';


                                        // stock status
                                        $alias['wItemCode'] = 'MaSanPham';
                                        $alias['wItemName'] = 'TenSanPham';
                                        $alias['wRefItem'] = 'Ref_MaSanPham';
                                        $alias['wUOM'] = 'DonViTinh';
                                        $alias['wRefUOM'] = 'Ref_DonViTinh';
                                        $alias['wRefAttr'] = 'Ref_MaThuocTinh';
                                        $alias['wAttr'] = 'MaThuocTinh';
                                        $alias['wLot'] = 'SoLo';
                                        $alias['wSerial'] = 'SoSerial';
                                        $alias['wWarehouse'] = 'Kho';
                                        $alias['wRefWarehouse'] = 'Ref_Kho';
                                        $alias['wBin'] = 'Bin';
                                        $alias['wQty'] = 'SoLuong';
                                        $alias['wReceiveDate'] = 'NgayNhan';
                                        $alias['wProductDate'] = 'NgaySX';
                                        $alias['wExpiryDate'] = 'NgayHan';
                                        $alias['wInventory'] = 'Inventory';
                                }
                                elseif ($extAlias == 2)
                                {
                                        // general
                                        $alias['IFID'] = 'IFID_M759';
                                        $alias['IOID'] = 'IOID';

                                        // lines
                                        $alias['ItemCode'] = 'Ma';
                                        $alias['ItemName'] = 'Ten';
                                        $alias['RefItem'] = 'Ref_Ma';
                                        $alias['UOM'] = 'DonViTinh';
                                        $alias['RefUOM'] = 'Ref_DonViTinh';
                                        $alias['RefAttr'] = 'Ref_ThuocTinh';
                                        $alias['Attr'] = 'ThuocTinh';
                                        $alias['Warehouse'] = 'KhoPheLieu';
                                        $alias['RefWarehouse'] = 'Ref_KhoPheLieu';
                                        $alias['UnitPrice'] = 'DonGia';
                                        $alias['Qty'] = 'SoLuong';


                                        // stock status
                                        $alias['wItemCode'] = 'MaSanPham';
                                        $alias['wItemName'] = 'TenSanPham';
                                        $alias['wRefItem'] = 'Ref_MaSanPham';
                                        $alias['wUOM'] = 'DonViTinh';
                                        $alias['wRefUOM'] = 'Ref_DonViTinh';
                                        $alias['wRefAttr'] = 'Ref_MaThuocTinh';
                                        $alias['wAttr'] = 'MaThuocTinh';
                                        $alias['wLot'] = 'SoLo';
                                        $alias['wSerial'] = 'SoSerial';
                                        $alias['wWarehouse'] = 'Kho';
                                        $alias['wRefWarehouse'] = 'Ref_Kho';
                                        $alias['wBin'] = 'Bin';
                                        $alias['wQty'] = 'SoLuong';
                                        $alias['wReceiveDate'] = 'NgayNhan';
                                        $alias['wProductDate'] = 'NgaySX';
                                        $alias['wExpiryDate'] = 'NgayHan';
                                        $alias['wInventory'] = 'Inventory';
                                }
                                break;
                }
                return $alias;
        }

        /**
         * 
         * @param string $moduleCode - Mxxx
         * @param id $refWarehouse - warehouse ioid
         * @param id $ifid - ifid of main object, @todo: get inventory of item filter by lines
		 * @todo: Chi lay nhung dong lien quan den dong cap nhat khong lay toan bo kho, kho co the lay tu stock status
         * @return inventory of specific warehouse
         */
        public function _geCurrentInventory( $refWarehouse)
        {
                $stock = array();
                $tempStock = $this->_common->getTable(array('*'), 'OKho'
                        , array('Ref_Kho' => $refWarehouse)
                        , array(), 'NO_LIMIT');

                foreach ($tempStock as $item)
                {
                        $item->Ref_ThuocTinh = @(int) $item->Ref_ThuocTinh;
                        $item->Ref_DonViTinh = @(int) $item->Ref_DonViTinh;
                        $stock[$item->Ref_MaSP][$item->Ref_ThuocTinh][$item->Ref_DonViTinh]['HienCo'] = $item->SoLuongHC; // HienCo
                        $stock[$item->Ref_MaSP][$item->Ref_ThuocTinh][$item->Ref_DonViTinh]['IFID'] = $item->IFID_M602;
                }// loop qua kho 
                return $stock;
        }

        /**
         * 
         * @param type $dataInsertConf
         *      'alias' =>  array
         *      'lines' =>  object
         *      'stockBegin' => array
         *      'type' => receive (1)/shipment (0)
         *      'module' =>
         *      'ifid' =>
         *      'warehouse=>
         *      'refWarehouse'=>
         *      'stockStatus' => sorted array
         *      'transactionDate' => date
         * @return err or update arr
         */
        protected function _getUpdateWarehouseArr($dataInsertConf)
        {

                $transactionArr = array();
                $warehouseArr = array();
                $arrayTypeUniTest = array(0, 1);
                $error = false;
                $errorType = 0;
                // @todo: check item enough (shipment)
                // @todo: check out of capacity (receive)
                // Init data
                $alias = @$dataInsertConf['alias'];        // alias for insert object element
                $lines = @$dataInsertConf['lines'];        // lines to insert
                $linesRequired = @@$dataInsertConf['required'];    // lines required
                $stockBegin = @$dataInsertConf['stockBegin'];   // begin status of stock
                $type = @$dataInsertConf['type'];         // receive or shipment
                $ifid = @$dataInsertConf['ifid'];         // ifid
                $module = @$dataInsertConf['module'];       // M123
                $warehouse = @$dataInsertConf['warehouse'];    // string
                $refWarehouse = @$dataInsertConf['refWarehouse']; // ioid
                $stockStatus = @$dataInsertConf['stockStatus'];  // sorted stockstatus
                $transactionDate = @$dataInsertConf['transactionDate'];
                $transactions = @$dataInsertConf['transactions']; // transaction (convert to base uom)
                $currency = $this->_common->getDefaultCurrency();



                // Break if has wrong @$dataInsertConf element
                // || !count((array)$stockStatus) || !$stockStatus kiem tra nay nen o tre tung module
                if (!is_array($alias) || !count((array) $alias) || !is_array($stockBegin) || !is_numeric($type) || !in_array($type, $arrayTypeUniTest) || !$ifid || !$module || !$warehouse || !$refWarehouse
                )
                {
                        return 1; // Error: wrong @$dataInsertConf element
                }

                if ($linesRequired && (!is_array($lines) || !count((array) $lines)))
                {
                        return 2; // empty line when line is required
                }

                if ($linesRequired && (!count((array) $transactions) || !$transactions))
                {
                        return 3; // empty transaction when line is required
                }


                // echo '<pre>: Kiem tra alias :'; print_r($alias); die;
                // echo '<pre>: Kiem tra lines :'; print_r($lines); die;
                // echo '<pre>: Kiem tra stock begin :'; print_r($stockBegin); die;
                // echo '<pre>: Nhap/xuat :'; print_r($type); die;

                foreach ($lines as $item)
                {

                        $warehouseTempArr = array();

                        // * Get current stock qty
                        $item->$alias['RefAttr'] = @(int)$item->$alias['RefAttr'];
                        $item->$alias['RefUOM'] = @(int)$item->$alias['RefUOM'];
						$item->$alias['RefItem']= @(int)$item->$alias['RefItem'];

                        $currentStockQty = isset($stockBegin[$item->$alias['RefItem']][$item->$alias['RefAttr']][$item->$alias['RefUOM']]) ?
                                $stockBegin[$item->$alias['RefItem']][$item->$alias['RefAttr']][$item->$alias['RefUOM']]['HienCo'] : 0;
                        //echo '<pre>: So luong ton kho '."{$item->$alias['ItemCode']}-{$item->$alias['Attr']}-{$item->$alias['UOM']}".':'; print_r($currentStockQty) ; 
                        // * update stock qty (main object)
                        if ($type == 1)
                        {
                                // ** Receive *** add qty
                                $newStockQty = $currentStockQty + $item->$alias['Qty'];
                        }
                        else
                        {
                                // ** Shipment *** div qty
                                $newStockQty = $currentStockQty - $item->$alias['Qty'];

                                if ($newStockQty < 0)
                                {
                                        $this->setMessage($item->$alias['ItemCode'] . '-' . $item->$alias['ItemName'] . ' ' . $this->_translate(100));
                                }

                                //$error     = true;
                                //$errorType = 2;// not enough item
                        }


                        // Put data into update arr
                        if (!$error)
                        {
                                // * update warehouse - main object *** $warehouseArr
                                $warehouseTempArr['OKho'][0]['MaSP'] = $item->$alias['ItemCode'];
                                $warehouseTempArr['OKho'][0]['DonViTinh'] = $item->$alias['UOM'];
                                $warehouseTempArr['OKho'][0]['ThuocTinh'] = $item->$alias['Attr'];
                                $warehouseTempArr['OKho'][0]['Kho'] = $warehouse;
                                $warehouseTempArr['OKho'][0]['SoLuongHC'] = $newStockQty;

//								echo 'REF ITEM: '.$item->$alias['RefItem'].'<br/>';
//								echo 'REF ATTR: '.$item->$alias['RefAttr'].'<br/>';
//								echo 'REF UOM: '.$item->$alias['RefUOM'].'<br/>';
//								echo '----------------------------------'.'<br/>';
//								
//								echo '<pre>'; print_r($stockStatus); die;
								
                                // * upadete warehouse - stock status *** $warehouseArr
                                if (isset($stockStatus[$item->$alias['RefItem']][@(int)$item->$alias['RefAttr']][$item->$alias['RefUOM']]))
                                {
                                        $updateStockStatus = $stockStatus[$item->$alias['RefItem']][$item->$alias['RefAttr']][$item->$alias['RefUOM']];
                                        $j = 0;


                                        foreach ($updateStockStatus as $stockStatusLine)
                                        {
                                                // Trang thai luu tru
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['MaSanPham'] = $stockStatusLine['ItemCode'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['DonViTinh'] = $stockStatusLine['UOM'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['SoSerial'] = $stockStatusLine['Serial'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['SoLo'] = $stockStatusLine['Lot'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['MaThuocTinh'] = $stockStatusLine['Attr'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['Kho'] = $stockStatusLine['Warehouse'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['Bin'] = $stockStatusLine['Bin'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['NgayNhan'] = $stockStatusLine['ReceiveDate'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['NgaySX'] = $stockStatusLine['ProductDate'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['NgayHan'] = $stockStatusLine['ExpiryDate'];
                                                $warehouseTempArr['OThuocTinhChiTiet'][$j]['SoLuong'] = $stockStatusLine['Qty'];

                                                if ($type == 1)
                                                {
                                                        // ** Receive *** add stock status
                                                        $warehouseTempArr['OThuocTinhChiTiet'][$j]['SoLuong'] = $stockStatusLine['Inventory'] + $stockStatusLine['Qty'];
                                                }
                                                else
                                                {
                                                        // ** Shipment *** div stock status
                                                        $warehouseTempArr['OThuocTinhChiTiet'][$j]['SoLuong'] = $stockStatusLine['Inventory'] - $stockStatusLine['Qty'];
                                                }

                                                $j++;
                                        }
                                }

                                // put to warehouse array
                                $warehouseArr[] = $warehouseTempArr;
                        }
                }
                return $error ? $errorType : $warehouseArr;
        }

        protected function _updateWarehouseDataToDB($updateDB)
        {
                // Update db
                if (!$this->isError())
                {
                        //insert warehouse
                        foreach ($updateDB['warehouse'] as $item)
                        {
                                $service = $this->services->Form->Manual('M602', 0, $item, false);
                                if ($service->isError())
                                {
                                        $this->setError();
                                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                                }
                        }


                        //insert transaction
            foreach ($updateDB['transaction'] as $item)
			{
                $service = $this->services->Form->Manual('M607', 0, $item, false);
                if ($service->isError())
				{
                    $this->setError();
                	$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            	}
        	}
    	}
	}
}

?>