<?php
class Qss_Bin_Validation_M751_Step2 extends Qss_Lib_WValidation
{
	public function onNext()
	{
		parent::init();
        $this->_checkEmpty();
        // $this->_checkEquipsReady();
	}

    /**
     * Yeu cau danh sach dieu dong phai co it n
     */
    public function _checkEmpty()
    {
        $danhSach = $this->_params->OYeuCauTrangThietBi;
        if(!count($danhSach))
        {
            $this->setError();
            $this->setMessage('Danh sách điều động phải có ít nhất một bản ghi.');
        }
    }

    // @todo: Kiem tra co du so luong
    public function _checkToolsReady()
    {
        $reqMovModel  = new Qss_Model_Maintenance_Equip_Requiremove();
    }

    /**
     * Kiểm tra loại thiết bị trên từng dòng có đủ số lượng điều động hay không?
     */
    public function _checkEquipsReady()
    {
        if(count($this->_params->OYeuCauTrangThietBi))
        {
            $reqMovModel  = new Qss_Model_Maintenance_Equip_Requiremove();
            $totalEquip   = $reqMovModel->getTotalEquipByReqEquipLine($this->_params->IFID_M751);
            $otherLine    = $reqMovModel->getTotalEquipTypeRequireInOtherLine($this->_params->IFID_M751);
            $otherRequire = $reqMovModel->getTotalEquipTypeRequireInOtherRequire($this->_params->IFID_M751);
            $retval       = array();
            $errLine      = array();

            /// Lấy tổng số lượng thiết bị theo loại thiêt bị trên từng dòng yêu cầu
            foreach($totalEquip as $item)
            {
                $retval[$item->IOID]['ID']         = $item->Ref_LoaiThietBi;
                $retval[$item->IOID]['Name']       = $item->LoaiThietBi;
                $retval[$item->IOID]['Req']        = $item->SoLuong;
                $retval[$item->IOID]['CurrentQty'] = $item->TongSoThietBiTheoDong;
            }

            // Tổng số lượng cùng loại thiết bị trên dòng khác với điều kiện thời gian của hai dòng giao nhau
            foreach($otherLine as $item)
            {
                if(!isset($retval[$item->IOID]))
                {
                    $retval[$item->IOID]['ID']         = $item->Ref_LoaiThietBi;
                    $retval[$item->IOID]['Name']       = $item->LoaiThietBi;
                    $retval[$item->IOID]['Req']        = $item->SoLuongTrenDongDangXet;
                    $retval[$item->IOID]['CurrentQty'] = 0 ;
                    $retval[$item->IOID]['TotalReq']   = $item->SoLuongTrenDongDangXet + $item->TongSoTrenDongKhac;
                }
                else
                {
                    $retval[$item->IOID]['TotalReq'] = $item->SoLuongTrenDongDangXet + $item->TongSoTrenDongKhac;
                }

            }

            // Tổng số lượng loại thiết bị (Ở đây cộng thêm số lượng cùng loại thiết bị trên yêu cầu khác) với điều kiện
            // thời gian giao nhau.
            foreach($otherRequire as $item)
            {
                if(!isset($retval[$item->IOID]))
                {
                    $retval[$item->IOID]['ID']         = $item->Ref_LoaiThietBi;
                    $retval[$item->IOID]['Name']       = $item->LoaiThietBi;
                    $retval[$item->IOID]['Req']        = $item->SoLuongTrenDongDangXet;
                    $retval[$item->IOID]['CurrentQty'] = 0 ;
                    $retval[$item->IOID]['TotalReq']   = $item->SoLuongTrenDongDangXet + $item->TongSoTrenDongKhac;
                }
                else
                {
                    $retval[$item->IOID]['TotalReq'] += $item->TongSoTrenDongKhac;
                }
            }

            // Cập nhật lỗi không đủ số lượng thiết bị yêu cầu vào mọt mảng
            foreach($retval as $item)
            {
                if($item['TotalReq'] > $item['CurrentQty'])
                {
                    $errLine[] = '- Loại thiết bị '
                        . $item['Name']
                        .' không đủ số lượng điều động! Tổng số yêu cầu: '
                        . $item['TotalReq']
                        .' (Bao gồm phiếu yêu cầu khác)/ Số lượng thiết bị hiện có: '
                        .$item['CurrentQty'];
                }
            }

            // In lỗi không đủ số lượng thiết bị yêu cầu
            if(count($errLine))
            {
                $this->setError();
                foreach($errLine as $item)
                {
                    $this->setMessage($item);
                }
            }
        }
    }
}