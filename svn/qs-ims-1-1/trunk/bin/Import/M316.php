<?php
class Qss_Bin_Import_M316 extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $sql = "
            UPDATE ODanhSachNhanVien
            INNER JOIN OBoPhan ON ODanhSachNhanVien.MaPhongBan = OBoPhan.MaPhongBan AND  ODanhSachNhanVien.MaBoPhan = OBoPhan.MaBoPhan 
            SET 
                ODanhSachNhanVien.MaBoPhan          = OBoPhan.MaBoPhan
                , ODanhSachNhanVien.TenBoPhan       = OBoPhan.TenBoPhan
                , ODanhSachNhanVien.Ref_MaBoPhan    = OBoPhan.IOID
                , ODanhSachNhanVien.Ref_TenBoPhan   = OBoPhan.IOID            
                , ODanhSachNhanVien.MaPhongBan      = OBoPhan.MaPhongBan
                , ODanhSachNhanVien.TenPhongBan     = OBoPhan.TenPhongBan
                , ODanhSachNhanVien.Ref_MaPhongBan  = OBoPhan.Ref_MaPhongBan
                , ODanhSachNhanVien.Ref_TenPhongBan = OBoPhan.Ref_MaPhongBan               
            WHERE IFNULL(ODanhSachNhanVien.Ref_MaBoPhan, 0) != OBoPhan.IOID
        ";
        $this->_db->execute($sql);
    }

}