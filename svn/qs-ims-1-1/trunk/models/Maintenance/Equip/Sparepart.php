<?php

/**
 * Class Qss_Model_Maintenance_Equip_Daily - Nhật trình thiết bị
 * alias - ONhatTrinhThietBi: nhattrinh
 * alias - ODanhSachThietBi: thietbi
 *
 */
class Qss_Model_Maintenance_Equip_Sparepart extends Qss_Model_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getByEquipment($eqid,$search = '')
    {
        $where = '';
        if($search)
        {
        	$where .= sprintf('
        	        and (
        	            PhuTung.ViTri like %1$s
        	            or PhuTung.BoPhan like %1$s
        	            or PhuTung.MaSP like %1$s
        	            or PhuTung.TenSP like %1$s
                    )'
                ,$this->_o_DB->quote('%'.$search.'%'));
        }

        $sql = sprintf('
            SELECT
                PhuTung.IOID AS Ref_ViTri
                , PhuTung.ViTri
                , PhuTung.BoPhan
                , (
                    SELECT count(*)
                    FROM OCauTrucThietBi AS CauTruc2
                    WHERE
                        CauTruc2.IFID_M705 = %1$d
                        AND CauTruc2.lft <= PhuTung.lft  AND CauTruc2.rgt >= PhuTung.rgt
                ) AS LEVEL
                , PhuTung.lft AS lft
                , PhuTung.Ref_MaSP
                , PhuTung.MaSP
                , PhuTung.TenSP
                , PhuTung.SoLuongHC
                , IFNULL(PhuTung.PhuTung, 0) AS LaPhuTung
            FROM OCauTrucThietBi AS PhuTung
            WHERE PhuTung.IFID_M705 = %1$d %2$s
            ORDER BY PhuTung.lft'
        , $eqid
        , $where);

        return $this->_o_DB->fetchAll($sql);

        /*
         *
               select
                    PhuTung.*,
                    (
                        SELECT count(*)
                        FROM OCauTrucThietBi AS CauTruc2
                        WHERE
                            CauTruc2.IFID_M705 = %1$d
                            AND IFNULL(CauTruc2.lft, 0) <= IFNULL(CauTruc.lft, 0)  AND IFNULL(CauTruc2.rgt, 0) >= IFNULL(CauTruc.rgt, 0)
                    ) AS LEVEL
                FROM ODanhSachPhuTung AS PhuTung
                LEFT JOIN OCauTrucThietBi AS CauTruc ON PhuTung.IFID_M705 = CauTruc.IFID_M705
                    AND IFNULL(PhuTung.Ref_ViTri, 0) = CauTruc.IOID
                where PhuTung.IFID_M705 = %1$d %2$s
                order by CauTruc.lft
         */
    }

  
}
