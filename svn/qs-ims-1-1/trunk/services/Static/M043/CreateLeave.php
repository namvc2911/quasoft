<?php
class Qss_Service_Static_M043_CreateLeave extends Qss_Lib_Service {
    function __doExecute($params) {
        $insert                                      = array();
        $insert['ODangKyNghi'][0]['MaNhanVien']      = $params['ODangKyNghi_MaNhanVien'];
        $insert['ODangKyNghi'][0]['TenNhanVien']     = $params['ODangKyNghi_TenNhanVien'];
        $insert['ODangKyNghi'][0]['LoaiNgayNghi']    = $params['ODangKyNghi_LoaiNgayNghi'];
        $insert['ODangKyNghi'][0]['NgayBatDau']      = $params['ODangKyNghi_NgayBatDau'];
        $insert['ODangKyNghi'][0]['NgayKetThuc']     = $params['ODangKyNghi_NgayKetThuc'];
        $insert['ODangKyNghi'][0]['BatDauNghi']      = $params['ODangKyNghi_BatDauNghi'];
        $insert['ODangKyNghi'][0]['DiLamLai']        = $params['ODangKyNghi_DiLamLai'];
        $insert['ODangKyNghi'][0]['SoGioNghi']    = $params['ODangKyNghi_SoGioNghi'];
        $insert['ODangKyNghi'][0]['EMail']    = 1;
        $insert['ODangKyNghi'][0]['LyDo']            = $params['ODangKyNghi_LyDo'];

        $service = $this->services->Form->Manual('M077', 0, $insert, false);

        if($service->isError()) {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
    }
}