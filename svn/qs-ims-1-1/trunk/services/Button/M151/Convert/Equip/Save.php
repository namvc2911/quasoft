<?php
class Qss_Service_Button_M151_Convert_Equip_Save extends Qss_Service_Abstract {
    public function __doExecute($params) {
        // Kiểm tra tính đúng đắn của dữ liệu truyền vào
        $this->_validateConvert($params);

        // Nếu không có lỗi xảy ra tiến hành update dữ liệu
        if(!$this->isError()) {
            $mAsset        = new Qss_Model_M151_Asset();
            $import        = new Qss_Model_Import_Form('M151',false, false);
            $i             = 0;
            $updateThietBi = array(); // Mảng update lại thông tin tài sản của thiết bị

            // Tạo 2 mảng insert tài sản và update lại thông tin thiết bị
            foreach($params['M151_Convert_Equip_IOID'] as $ioid) {
                // Mảng insert tài sản
                $insert                                    = array();
                $insert['ODanhMucCongCuDungCu'][0]['Ma']   = $params['M151_Convert_Equip_MaTaiSan'][$i];
                $insert['ODanhMucCongCuDungCu'][0]['Ten']  = $params['M151_Convert_Equip_TenTaiSan'][$i];
                $insert['ODanhMucCongCuDungCu'][0]['Loai'] = (int)$params['M151_Convert_Equip_LoaiTaiSan'][$i];
                $import->setData($insert);

                // Mảng update lại thông tin tài sản của thiết bị
                $updateThietBi[] = array('EquipIOID'=>$ioid, 'AssetCode'=>$params['M151_Convert_Equip_MaTaiSan'][$i]);
                $i++;
            }

            $import->generateSQL();

            // Khi tiến hành cập nhật nếu xảy ra lỗi ở hàm generate thì thông báo cập nhật không thành công
            // Ngược lại sẽ tiến hành update lại thông tin tài sản trong thiết bị, thông báo thành công
            if($import->countFormError()) {
                $this->setError();
                $this->setMessage('Cập nhật không thành công!');
            }
            else {
                // Không có lỗi gì thì update vào danh sách thiết bị
                $mAsset->updateTaiSanThietBi($updateThietBi);
                $this->setMessage('Cập nhật thành công!');
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
        if(isset($params['M151_Convert_Equip_IOID'])) {
            $i      = 0;
            $mAsset = new Qss_Model_M151_Asset();

            // Với trường hợp chưa gắn tài sản cần kiểm tra xem mã tài sản đã tồn tại hay chưa
            // Hoặc đã gắn mã tài sản mà chưa tạo tài sản cũng cần kiểm tra
            if(
                (isset($params['M151_Convert_Equip_NoAssetCode']) && $params['M151_Convert_Equip_NoAssetCode'])
                || (isset($params['M151_Convert_Equip_NotInAssets']) && $params['M151_Convert_Equip_NotInAssets'])
            ) {
                // Kiểm tra xem mã tài sản đã tồn tại hay chưa
                if(isset($params['M151_Convert_Equip_MaTaiSan']) && count($params['M151_Convert_Equip_MaTaiSan'])) {
                    $maTaiSanDaTonTai = $mAsset->getTaiSanTheoMangMa($params['M151_Convert_Equip_MaTaiSan']);

                    // Nếu có tài sản đã tồn tại thì in ra
                    if(count($maTaiSanDaTonTai)) {
                        foreach ($maTaiSanDaTonTai as $maDaTonTai) {
                            $this->setMessage('Mã tài sản "'.$maDaTonTai->Ma.'" đã tồn tại!');
                        }
                        $this->setError();
                    }
                }
            }

            // Với trường hợp chưa đã gắn mã tài sản mà chưa tạo tài sản cũng kiểm tra

            // Kiểm tra xem các thông tin như mã tài sản, tên tài sản, loại tài sản đã được điền đầy đủ hết chưa
            $thieuThongTinTaiSan = false;
            foreach($params['M151_Convert_Equip_IOID'] as $ioid) {
                if(
                    (!isset($params['M151_Convert_Equip_MaTaiSan'][$i]) || !$params['M151_Convert_Equip_MaTaiSan'][$i])
                    || (!isset($params['M151_Convert_Equip_TenTaiSan'][$i]) || !$params['M151_Convert_Equip_TenTaiSan'][$i])
                    || (!isset($params['M151_Convert_Equip_LoaiTaiSan'][$i]) || $params['M151_Convert_Equip_LoaiTaiSan'][$i] == 0)
                ) {
                    // $err = 1; // Có dòng điền thiếu
                    $thieuThongTinTaiSan = true;
                }
                $i++;
            }

            if($thieuThongTinTaiSan) {
                $this->setMessage('Thông tin tài sản chưa được điền đầy đủ.');
                $this->setError();
            }

        }
        else {
            // Nếu không có dòng thiết bị nào chứng tỏ chưa có dòng nào được chọn
            // $err = 2; // Chưa có dòng nào
            $this->setMessage('Cần ít nhất một thiết bị để chuyển thành tài sản.');
            $this->setError();
        }
    }
}
