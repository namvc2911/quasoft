<?php

class Qss_Model_M724_Period extends Qss_Model_Abstract {
    /**
     * Đếm số chu kỳ của một kế hoạch theo IFID của kế hoạch.
     * @param $ifidM724
     * @return int
     */
    public function countPeriodByIFID($ifidM724) {
        $sql = sprintf('
			SELECT count(1) AS Total 
			FROM OChuKyBaoTri
			WHERE IFID_M724 = %1$d
			GROUP BY IFID_M724
        ', $ifidM724);
        $dat = $this->_o_DB->fetchOne($sql);

        return $dat?$dat->Total:0;
    }

    /**
     * Cập nhật trường điều chỉnh theo phiếu về 0
     * @param $ifidM724
     */
    public function updateDieuChinhTheoPhieuVeKhongByIFIDs($ifidM724s = array()) {
        $ifidM724s[] = 0;

        $sql = sprintf('
            UPDATE OChuKyBaoTri
            SET DieuChinhTheoPBT = 0
            WHERE IFID_M724 IN (%1$s) AND IFNULL(DieuChinhTheoPBT, 0) = 1
        ', implode(',', $ifidM724s));

        $this->_o_DB->execute($sql);
    }
}