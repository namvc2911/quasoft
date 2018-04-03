<?php
class Qss_Bin_Onload_OPhepKiemTraPhieuKiemDinh extends Qss_Lib_Onload
{
    public function PhepKiemTra() {
        $arrFilterByIOID   = array();
        $arrFilterByIOID[] = 0;
        $SoHieuMau         = (int)$this->_object->getFieldByCode('SoHieuMau')->getRefIOID();
        $ifid              = (int)$this->_object->i_IFID;

        $sql  = sprintf('SELECT * FROM ODanhMucMauKiemDinh WHERE IFID_M870 = %1$d AND IOID = %2$d', $ifid, $SoHieuMau);
        $data = $this->_db->fetchOne($sql);

        // echo '<pre>'; print_r($sql); die;

        if($data) {
            // Lấy phép kiểm tra theo loại mẫu
            $sqlLoaiMau = sprintf('
                SELECT ODanhMucPhepKiemTra.* 
                FROM OChiTietKiemTraMau
                INNER JOIN OPhanLoaiMauKiemDinh ON OChiTietKiemTraMau.IFID_M868 = OPhanLoaiMauKiemDinh.IFID_M868
                INNER JOIN ODanhMucPhepKiemTra ON OChiTietKiemTraMau.Ref_PhepKiemTra = ODanhMucPhepKiemTra.IOID
                WHERE OPhanLoaiMauKiemDinh.IOID = %1$d                                
            ', @(int)$data->Ref_Ten);



            $datLoaiMau = $this->_db->fetchAll($sqlLoaiMau);

            foreach ($datLoaiMau as $item) {
                if($item->IFID_M867) {
                    $arrFilterByIOID[] = $item->IFID_M867;
                }
            }

            // echo '<pre>'; print_r($datLoaiMau);

            // Lấy phép kiểm tra theo phân hệ và khối
            $sqlPhanHeKhoi = sprintf('
                SELECT ODanhMucPhepKiemTra.* 
                FROM OChiTietKiemTraKhoi
                INNER JOIN ODanhMucPhanHeVaKhoi ON OChiTietKiemTraKhoi.IFID_M869 = ODanhMucPhanHeVaKhoi.IFID_M869
                INNER JOIN ODanhMucPhepKiemTra ON OChiTietKiemTraKhoi.Ref_PhepKiemTra = ODanhMucPhepKiemTra.IOID
                WHERE ODanhMucPhanHeVaKhoi.IOID = %1$d                                
            ', @(int)$data->Ref_PhanHeKhoi);

            // echo '<pre>'; print_r($sqlPhanHeKhoi); die;

            $datPhanHeKhoi = $this->_db->fetchAll($sqlPhanHeKhoi);

            // echo '<pre>'; print_r($datPhanHeKhoi);

            foreach ($datPhanHeKhoi as $item) {
                if($item->IFID_M867 && !in_array($item->IFID_M867, $arrFilterByIOID)) {
                    $arrFilterByIOID[] = $item->IFID_M867;
                }
            }
        }

        $this->_object->getFieldByCode('PhepKiemTra')->arrFilters[] = sprintf(' 
            v.IFID_M867 in (select IFID_M867 from ODanhMucPhepKiemTra  WHERE IFID_M867 IN (%1$s))
        ', implode(',', $arrFilterByIOID));

        // echo '<pre>'; print_r($this->_object->getFieldByCode('PhepKiemTra')->arrFilters); die;


    }
}