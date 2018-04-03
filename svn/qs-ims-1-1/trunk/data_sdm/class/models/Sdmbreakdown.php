<?php
class Qss_Model_Sdmbreakdown  extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getVatTuTheoHangMuc($ifidM759)
    {
        $sql = sprintf('
            SELECT OCongViecBTPBT.MaHangMuc, OCongViecBTPBT.MoTa AS MoTaHangMuc, OCongViecBTPBT.GiaiPhapTrucThuoc, OVatTuPBT.*
            FROM OVatTuPBT
            INNER JOIN OCongViecBTPBT ON IFNULL(OVatTuPBT.Ref_CongViec, 0) = OCongViecBTPBT.IOID
            WHERE OVatTuPBT.IFID_M759 = %1$d
            ORDER BY OCongViecBTPBT.MaHangMuc            
        ', $ifidM759);

        return $this->_o_DB->fetchAll($sql);
    }


//    public function getNhanCongTheoHangMuc($ifidM759)
//    {
//        $sql = sprintf('
//            SELECT *
//            FROM ONhanCongPBT
//            INNER JOIN OCongViecBTPBT ON IFNULL(ONhanCongPBT.Ref_CongViec, 0) = OCongViecBTPBT.IOID
//            WHERE ONhanCongPBT.IFID_M759 = %1$d
//            ORDER BY OCongViecBTPBT.MaHangMuc
//        ', $ifidM759);
//
//        return $this->_o_DB->fetchAll($sql);
//    }
}