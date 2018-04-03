<?php
class Qss_Bin_Validation_M506_Step2 extends Qss_Lib_WValidation
{
	/**
	 * onNext()
	 * - Kiểm tra phiếu bảo trì ở tình trạng ban hành
	 */
	public function onNext()
	{
		parent::init();
		$this->checkWorkOrderStatusAvailable();
	}

	/**
	 * next()
	 * - Cập nhật xuất kho vào kho và giao dịch kho
	 */
	public function next()
	{
		parent::init();
		if(!$this->isError())
		{
			$update = $this->services->Extra->Inventory->Update($this->_params->IFID_M506, 0);

			if($update->isError())
			{
				$this->setMessage($update->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				$this->setError();
			}
		}

        // So sanh mat hang voi vat tu phieu bao tri
        // $this->compareItemsWithWorkOrder();

        // Tao phieu nhap hang voi truong hop xuat chuyen kho
        $this->createInput();
	}

    public function move()
    {
        parent::init();
        if(!$this->isError())
        {
            $update = $this->services->Extra->Inventory->Revert($this->_form, 0);

            if($update->isError())
            {
                $this->setMessage($update->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                $this->setError();
            }
        }

        // So sanh mat hang voi vat tu phieu bao tri
        // $this->compareItemsWithWorkOrder();

        // Tao phieu nhap hang voi truong hop xuat chuyen kho
        $this->createInput();
    }

    private function compareItemsWithWorkOrder()
    {
        if(!$this->isError() && Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri'))
        {
            if($this->_params->Ref_PhieuBaoTri)
            {
                $mWorkorder = new Qss_Model_Maintenance_Workorder();
                $items      = $this->_params->ODanhSachXuatKho;
                $materials  = $mWorkorder->getMaterials(false, $this->_params->Ref_PhieuBaoTri);
                $aItems     = array();
                $aMaterials = array();
                $msg        = array();

                foreach($items as $item)
                {
                    $code = $item->Ref_MaSP . '-' . (int)$item->Ref_ThuocTinh . '-' . $item->Ref_DonViTinh ;

                    $aItems[$code]['Code'] = $item->MaSP;
                    $aItems[$code]['Name'] = $item->TenSP;
                    $aItems[$code]['UOM']  = $item->DonViTinh;
                    $aItems[$code]['Attr'] = $item->ThuocTinh;
                    $aItems[$code]['Qty']  = $item->SoLuong;
                }

                foreach($materials as $item)
                {
                    $code = $item->Ref_MaVatTu . '-' . (int)$item->Ref_ThuocTinh . '-' . $item->Ref_DonViTinh ;

                    $aMaterials[$code]['Code'] = $item->MaVatTu;
                    $aMaterials[$code]['Name'] = $item->TenVatTu;
                    $aMaterials[$code]['UOM']  = $item->DonViTinh;
                    $aMaterials[$code]['Attr'] = $item->ThuocTinh;
                    $aMaterials[$code]['Qty']  = $item->SoLuong;
                }

                // So sanh xuat kho voi phieu bao tri
                foreach($aItems as $key=>$item)
                {
                    // Giong nhau so sanh so luong
                    if(isset($aMaterials[$key]))
                    {
                        if($item['Qty'] != $aMaterials[$key]['Qty'])
                        {
                            $msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) trong xuất kho khác số lượng với vật tư phiếu bảo trì.";
                        }
                    }
                    // khong ton tai bao loi
                    else
                    {
                        $msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) không có trong vật tư phiếu bảo trì.";
                    }
                }

                // So sanh phieu bao tri voi xuat kho
                foreach($aMaterials as $key=>$item)
                {
                    // khong ton tai bao loi
                    if(!isset($aItems[$key]))
                    {
                        $msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) trong vật tư phiếu bảo trì không có trong phiếu xuất kho.";
                    }
                }

                foreach($msg as $item)
                {
                    $this->setMessage($item);
                }
            }
        }
    }

    private function checkWorkOrderStatusAvailable()
    {
        if(!$this->isError() && Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri'))
        {
            if($this->_params->Ref_PhieuBaoTri)
            {
                $mWorkorder = new Qss_Model_Maintenance_Workorder();
                $order      = $mWorkorder->getOrderByIOID($this->_params->Ref_PhieuBaoTri);

                if($order)
                {
                    if(!in_array($order->Status, array(2,3)))
                    {
                        $this->setError();
                        $this->setMessage('Phiếu bảo trì không thuộc tình trạng ban hành hoặc hoàn thành!');
                    }
                }
                else
                {
                    $this->setError();
                    $this->setMessage('Phiếu bảo trì không tồn tại!');
                }
            }
        }
    }


    /**
     * Tạo phiếu nhập kho với trường hợp xuất chuyển kho
     */
    private function createInput()
    {
        if(!$this->isError())
        {
            $mInventory = new Qss_Model_Inventory_Inventory();
            $insert     = array(); // Mảng dữ liệu cập nhật phiếu bảo trì
            $i          = 0; // Thứ tự đối tượng phụ mảng $insert
            $outputType = $mInventory->getOutputTypeByIOID(@(int)$this->_params->Ref_LoaiXuatKho);
            $outputType = $outputType?$outputType->Loai:'';
            $inputType  = $mInventory->getInputTypeByCode('CHUYENKHO');
            $inputType  = $inputType?$inputType->IOID:0;

            if($outputType == 'CHUYENKHO' && $this->_params->Ref_KhoChuyenDen && !$inputType)
            {
                $this->setError();
                $this->setMessage('Chưa định nghĩa loại nhập chuyển kho!');
            }

            if($outputType == 'CHUYENKHO' && $this->_params->Ref_KhoChuyenDen && $inputType != 0)
            {
                $insert['ONhapKho'][0]['Kho']            = @(int)$this->_params->Ref_KhoChuyenDen;
                $insert['ONhapKho'][0]['LoaiNhapKho']    = (int)$inputType;
                $insert['ONhapKho'][0]['NgayChungTu']    = $this->_params->NgayChuyenHang;
                $insert['ONhapKho'][0]['SoChungTu']      = 'COPY.FROM.'.$this->_params->SoChungTu.'.'.uniqid();
                $insert['ONhapKho'][0]['NgayChuyenHang'] = $this->_params->NgayChuyenHang;
                $insert['ONhapKho'][0]['PhieuXuatKho']   = (int)$this->_params->IOID;

                if(Qss_Lib_System::fieldActive('ONhapKho', 'SoYeuCau')
                && Qss_Lib_System::fieldActive('OXuatKho', 'SoYeuCau')) {
                    $insert['ONhapKho'][0]['SoYeuCau']   = $this->_params->SoYeuCau;
                }



                foreach($this->_params->ODanhSachXuatKho AS $item)
                {
                    $insert['ODanhSachNhapKho'][$i]['MaSanPham'] = (int)$item->Ref_MaSP;
                    $insert['ODanhSachNhapKho'][$i]['DonViTinh'] = (int)$item->Ref_DonViTinh;
                    $insert['ODanhSachNhapKho'][$i]['SoLuong']   = $item->SoLuong;
                    $insert['ODanhSachNhapKho'][$i]['DonGia']    = $item->DonGia;
                    $i++;
                }

                $service = $this->services->Form->Manual('M402', 0, $insert ,false);

                if($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }
        }
    }
}