<?php

/**
 * Class Qss_Model_Maintenance_Equip_Moving - Dieu dong thiet bi
 * alias - OLichThietBi: dieudongtb
 *
 */
class Qss_Model_Maintenance_Equip_Moving extends Qss_Model_Abstract
{
    public $filter = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function getLastMoving($equipIOID)
    {
        $sql = sprintf('
            SELECT dieudongtb.*
            FROM OLichThietBi AS dieudongtb
            INNER JOIN ODanhSachDieuDongThietBi AS dsdieudongtb ON dieudongtb.IFID_M706 = dsdieudongtb.IFID_M706
            INNER JOIN qsiforms ON dieudongtb.IFID_M706 = qsiforms.IFID
            WHERE  dsdieudongtb.Ref_MaThietBi = %1$d AND qsiforms.Status IN (2,3)
            ORDER BY dieudongtb.NgayBatDau DESC
            LIMIT 1
        ', $equipIOID);
        return $this->_o_DB->fetchOne($sql);
    }
/**
	 * Lich su cai dat di chuyen thiet bi
	 * @param date $start ngay bat dau YYYY-mm-dd
	 * @param date $end ngay ket thuc YYYY-mm-dd 
	 * @param int $eqIOID IOID cua thiet bi
	 * @param int $locationIOID IOID cua khu vuc 
	 * @param int $eqGroupIOID IOID cua nhom thiet bi
	 * @param int $eqTypeIOID IOID cua loai thiet bi
	 * @return object danh sach cai dat thiet bi
	 */
	public function getInstallHistoryOfEquipmentByLocation(
		$start
		, $end
		, $eqIOID
		, $locationIOID
		, $eqGroupIOID
		, $eqTypeIOID
        , $projectIOID
        , $employeeIOID            
		, $selectAll = false)
	{
		$filter = Qss_Model_Extra_Sql::createInstance();
		$filterTime = $filter->getInDateRangeCond('ltb.NgayBatDau', 'ltb.NgayKetThuc', $start, $end);
		$filterTime = $filterTime?' and '.$filterTime:'';
		
		
		$filter->setCondWithCustomOperatorAndStrValRequire('TB.NgayDuaVaoSuDung', '<=', $end);
		//$filter->setEqualCondWithValIsZero('TB.NgungHoatDong');
        $filter->setCond(sprintf('TB.TrangThai not in (%1$s)', implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP)));
		$filter->setEqualCond('TB.Ref_NhomThietBi', $eqGroupIOID);
		$filter->setEqualCond('TB.Ref_LoaiThietBi', $eqTypeIOID);
        $filter->setEqualCond('CDTB.Ref_DuAn', $projectIOID);
		$filter->setEqualCond('CDTB.Ref_ThoVanHanh', $employeeIOID);        
		$filter->setEqualCond('TB.IOID', $eqIOID);
		$where = '';
		if($locationIOID)
		{
			$findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
			$findLoc    = $this->_o_DB->fetchOne($findLocSql);
			if($findLoc)
			{
				$where .= sprintf(' and Ref_MaKhuVuc in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in 
						(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))'
					, $findLoc->lft, $findLoc->rgt);
			}
		}
		$joinCaiDat = ($selectAll)?'left join':'inner join';
		$sql = sprintf('SELECT 
		TB.*
		, if( ifnull(CDTB.LichLamViec, \'\') = \'\', ifnull(TB.LichLamViec, \'\'), ifnull(CDTB.LichLamViec, \'\'))  AS CDLich
		, TB.LichLamViec AS TBLich
		,  TB.MaKhuVuc AS MaKVHienTai
		, ifnull(TB.Ref_MaKhuVuc,0) AS Ref_MaKVHienTai
		, IF(ifnull(CDTB.MaKhuVuc, \'\') = \'\', TB.MaKhuVuc, CDTB.MaKhuVuc) AS MaKVMoi
		, IF(ifnull(CDTB.Ref_MaKhuVuc,0) = 0, ifnull(TB.Ref_MaKhuVuc,0), ifnull(CDTB.Ref_MaKhuVuc,0)) AS Ref_MaKVMoi
		/*,ifnull(CDTB.ThoiGian, 0) as ThoiGian*/
		,ifnull(if(%3$s < TB.NgayDuaVaoSuDung,TB.NgayDuaVaoSuDung,CDTB.NgayBatDau), \'\') as NgayBatDau
		,ifnull(CDTB.NgayKetThuc, \'\') as NgayKetThuc
		, if(CDTB.IOID is null, TB.IOID, CDTB.Ref_MaThietBi) as Ref_MaThietBi
		, TB.IOID as TBIOID
        , CDTB.ThoVanHanh, CDTB.DuAn
        , ifnull(nhattrinh.SoGio, 0) AS SoGioHoatDong
		FROM ODanhSachThietBi AS TB 
		%4$s 
			(
				SELECT ltb.*
				FROM OLichThietBi AS ltb
				inner join qsiforms on qsiforms.IFID = ltb.IFID_M706
				WHERE ifnull(qsiforms.Status, 0) >= 2
				%1$s /* Loc theo mot khoang thoi gian */
				ORDER BY ltb.NgayBatDau ASC
			) as CDTB on TB.IOID = CDTB.Ref_MaThietBi 
        LEFT JOIN
            (
				SELECT sum(ifnull(ONhatTrinhThietBi.SoHoatDong, 0)) AS SoGio, Ref_MaTB
				FROM ONhatTrinhThietBi
				WHERE Ngay between %3$s and %5$s
				GROUP BY Ref_MaTB
            ) AS nhattrinh ON  TB.IOID = nhattrinh.Ref_MaTB
		LEFT JOIN OKhuVuc AS KV ON KV.IOID = CDTB.Ref_MaKhuVuc 
		%2$s /* Loc theo NgungHoatDong, khu vuc, nhom tb, loai tb va thiet bi*/
		ORDER BY TB.IOID, CDTB.NgayBatDau ASC
		', 
		$filterTime, 
		$where,
		$this->_o_DB->quote($start),
		$joinCaiDat,
        $this->_o_DB->quote($end));
//        echo '<pre>';
//      	echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}	
}
