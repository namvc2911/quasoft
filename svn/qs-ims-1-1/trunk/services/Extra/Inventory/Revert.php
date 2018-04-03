<?php
Class Qss_Service_Extra_Inventory_Revert extends  Qss_Service_Abstract
{
    const STOCK_MODULE_CODE = 'M602';

    public $_form;
    
	public function __doExecute($_form, $type = 1)
	{
        if(Qss_Lib_System::formActive(self::STOCK_MODULE_CODE))
        {
            $this->_form = $_form;

            if($type == 1) // revert phần nhập kho
            {
                $this->revertInput();
            }
            elseif($type == 0) // revert phần xuất kho
            {
                $this->revertOutput();
            }
        }

	}
    
    /**
     * Tru di so luong da cong vao kho
     * Xoa di phan giao dich kho da nhap
     */
    private function revertInput()
    {
        $ifid        = $this->_form->i_IFID;
        $common      = new Qss_Model_Extra_Extra();
        $warehouse   = new Qss_Model_Extra_Warehouse();
        // $iForm       = $common->getTableFetchOne('qsiforms', array('IFID'=>$ifid));


        if($this->_form->i_Status == 2)
        {
            $line        = $common->getTableFetchOne('ONhapKho', array('IFID_M402'=>$ifid));
            //$detail      = $common->getTableFetchAll('ODanhSachNhapKho', array('IFID_M402'=>$ifid));
            $stockStatus = $warehouse->getStockStatusOfWarehouseFormStockStatusOfInput($ifid);
            $stock       = $warehouse->getCurrentStockByInputLines($ifid);
            $revertStock = $this->getRevertWarehouseByInputData($stock, $stockStatus);
            
            //$this->update(self::STOCK_MODULE_CODE, 0, $revertStock);

//            if(count($revertStock))
//            {
//                foreach($revertStock as $item)
//                {
//                    $ifid =(isset($item['OKho'][0]['ifid']) && $item['OKho'][0]['ifid']) ? $item['OKho'][0]['ifid']: 0;
//                    $this->update(self::STOCK_MODULE_CODE, $ifid, $item);
//                }
//            }

            if(count($revertStock))
            {
                $model  = new Qss_Model_Import_Form('M602', false, false);
                foreach($revertStock as $item)
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
                    $this->setMessage($model->getErrorRows());
                }
            }


            //re-calculate $trans
            $this->updateCostTable($stock, 1);
//            foreach ($stock as $item)
//            {
//            	$newdate = Qss_Lib_Date::i_fMysql2Time($item->NgayGiaoDich);
//				$month = date('m',$newdate);
//				$year = date('Y',$newdate);
//				$model = new Qss_Model_Inventory_Cost();
//				$model->update($item->MaSanPham, $month, $year,$item->Kho);
//            }
        }
    }
    /**
     * Lấy dữ liệu revert của nhập kho
     * @param type $stock Giá trị trả về của hàm  $warehouse->getCurrentStockByInputLines($ifid);
     * @param type $stockStatus Giá trị trả về của hàm $warehouse->getStockStatusOfWarehouseFormStockStatusOfInput($ifid);
     * @return type
     */
    private function getRevertWarehouseByInputData($stock, $stockStatus)
    {
        $revert = array();
        $i      = 0;

        foreach($stock as $item)
        {
            $code = @(int)$item->IFID_M402.'_'. @(int)$item->Ref_MaSanPham.'_'. @(int)$item->Ref_DonViTinh.'_'. @(int)$item->Ref_ThuocTinh;

            // @todo: Sau can them ca thuoc tinh vao phan revert
            $revert[$code]['OKho'][0]['Kho']       = $item->Kho;
            $revert[$code]['OKho'][0]['MaSP']      = $item->MaSanPham;
            $revert[$code]['OKho'][0]['DonViTinh'] = $item->DonViTinh;
            $revert[$code]['OKho'][0]['ThuocTinh'] = @$item->ThuocTinh;
            $revert[$code]['OKho'][0]['SoLuongHC'] = @(double)$item->SoLuongHC - @(double)$item->SoLuong;

            if($item->WIOID)
            {
                $revert[$code]['OKho'][0]['ioid'] = $item->WIOID;
            }

            $revert[$code]['OKho'][0]['ifid'] = $item->WIFID;
        }      

        foreach($stockStatus as $item)
        {
            $code = @(int)$item->IFID_M402.'_'. @(int)$item->Ref_MaSanPham.'_'. @(int)$item->Ref_DonViTinh.'_'. @(int)$item->Ref_MaThuocTinh;

            if (isset($revert[$code]))
            {

                $revert[$code]['OThuocTinhChiTiet'][$i]['Kho'] = $item->Kho;
                $revert[$code]['OThuocTinhChiTiet'][$i]['Bin'] = $item->Bin;
                $revert[$code]['OThuocTinhChiTiet'][$i]['MaSanPham'] = $item->MaSanPham;
                $revert[$code]['OThuocTinhChiTiet'][$i]['MaThuocTinh'] = @$item->MaThuocTinh;
                $revert[$code]['OThuocTinhChiTiet'][$i]['SoLo'] = $item->SoLo;
                $revert[$code]['OThuocTinhChiTiet'][$i]['SoSerial'] = $item->SoSerial;
                $revert[$code]['OThuocTinhChiTiet'][$i]['DonViTinh'] = $item->DonViTinh;
                $revert[$code]['OThuocTinhChiTiet'][$i]['SoLuong'] = @(double)$item->SoLuongKho - @(double)$item->SoLuong;

                if ($item->WIOID)
                {
                    $revert[$code]['OThuocTinhChiTiet'][$i]['ioid'] = $item->WIOID;
                }
                $i++;
            }
        }
        return $revert;
    } 
    
 

    /**
     * Chuyển số lượng và trạng thái lưu trữ công lại số lượng xuất, xóa đi giao dịch kho đã tạo
     * @param type $ifid
     */
    private function revertOutput()
    {
        $ifid        = $this->_form->i_IFID;
        $common      = new Qss_Model_Extra_Extra();
        $warehouse   = new Qss_Model_Extra_Warehouse();
        // $iForm       = $common->getTableFetchOne('qsiforms', array('IFID'=>$ifid));

        if($this->_form->i_Status == 2)
        {
            $line        = $common->getTableFetchOne('OXuatKho', array('IFID_M506'=>$ifid));
            //$detail      = $common->getTableFetchAll('ODanhSachXuatKho', array('IFID_M506'=>$ifid));
            $stockStatus = $warehouse->getStockStatusOfWarehouseFormStockStatusOfOutput($ifid);
            $stock       = $warehouse->getCurrentStockByOutputLines($ifid);
            
            $revertStock = $this->getRevertWarehouseByOutputData($stock, $stockStatus);


//            if(count($revertStock))
//            {
//                foreach($revertStock as $item)
//                {
//                    $ifid =(isset($item['OKho'][0]['ifid']) && $item['OKho'][0]['ifid']) ? $item['OKho'][0]['ifid']: 0;
//                    $this->update(self::STOCK_MODULE_CODE, $ifid, $item);
//                }
//            }


            if(count($revertStock))
            {
                $model  = new Qss_Model_Import_Form('M602', false, false);
                foreach($revertStock as $item)
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
                    $this->setMessage($model->getErrorRows());
                }
            }

            //update cost
            $this->updateCostTable($stock, 0);
//            foreach ($stock as $item)
//            {
//            	$newdate = Qss_Lib_Date::i_fMysql2Time($item->NgayGiaoDich);
//				$month = date('m',$newdate);
//				$year = date('Y',$newdate);
//				$model = new Qss_Model_Inventory_Cost();
//				$model->update($item->MaSP, $month, $year,$item->Kho);
//            }
        }            
    }
      


    /**
     * Lấy dữ liệu revert của nhập kho
     * @param type $stock Giá trị trả về của hàm  $warehouse->getCurrentStockByOutputLines($ifid);
     * @param type $stockStatus Giá trị trả về của hàm $warehouse->getStockStatusOfWarehouseFormStockStatusOfOutput($ifid);
     * @return type
     */
    private function getRevertWarehouseByOutputData($stock, $stockStatus)
    {
        $revert = array();
        $i      = 0;

        foreach($stock as $item)
        {
            $code = @(int)$item->IFID_M506.'_'. @(int)$item->Ref_MaSP.'_'. @(int)$item->Ref_DonViTinh.'_'. @(int)$item->Ref_ThuocTinh;

            // @todo: Sau can them ca thuoc tinh vao phan revert
            $revert[$code]['OKho'][0]['Kho']       = $item->Kho;
            $revert[$code]['OKho'][0]['MaSP']      = $item->MaSP;
            $revert[$code]['OKho'][0]['DonViTinh'] = $item->DonViTinh;
            $revert[$code]['OKho'][0]['ThuocTinh'] = @$item->ThuocTinh;
            $revert[$code]['OKho'][0]['SoLuongHC'] = @(double)$item->SoLuongHC + @(double)$item->SoLuong;

            if($item->WIOID)
            {
                $revert[$code]['OKho'][0]['ioid'] = $item->WIOID;
            }

            $revert[$code]['OKho'][0]['ifid'] = $item->WIFID;
        }      

        foreach($stockStatus as $item)
        {
            $code = @(int)$item->IFID_M506 . '_' . @(int)$item->Ref_MaSanPham . '_' . @(int)$item->Ref_DonViTinh . '_' . @(int)$item->Ref_MaThuocTinh;

            if(isset($revert[$code]))
            {
                $revert[$code]['OThuocTinhChiTiet'][$i]['Kho']         = $item->Kho;
                $revert[$code]['OThuocTinhChiTiet'][$i]['Bin']         = $item->Bin;
                $revert[$code]['OThuocTinhChiTiet'][$i]['MaSanPham']   = $item->MaSanPham;
                $revert[$code]['OThuocTinhChiTiet'][$i]['MaThuocTinh'] = @$item->MaThuocTinh;
                $revert[$code]['OThuocTinhChiTiet'][$i]['SoLo']        = $item->SoLo;
                $revert[$code]['OThuocTinhChiTiet'][$i]['SoSerial']    = $item->SoSerial;
                $revert[$code]['OThuocTinhChiTiet'][$i]['DonViTinh']   = $item->DonViTinh;
                $revert[$code]['OThuocTinhChiTiet'][$i]['SoLuong']     = @(double)$item->SoLuongKho + @(double)$item->SoLuong;

                if($item->WIOID)
                {
                    $revert[$code]['OThuocTinhChiTiet'][$i]['ioid'] = $item->WIOID;
                }
                $i++;
            }
        }
        return $revert;
    }


    private function updateCostTable($stock, $type)
    {
        foreach ($stock as $item)
        {
            $newdate = Qss_Lib_Date::i_fMysql2Time($item->NgayGiaoDich);
            $month   = date('m',$newdate);
            $year    = date('Y',$newdate);
            $model   = new Qss_Model_Inventory_Cost();
         	$j = $month;
            for ($i = $year; $i <= date('Y');$i++)
            {
            	while ($j < date('m') || $i < date('Y'))
                {
                	if($type == 1)
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
            //$model->update($item->MaSP, $month, $year,$item->Kho);
        }
    }

    private function update($module, $ifid, $data)
    {
        if(!$this->isError() && is_array($data) && count($data))
        {
//            $service = $this->services->Form->Manual($module, $ifid, $data, false);
//            if ($service->isError())
//            {
//                $this->setError();
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
                $this->setMessage($model->getErrorRows());
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