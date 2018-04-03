<?php
Class Qss_Service_Extra_Inventory_Update extends  Qss_Service_Abstract
{
    const STOCK_MODULE_CODE       = 'M602';
    const OUTPUT_MODULE           = 'M506';
    const INPUT_MODULE            = 'M402';

    public  $ifid;
    public  $main;
    public  $lines;
    public  $stockstatus;
    public  $currentStock;
    public  $currentStockStatus;
    public  $updateStock;
    public  $bin        = array(); // kho chua
    public $type;

    public function __construct()
    {
        $this->main               = new stdClass();
        $this->lines              = new stdClass();
        $this->stockstatus        = new stdClass();
        $this->currentStock       = new stdClass();
        $this->currentStockStatus = new stdClass();
        $this->updateStock        = array();
        $this->bin                = array();
        $this->ifid               = 0;
    }


    public function __doExecute($ifid, $type = 1)
    {
        if(Qss_Lib_System::formActive(self::STOCK_MODULE_CODE))
        {
            $this->type = $type;
            if ($type == 1) // update phần nhập kho
            {
                $this->initInput($ifid);
                $this->validInput();
                $this->updateInput();
                $this->updateCostTable();
            }
            elseif ($type == 0) // update phần xuất kho
            {
                $this->initOutput($ifid);
                $this->validOutput();
                $this->updateOutput();
                $this->updateCostTable();
            }
        }
    }

    /*******************************************************************************************************************
     * NHAP KHO - INPUT
     ******************************************************************************************************************/

    private function initInput($ifid)
    {
        $common            = new Qss_Model_Extra_Extra();
        $warhouse          = new Qss_Model_Extra_Warehouse();
        $this->ifid        = $ifid;
        $this->main        = $common->getTableFetchOne('ONhapKho', array('IFID_M402'=>$ifid));
        $this->lines       = $common->getTableFetchAll('ODanhSachNhapKho', array('IFID_M402'=>$ifid));
        $this->stockstatus = $common->getTableFetchAll('OThuocTinhChiTiet', array('IFID_M402'=>$ifid));

        $this->currentStock       = $warhouse->getCurrentStockByInputLines($ifid);
        $this->currentStockStatus = $warhouse->getStockStatusOfWarehouseFormStockStatusOfInput($ifid);

        $this->updateStock       = $this->getInputStock();

    }

    private function updateInput()
    {
        if(!$this->isError())
        {
            // Update ton kho

//            if(count($this->updateStock))
//            {
//                foreach($this->updateStock as $item)
//                {
//                    $ifid =(isset($item['OKho'][0]['ifid']) && $item['OKho'][0]['ifid']) ? $item['OKho'][0]['ifid']: 0;
//                    $this->update(self::STOCK_MODULE_CODE, $ifid, $item);
//                }
//            }


            if(count($this->updateStock))
            {
                $model  = new Qss_Model_Import_Form('M602', false, false);
                foreach($this->updateStock as $item)
                {
                    $ifid =(isset($item['OKho'][0]['ifid']) && $item['OKho'][0]['ifid']) ? $item['OKho'][0]['ifid']: 0;
                    // $this->update(self::STOCK_MODULE_CODE, $ifid, $item);
                    $model->setData($item);
                }

                $model->generateSQL();

                $formError   = $model->countFormError();
                $objectError = $model->countObjectError();
                $error       = $formError + $objectError;

                // echo '<pre>'; print_r($model->getImportRows()); die;

                if($error > 0)
                {
                    $this->setError();
                    $arrErrors = $model->getErrorRows();
                    ob_start();
					print_r($arrErrors);
					$ret = ob_get_clean();
                    $this->setMessage($ret);
                }
            }
        }
    }

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
                $this->setMessage('Kho '.$lock['Warehouse'].' đang thực hiện kiểm kê! Không thể thực hiện xuất nhập kho.'); // $this->_translate(2);
                $this->setError();
            }
            // Mot bin duoc kiem ke
            elseif(count($lock['LockBin']))
            {
                $this->setMessage('Bin '. implode(', ', $lock['LockBin']) .' trong kho '.$lock['Warehouse'].' đang thực hiện kiểm kê! Không thể thực hiện xuất nhập kho.'); // $this->_translate(2);
                $this->setError();
            }
        }
        return $ret;
    }

    /**
     * Kiem tra dieu kien nhap kho
     */
    private function validInput()
    {
        // Kiem tra xem kho co bi khoa hay khong?
        $this->_checkWarehouseLocked($this->main->Ref_Kho, $this->stockstatus, self::INPUT_MODULE);

        // Ngay giao hang da co chua?
        $this->validInputDeliveryDate();

        // Danh Sach nhap kho da co chua?
        $this->validInputLines();

        // Da cai dat du trang thai luu tru hay chua
        $this->validInputStockStatus();

        // So lo va serial da ton tai
        $this->validInputLotAndSerial();

        // Nhap kho du suc chua hay khong? (chu yeu la bin )
        $this->validInputCapacity();
    }

    /**
     * Kiem tra ngay giao hang cua phieu nhap kho da co chua?
     */
    private function validInputDeliveryDate()
    {
        if(!@$this->main->NgayChuyenHang || $this->main->NgayChuyenHang == '0000-00-00')
        {
            $this->setMessage('Ngày giao hàng yêu cầu bắt buộc!'); // $this->_translate(1);
            $this->setError();
        }
    }

    /**
     * Kiem tra xem danh sach nhap kho da duoc nhap hay chua?
     */
    private function validInputLines()
    {
        if(!count($this->lines))
        {
            $this->setMessage('Danh sách nhập kho yêu cầu bắt buộc!'); // $this->_translate(2);
            $this->setError();
        }
    }

    /**
     * Kiem tra da cai dat du trang thai luu tru hay chua
     */
    private function validInputStockStatus()
    {
        $model            = new Qss_Model_Extra_Warehouse();
        $total            = $model->getTotalReceiveHasAttr($this->ifid, self::INPUT_MODULE);
        $totalStockStatus = 0;

        foreach ($this->stockstatus as $val)
        {
            $totalStockStatus += $val->SoLuong;
        }

        if($total && $total != $totalStockStatus)
        {
            $this->setMessage('Kiểm tra lại số lượng trong các vị trí của kho!'); // $this->_translate(3);
            $this->setError();
        }
    }

    private function validInputLotAndSerial()
    {
        $mProduct = new Qss_Model_Extra_Products;
        $lotArr   = array();
        $seriArr  = array();

        // put lots and serials to array
        foreach ($this->stockstatus as $val)
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
                $this->setMessage("Số lô \"{$lot->SoLo}\" đã tồn tại"); // $this->_translate(3);
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
                $this->setMessage("Số serial \"{$lot->SoSerial}\" đã tồn tại"); // $this->_translate(3);
            }
            $this->setError();
        }
    }

    /**
     * Kiem tra suc chua cua bin
     */
    private function validInputCapacity()
    {
        foreach($this->currentStockStatus as $item)
        {
            if($item->RefBinItem != 0 && $item->BinUOM != '')
            {
                if($item->Ref_MaSanPham == $item->RefBinItem && $item->DonViTinh == $item->BinUOM)
                {
                    if(!$this->checkCapacity($item->SoLuongKho, $item->SoLuong, $item->BinCapacity))
                    {
                        $this->setMessage("Bin {$item->Bin} không đủ sức chứa mặt hàng {$item->MaSanPham}");
                        $this->setError();
                    }
                }
                else
                {
                    $this->setMessage("Bin {$item->Bin} không chứa mặt hàng {$item->MaSanPham}");
                    $this->setError();
                }
            }
            elseif($item->RefBinItem != 0)
            {
                if($item->Ref_MaSanPham == $item->RefBinItem)
                {
                    if(!$this->checkCapacity($item->SoLuongKho, $item->SoLuong, $item->BinCapacity))
                    {
                        $this->setMessage("Bin {$item->Bin} không đủ sức chứa mặt hàng {$item->MaSanPham}");
                        $this->setError();
                    }
                }
                else
                {
                    $this->setMessage("Bin {$item->Bin} không chứa mặt hàng {$item->MaSanPham}");
                    $this->setError();
                }
            }
            elseif($item->BinUOM != '')
            {
                if($item->DonViTinh == $item->BinUOM)
                {
                    if(!$this->checkCapacity($item->SoLuongKho, $item->SoLuong, $item->BinCapacity))
                    {
                        $this->setMessage("Bin {$item->Bin} không đủ sức chứa mặt hàng {$item->MaSanPham}");
                        $this->setError();
                    }
                }
                else
                {
                    $this->setMessage("Bin {$item->Bin} không chứa mặt hàng có đơn vị tính là {$item->DonViTinh}");
                    $this->setError();
                }
            }
        }
    }

    private function checkCapacity($hientai, $themvao, $succhua)
    {
        return (($hientai + $themvao) > $succhua)?false:true;
    }

    private function getInputStock()
    {
        $update = array();

        if(!$this->isError())
        {

            $i      = 0;

            // Update kho: mang gom kho va trang thai luu tru kho tu danh sach nhap kho
            foreach($this->currentStock as $item)
            {
                $code = @(int)$item->IFID_M402.'_'. @(int)$item->Ref_MaSanPham.'_'. @(int)$item->Ref_DonViTinh.'_'. @(int)$item->Ref_ThuocTinh;

                // @todo: Sau can them ca thuoc tinh vao phan revert
                $update[$code]['OKho'][0]['Kho']       = $item->Kho;
                $update[$code]['OKho'][0]['MaSP']      = $item->MaSanPham;
                $update[$code]['OKho'][0]['TenSP']     = '';
                $update[$code]['OKho'][0]['DonViTinh'] = $item->DonViTinh;
                $update[$code]['OKho'][0]['ThuocTinh'] = @$item->ThuocTinh;
                $update[$code]['OKho'][0]['SoLuongHC'] = @(double)$item->SoLuongHC + @(double)$item->SoLuong;

                if($item->WIOID)
                {
                    $update[$code]['OKho'][0]['ioid'] = $item->WIOID;
                }

                $update[$code]['OKho'][0]['ifid'] = $item->WIFID;
            }

            // Update giao dich kho: lay tu danh sach nhap kho
            foreach($this->currentStockStatus as $item)
            {
                $code = @(int)$item->IFID_M402.'_'. @(int)$item->Ref_MaSanPham.'_'. @(int)$item->Ref_DonViTinh.'_'. @(int)$item->Ref_MaThuocTinh;

                if (isset($update[$code]))
                {
                    $update[$code]['OThuocTinhChiTiet'][$i]['Kho']         = $item->Kho;
                    $update[$code]['OThuocTinhChiTiet'][$i]['Bin']         = $item->Bin;
                    $update[$code]['OThuocTinhChiTiet'][$i]['MaSanPham']   = $item->MaSanPham;
                    $update[$code]['OThuocTinhChiTiet'][$i]['TenSanPham']  = '';
                    $update[$code]['OThuocTinhChiTiet'][$i]['MaThuocTinh'] = @$item->MaThuocTinh;
                    $update[$code]['OThuocTinhChiTiet'][$i]['SoLo']        = $item->SoLo;
                    $update[$code]['OThuocTinhChiTiet'][$i]['SoSerial']    = $item->SoSerial;
                    $update[$code]['OThuocTinhChiTiet'][$i]['DonViTinh']   = $item->DonViTinh;
                    $update[$code]['OThuocTinhChiTiet'][$i]['SoLuong']     = @(double)$item->SoLuongKho + @(double)$item->SoLuong;
                    if($item->WIOID)
                    {
                        $update[$code]['OThuocTinhChiTiet'][$i]['ioid'] = $item->WIOID;
                    }
                    $i++;
                }

            }

        }

        return $update;
    }



    /*******************************************************************************************************************
     * XUAT KHO - OUTPUT
     ******************************************************************************************************************/

    private function initOutput($ifid)
    {
        $common            = new Qss_Model_Extra_Extra();
        $warhouse          = new Qss_Model_Extra_Warehouse();
        $this->ifid        = $ifid;
        $this->main        = $common->getTableFetchOne('OXuatKho', array('IFID_M506'=>$ifid));
        $this->lines       = $common->getTableFetchAll('ODanhSachXuatKho', array('IFID_M506'=>$ifid));
        $this->stockstatus = $common->getTableFetchAll('OThuocTinhChiTiet', array('IFID_M506'=>$ifid));
        
        $this->currentStock       = $warhouse->getCurrentStockByOutputLines($ifid);
        $this->currentStockStatus = $warhouse->getStockStatusOfWarehouseFormStockStatusOfOutput($ifid);

        $this->updateStock       = $this->getOutputStock();

    }

    private function updateOutput()
    {
        if(!$this->isError())
        {
            if(count($this->updateStock))
            {
                $model  = new Qss_Model_Import_Form('M602', false, false);
                foreach($this->updateStock as $item)
                {
                    $ifid =(isset($item['OKho'][0]['ifid']) && $item['OKho'][0]['ifid']) ? $item['OKho'][0]['ifid']: 0;
                    $this->update(self::STOCK_MODULE_CODE, $ifid, $item);
                    $model->setData($item);
                }

                $model->generateSQL();

                $formError   = $model->countFormError();
                $objectError = $model->countObjectError();
                $error       = $formError + $objectError;

                // echo '<pre>'; print_r($model->getImportRows()); die;

                if($error > 0)
                {
                    $this->setError();
                    $arrErrors = $model->getErrorRows();
                    ob_start();
					print_r($arrErrors);
					$ret = ob_get_clean();
                    $this->setMessage($ret);
                }
            }

        }
    }


    private function validOutput()
    {
        // Kiem tra xem kho co bi khoa hay khong?
        $this->_checkWarehouseLocked($this->main->Ref_Kho, $this->stockstatus, self::OUTPUT_MODULE);

        // Ngay giao hang da co chua?
        $this->validOutputDeliveryDate();

        // Danh Sach xuat kho da co chua?
        $this->validOutputLines();

        // Da cai dat du trang thai luu tru hay chua
        $this->validOutputStockStatus();

        // Xuat kho co du so luong hay khong?
        $this->validOutputQty();

    }


    /**
     * Kiem tra ngay giao hang cua phieu nhap kho da co chua?
     */
    private function validOutputDeliveryDate()
    {
        if(!@$this->main->NgayChuyenHang || $this->main->NgayChuyenHang == '0000-00-00')
        {
            $this->setMessage('Ngày giao hàng yêu cầu bắt buộc!'); // $this->_translate(1);
            $this->setError();
        }
    }

    /**
     * Kiem tra xem danh sach nhap kho da duoc nhap hay chua?
     */
    private function validOutputLines()
    {
        if(!count($this->lines))
        {
            $this->setMessage('Danh sách xuất kho yêu cầu bắt buộc!'); // $this->_translate(2);
            $this->setError();
        }
    }

    /**
     * Kiem tra da cai dat du so trang thai luu tru hay chua?
     */
    private function validOutputStockStatus()
    {

        $model              = new Qss_Model_Extra_Warehouse();
        $total              = $model->getTotalReceiveHasAttr($this->ifid, self::OUTPUT_MODULE);
        $totalStockStatus   = 0;

        foreach ($this->stockstatus as $val)
        {
            $totalStockStatus += $val->SoLuong;
        }

        if($total && $total != $totalStockStatus)
        {
            $this->setMessage('Ít nhất một mặt hàng chưa đươc cài đặt hoàn thiện trạng thái lưu trữ!'); // $this->_translate(3);
            $this->setError();
        }
    }

    /**
     * Kiem tra co du so luong xuat kho hay khong
     */
    private function validOutputQty()
    {
        foreach($this->updateStock as $item)
        {

            if(isset($item['OKho'][0]['SoLuongHC']) && $item['OKho'][0]['SoLuongHC']  < 0 )
            {
                $this->setError();
                $this->setMessage("Mặt hàng {$item['OKho'][0]['MaSP']}  không đủ số lượng xuất kho");
            }


            if(isset($item['OThuocTinhChiTiet']) && count($item['OThuocTinhChiTiet']))
            {
                foreach($item['OThuocTinhChiTiet'] as $item2)
                {
                    if($item2['SoLuong'] < 0)
                    {
                        $this->setError();
                        $this->setMessage("Mặt hàng {$item['OThuocTinhChiTiet'][0]['MaSanPham']}  không đủ số lượng xuất kho");
                    }
                }
            }

        }
    }



    private function getOutputStock()
    {
        $update = array();

        if(!$this->isError())
        {

            $i      = 0;

            // Update kho: mang gom kho va trang thai luu tru kho tu danh sach xuat kho
            foreach($this->currentStock as $item)
            {
                $code = @(int)$item->IFID_M506.'_'. @(int)$item->Ref_MaSP.'_'. @(int)$item->Ref_DonViTinh.'_'. @(int)$item->Ref_ThuocTinh;

                // @todo: Sau can them ca thuoc tinh vao phan revert
                $update[$code]['OKho'][0]['Kho']       = $item->Kho;
                $update[$code]['OKho'][0]['MaSP']      = $item->MaSP;
                $update[$code]['OKho'][0]['TenSP']     = '';
                $update[$code]['OKho'][0]['DonViTinh'] = $item->DonViTinh;
                $update[$code]['OKho'][0]['ThuocTinh'] = @$item->ThuocTinh;
                $update[$code]['OKho'][0]['SoLuongHC'] = @(double)$item->SoLuongHC - @(double)$item->SoLuong;

                if($item->WIOID)
                {
                    $update[$code]['OKho'][0]['ioid'] = $item->WIOID;
                }

                $update[$code]['OKho'][0]['ifid'] = $item->WIFID;
            }

            // Update giao dich kho: lay tu danh sach xuat kho
            foreach($this->currentStockStatus as $item)
            {
                $code = @(int)$item->IFID_M506 . '_' . @(int)$item->Ref_MaSanPham . '_' . @(int)$item->Ref_DonViTinh . '_' . @(int)$item->Ref_MaThuocTinh;

                if (isset($update[$code]))
                {
                    $update[$code]['OThuocTinhChiTiet'][$i]['Kho'] = $item->Kho;
                    $update[$code]['OThuocTinhChiTiet'][$i]['Bin'] = $item->Bin;
                    $update[$code]['OThuocTinhChiTiet'][$i]['MaSanPham'] = $item->MaSanPham;
                    $update[$code]['OThuocTinhChiTiet'][$i]['TenSanPham'] = '';
                    $update[$code]['OThuocTinhChiTiet'][$i]['MaThuocTinh'] = @$item->MaThuocTinh;
                    $update[$code]['OThuocTinhChiTiet'][$i]['SoLo'] = $item->SoLo;
                    $update[$code]['OThuocTinhChiTiet'][$i]['SoSerial'] = $item->SoSerial;
                    $update[$code]['OThuocTinhChiTiet'][$i]['DonViTinh'] = $item->DonViTinh;
                    $update[$code]['OThuocTinhChiTiet'][$i]['SoLuong'] = @(double)$item->SoLuongKho - @(double)$item->SoLuong;

                    if ($item->WIOID)
                    {
                        $update[$code]['OThuocTinhChiTiet'][$i]['ioid'] = $item->WIOID;
                    }
                    $i++;
                }
            }

        }

        return $update;
    }

    private function update($module, $ifid, $data)
    {
        if(!$this->isError() && is_array($data) && count($data))
        {
//            $service = $this->services->Form->Manual($module, $ifid, $data, false);
//            if ($service->isError())
//            {
//            	$this->setError();
//                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//            }

            $model  = new Qss_Model_Import_Form($module, false, false);
            $model->setData($data);
            $model->generateSQL();

            $formError   = $model->countFormError();
            $objectError = $model->countObjectError();
            $error       = $formError + $objectError;

            // echo '<pre>'; print_r($model->getImportRows()); die;

            if($error > 0)
            {
                $this->setError();
                $arrErrors = $model->getErrorRows();
                    ob_start();
					print_r($arrErrors);
					$ret = ob_get_clean();
                    $this->setMessage($ret);
            }
        }
    }

    private function updateCostTable()
    {
        if(!$this->isError())
        {
            $model    = new Qss_Model_Inventory_Cost();

            foreach($this->currentStock as $item)
            {
                $newdate  = Qss_Lib_Date::i_fMysql2Time($item->NgayGiaoDich);
             	$month    = date('m',$newdate);
                $year     = date('Y',$newdate);
                $j = $month;
                for ($i = $year; $i <= date('Y');$i++)
                {
                	while ($j < date('m') || $i < date('Y'))
                	{
                		if($this->type == 1)
                		{
                			$model->update($item->MaSanPham, $j, $i,$item->Kho);
                		}	
                		else
                		{
                			$model->update($item->MaSP, $j, $i,$item->Kho);
                		}	
                		$j++;
                		if($j == 13)
                		{
                			$j = 1;
                			$i++;
                		}
                	}
                }
                
            }
        }

    }

    private function remove($module, $ifid, $data)
    {
        if(!$this->isError())
        {
            $remove =  $this->services->Form->Remove($module, $ifid, $data);
            if($remove->isError())
            {
                $this->setError();
                $this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }
    }

}