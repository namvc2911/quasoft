<?php
class Qss_Model_Maintenance_Calibration extends Qss_Model_Abstract
{
    public $filter = array();

    public function __construct()
    {
        parent::__construct();
    }

     public function getCalibrationOrderByDateRange($start,$end)
    {
        $sql = sprintf('
			SELECT
			    hieuchuan.*
			    , ifo.Status
			    , khuvuctructhuoc.Ten AS TenKhuVucThietBi
			    , khuvuctructhuoc.MaKhuVuc AS MaKhuVucThietBi
			    , thietbi.IOID as EIOID
			FROM OHieuChuanKiemDinh AS hieuchuan
			INNER JOIN qsiforms AS ifo ON hieuchuan.IFID_M753 = ifo.IFID
			INNER JOIN ODanhSachThietBi AS thietbi ON hieuchuan.Ref_MaThietBi = thietbi.IOID
			LEFT JOIN OKhuVuc AS khuvuctructhuoc ON thietbi.Ref_MaKhuVuc = khuvuctructhuoc.IOID
			WHERE NgayKiemDinhTiepTheo between %1$s and %2$s
			and ifo.DepartmentID in (%3$s)'
                , $this->_o_DB->quote($start)
                , $this->_o_DB->quote($end)
                , $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }

}
