<?php
/**
 * Description of OBangThuocTinh
 *
 * @author Thinh
 */
class Qss_Bin_Trigger_OVatTuPBT extends Qss_Lib_Trigger
{
    /**
     * 
     */
    public function onInserted($object)
    {

    }
    
    /**
     * 
     */
    public function onUpdated($object)
    {

    }

    public function onUpdate($object)
    {
        parent::init();
        $this->dateNotify($object);
        $this->validateUpdateStructure($object);
    }

    public function onInsert($object)
    {
        parent::init();
        $this->dateNotify($object);
        $this->validateUpdateStructure($object);
    }

    private function validateUpdateStructure(Qss_Model_Object $object)
    {
    	//@todo khi đóng phiếu thì chuyển nếu là thay thế và lắp mới update sang bên phụ tùng thay thế
        $item              = new stdClass();
        $item->HinhThuc    = (int)$object->getFieldByCode('HinhThuc')->getValue();
        $item->Ref_ViTri   = (int)$object->getFieldByCode('ViTri')->intRefIOID;
        $item->ViTri       = $object->getFieldByCode('ViTri')->getValue();
        $item->Ref_MaVatTu = (int)$object->getFieldByCode('MaVatTu')->intRefIOID;
        $item->SoLuong     = $object->getFieldByCode('SoLuong')->getValue();
        $hinhThuc          = (int)$item->HinhThuc;

        if($this->_params->Ref_MaThietBi)
        {
            $sql = sprintf('
                select OCauTrucThietBi.*
                from ODanhSachThietBi
                inner join OCauTrucThietBi on ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                where OCauTrucThietBi.IOID = %1$d',
                $item->Ref_ViTri);
            $dataSQL   = $this->_db->fetchOne($sql);

            if($hinhThuc != 0)
            {
                // Bao loi voi thay the
                if($dataSQL)
                {
                    $dataSQL->SoLuongHC = $dataSQL->SoLuongHC?$dataSQL->SoLuongHC:0;

                    if($hinhThuc == 1 || $hinhThuc == 2 || $hinhThuc == 3)//Phải kiểm tra khi thay thế
                    {
                        if((int)$dataSQL->Ref_MaSP == 0)
                        {
                            $this->setError();
                            $this->setMessage('Vị trí '.$item->ViTri.' chưa được cài đặt mặt hàng trước đó!');
                        }
                        else
                        {
                            if((int)$dataSQL->Ref_MaSP == (int)$item->Ref_MaVatTu)
                            {
                                if($item->SoLuong > $dataSQL->SoLuongHC)
                                {
                                    $this->setError();
                                    $this->setMessage('Số lượng thay thế tại vị trí '.$item->ViTri.' khi thay cùng mặt hàng phải nhỏ hơn hoặc bằng số lượng hiện có! (Số lượng hiện có: '. $dataSQL->SoLuongHC. ' '. $dataSQL->DonViTinh .')');
                                }
                            }
                            else
                            {
                                if($item->SoLuong != $dataSQL->SoLuongHC)
                                {
                                    $this->setError();
                                    $this->setMessage('Số lượng thay thế tại vị trí '.$item->ViTri.' khi thay khác mặt hàng phải bằng với số lượng hiện có! (Số lượng hiện có: '. $dataSQL->SoLuongHC. ' '. $dataSQL->DonViTinh. ')');
                                }
                            }
                        }
                    }
                    elseif($hinhThuc == 4) // Bao loi voi lap moi
                    {
                        if((int)$dataSQL->Ref_MaSP && (int)$dataSQL->Ref_MaSP != (int)$item->Ref_MaVatTu)
                        {
                            $this->setError();
                            $this->setMessage('Không hỗ trợ nhiều mã vật tư cùng một vị trí, xem vị trí '.$item->ViTri.'!');
                        }
                    }
                    elseif($hinhThuc == 5)//Tháo ra thì số lượng phai ít hơn
                    {
                    	if($item->SoLuong > $dataSQL->SoLuongHC)
                        {
							$this->setError();
							$this->setMessage('Số lượng tháo tại vị trí '.$item->ViTri.' phải nhỏ hơn hoặc bằng với số lượng hiện có! (Số lượng hiện có: '. $dataSQL->SoLuongHC. ' '. $dataSQL->DonViTinh. ')');
						}
                    }
                }
                else
                {
                    $this->setError();
                    $this->setMessage('Vị trí '.$item->ViTri.' không tồn tại!');
                }
            }

        }

    }

    private function dateNotify(Qss_Model_Object $object)
    {
        $mOrder       = new Qss_Model_Maintenance_Workorder();
        $mEquip       = new Qss_Model_Maintenance_Equip_List();
        $order        = $mOrder->getOrderByIFID($this->_form->i_IFID);
        $refEquip     = $order?$order->Ref_MaThietBi:0;
        $refItem      = (int)$object->getFieldByCode('MaVatTu')->intRefIOID;
        $refPosition  = (int)$object->getFieldByCode('ViTri')->intRefIOID;
        $deliveryDate = $object->getFieldByCode('Ngay')->getValue();
        $deliveryDate = $deliveryDate?$deliveryDate:($order?$order->Ngay:date('Y-m-d'));



        // Thực hiện kiểm tra canh bao khi co mã thiết bị
        if($refEquip)
        {
            $info = $mEquip->getSparepartInfo($refEquip, $refItem, $refPosition);

            // Neu co phu tung nay thi moi thuc hien kiem tra canh bao
            if($info && @(double)$info->SoNgayCanhBao)
            {
                // Tinh toan thoi gian xuat phu tung lan gan day nhat
                $last = $mOrder->getLastestOrderByMaterial($refEquip, $refItem, $refPosition);

				// Neu co ngay xuat kho truoc do moi tien hanh kiem tra tiep
				if($last && @(string)$last->Ngay && @(string)$last->Ngay != '0000-00-00')
                {
                    $deliveryDate = (!$deliveryDate || $deliveryDate=='0000-00-00')?date('Y-m-d'):$deliveryDate;
                    $divDate      = Qss_Lib_Date::divDate($last->Ngay, $deliveryDate);

                    if($divDate <= $info->SoNgayCanhBao)
                    {
                        $this->setMessage('Phụ tùng này mới được xuất cho thiết bị '.$divDate.' ngày trước trong '
                            . ' phiếu bảo trì <a href="#1" onclick="openModule(\'M759\',\'/user/form/edit?ifid='.$last->IFID_M759
                            .'&deptid=1\')">'.$last->SoChungTu.'</a>!');
                        //$this->setError();
                    }
                }
			}
        }

    }
}
