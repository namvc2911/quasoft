<?php
class Qss_Model_M221_Main extends Qss_Model_Abstract
{
	public function getPaymentIn($start, $end, $project = 0)
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
                SUM(SoTienThanhToan)/1000 AS TongThu
            FROM OThuTien
            INNER JOIN qsiforms AS iform ON iform.IFID = IFID_M221
            WHERE Status = 2 %1$s
            ', $where);
        return $this->_o_DB->fetchOne($sql);
	}
	public function getPaymentInByProject($start, $end, $project = 0)
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
                SUM(SoTienThanhToan)/1000 AS TongThu,
                DuAn
            FROM OThuTien
            INNER JOIN qsiforms AS iform ON iform.IFID = IFID_M221
            WHERE Status = 2 %1$s
            group by DuAn
            ', $where);
        return $this->_o_DB->fetchAll($sql);
	}

}