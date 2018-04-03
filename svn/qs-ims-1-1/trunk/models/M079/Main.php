<?php
class Qss_Model_M079_Main extends Qss_Model_Abstract
{
	public function checkPeriodClose($kycong,$phongban)
    {
        $sql = sprintf('
			SELECT 1
			FROM OXuLyKyCong
			inner join qsiforms on OXuLyKyCong.IFID_M079 = qsiforms.IFID 
			left join OPhongBan on OPhongBan.IOID = OXuLyKyCong.Ref_PhongBan
			left join OPhongBan as con on con.lft >= OPhongBan.lft and con.rgt <= OPhongBan.rgt and con.IOID = %2$d 
			where OXuLyKyCong.Ref_KyCong= %1$d and qsiforms.Status=2 and
			(ifnull(OXuLyKyCong.Ref_PhongBan,0) = 0 or ifnull(con.IOID,0) <> 0)'
            , $kycong
            , $phongban);
        $dataSQL = $this->_o_DB->fetchOne($sql);
        return $dataSQL?1:0; 
    }
	public function getPeriod($ngay)
    {
        $sql = sprintf('
			SELECT *
			FROM OKyCong
			inner join qsiforms on OKyCong.IFID_M339 = qsiforms.IFID 
			where %1$s between OKyCong.ThoiGianBatDau and  OKyCong.ThoiGianKetThuc'
            , $this->_o_DB->quote($ngay));
 		return $this->_o_DB->fetchOne($sql);
    }
}