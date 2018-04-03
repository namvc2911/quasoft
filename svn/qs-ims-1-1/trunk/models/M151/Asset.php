<?php
class Qss_Model_M151_Asset extends Qss_Model_Abstract {
    // Dùng trong này sẽ khác dùng trong thiết bị, ở đây đếm theo việc hay không có mã tài sản
    public function countEquips($hasNotAssetCode, $notInAsset, $duplicateCode) {
        $where  = $hasNotAssetCode?sprintf(' AND IFNULL(MaTaiSan, "") = ""'):'';
        $where .= $notInAsset?sprintf(' 
            AND IFID_M705 IN (
                SELECT ODanhSachThietBi.IFID_M705
                FROM ODanhSachThietBi
                LEFT JOIN ODanhMucCongCuDungCu ON ODanhSachThietBi.MaTaiSan = ODanhMucCongCuDungCu.Ma
                WHERE IFNULL(MaTaiSan, "") != "" AND IFNULL(ODanhMucCongCuDungCu.IOID, 0) = 0
            )
        '):'';

        $where .= $duplicateCode?sprintf(' 
            AND IFID_M705 IN (
                SELECT ODanhSachThietBi.IFID_M705 
                FROM ODanhSachThietBi 
                INNER JOIN  (
                    SELECT ODanhSachThietBi.MaTaiSan, count(1) as Total
                    FROM ODanhSachThietBi
                    WHERE IFNULL(ODanhSachThietBi.MaTaiSan, "") != ""
                    GROUP BY ODanhSachThietBi.MaTaiSan    
                    HAVING Total > 1
                ) AS DuplicateAssetTable   ON DuplicateAssetTable.MaTaiSan = ODanhSachThietBi.MaTaiSan
                           
            )
        '):'';

        $sql = sprintf('
            SELECT count(1) AS Total
            FROM ODanhSachThietBi
            WHERE 1=1 %1$s 
        ', $where);
        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->Total:0;
    }

    // Dùng trong này sẽ khác dùng trong thiết bị, ở đây lấy theo việc có hay không mã tài sản
    public function getEquips($hasNotAssetCode, $notInAsset, $duplicateCode, $page = 1, $display = 20) {
        $where  = $hasNotAssetCode?sprintf(' AND IFNULL(MaTaiSan, "") = ""'):'';
        $where .= $notInAsset?sprintf(' 
            AND IFID_M705 IN (
                SELECT ODanhSachThietBi.IFID_M705
                FROM ODanhSachThietBi
                LEFT JOIN ODanhMucCongCuDungCu ON ODanhSachThietBi.MaTaiSan = ODanhMucCongCuDungCu.Ma
                WHERE IFNULL(MaTaiSan, "") != "" AND IFNULL(ODanhMucCongCuDungCu.IOID, 0) = 0
            )
        '):'';
        $where .= $duplicateCode?sprintf(' 
            AND IFID_M705 IN (
                SELECT ODanhSachThietBi.IFID_M705 
                FROM ODanhSachThietBi 
                INNER JOIN  (
                    SELECT ODanhSachThietBi.MaTaiSan, count(1) as Total
                    FROM ODanhSachThietBi
                    WHERE IFNULL(ODanhSachThietBi.MaTaiSan, "") != ""
                    GROUP BY ODanhSachThietBi.MaTaiSan    
                    HAVING Total > 1
                ) AS DuplicateAssetTable   ON DuplicateAssetTable.MaTaiSan = ODanhSachThietBi.MaTaiSan
                         
            )
        '):'';


        $start = ($page - 1) * $display;

        $sql = sprintf('
            SELECT *
            FROM ODanhSachThietBi
            WHERE 1=1 %1$s 
            ORDER BY ODanhSachThietBi.MaTaiSan
            LIMIT %2$d, %3$d
        ', $where, $start, $display);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function updateTaiSanThietBi($arrayTaiSanThietBi) {
        if(is_array($arrayTaiSanThietBi) && count($arrayTaiSanThietBi)) {
            $updateTemp = '';

            foreach ($arrayTaiSanThietBi as $item) {
                $this->_o_DB->execute(sprintf('
                        UPDATE ODanhSachThietBi SET MaTaiSan = "%1$s" WHERE IOID = %2$d;'
                        , $item['AssetCode'], $item['EquipIOID']
                    )
                );
            }
        }
    }

    public function getTaiSanTheoMangMa($arrMaTaiSan) {
        $arrMaTaiSan[] = '';
        $strMaTaiSan   = '';

        foreach ($arrMaTaiSan as $maTaiSan) {
            if($strMaTaiSan) {
                $strMaTaiSan .= ',';
            }

            $strMaTaiSan .= "\"{$maTaiSan}\"";
        }

        $sql = sprintf('
            SELECT * 
            FROM ODanhMucCongCuDungCu 
            WHERE Ma IN (%1$s)
        ', $strMaTaiSan);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}