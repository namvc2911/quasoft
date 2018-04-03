<?php
class Qss_Model_M220_Main extends Qss_Model_Abstract
{
	public function getPaymentOut($start, $end, $project = 0)
	{
		$where = '';
        // Lọc theo thời gian truyền vào
        $where .= sprintf(' AND (NgayThanhToan BETWEEN %1$s AND %2$s)'
                , $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        // Lọc theo dự án
        if($project) {
            $where .= sprintf(' AND IFNULL(Ref_DuAn, 0) = %1$d ', $project);
        }

        $sql = sprintf('
            SELECT
                SUM(SoTienDaTT)/1000 AS TongChi,
                SUM(case when CoHoaDon = 1 then SoTienDaTT else 0 end)/1000 AS ChiCoThue
            FROM OThanhToan
            INNER JOIN qsiforms AS iform ON iform.IFID = IFID_M220
            WHERE Status = 2 %1$s
            ', $where);
        return $this->_o_DB->fetchOne($sql);
	}
	public function getPaymentOutByProject($start, $end, $project = 0)
	{
		$where = '';
        // Lọc theo thời gian truyền vào
        $where .= sprintf(' AND (NgayThanhToan BETWEEN %1$s AND %2$s)'
                , $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        // Lọc theo dự án
        if($project) {
            $where .= sprintf(' AND IFNULL(Ref_DuAn, 0) = %1$d ', $project);
        }

        $sql = sprintf('
            SELECT
                SUM(SoTienDaTT)/1000 AS TongChi,
                DuAn
            FROM OThanhToan
            INNER JOIN qsiforms AS iform ON iform.IFID = IFID_M220
            WHERE Status = 2 and ifnull(Ref_DuAn,0)<>0 %1$s
            group by DuAn
            ', $where);
        return $this->_o_DB->fetchAll($sql);
	}
	public function getPaymentOutByCostCenter($start, $end, $project = 0)
	{
		$where = '';
        // Lọc theo thời gian truyền vào
        $where .= sprintf(' AND (NgayThanhToan BETWEEN %1$s AND %2$s)'
                , $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        // Lọc theo dự án
        if($project) {
            $where .= sprintf(' AND IFNULL(Ref_DuAn, 0) = %1$d ', $project);
        }

        $sql = sprintf('
            SELECT
                SUM(SoTienDaTT)/1000 AS TongChi,
                TrungTamChiPhi
            FROM OThanhToan
            INNER JOIN qsiforms AS iform ON iform.IFID = IFID_M220
            WHERE Status = 2 %1$s
            group by TrungTamChiPhi
            ', $where);
        return $this->_o_DB->fetchAll($sql);
	}
}