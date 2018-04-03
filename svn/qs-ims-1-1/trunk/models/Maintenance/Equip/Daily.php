<?php

/**
 * Class Qss_Model_Maintenance_Equip_Daily - Nhật trình thiết bị
 * alias - ONhatTrinhThietBi: nhattrinh
 * alias - ODanhSachThietBi: thietbi
 *
 */
class Qss_Model_Maintenance_Equip_Daily extends Qss_Model_Abstract
{
     public function __construct()
    {
        parent::__construct();
    }

	
    public function getActiveTimeOfEquip($equipIOID)
    {
        $where = '';

        if(Qss_Lib_System::fieldActive('ODanhSachDiemDo', 'NgayReset')) {
            $sql = sprintf('
                SELECT ODanhSachDiemDo.NgayReset 
                FROM ODanhSachDiemDo
                INNER  JOIN ODanhSachThietBi ON ODanhSachDiemDo.IFID_M705 = ODanhSachThietBi.IFID_M705
                INNER JOIN OChiSoMayMoc AS ChiSo ON ChiSo.IOID = ODanhSachDiemDo.Ref_ChiSo
                WHERE ODanhSachThietBi.IOID = %1$d  
                    AND IFNULL(ODanhSachDiemDo.NgayReset, "") != "" 
                    AND IFNULL(ODanhSachDiemDo.Ref_BoPhan, 0) = 0 
                    AND IFNULL(ChiSo.Gio, 0) = 1
                ORDER BY NgayReset DESC 
                LIMIT 1
            ', $equipIOID);
            $ngayReset = $this->_o_DB->fetchOne($sql);
            $ngayReset = $ngayReset ? $ngayReset->NgayReset : '';

            if($ngayReset) {
                $where = sprintf(' AND ONhatTrinhThietBi.Ngay > %1$s ', $this->_o_DB->quote($ngayReset));
            }
        }

        $sql = sprintf('
            SELECT SUM(SoHoatDong) AS TongSo
            FROM ONhatTrinhThietBi
            INNER  JOIN qsiforms ON ONhatTrinhThietBi.IFID_M765 = qsiforms.IFID
            INNER JOIN OChiSoMayMoc as cs ON cs.IOID = ONhatTrinhThietBi.Ref_ChiSo
            WHERE ONhatTrinhThietBi.Ref_MaTB = %1$d  
                AND IFNULL(cs.Gio, 0) = 1
                AND ONhatTrinhThietBi.Ngay <= %2$s 
                AND IFNULL(ONhatTrinhThietBi.Ref_BoPhan, 0) = 0 %3$s
            GROUP BY ONhatTrinhThietBi.Ref_MaTB'
        , $equipIOID, $this->_o_DB->quote(date('Y-m-d')), $where);

        // echo '<pre>'; print_r($sql); die;
        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->TongSo:0;
    }
	/*
    public function getActiveTimeOfEquipFromAuto($equipIOID)
    {
        $sql = sprintf('select GiaTri as TongSo
                        from ONhapChiSoTuDong
						where Ref_MaThietBi = %1$d
						AND Ngay <= %2$s
						ORDER BY Ngay DESC, Gio DESC, IOID DESC
						LIMIT 1',
            $equipIOID, $this->_o_DB->quote(date('Y-m-d')));
        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->TongSo:0;
    }*/
}
