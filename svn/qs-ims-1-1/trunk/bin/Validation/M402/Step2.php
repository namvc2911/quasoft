<?php
class Qss_Bin_Validation_M402_Step2 extends Qss_Lib_Warehouse_WValidation
{
	/**
	 * - Kiểm tra số lượng dòng đơn hàng.
	 * - Kiểm tra ngày nhận hàng.
	 * - So sánh danh sách nhận hàng với trạng thái lưu trữ.
	 * - Kiểm tra có thuộc tính bắt buộc.
	 * - Kiểm tra sức chứa của kho nhận hàng.
	 */
	public function onNext()
	{
		parent::init();
        $this->checkWorkOrderStatusAvailable();
	}

	/**
	 * - Cập nhật kho
	 * - Cập nhật giao dịch kho
	 */
	public function next()
	{
		parent::init();



		if(!$this->isError())
		{
			$update = $this->services->Extra->Inventory->Update($this->_params->IFID_M402, 1);

			if($update->isError())
			{
				$this->setMessage($update->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				$this->setError();
			}
		}

		// $this->compareItemsWithOutput();

        // $this->compareItemsWithWorkOrder();
	}

    public function move()
    {
        parent::init();



        if(!$this->isError())
        {
            $update = $this->services->Extra->Inventory->Revert($this->_form, 1);

            if($update->isError())
            {
                $this->setMessage($update->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                $this->setError();
            }
        }

        // $this->compareItemsWithOutput();

        // $this->compareItemsWithWorkOrder();
    }

	private function compareItemsWithOutput()
	{
		if(Qss_Lib_System::fieldActive('ONhapKho', 'PhieuXuatKho'))
		{
			if($this->_params->Ref_PhieuXuatKho)
			{
				$mInventory  = new Qss_Model_Inventory_Inventory();
				$outputLines = $mInventory->getOutputLineByOuputIOID($this->_params->Ref_PhieuXuatKho);
				$inputLines  = $this->_params->ODanhSachNhapKho;
				$aOutput     = array();
				$aInput      = array();
				$msg         = array();

				foreach($inputLines as $item)
				{
					$code = $item->Ref_MaSanPham . '-' . (int)$item->Ref_ThuocTinh . '-' . $item->Ref_DonViTinh ;

					$aInput[$code]['Code'] = $item->MaSanPham;
					$aInput[$code]['Name'] = $item->TenSanPham;
					$aInput[$code]['UOM']  = $item->DonViTinh;
					$aInput[$code]['Attr'] = $item->ThuocTinh;
					$aInput[$code]['Qty']  = $item->SoLuong;
				}

				foreach($outputLines as $item)
				{
					$code = $item->Ref_MaSP . '-' . (int)$item->Ref_ThuocTinh . '-' . $item->Ref_DonViTinh ;

					$aOutput[$code]['Code'] = $item->MaSP;
					$aOutput[$code]['Name'] = $item->TenSP;
					$aOutput[$code]['UOM']  = $item->DonViTinh;
					$aOutput[$code]['Attr'] = $item->ThuocTinh;
					$aOutput[$code]['Qty']  = $item->SoLuong;
				}

				// So sanh nhap kho voi xuat kho
				foreach($aInput as $key=>$item)
				{
					// Giong nhau so sanh so luong
					if(isset($aOutput[$key]))
					{
						if($item['Qty'] != $aOutput[$key]['Qty'])
						{
							$msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) trong nhập kho khác số lượng với phiếu xuất kho.";
						}
					}
					// khong ton tai bao loi
					else
					{
						$msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) không có trong phiếu xuất kho.";
					}
				}

				// So sanh xuat kho voi nhap kho
				foreach($aOutput as $key=>$item)
				{
					// khong ton tai bao loi
					if(!isset($aInput[$key]))
					{
						$msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) trong xuất kho không có trong phiếu nhập kho.";
					}
				}

				if(count($msg))
				{
					$this->setError();
				}

				foreach($msg as $item)
				{
					$this->setMessage($item);
				}
			}
		}
	}

	private function compareItemsWithWorkOrder()
	{
		if(Qss_Lib_System::fieldActive('ONhapKho', 'PhieuXuatKho'))
		{
			if($this->_params->Ref_PhieuXuatKho)
			{
				$mInventory = new Qss_Model_Inventory_Inventory();
				$output     = $mInventory->getOutputByIOID($this->_params->Ref_PhieuXuatKho);

				if(Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri') && $output && $output->Ref_PhieuBaoTri)
				{
					$mWorkorder = new Qss_Model_Maintenance_Workorder();
					$items      = $this->_params->ODanhSachNhapKho;
					$materials  = $mWorkorder->getMaterials(false, $output->Ref_PhieuBaoTri);
					$aItems     = array();
					$aMaterials = array();
					$msg        = array();

					foreach($items as $item)
					{
						$code = $item->Ref_MaSanPham . '-' . (int)$item->Ref_ThuocTinh . '-' . $item->Ref_DonViTinh ;

						$aItems[$code]['Code'] = $item->MaSanPham;
						$aItems[$code]['Name'] = $item->TenSanPham;
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
								$msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) trong nhập kho khác số lượng với vật tư phiếu bảo trì.";
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
							$msg[$key] = "Mặt hàng {$item['Code']} ({$item['UOM']}) trong vật tư phiếu bảo trì không có trong phiếu nhập kho.";
						}
					}

					foreach($msg as $item)
					{
						$this->setMessage($item);
					}
				}
			}
		}
	}

	private function checkWorkOrderStatusAvailable()
	{
		if(Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri'))
		{
			if($this->_params->Ref_PhieuXuatKho)
			{
				$mInventory = new Qss_Model_Inventory_Inventory();
				$output     = $mInventory->getOutputByIOID($this->_params->Ref_PhieuXuatKho);

				if(Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri') && $output && $output->Ref_PhieuBaoTri)
				{
					$mWorkorder = new Qss_Model_Maintenance_Workorder();
					$order      = $mWorkorder->getOrderByIOID($output->Ref_PhieuBaoTri);

					if($order)
					{
						if(!in_array($order->Status, array(2,3)))
						{
							$this->setError();
							$this->setMessage('Phiếu bảo trì chưa ban ban hành hoặc đã hủy!');
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
	}
        
}
