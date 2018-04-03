<?php
class Qss_Service_Static_M043_CreateOT extends Qss_Lib_Service {
    function __doExecute($params) {
        $insert                                             = array();
        $insert['ODangKyLamThem'][0]['MaNhanVien']          = $params['ODangKyLamThem_MaNhanVien'];
        $insert['ODangKyLamThem'][0]['TenNhanVien']         = $params['ODangKyLamThem_TenNhanVien'];
        $insert['ODangKyLamThem'][0]['LoaiTangCa']          = $params['ODangKyLamThem_LoaiTangCa'];
        $insert['ODangKyLamThem'][0]['GioDangKy']           = $params['ODangKyLamThem_GioDangKy'];
        $insert['ODangKyLamThem'][0]['NgayDangKy']          = $params['ODangKyLamThem_NgayDangKy'];
        $insert['ODangKyLamThem'][0]['CaLamViec']           = $params['ODangKyLamThem_CaLamViec'];
        $insert['ODangKyLamThem'][0]['ThoiGianBatDau']      = $params['ODangKyLamThem_ThoiGianBatDau'];
        $insert['ODangKyLamThem'][0]['ThoiGianKetThuc']     = $params['ODangKyLamThem_ThoiGianKetThuc'];
        $insert['ODangKyLamThem'][0]['PhuongThucThanhToan'] = $params['ODangKyLamThem_PhuongThucThanhToan'];
        $insert['ODangKyLamThem'][0]['LyDoTangCa']          = $params['ODangKyLamThem_LyDoTangCa'];

        $service = $this->services->Form->Manual('M078', 0, $insert, false);

        if($service->isError()) {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
    }
}