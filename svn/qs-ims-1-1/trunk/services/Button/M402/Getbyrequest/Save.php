<?php
class Qss_Service_Button_M402_Getbyrequest_Save extends Qss_Service_Abstract {
    public function __doExecute($params) {
        // Kiểm tra tính đúng đắn của dữ liệu truyền vào
        $this->_validateConvert($params);

        if(!$this->isError()) {
            $import         = new Qss_Model_Import_Form('M402',false, false);
            $common         = new Qss_Model_Extra_Extra();
            $mMaterial      = new Qss_Model_M751_Material();
            $IFID_M506      = $params['M402_Get_By_Request_IFID'];
            $OXuatKho       = $common->getTableFetchOne('ONhapKho', array('IFID_M402'=>$IFID_M506));
            $OYeuCauVatTu   = $mMaterial->getMaterialsByIOIDs($params['M402_Get_By_Request_IOID']);
            $arrYeuCauVatTu = array();
            $insert         = array();
            $i              = 0;

            // Lấy thông tin của yêu cầu vật tư theo mảng với key là IOID của yêu cầu.
            foreach ($OYeuCauVatTu as $item) {
                $arrYeuCauVatTu[$item->IOID] = $item;
            }

            // echo '<pre>'; print_r($arrYeuCauVatTu); die;

            // Tạo mảng insert
            if($OXuatKho && count($params['M402_Get_By_Request_IOID'])) {
                $insert['ONhapKho'][0]['SoChungTu'] = (string)$OXuatKho->SoChungTu;

                foreach ($params['M402_Get_By_Request_IOID'] as $ioid) {
                    $dat = isset($arrYeuCauVatTu[$ioid])?$arrYeuCauVatTu[$ioid]:array();
                    if(count($dat)) {
                        $insert['ODanhSachNhapKho'][$i]['MaSanPham']  = $dat->MaVatTu;
                        $insert['ODanhSachNhapKho'][$i]['TenSanPham'] = $dat->TenVatTu;
                        $insert['ODanhSachNhapKho'][$i]['DonViTinh']  = (int)$dat->Ref_DonViTinh;
                        $insert['ODanhSachNhapKho'][$i]['SoLuong']    = $params['M402_Get_By_Request_SoLuongNhapKho'][$i];
                    }

                    $i++;
                }

                // echo '<pre>'; print_r($insert); die;
                $import->setData($insert);
                $import->generateSQL();

                // Khi tiến hành cập nhật nếu xảy ra lỗi ở hàm generate thì thông báo cập nhật không thành công
                // Ngược lại sẽ tiến hành update lại thông tin tài sản trong thiết bị, thông báo thành công
                if($import->countFormError()) {
                    $this->setError();
                    $this->setMessage('Cập nhật không thành công!');
                }
                else {
                    // Không có lỗi gì thì update vào danh sách thiết bị
                    $this->setMessage('Cập nhật thành công!');
                }
            }


        }
    }

    /**
     * 1. Kiểm tra xem có dòng nào để insert?
     * 2. Kiểm tra xem mã, tên, loại đã được điền đầy đủ hay chưa?
     * 3. Kiểm tra xem mã tài sản đã tồn tại hay chưa?
     * @param $params
     */
    private function _validateConvert($params) {
        if(!isset($params['M402_Get_By_Request_IOID']) || !count($params['M402_Get_By_Request_IOID'])) {
            $this->setMessage('Cần ít nhất một dòng để lưu danh sách nhập kho.');
            $this->setError();
        }
    }
}
