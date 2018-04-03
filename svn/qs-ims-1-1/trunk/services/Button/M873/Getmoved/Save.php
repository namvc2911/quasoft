<?php
class Qss_Service_Button_M873_Getmoved_Save extends Qss_Service_Abstract {
    public function __doExecute($params) {
        // Kiểm tra tính đúng đắn của dữ liệu truyền vào
        $this->_validateConvert($params);

        if(!$this->isError()) {
            $import        = new Qss_Model_Import_Form('M873',false, false);
            $common         = new Qss_Model_Extra_Extra();
            $mM873          = new Qss_Model_M873_Moving();
            $IFID_M873      = $params['M873_Get_By_Request_IFID'];
            $ODieuDong      = $common->getTableFetchOne('ODieuDongThietBiVe', array('IFID_M873'=>$IFID_M873));


            $dbOYeuCauThietBi   = Qss_Model_Db::Table('ODanhSachThietBi');
            $dbOYeuCauThietBi->where(sprintf(' IOID IN (%1$s)', implode(',', $params['M873_Get_By_Request_IOID'])));
            $OYeuCauThietBi = $dbOYeuCauThietBi->fetchAll();
            $arrYeuCauThietBi = array();
            $insert         = array();
            $i              = 0;

            // Lấy thông tin của yêu cầu vật tư theo mảng với key là IOID của yêu cầu.
            foreach ($OYeuCauThietBi as $item) {
                $arrYeuCauThietBi[$item->IOID] = $item;
            }

            // Tạo mảng insert
            if($ODieuDong && count($params['M873_Get_By_Request_IOID'])) {
                $insert['ODieuDongThietBiVe'][0]['SoPhieu'] = (string)$ODieuDong->SoPhieu;

                foreach ($params['M873_Get_By_Request_IOID'] as $ioid) {
                    $dat = isset($arrYeuCauThietBi[$ioid])?$arrYeuCauThietBi[$ioid]:array();
                    if(count($dat)) {
                        $insert['OThietBiDieuDongVe'][$i]['MaThietBi']   = $dat->MaThietBi;
                        $insert['OThietBiDieuDongVe'][$i]['TenThietBi']  = $dat->TenThietBi;
                        $insert['OThietBiDieuDongVe'][$i]['LoaiThietBi'] = @$dat->LoaiThietBi;
                        $insert['OThietBiDieuDongVe'][$i]['DonViTinh']   = @(int)$dat->Ref_DonViTinh;
                        $insert['OThietBiDieuDongVe'][$i]['Serial']      = $dat->Serial;
                    }

                    $i++;
                }

                $import->setData($insert);
                $import->generateSQL();

                // Khi tiến hành cập nhật nếu xảy ra lỗi ở hàm generate thì thông báo cập nhật không thành công
                // Ngược lại sẽ tiến hành update lại thông tin tài sản trong thiết bị, thông báo thành công
                if($import->countFormError()) {
                    $this->setError();
                    $this->setMessage('Cập nhật không thành công!');
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
        if(!isset($params['M873_Get_By_Request_IOID']) || !count($params['M873_Get_By_Request_IOID'])) {
            $this->setMessage('Cần ít nhất một dòng để lưu danh sách thiết bị.');
            $this->setError();
        }
    }
}
