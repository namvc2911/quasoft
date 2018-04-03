<?php

class Qss_Bin_Onload_OCauTrucThietBi extends Qss_Lib_Onload
{
    public function __doExecute()
    {
        parent::__doExecute();
    }

    public function Serial()
    {
        //lọc trạng thái lưu trữ của kho theo vật tư và phải có số lượng = 1
        $vatTu = (int)$this->_object->getFieldByCode('MaSP')->intRefIOID;

        $this->_object->getFieldByCode('Serial')->arrFilters[] =
            sprintf('
                v.IOID in (
                    SELECT TrangThai.IOID
                    FROM OThuocTinhChiTiet AS TrangThai
                    WHERE IFNULL(TrangThai.IFID_M602, 0) != 0
                        AND IFNULL(TrangThai.SoSerial, \'\') != \'\'
                        AND IFNULL(TrangThai.SoLuong, 0) = 1
                        AND IFNULL(TrangThai.Ref_MaSanPham, 0) = %1$d
                )
            ', $vatTu);
    }
}