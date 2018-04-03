<?php
/**
 * User: huy.bv
 * Date: 4/14/2017
 * Time: 5:17 PM
 */
class Qss_Model_Maintenance_Equip_Indicator extends Qss_Model_Abstract {

    public function __construct() {
        parent::__construct();
    }

    public function showChartByIndicator($inDecator=array(), $start, $end) {
        $where = '';
        if($inDecator) {
            $inDecator = implode(',',$inDecator);
            $where .= sprintf(' AND NhatTrinh.Ref_DiemDo IN (%1$s) ORDER BY Ngay, ThoiGian', $inDecator);
        }
        $sql = sprintf('
                   SELECT
                       NhatTrinh.*, DiemDo.GioiHanTren, DiemDo.GioiHanDuoi,
                       DiemDo.BoPhan AS diemDoBoPhan,
                       DiemDo.ChiSo AS diemDoChiSo,
                       OChiSo.DonViTinh,
                       DiemDo.Ma
                   FROM ONhatTrinhThietBi AS NhatTrinh
                   INNER JOIN ODanhSachDiemDo AS DiemDo ON DiemDo.IOID = NhatTrinh.Ref_DiemDo
                   INNER JOIN OChiSoMayMoc AS OChiSo ON OChiSo.IOID = DiemDo.Ref_ChiSo
                   WHERE (NhatTrinh.Ngay BETWEEN %1$s AND %2$s)',
                   $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        $sql .= $where;
        //print_r($sql);die;
        return $this->_o_DB->fetchAll($sql);
    }
}