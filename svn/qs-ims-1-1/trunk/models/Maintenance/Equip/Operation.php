<?php

/**
 * Class Qss_Model_Maintenance_Equip_Operation - Định mức thiết bị
 * + F1: getLastOperationBOMOfEquip($eqIOID)
 * Lấy định mức thiết bị mới nhất của một thiết bị
 */
class Qss_Model_Maintenance_Equip_Operation extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy định mức thiết bị mới nhất của một thiết bị
     * @param $eqIOID
     * @return mixed
     */
    public function getLastOperationBOMOfEquip($eqIOID)
    {
        $sql = sprintf('
            SELECT *
            FROM ODinhMucThietBi
            WHERE Ref_MaThietBi = %1$d
            ORDER BY NgayBatDau DESC
            LIMIT 1
        ', $eqIOID);
        return $this->_o_DB->fetchOne($sql);
    }
 	public function getEquipmentIndicators($ifid)
    {
        $sql = sprintf('select bnt.*,cs.IOID as ChiSoIOID, cs.ChiSo,diemdo.Ma as DiemDo,diemdo.BoPhan,diemdo.IOID as DiemDoIOID 
        		from ONhatTrinhThietBi as nt
        		inner join ODanhSachDiemDo as diemdo on nt.Ref_DiemDo = diemdo.IOID
        		inner join OChiSoMayMoc as cs on cs.IOID = diemdo.Ref_ChiSo
				left join OBangNhatTrinhTB as bnt 
						on bnt.Ref_DiemDo = diemdo.IOID   
						and nt.IFID_M765 = bnt.IFID_M765
				where nt.IFID_M765 = %1$d'
        	,$ifid);
       	//echo '<pre>';
        //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }
    public function getPerformance($startDate, $endDate, $equipIOID = 0, $itemIOID = 0)
	{
	    // Điều kiện
	   	$where = '';
	    if($equipIOID)
	    {
	        $where .= sprintf(' and thietbi.IOID = %1$d ', $equipIOID);
	    }
        
        if($itemIOID)
        {
            $where .= sprintf(' and spham.IOID = %1$d ', $itemIOID);
        }
	    $sql = sprintf('
	        SELECT
	           ifnull(thietbi.IOID, 0) AS EqIOID,
	           thietbi.MaThietBi,
	           thietbi.TenThietBi,
	           ifnull(spham.IOID, 0) AS ItemIOID,
	           spham.MaSanPham,
	           	spham.TenSanPham,
	           	spham.DonViTinh,
	           	sum(DATEDIFF(%2$s,%1$s)+1) * ifnull(diemdo.SoHoatDongNgay,0) as SoHoatDongKeHoach,
	           	sum(ifnull(nt.SoHoatDong,0)) as SoHoatDongThucTe,
	           	sum((DATEDIFF(%2$s,%1$s)+1) * ifnull(diemdo.SoHoatDongNgay,0) * cdbom.SoLuongGio)  as SanLuongKeHoach,
	           	sum(ifnull(sanluong.SoLuong,0)) as SanLuongThucTe,
	           	sum(ifnull(sanluong.SoLuongLoi,0)) as SoLuongLoi,
	           	sum(ifnull(nt.SoHoatDong,0) * cdbom.SoLuongGio)  as SanLuongLyThuyet,
	           	sum(ifnull(sanluong.SoLuong,0) / cdbom.SoLuongGio)  as SoHoatDongYeuCauChoSanLuongThucTe,
	           	sum(ifnull(sanluong.SoLuongLoi,0) / cdbom.SoLuongGio)  as SoHoatDongChoSanPhamLoi,
	           	/*sum(ifnull(nt.SoHoatDong,0) * cdbom.SoLuongGio)  as SoHoatDongNangSuat,*/
	           	(select sum(ifnull(ThoiGianDungMay,0)) from OPhieuSuCo where Ref_MaThietBi = thietbi.IOID and NgayDungMay between %1$s and %2$s)
	           	+ (select sum(ifnull(ThoiGianDungMay,0)) from OPhieuBaoTri where Ref_MaThietBi = thietbi.IOID and NgayDungMay between %1$s and %2$s) as ThoiGianDungMay,
	           	(select count(*) from OPhieuBaoTri
	           	inner join OPhanLoaiBaoTri on OPhanLoaiBaoTri.IOID = OPhieuBaoTri.Ref_LoaiBaoTri where OPhanLoaiBaoTri.LoaiBaoTri="%4$s" and Ref_MaThietBi = thietbi.IOID and NgayDungMay between %1$s and %2$s) as SoLanDungMay
	        FROM ODanhSachThietBi AS thietbi
	        LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = thietbi.IFID_M705
	        left join (select Ref_DiemDo, sum(ONhatTrinhThietBi.SoHoatDong) as SoHoatDong from ONhatTrinhThietBi 
	        	where Ngay between %1$s and %2$s
	        	group by Ref_DiemDo) as nt on nt.Ref_DiemDo = diemdo.IOID 
	        left join OChiSoMayMoc as cs on cs.IOID = diemdo.Ref_ChiSo and cs.Gio = 1
	        left join OCongDoanDayChuyen as cd on cd.Ref_MaThietBi = thietbi.IOID
	        left join ODayChuyen as dc on dc.IFID_M702 = cd.IFID_M702
	        left join OCauThanhSanPham as cauthanh on cauthanh.Ref_DayChuyen = dc.IOID
	        left join OCongDoanBOM as cdbom on cdbom.IFID_M114 = cauthanh.IFID_M114 and cdbom.Ref_Ten = cd.Ref_CongDoan
	        LEFT JOIN OSanPham AS spham ON cauthanh.Ref_MaSanPham = spham.IOID
	        LEFT JOIN ODonViTinhSP AS dvtspham ON spham.IFID_M113 = dvtspham.IFID_M113
	           AND ifnull(cauthanh.Ref_DonViTinh, 0) = dvtspham.IOID
	        left join (select Ref_DayChuyen,Ref_CongDoan, Ref_MaSP,Ref_DonViTinh,sum(SoLuong) as SoLuong,sum(SoLuongLoi) as SoLuongLoi 
	        			from OThongKeSanLuong where Ngay between %1$s and %2$s
	        			group by Ref_DayChuyen,Ref_CongDoan, Ref_MaSP,Ref_DonViTinh) as sanluong on  
	        	sanluong.Ref_DayChuyen = dc.IOID and sanluong.Ref_DayChuyen and sanluong.Ref_CongDoan = cd.IOID
	       		and spham.IOID = sanluong.Ref_MaSP and sanluong.Ref_DonViTinh = dvtspham.IOID  
	        where ifnull(spham.IOID, 0) <> 0
	        %3$s
	        Group by thietbi.IOID, spham.IOID
	        ORDER BY thietbi.IOID, spham.IOID
        ' , $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate),$where, Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN);
	   	//echo '<pre>';echo $sql;die;
	    return $this->_o_DB->fetchAll($sql);
	}
	public function getItemsFromBOM($eqIOID, $sDate, $eDate)
	{
		$sql = sprintf('select cauthanh.*
		    from OCauThanhSanPham AS cauthanh
		    inner join ODayChuyen as dc on dc.IOID = cauthanh.Ref_DayChuyen
		    inner join OCongDoanDayChuyen as congdoan ON congdoan.IFID_M702 = dc.IFID_M702
		    where congdoan.Ref_MaThietBi = %1$d'
            , $eqIOID
            , $this->_o_DB->quote($sDate)
            , $this->_o_DB->quote($eDate)
	    );
	    //echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function getEquipmentHasBOM()
	{
		$sql = sprintf('select dstb.*
		    from ODanhSachThietBi as dstb
		    inner join OCongDoanDayChuyen as dm on dm.Ref_MaThietBi = dstb.IOID
		    order by MaThietBi');
	    //echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}	
	public function getWorkingNumberByRange($start,$end,$locationIOID,$eqTypeIOID,$eqGroupIOID)
	{
		$where = '';
		if($eqGroupIOID)
        {
            $where .= sprintf(' dstb.Ref_NhomThietBi = %1$d ',$eqGroupIOID);
        }
	 	if ($eqTypeIOID)
        {
            $sql     = sprintf('select * from OLoaiThietBi where IOID = %1$d',$eqTypeIOID);
            $eqTypes = $this->_o_DB->fetchOne($sql);

            if ($eqTypes)
            {
                $where .= sprintf('
                    (dstb.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) '
                ,  $eqTypes->lft, $eqTypes->rgt);
            }
        }
	 	if ($locationIOID)
        {
            $sqlLocName = sprintf('select * from OKhuVuc where IOID = %1$d',$locationIOID);
            $locName    = $this->_o_DB->fetchOne($sqlLocName);
            if ($locName)
            {
                $where .= sprintf(
                    ' (dstb.Ref_MaKhuVuc IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) '
                    , $locName->lft, $locName->rgt);
            }
        }
        $sql = sprintf('select * from ODanhSachThietBi as dstb
        		left join ONhatTrinhThietBi as nt on nt.Ref_MaTB = dstb.IOID
        		left join OChiSoMayMoc as cs on cs.IOID = nt.Ref_ChiSo and cs.Gio = 1
        		where nt.Ngay between %1$s and %2$s
        		%3$s'
        	, $this->_o_DB->quote($start)
        	, $this->_o_DB->quote($end)
        	, $where);
        //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
	}
	/**
	 * Lay danh sach vat tu theo dinh muc cua thiet bi
	 * @param unknown $startDate
	 * @param unknown $endDate
	 * @param unknown $equipIOID
	 * @param unknown $itemIOID
	 */
	public function getMaterialsCompare(
        $startDate
        , $endDate
        , $locIOID = 0
	    , $eqGroupIOID = 0
	    , $eqTypeIOID = 0
	    , $equipIOID = 0
	    , $itemIOID = 0)
	{
	    if(!$startDate || !$endDate) return array();
	    $where = '';
	    if($equipIOID)
	    {
	        $where .= sprintf(' and thietbi.Ref_MaThietBi = %1$d ', $equipIOID);
	    }
	    
	    if($itemIOID)
	    {
	        $where .= sprintf(' and spham.IOID = %1$d ', $itemIOID);
	    }	

	    if($locIOID)
	    {
	        $findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
	        $findLoc    = $this->_o_DB->fetchOne($findLocSql);
	        if($findLoc)
	        {
	            $where .= sprintf(' and thietbi.Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d) ', $findLoc->lft, $findLoc->rgt);
	        }
	    }
	    
	    if($eqGroupIOID)
	    {
	        $where .= sprintf(' and thietbi.Ref_NhomThietBi = %1$d ', $eqGroupIOID);
	    }
	    
	    if($eqTypeIOID)
	    {
	        $where .= sprintf(' and thietbi.Ref_LoaiThietBi = %1$d ', $eqTypeIOID);
	    }	    
	    
	    $sql = sprintf('
	        SELECT 
	           ifnull(thietbi.IOID, 0) AS EqIOID,
	           thietbi.MaThietBi AS EqCode,
	           thietbi.TenThietBi AS EqName,
	           ifnull(dmvtu.Ref_MaSanPham, 0) AS ItemIOID,
	           dmvtu.MaSanPham AS ItemCode,
	           dmvtu.TenSanPham AS ItemName, 
	           spham.DonViTinh AS ItemUOM,
	           sum(ifnull(vttieuhao.SoLuong,0)) as SoLuongThucTe,
           	   sum(ifnull(nt.SoHoatDong,0) * (dmvtu.SoLuong/dmtbi.HanMucCoSo))  as SoLuongLyThuyet
	        FROM ODanhSachThietBi AS thietbi
	        INNER JOIN 
	        (
                SELECT * from (select 
    	           *
    	        FROM ODinhMucThietBi
    	        where (NgayBatDau <= %1$s or NgayBatDau <= %2$s)
    	        Order by NgayBatDau desc
    	        ) as t
    	        GROUP BY Ref_MaThietBi    
	        ) AS dmtbi ON dmtbi.Ref_MaThietBi = thietbi.IOID
	        INNER JOIN ODinhMucVatTu AS dmvtu ON dmtbi.IFID_M732 = dmvtu.IFID_M732
	        left join OLichLamViec as lich on lich.IOID = thietbi.Ref_LichLamViec
	        LEFT JOIN OSanPham AS spham ON dmvtu.Ref_MaSanPham = spham.IOID
	        LEFT JOIN ODonViTinhSP AS dvtspham ON spham.IFID_M113 = dvtspham.IFID_M113
	           AND ifnull(dmvtu.Ref_DonViTinh, 0) = dvtspham.IOID
	        left join ONhatTrinhThietBi as nt on nt.Ref_MaTB = thietbi.IOID and nt.Ngay between %1$s and %2$s
	        -- left join OBangNhatTrinhTB as ct on ct.IFID_M765 = nt.IFID_M765 and ct.Ref_ChiSo = dmtbi.Ref_ChiSo
	        left join OVatTuTieuHao as vttieuhao on vttieuhao.IFID_M765= nt.IFID_M765 and vttieuhao.Ref_MaSanPham = spham.IOID and vttieuhao.Ref_DonViTinh = dvtspham.IOID 
	        %3$s
	        group by thietbi.IOID, spham.IOID
	        ORDER BY thietbi.IOID, spham.IOID'
	    , $this->_o_DB->quote($startDate)
	    , $this->_o_DB->quote($endDate)
	    ,$where);
	    //echo '<pre>';print_r($sql);die;
	    return $this->_o_DB->fetchAll($sql);
	}
	public function getAvailabilityByPeriod($start, $end, $period,$group = 0,$type = 0,$equip = 0)
	{
		$where = '';
		$groupby  = '';
		$count = '';
		switch ($period)
		{
			case 'D':
				$groupby = 'nt.Ngay';
				$count = '1';
				break;
			case 'W':
				$groupby = 'week(nt.Ngay,3),year(nt.Ngay)';
				$count = '7';
				break;
			case 'M':
				$groupby = 'month(nt.Ngay),year(nt.Ngay)';
				$count = '30';
				break;
			case 'Q':
				$groupby = 'quarter(nt.Ngay),year(nt.Ngay)';
				$count = '90';
				break;
			case 'Y':
				$groupby = 'year(nt.Ngay)';
				$count = '365';
				break;
		}

		if ($group)
		{
			$where = sprintf(' and thietbi.Ref_NhomThietBi = %1$d', $group);
		}
		
		if ($type)
		{
			$where = sprintf(' and thietbi.Ref_LoaiThietBi = %1$d', $type);
		}
		
		if ($equip)
		{
			$where = sprintf(' and thietbi.IOID= %1$d', $equip);
		}
		$sql = sprintf('
	        SELECT
	           thietbi.*,
	           	nt.Ngay,
	           	week(nt.Ngay,3) as Tuan,
	           	month(nt.Ngay) as Thang,
	           	quarter(nt.Ngay) as Qui,
	           	year(nt.Ngay) as Nam,
	           	sum(%4$s * ifnull(diemdo.SoHoatDongNgay,0)) as SoHoatDongKeHoach,
	           	sum(ifnull(nt.SoHoatDong,0)) as SoHoatDongThucTe
	        FROM ODanhSachThietBi AS thietbi
	        LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = thietbi.IFID_M705
	        left join ONhatTrinhThietBi as nt on nt.Ref_DiemDo= diemdo.IOID and nt.Ngay between %1$s and %2$s 
	        left join OChiSoMayMoc as cs on diemdo.Ref_ChiSo = cs.IOID 
	        where 1=1
	        %3$s
	        Group by thietbi.IOID, %5$s
	        ORDER BY thietbi.MaKhuVuc, thietbi.MaThietBi' 
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end)
			, $where
			, $count
			, $groupby);
	   	//echo '<pre>';echo $sql;die;
	    return $this->_o_DB->fetchAll($sql);
	} 
	public function getMeasurePoints()
	{
		$sql = sprintf('
		    select
		    	diemdo.*
		        , chiso.*
		        , tudong.SoHoatDong as GiaTri
		        , tb.*
		    from ODanhSachDiemDo as diemdo
            inner join OChiSoMayMoc as chiso on chiso.IOID = diemdo.Ref_ChiSo
            inner join ODanhSachThietBi as tb on tb.IFID_M705 = diemdo.IFID_M705 
            left join ONhatTrinhThietBi as tudong on tudong.Ref_DiemDo = diemdo.IOID
            group by diemdo.IOID
            order by tudong.Ngay');
		return $this->_o_DB->fetchAll($sql);
	}	
	public function getRuntimeByPeriod($start, $end, $period, $eqIOID)
	{
		$groupby = '';
		switch ($period)
		{
			case Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY:
				$groupby  = ' GROUP BY day(nt.Ngay)';
				$groupby .= ' ORDER BY day(nt.Ngay)';
			break;
			case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
				$groupby = ' GROUP BY Year(nt.Ngay),  WEEK(nt.Ngay, 3) ';
				$groupby .= ' ORDER BY Year(nt.Ngay),  WEEK(nt.Ngay, 3)';
			break;
			case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
				$groupby = ' GROUP BY Year(nt.Ngay), month(nt.Ngay)';
				$groupby .= ' ORDER BY  Year(nt.Ngay), month(nt.Ngay)';
			break;
			case Qss_Lib_Extra_Const::PERIOD_TYPE_QUARTERLY:
				$groupby = ' GROUP BY year(nt.Ngay), quarter(nt.Ngay)';
				$groupby .= ' ORDER BY  year(nt.Ngay), quarter(nt.Ngay)';
			break;		
			case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
				$groupby = ' GROUP BY year(nt.Ngay)';
				$groupby .= ' ORDER BY year(nt.Ngay)';
			break;
		}
		$sql = sprintf('SELECT sum(nt.SoHoatDong) as SoGio,
						nt.Ngay as NgayThu
						, nt.Ngay as Ngay
						, week(nt.Ngay,3) as Tuan
						, month(nt.Ngay) as Thang
						, quarter(nt.Ngay) as Quy
						, year(nt.Ngay) as Nam
					FROM ONhatTrinhThietBi AS nt
					INNER JOIN OChiSoMayMoc AS cs ON cs.IOID = nt.Ref_ChiSo and cs.Gio = 1
					WHERE 
						(nt.Ngay between %1$s and %2$s)
						and nt.Ref_MaTB = %3$d
						%4$s'
						, $this->_o_DB->quote($start)
						, $this->_o_DB->quote($end)
						, $eqIOID
						, $groupby);
					//	echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function getHoursMeasurePoints()
	{
		$sql = sprintf('
            select
                dstb.*
            from ODanhSachDiemDo as diemdo
            inner join ODanhSachThietBi as dstb on dstb.IFID_M705 = diemdo.IFID_M705
            inner join OChiSoMayMoc as chiso on chiso.IOID = diemdo.Ref_ChiSo
            where chiso.Gio = 1');
		return $this->_o_DB->fetchAll($sql);
	}	
	public function getEquipHoursMeasurePoints($eqIOID)
	{
		$sql = sprintf('
            select
                diemdo.*
            from ODanhSachDiemDo as diemdo
            inner join ODanhSachThietBi as dstb on dstb.IFID_M705 = diemdo.IFID_M705
            inner join OChiSoMayMoc as chiso on chiso.IOID = diemdo.Ref_ChiSo
            where dstb.IOID = %1$d and chiso.Gio = 1',$eqIOID);
		return $this->_o_DB->fetchOne($sql);
	}
}
