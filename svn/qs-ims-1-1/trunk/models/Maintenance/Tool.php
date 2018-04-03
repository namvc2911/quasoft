<?php

class Qss_Model_Maintenance_Tool extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getToolsOrderByEmployee($employee = 0, $asset = 0)
    {
        $where  = $employee?sprintf(' AND TaiSan.Ref_MaNhanVien = %1$d ', $employee):'';
        $where .= $asset?sprintf(' AND TaiSan.Ref_MaTaiSan = %1$d ', $asset):'';

        return $this->_o_DB->fetchAll(
            sprintf('
                SELECT
                    TaiSan.*
                    , (SoLuongDaGiao - TongSoTraLai) AS SoLuongConLai
                FROM
                (
                    SELECT
                        TaiSan.*
                        , IFNULL(TaiSan.SoLuong, 0) AS SoLuongDaGiao
                        , SUM(IFNULL(TraLai.SoLuong, 0)) AS TongSoTraLai
                    FROM OTaiSanCaNhan AS TaiSan
                    LEFT JOIN OTraLaiTaiSanCaNhan AS TraLai ON TaiSan.IFID_M419 = TraLai.IFID_M419
                    WHERE 1=1 %1$s
                    GROUP BY TaiSan.IFID_M419
                ) AS TaiSan
                WHERE (SoLuongDaGiao - TongSoTraLai) > 0
                GROUP BY TaiSan.Ref_MaNhanVien, TaiSan.Ref_MaTaiSan
                ORDER BY TaiSan.MaNhanVien, TaiSan.MaTaiSan
            ', $where)
        );
    }
}