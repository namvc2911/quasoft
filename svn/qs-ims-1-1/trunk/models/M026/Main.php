<?php
class Qss_Model_M026_Main extends Qss_Model_Abstract
{
	public function sumaryByPeriod($startdate,$enddate,$departmentid = 0)
    {
    	$sqlwhere = sprintf('  NgayCong between %1$s and %2$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
    	if($departmentid) 
    	{
    		$sqlwhere .= sprintf(' and (pb.lft >= (select lft from OPhongBan where IOID = %1$d)
    						and pb.rgt >= (select rgt from OPhongBan where IOID = %1$d))'
					,$departmentid);
    	}
    	if(Qss_Lib_System::formSecure('M319'))
		{
			$sqlwhere .= sprintf(' and pb.IFID_M319 in (select IFID from qsrecordrights where FormCode = "M319" and UID=%1$d)'
					,$this->_user->user_id);
		}
        
        $sql = sprintf('
			SELECT
				Ref_MaNhanVien,MaNhanVien,TenNhanVien,PhongBanHienTai,
				count(case when cong.Ref_CaLamViec is not null then 1 else 0 end) as CongChuan,
				count(case when cong.Ref_CaDiLamThucTe is not null then 1 else 0 end) as CongThucTe,  
				sum(case when OCa.CaToi = 1 then OCa.SoGio else 0 end) as SoGioCaDem,
				sum(ifnull(cong.TongPhutMuonSom,0)) as TongPhutMuonSom
			FROM OBangCongTheoNgay as cong
			inner join OPhongBan as pb on pb.IOID = cong.Ref_PhongBanHienTai
			left join OCa on OCa.IOID = ifnull(cong.Ref_CaLamViec,0)  
			WHERE %1$s
			group by Ref_MaNhanVien'
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
	public function sumaryOTByPeriod($startdate,$enddate,$departmentid = 0)
    {
    	$sqlwhere = sprintf('  NgayCong between %1$s and %2$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
    	if($departmentid) 
    	{
    		$sqlwhere .= sprintf(' and (pb.lft >= (select lft from OPhongBan where IOID = %1$d)
    						and pb.rgt >= (select rgt from OPhongBan where IOID = %1$d))'
					,$departmentid);
    	}
    	if(Qss_Lib_System::formSecure('M319'))
		{
			$sqlwhere .= sprintf(' and pb.IFID_M319 in (select IFID from qsrecordrights where FormCode = "M319" and UID=%1$d)'
					,$this->_user->user_id);
		}
        
        $sql = sprintf('
			SELECT
				Ref_MaNhanVien,MaNhanVien,TenNhanVien,
				LoaiTangCa,Ref_LoaiTangCa,
				sum(case when OTangCaHangNgay.SoGioDuyet is not null then OTangCaHangNgay.SoGioDuyet else 0 end) as SoGioTangCa  
			FROM OBangCongTheoNgay as cong
			inner join OPhongBan as pb on pb.IOID = cong.Ref_PhongBanHienTai
			inner join OTangCaHangNgay on OTangCaHangNgay.IFID_M026 = cong.IFID_M026  
			group by Ref_MaNhanVien,OTangCaHangNgay.Ref_LoaiTangCa'
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
	public function sumaryLeaveByPeriod($startdate,$enddate,$departmentid = 0)
    {
    	$sqlwhere = sprintf('  NgayCong between %1$s and %2$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
    	if($departmentid) 
    	{
    		$sqlwhere .= sprintf(' and (pb.lft >= (select lft from OPhongBan where IOID = %1$d)
    						and pb.rgt >= (select rgt from OPhongBan where IOID = %1$d))'
					,$departmentid);
    	}
    	if(Qss_Lib_System::formSecure('M319'))
		{
			$sqlwhere .= sprintf(' and pb.IFID_M319 in (select IFID from qsrecordrights where FormCode = "M319" and UID=%1$d)'
					,$this->_user->user_id);
		}
        
        $sql = sprintf('
			SELECT
				Ref_MaNhanVien,MaNhanVien,TenNhanVien,
				LoaiNgayNghi,Ref_LoaiNgayNghi,
				sum(case when cong.SoGioNghi is not null then cong.SoGioNghi else 0 end) as SoGioNghi  
			FROM OBangCongTheoNgay as cong
			inner join OPhongBan as pb on pb.IOID = cong.Ref_PhongBanHienTai
			WHERE %1$s and ifnull(Ref_LoaiNgayNghi,0) <>0
			group by Ref_MaNhanVien,Ref_LoaiNgayNghi'
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
}