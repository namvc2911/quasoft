<?php

class Qss_Model_Maintenance_Plan extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}

    public static function createInstance()
    {
        return new self();
    }

    public function getPlansForYearReportDataByLocOfTopEquips(
        $start = '', $end = '', $eqIOIDs = array(), $locIOID=0, $eqTypeIOID=0, $eqGroupIOID=0, $workcenter=0)
    {
        $where = '';
        if($start && $end) {
            $where .= sprintf(' and (ifnull(dstb.NgayDuaVaoSuDung, \'\') = \'\' OR dstb.NgayDuaVaoSuDung between %1$s and %2$s )'
                , $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }
        elseif($start) {
            $where .= sprintf(' and (ifnull(dstb.NgayDuaVaoSuDung, \'\') = \'\' OR   dstb.NgayDuaVaoSuDung >= %1$s  )'
                , $this->_o_DB->quote($start));
        }
        elseif($end) {
            $where .= sprintf(' and (ifnull(dstb.NgayDuaVaoSuDung, \'\') = \'\' OR   dstb.NgayDuaVaoSuDung <= %1$s  )'
                , $this->_o_DB->quote($end));
        }

        $where  .= (is_array($eqIOIDs) && count($eqIOIDs))?sprintf(' and dstb.IOID IN (%1$s) ', implode(', ', $eqIOIDs)):'';
        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $where  .= $locName?sprintf(' and dstb.Ref_MaKhuVuc IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)'
            , $locName->lft, $locName->rgt):'';
        $where  .= $eqGroupIOID?sprintf(' and dstb.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';
        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';
        $where  .= $eqTypes?sprintf(' and (dstb.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqTypes->lft, $eqTypes->rgt):'';
        $oWC     = $workcenter?$this->_o_DB->fetchOne(sprintf('SELECT * FROM ODonViSanXuat WHERE IOID = %1$d', $workcenter)):'';
        $where  .= ($oWC)?sprintf('and IFNULL(btdk.`Ref_DVBT`, 0) IN (SELECT IOID FROM ODonViSanXuat WHERE lft >= %1$d AND rgt <= %2$d)', $oWC->lft, $oWC->rgt):'';

        $sql = sprintf('
            SELECT
                btdk.IFID_M724 AS IFID,
                0 as MTIOID,
                null AS MaLoaiBaoTri,
                btdk.NgayBatDau,
                btdk.NgayKetThuc,
                ifnull(chuky.LapLai, 0) AS LapLai,
                btdk.Ref_MaThietBi,
                btdk.MaThietBi,
                btdk.TenThietBi,
                btdk.`Ref_DVBT`,
                btdk.DVBT AS TenDVBT,
                chuky.Ngay,
                chuky.Thang,
                ifnull(chuky.IOID, 0) AS CKIOID,
                ifnull(chuky.CanCu, 0) AS CanCu,
                ifnull(chuky.ChiSo, "") AS ChiSo,
                ifnull( chuky.Ref_ChiSo, 0) AS Ref_ChiSo,
                ifnull(chuky.GiaTri, 0) AS GiaTriChiSo,
                chuky.KyBaoDuong as MaKy,
                dstb.IOID AS LIOID /*dc.IOID AS LIOID Day chuyen = Thiet bi*/,
                dstb.Serial,
                dstb.MaTaiSan,
                ifnull(chuky.GiaTri, 0) AS GiaTri,
                KhuVucChaLonNhat.IOID AS EqGroupIOID,
                KhuVucChaLonNhat.Ten AS EqGroup,
                ltbtructhuoc.IOID AS EqTypeIOID,
                ltbtructhuoc.TenLoai AS EqType,
                (
                    SELECT
                        count(*)
                    FROM
                        OLoaiThietBi AS u
                    WHERE
                        u.lft <= ltbtructhuoc.lft
                    AND u.rgt >= ltbtructhuoc.rgt
                ) AS EqTypeLevel,
                ltb.IOID AS CurrentEqTypeIOID,
                dstb.lft AS EqLft,
                dstb.rgt AS EqRgt,
                dstb.TrangThai AS EqStatus
            FROM
                OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
            INNER JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
            INNER JOIN OKhuVuc AS kv1 ON kv1.IOID = dstb.Ref_MaKhuVuc
            INNER JOIN OKhuVuc As KhuVucChaLonNhat ON kv1.lft <= KhuVucChaLonNhat.lft AND kv1.rgt >= KhuVucChaLonNhat.rgt 
                AND IFNULL(KhuVucChaLonNhat.Ref_TrucThuoc, 0) = 0
            LEFT JOIN (
                SELECT
                    kv3.IOID,
                    lft,
                    rgt
                FROM
                    OKhuVuc AS kv3
                WHERE
                    kv3.NgungHoatDong = 1
            ) AS khuvucngunghoatdong ON kv1.lft >= khuvucngunghoatdong.lft
            AND kv1.rgt <= khuvucngunghoatdong.rgt
            LEFT JOIN OLoaiThietBi AS ltb ON dstb.Ref_LoaiThietBi = ltb.IOID
            LEFT JOIN OLoaiThietBi AS ltbtructhuoc ON ltb.lft >= ltbtructhuoc.lft
            AND ltb.rgt <= ltbtructhuoc.rgt
            WHERE
                khuvucngunghoatdong.IOID IS NULL
                AND ifnull(dstb.Ref_TrucThuoc, 0) = 0
                %1$s
            GROUP BY
                btdk.IOID,
                KhuVucChaLonNhat.IOID,
                ltbtructhuoc.lft ASC,
                dstb.LoaiThietBi,
                btdk.IOID,
                chuky.IOID
            ORDER BY
                KhuVucChaLonNhat.lft,
                KhuVucChaLonNhat.Ten,
                ltbtructhuoc.lft ASC,
                dstb.LoaiThietBi,
                dstb.MaThietBi,
                btdk.IOID,
                chuky.IOID,
                dstb.MaThietBi
            LIMIT 100000
        ', $where);

        //echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getPlansForYearReportDataOfTopEquips(
        $start = '', $end = '', $eqIOIDs = array(), $locIOID=0, $eqTypeIOID=0, $eqGroupIOID=0, $workcenter=0)
    {
        $where = '';
     	if($start && $end) {
            $where .= sprintf(' and (ifnull(dstb.NgayDuaVaoSuDung, \'\') = \'\' OR dstb.NgayDuaVaoSuDung between %1$s and %2$s )'
                , $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }
        elseif($start) {
            $where .= sprintf(' and (ifnull(dstb.NgayDuaVaoSuDung, \'\') = \'\' OR   dstb.NgayDuaVaoSuDung >= %1$s  )'
                , $this->_o_DB->quote($start));
        }
        elseif($end) {
            $where .= sprintf(' and (ifnull(dstb.NgayDuaVaoSuDung, \'\') = \'\' OR   dstb.NgayDuaVaoSuDung <= %1$s  )'
                , $this->_o_DB->quote($end));
        }

        $where  .= (is_array($eqIOIDs) && count($eqIOIDs))?sprintf(' and dstb.IOID IN (%1$s) ', implode(', ', $eqIOIDs)):'';
        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $where  .= $locName?sprintf(' and dstb.Ref_MaKhuVuc IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)'
            , $locName->lft, $locName->rgt):'';
        $where  .= $eqGroupIOID?sprintf(' and dstb.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';
        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';
        $where  .= $eqTypes?sprintf(' and (dstb.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqTypes->lft, $eqTypes->rgt):'';
        $oWC     = $workcenter?$this->_o_DB->fetchOne(sprintf('SELECT * FROM ODonViSanXuat WHERE IOID = %1$d', $workcenter)):'';
        $where  .= ($oWC)?sprintf('and IFNULL(btdk.`Ref_DVBT`, 0) IN (SELECT IOID FROM ODonViSanXuat WHERE lft >= %1$d AND rgt <= %2$d)', $oWC->lft, $oWC->rgt):'';

       $sql = sprintf('
            SELECT
                btdk.IFID_M724 AS IFID,
                0 as MTIOID,
                null AS MaLoaiBaoTri,
                btdk.NgayBatDau,
                btdk.NgayKetThuc,
                ifnull(chuky.LapLai, 0) AS LapLai,
                btdk.Ref_MaThietBi,
                btdk.MaThietBi,
                btdk.TenThietBi,
                btdk.`Ref_DVBT`,
                btdk.DVBT AS TenDVBT,
                chuky.Ngay,
                chuky.Thang,
                ifnull(chuky.IOID, 0) AS CKIOID,
                ifnull(chuky.CanCu, 0) AS CanCu,
                ifnull(chuky.ChiSo, "") AS ChiSo,
                ifnull( chuky.Ref_ChiSo, 0) AS Ref_ChiSo,
                ifnull(chuky.GiaTri, 0) AS GiaTriChiSo,
                chuky.KyBaoDuong as MaKy,
                dstb.IOID AS LIOID /*dc.IOID AS LIOID Day chuyen = Thiet bi*/,
                dstb.Serial,
                dstb.MaTaiSan,
                ifnull(chuky.GiaTri, 0) AS GiaTri,
                ntb.IOID AS EqGroupIOID,
                ntb.LoaiThietBi AS EqGroup,
                ltbtructhuoc.IOID AS EqTypeIOID,
                ltbtructhuoc.TenLoai AS EqType,
                (
                    SELECT
                        count(*)
                    FROM
                        OLoaiThietBi AS u
                    WHERE
                        u.lft <= ltbtructhuoc.lft
                    AND u.rgt >= ltbtructhuoc.rgt
                ) AS EqTypeLevel,
                ltb.IOID AS CurrentEqTypeIOID,
                dstb.lft AS EqLft,
                dstb.rgt AS EqRgt,
                dstb.TrangThai AS EqStatus
            FROM
                OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
            INNER JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
            INNER JOIN OKhuVuc AS kv1 ON kv1.IOID = dstb.Ref_MaKhuVuc
            LEFT JOIN (
                SELECT
                    kv3.IOID,
                    lft,
                    rgt
                FROM
                    OKhuVuc AS kv3
                WHERE
                    kv3.NgungHoatDong = 1
            ) AS khuvucngunghoatdong ON kv1.lft >= khuvucngunghoatdong.lft
            AND kv1.rgt <= khuvucngunghoatdong.rgt
            LEFT JOIN ONhomThietBi AS ntb ON dstb.Ref_NhomThietBi = ntb.IOID
            LEFT JOIN OLoaiThietBi AS ltb ON dstb.Ref_LoaiThietBi = ltb.IOID
            LEFT JOIN OLoaiThietBi AS ltbtructhuoc ON ltb.lft >= ltbtructhuoc.lft
            AND ltb.rgt <= ltbtructhuoc.rgt
            WHERE
                khuvucngunghoatdong.IOID IS NULL
                AND ifnull(dstb.Ref_TrucThuoc, 0) = 0
                %1$s
            GROUP BY
                btdk.IOID,
                dstb.NhomThietBi,
                ltbtructhuoc.lft ASC,
                dstb.LoaiThietBi,
                btdk.IOID,
                chuky.IOID
            ORDER BY
                ntb.lft,
                dstb.NhomThietBi,
                ltbtructhuoc.lft ASC,
                dstb.LoaiThietBi,
                dstb.MaThietBi,
                btdk.IOID,
                chuky.IOID,
                dstb.MaThietBi
            LIMIT 100000
        ', $where);

        //echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getPlansForYearReportDataOfBelowTopEquips()
    {
        $sql = sprintf('
            SELECT
                btdk.IFID_M724 AS IFID,
                0 as MTIOID,
                null AS MaLoaiBaoTri,
                btdk.NgayBatDau,
                btdk.NgayKetThuc,
                ifnull(chuky.LapLai, 0) AS LapLai,
                btdk.Ref_MaThietBi,
                btdk.MaThietBi,
                btdk.TenThietBi,
                btdk.`Ref_DVBT`,
                btdk.DVBT AS TenDVBT,
                chuky.Ngay,
                chuky.Thang,
                ifnull(chuky.IOID, 0) AS CKIOID,
                ifnull(chuky.CanCu, 0) AS CanCu,
                ifnull(chuky.ChiSo, "") AS ChiSo,
                ifnull( chuky.Ref_ChiSo, 0) AS Ref_ChiSo,
                ifnull(chuky.GiaTri, 0) AS GiaTriChiSo,
                chuky.KyBaoDuong as MaKy,
                dstb.IOID AS LIOID /*dc.IOID AS LIOID Day chuyen = Thiet bi*/,
                dstb.Serial,
                dstb.MaTaiSan,
                ifnull(chuky.GiaTri, 0) AS GiaTri,
                ntb.IOID AS EqGroupIOID,
                ntb.LoaiThietBi AS EqGroup,
                dstb.Ref_LoaiThietBi AS EqTypeIOID,
                dstb.LoaiThietBi AS EqType,
                dstb.lft AS EqLft,
                dstb.rgt AS EqRgt,
                dstb.TrangThai AS EqStatus
            FROM
                OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
            INNER JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
            INNER JOIN OKhuVuc AS kv1 ON kv1.IOID = dstb.Ref_MaKhuVuc
            LEFT JOIN ONhomThietBi AS ntb ON dstb.Ref_NhomThietBi = ntb.IOID
            WHERE
                ifnull(dstb.Ref_TrucThuoc, 0) != 0
            GROUP BY
                btdk.IOID,
                chuky.IOID
            ORDER BY
                dstb.lft
            LIMIT 100000
        ');

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

	public function getTasksByPlanIFID($planIFIDArr = array())
	{
		$planIFIDArr[] = 0;
		$sql = sprintf('SELECT					
				cvbt.IFID_M724 AS IFID
				, cvbt.*						
				, cvbt.MoTa AS MoTaCongViec
                , plcv.Ten AS CongViec
				, NULL AS GhiChuCongViec
				, NULL AS Dat			
				, NULL AS DanhGia
				, nv.TenNhanVien as NguoiThucHien
			FROM OCongViecBT AS cvbt
			INNER JOIN OBaoTriDinhKy as dk on dk.IFID_M724 = cvbt.IFID_M724
			LEFT JOIN ODanhSachThietBi as tb on tb.IOID = dk.Ref_MaThietBi
			LEFT JOIN ODanhSachNhanVien as nv on nv.IOID = tb.Ref_QuanLy
			LEFT JOIN OCongViecBaoTri as plcv ON ifnull(cvbt.Ref_Ten,0) = plcv.IOID
			WHERE cvbt.IFID_M724 in (%1$s)
			ORDER BY cvbt.IFID_M724, cvbt.Ref_ViTri
			LIMIT 100000
		', implode(', ', $planIFIDArr));
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getMaterialsByPlanIFIDGroupByPosition($planIFIDArr = array())
	{
		$planIFIDArr[] = 0;
		$sql = sprintf('
			SELECT
				vt.IFID_M724 AS IFID
				, vt.BoPhan
				, vt.ViTri
				, vt.Ref_ViTri
				, group_concat(DISTINCT concat(vt.MaVatTu,\': \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu
                , group_concat(DISTINCT concat(vt.TenVatTu,\' (\', vt.MaVatTu ,\') : \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu2
			FROM OVatTu AS vt 
			WHERE
				vt.IFID_M724 in (%1$s)
			GROUP BY
				vt.IFID_M724
			ORDER BY
				vt.IFID_M724, vt.Ref_ViTri
			LIMIT 100000
		', implode(', ', $planIFIDArr));
		return $this->_o_DB->fetchAll($sql);
	}

    /**
     * @param int $locID
     * @param int $eqTypeID
     * @param int $eqGroupID
     * @param int $eqID
     * @param string $date
     * @return mixed Hàm trả về các kế hoạch gồm nhiều dòng công việc, mỗi công việc lại có nhiều dòng vật tư theo công
     * việc đi kèm
     */
	public function getPlanConfigs($locID = 0, $eqTypeID = 0, $eqGroupID = 0, $eqID = 0, $date = '')
	{
	    $extSelect = '';
	    $innerJoin = '';
	    $leftJoin  = '';
		$where     = '';
		$where    .= ($eqID) ? sprintf(' and IFNULL(ThietBi.IOID, 0) = %1$d', $eqID) : '';
        $where    .= ($eqGroupID) ? sprintf(' and IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d', $eqGroupID) : '';

        $objLoc    = $locID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locID)):'';
        if($objLoc) {
            $tmp   = ' and ( IFNULL(ThietBi.Ref_MaKhuVuc, 0) in ';
            $tmp  .= sprintf(' (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $objLoc->lft, $objLoc->rgt);
            $tmp  .= sprintf(' or IFNULL(KeHoach.Ref_MaKhuVuc, 0) IN ');
            $tmp  .= sprintf(' (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $objLoc->lft, $objLoc->rgt);
            $tmp  .= ' )';
            $where.= $tmp;
        }

        $objEqType = $eqTypeID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eqTypeID)):'';
        if($objEqType) {
            $tmp   = ' and IFNULL(ThietBi.Ref_LoaiThietBi, 0) in ';
            $tmp  .= sprintf('(select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)', $objEqType->lft, $objEqType->rgt);
            $where.= $tmp;
        }

        if($date) {

            $mDate      = date_create($date);
            $extSelect .= sprintf(', %1$s AS NgayBaoTri ', $this->_o_DB->quote($date));
            $innerJoin .= sprintf('INNER JOIN OChuKyBaoTri AS ChuKy ON KeHoach.IFID_M724 = ChuKy.IFID_M724').PHP_EOL;
            $leftJoin  .= '
                LEFT JOIN ODanhSachDiemDo as KiemTra on KiemTra.IFID_M705 = ThietBi.IFID_M705
                AND IFNULL(ChuKy.CanCu, 0) IN (1,2)  -- Phai la loai dinh ky chi so moi join
                AND IFNULL(ChuKy.Ref_ChiSo, 0) = IFNULL(KiemTra.Ref_ChiSo, 0) -- Phai do cung 1 loai chi so  moi join
                AND IFNULL(KeHoach.Ref_BoPhan, 0) = IFNULL(KiemTra.Ref_BoPhan, 0) -- Phai cung 1 bo phan moi join
                AND IFNULL(ChuKy.GiaTri, 0) > 0 -- Chỉ số phải có giá trị mới join
            '.PHP_EOL;

            $where .= sprintf('
            AND (
                (
                    ifnull(ChuKy.CanCu,0) = 0
                    AND ( 
                        (
                            ChuKy.KyBaoDuong = "D" 
                            AND IFNULL(TIMESTAMPDIFF(DAY, KeHoach.NgayBatDau , %1$s) %% ChuKy.LapLai,0) = 0
                        )
                        OR (
                            ChuKy.KyBaoDuong = \'W\' 
                            AND ChuKy.Thu = %2$d
                            AND IFNULL(TIMESTAMPDIFF(WEEK, KeHoach.NgayBatDau ,%1$s) %% ChuKy.LapLai,0) = 0
                        )
                        OR (
                            ChuKy.KyBaoDuong = \'M\' 
                            AND (ChuKy.Ngay = %3$d or (LAST_DAY(%1$s) = %1$s and ChuKy.Ngay > %3$d))
                            AND IFNULL(TIMESTAMPDIFF(MONTH, date_add(KeHoach.NgayBatDau,INTERVAL -day(KeHoach.NgayBatDau) DAY) ,%1$s) 
                                %% ChuKy.LapLai,0) = 0
                        )
                        OR (
                            ChuKy.KyBaoDuong = \'Y\' 
                            AND (ChuKy.Ngay = %3$d or (LAST_DAY(%1$s) = %1$s and ChuKy.Ngay > %3$d))
                            AND ChuKy.Thang = %4$d
                            AND IFNULL(TIMESTAMPDIFF(YEAR, date_add(KeHoach.NgayBatDau,INTERVAL -day(KeHoach.NgayBatDau) DAY) ,%1$s) 
                                %% ChuKy.LapLai,0) = 0
                        )
                    )
                )
                OR
                (
                    ChuKy.CanCu in(1,2)
                    AND IFNULL(
                        (
                            select sum(SoHoatDong)
                            from ONhatTrinhThietBi
                            where Ref_MaTB = KeHoach.Ref_MaThietBi
                            and ifnull(Ref_DiemDo,0) = ifnull(ChuKy.Ref_ChiSo,0)
                            and Ngay >= ifnull(
                                (
                                    select NgayYeuCau
                                    from OPhieuBaoTri as lastin
                                    where lastin.Ref_MoTa = KeHoach.IOID AND lastin.Ref_ChuKy = ChuKy.IOID
                                    order by NgayYeuCau DESC LIMIT 1
                                ),KeHoach.NgayBatDau
                            )),0
                    )                    
                    + (DATEDIFF(%1$s,now()) * ifnull(KiemTra.SoHoatDongNgay,0)) >= ChuKy.GiaTri
                    
                    AND ifnull(
                        (
                            select sum(SoHoatDong)
                            from ONhatTrinhThietBi
                            where Ref_MaTB = KeHoach.Ref_MaThietBi
                                and ifnull(Ref_DiemDo,0) = ifnull(ChuKy.Ref_ChiSo,0)
                                and Ngay >= ifnull(
                                    (select NgayYeuCau
                                    from OPhieuBaoTri as lastin
                                    where lastin.Ref_MoTa = KeHoach.IOID AND lastin.Ref_ChuKy = ChuKy.IOID
                                    order by NgayYeuCau DESC LIMIT 1
                                ),KeHoach.NgayBatDau)
                        ),0
                    )
                    + (DATEDIFF(%1$s,now()) * ifnull(KiemTra.SoHoatDongNgay,0)) %% ChuKy.GiaTri < KiemTra.SoHoatDongNgay
                )
            )
            AND (%1$s >= KeHoach.NgayBatDau OR IFNULL(KeHoach.NgayBatDau, "") = "")
            AND (%1$s <= KeHoach.NgayKetThuc OR IFNULL(KeHoach.NgayKetThuc, "") = "")
            ', $this->_o_DB->quote($date), $mDate->format('w'), $mDate->format('d'), $mDate->format('m'));
        }

		$sql = sprintf('
            SELECT                 
                KeHoach.*, CongViec.*, VatTu.*, KeHoach.IFID_M724 AS IFID
                , KeHoach.MoTa AS MoTaKeHoach, CongViec.MoTa AS MoTaCongViec    
                %4$s
            FROM OBaoTriDinhKy AS KeHoach
            INNER JOIN OCongViecBT As CongViec ON CongViec.IFID_M724 = KeHoach.IFID_M724
            %2$s
            LEFT JOIN OVatTu AS VatTu ON VatTu.IFID_M724 = KeHoach.IFID_M724 AND CongViec.IOID = VatTu.Ref_CongViec
            LEFT JOIN ODanhSachThietBi AS ThietBi ON ThietBi.IOID = KeHoach.Ref_MaThietBi
            %3$s
            WHERE KeHoach.DeptID IN (%5$s)  AND ifnull(ThietBi.TrangThai, 0) = 0 %1$s
            ORDER BY KeHoach.Ref_MaThietBi, KeHoach.IFID_M724, CongViec.Ref_ViTri, CongViec.IOID
            ', $where, $innerJoin, $leftJoin, $extSelect, $this->_user->user_dept_list);
        // echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * 
	 * @param unknown $equipIOIDArr
	 * @param unknown $maintainType array('SUCO', 'DINHKY')
	 * @param unknown $maintypeIOIDArr
	 */
	public function getMaintainStatus($locIOID = 0, $eqTypeIOID = 0, $eqGroupIOID = 0,$priority = 0)
	{
	    $where    = '';
	// LOCATION FILTER
	    if($locIOID)
	    {
            $findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
            $findLoc    = $this->_o_DB->fetchOne($findLocSql);
            	
            if($findLoc){
                $where .= sprintf(' and dstb.Ref_MaKhuVuc in
					(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)'
                    , $findLoc->lft, $findLoc->rgt);
            }
	    }
		if($priority)
	    {
            $where .= sprintf(' and btdk.Ref_MucDoUuTien = %1$d', $priority);
	    }
	    // EQUIP GROUP
	    if($eqGroupIOID)
	    {
            $where .= sprintf(' and dstb.Ref_NhomThietBi = %1$d', $eqGroupIOID);
	    }
	    
	    // EQUIP TYPE
	    if($eqTypeIOID)
	    {
	        $where .= sprintf(' and dstb.Ref_LoaiThietBi = %1$d', $eqTypeIOID);
	    }
	    
	    $sql = sprintf('
            SELECT
                btdk.*
                , dstb.*
                , pbt.Ngay as NgayYeuCau
                ,case when ifnull(chuky.CanCu,0) = 0 then
                	case 
                		when chuky.KyBaoDuong = \'D\' then 1
                		when chuky.KyBaoDuong = \'W\' then 7
                		when chuky.KyBaoDuong = \'M\' then 30
                		when chuky.KyBaoDuong = \'Y\' then 365 
                	end
                	* 
                	chuky.LapLai
                else
                	chuky.GiaTri / diemdo.SoHoatDongNgay
                end as khoangcach
               ,DATEDIFF(now(),btdk.NgayBatDau) as khoangcachbatdau
               ,if(pbt.NgayYeuCau is null or pbt.NgayYeuCau = \'0000-00-00\',-1,DATEDIFF(now(),pbt.NgayYeuCau)) as khoangcachthucte
               , (select group_concat(concat(cv.MoTa,\' \',cv.BoPhan) SEPARATOR \'\n\') from OCongViecBT as cv where cv.IFID_M724 = btdk.IFID_M724) as CongViec
            FROM OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri as chuky on chuky.IFID_M724 = btdk.IFID_M724
            INNER JOIN ODanhSachThietBi as dstb on dstb.IOID = btdk.Ref_MaThietBi
            LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = dstb.IFID_M705
            	AND IFNULL(chuky.CanCu, 0) IN (1,2)  -- Phai la loai dinh ky chi so moi join
                  AND IFNULL(chuky.Ref_ChiSo, 0) = diemdo.IOID -- Phai do cung 1 loai chi so  moi join
                  AND IFNULL(btdk.Ref_BoPhan, 0) = IFNULL(diemdo.Ref_BoPhan, 0) -- Phai cung 1 bo phan moi join 
                  AND IFNULL(chuky.GiaTri, 0) > 0 -- Chỉ số phải có giá trị mới join        
	        LEFT JOIN (select t.* from (select * from OPhieuBaoTri as sub
            	order by 
            	ifnull(sub.Ref_MaThietBi,0),
            	ifnull(sub.Ref_BoPhan,0),
            	ifnull(sub.Ref_LoaiBaoTri,0),
            	ifnull(sub.Ref_ChuKy,0),
            	Ngay desc limit 99999999) as t
            	inner join qsiforms on qsiforms.IFID = t.IFID_M759
            	where Status IN (3, 4)
            	group by 
            	ifnull(t.Ref_MaThietBi,0),
            	ifnull(t.Ref_BoPhan,0),
            	ifnull(t.Ref_LoaiBaoTri,0),
            	ifnull(t.Ref_ChuKy,0)) as pbt
            	on pbt.Ref_MoTa  = btdk.IOID
            	and pbt.Ref_ChuKy = chuky.IOID
	        WHERE 1=1
            %1$s 
            ORDER BY dstb.MaKhuVuc,btdk.Ref_MaThietBi,btdk.Ref_BoPhan'
	        , $where);
	        //\echo '<pre>';echo $sql;die;
	    return $this->_o_DB->fetchAll($sql);	    
	}
	
	public function getMaterialsByPlanIFID($planIFIDArr)
	{
        $planIFIDArr[] = 0;

	    $sql = sprintf('
			SELECT vt.*, vt.IFID_M724 AS IFID, sp.Anh AS `Image`	
	        FROM OVatTu AS vt         
	        LEFT JOIN OSanPham AS sp ON vt.Ref_MaVatTu = sp.IOID
			WHERE vt.IFID_M724 in (%1$s)
			ORDER BY vt.IFID_M724, vt.Ref_ViTri
			LIMIT 100000
		', implode(', ', $planIFIDArr));
        // echo '<pre>'; print_r($sql); die;
	    return $this->_o_DB->fetchAll($sql);
	}

	public function getPlansByDate($filterdate,$locIOID = 0,$dvbt = 0,$maintainType = 0, $eqGroupIOID=0, $eqTypeIOID=0, $eqIOID=0, $removeCreated=true)
    {
        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';
    	$date    = date_create($filterdate);

        // Filter
        $where   = '';
    	$where  .= $locName?sprintf('
            AND IFNULL(dstb.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)'
            , $locName->lft, $locName->rgt):'';
        $where  .= $eqTypes?sprintf('
            and dstb.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d) '
            ,$eqTypes->lft, $eqTypes->rgt):'';
        $where  .= $dvbt?sprintf(' and btdk.Ref_DVBT = %1$d ', $dvbt):'';
        $where  .= $eqGroupIOID?sprintf(' and dstb.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';
        $where  .= $eqIOID?sprintf(' and dstb.IOID = %1$d ', $eqIOID):'';

        if($removeCreated) {
            $where  .= ' AND pbt.IOID is null';
        }

//        if(Qss_Lib_System::formSecure('M125'))
//        {
//            $where .= sprintf('
//                AND btdk.Ref_DVBT in (
//                SELECT IOID FROM ODonViSanXuat
//                inner join qsrecordrights on ODonViSanXuat.IFID_M125 = qsrecordrights.IFID
//                WHERE UID = %1$d)
//            ', $this->_user->user_id);
//        }

        if(Qss_Lib_System::formSecure('M720'))
        {
            $where .= sprintf(' 
			    AND (
                    IFNULL(btdk.Ref_MaThietBi, 0) IN (
                        SELECT ODanhSachThietBi.IOID
                        FROM ODanhSachThietBi 
                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt			 
                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID 
                        WHERE UID = %1$d
                    )
                    OR IFNULL(btdk.Ref_MaThietBi, 0) = 0
                )
			',$this->_user->user_id);
        }

        $sql = sprintf('
            SELECT
	             dstb.*, chuky.*, btdk.*
	             , btdk.IOID AS PlanIOID
	            , ifnull(btdk.SoPhut, 0) AS XuLy
	            , kv1.IOID AS Ref_KhuVuc, dstb.MaKhuVuc AS MaKhuVucTheoDS, kv1.Ten AS TenKhuVucTheoDS
	            , chuky.IOID as ChuKyIOID
	            , pbt.SoPhieu
	            , pbt.NgayYeuCau as NgayYCBT
	            , TongThe.IOID AS GeneralPlanIOID -- Ke hoach tong the
	            , TongThe.Ma AS GeneralPlanNo
	            , ChiTietTongThe.IOID AS GeneralPlanDetailIOID -- Ket noi voi chi tiet mot ke hoach
	            , btdk.MoTa AS MoTaKeHoachBaoTri
            FROM OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
            LEFT JOIN ODanhSachThietBi AS dstb on dstb.`IOID` = btdk.`Ref_MaThietBi`
            LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = dstb.IFID_M705                   
                  AND IFNULL(chuky.CanCu, 0) IN (1,2)  -- Phai la loai dinh ky chi so moi join
                  AND IFNULL(chuky.Ref_ChiSo, 0) = diemdo.IOID -- Phai do cung 1 loai chi so  moi join
                  AND IFNULL(btdk.Ref_BoPhan, 0) = IFNULL(diemdo.Ref_BoPhan, 0) -- Phai cung 1 bo phan moi join 
                  AND IFNULL(chuky.GiaTri, 0) > 0 -- Chỉ số phải có giá trị mới join
            LEFT JOIN OKhuVuc as kv1 on kv1.IOID = dstb.Ref_MaKhuVuc
            LEFT JOIN OPhieuBaoTri AS pbt ON            
                pbt.MoTa = btdk.MoTa
                AND pbt.Ref_ChuKy = chuky.IOID 
                AND ((chuky.KyBaoDuong = \'D\' AND ((chuky.LapLai = 1 and pbt.NgayYeuCau = %1$s) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 DAY) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 DAY))))
                    or (chuky.KyBaoDuong = \'W\' AND ((chuky.LapLai = 1 and %5$d = WEEK(pbt.NgayYeuCau, 3) AND  %7$d = YEAR(pbt.NgayYeuCau)) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 WEEK) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 WEEK))))
                    or (chuky.KyBaoDuong = \'M\' AND ((chuky.LapLai = 1 and %6$d = MONTH(pbt.NgayYeuCau) AND  %7$d = YEAR(pbt.NgayYeuCau)) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 MONTH) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 MONTH))))
                    or (chuky.KyBaoDuong = \'Y\' AND  ((chuky.LapLai = 1 and %7$d = YEAR(pbt.NgayYeuCau)) or  (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 YEAR) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 YEAR)))))
            LEFT JOIN OKeHoachBaoTri AS ChiTietTongThe ON 
                ChiTietTongThe.MoTa = btdk.MoTa
                AND ChiTietTongThe.Ref_ChuKy = chuky.IOID                 
                AND ((chuky.KyBaoDuong = \'D\' AND ((chuky.LapLai = 1 and ChiTietTongThe.NgayBatDau = %1$s) or (chuky.LapLai > 1 and ChiTietTongThe.NgayBatDau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 DAY) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 DAY))))
                    or (chuky.KyBaoDuong = \'W\' AND ((chuky.LapLai = 1 and %5$d = WEEK(ChiTietTongThe.NgayBatDau, 3) AND  %7$d = YEAR(ChiTietTongThe.NgayBatDau)) or (chuky.LapLai > 1 and ChiTietTongThe.NgayBatDau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 WEEK) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 WEEK))))
                    or (chuky.KyBaoDuong = \'M\' AND ((chuky.LapLai = 1 and %6$d = MONTH(ChiTietTongThe.NgayBatDau) AND  %7$d = YEAR(ChiTietTongThe.NgayBatDau)) or (chuky.LapLai > 1 and ChiTietTongThe.NgayBatDau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 MONTH) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 MONTH))))
                    or (chuky.KyBaoDuong = \'Y\' AND  ((chuky.LapLai = 1 and %7$d = YEAR(ChiTietTongThe.NgayBatDau)) or  (chuky.LapLai > 1 and ChiTietTongThe.NgayBatDau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 YEAR) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 YEAR)))))  
            LEFT JOIN OKeHoachTongThe AS TongThe ON IFNULL(ChiTietTongThe.Ref_KeHoachTongThe, 0) = TongThe.IOID
            LEFT JOIN (SELECT kv3.IOID, lft, rgt FROM OKhuVuc AS kv3 WHERE kv3.NgungHoatDong = 1)
                as khuvucngunghoatdong on kv1.lft >= khuvucngunghoatdong.lft and kv1.rgt <= khuvucngunghoatdong.rgt
            WHERE
                khuvucngunghoatdong.IOID is null
                AND ifnull(dstb.TrangThai , 0) = 0 AND btdk.DeptID in (%3$s) AND ifnull(btdk.NgungHoatDong,0) = 0
            	AND (( ifnull(chuky.CanCu,0) =0
                        
                        AND (
                        (chuky.KyBaoDuong = \'D\' AND IFNULL(TIMESTAMPDIFF(DAY, btdk.NgayBatDau ,%1$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'W\' AND chuky.Thu =%8$d
                            AND IFNULL(TIMESTAMPDIFF(WEEK, btdk.NgayBatDau ,%1$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'M\' AND (chuky.Ngay =%4$d or (LAST_DAY(%1$s) = %1$s and chuky.Ngay > %4$d))
                            AND IFNULL(TIMESTAMPDIFF(MONTH, date_add(btdk.NgayBatDau,INTERVAL -day(btdk.NgayBatDau) DAY) ,%1$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'Y\' AND (chuky.Ngay =%4$d or (LAST_DAY(%1$s) = %1$s and chuky.Ngay > %4$d))
                            AND chuky.Thang =%6$d
                            AND IFNULL(TIMESTAMPDIFF(YEAR, date_add(btdk.NgayBatDau,INTERVAL -day(btdk.NgayBatDau) DAY) ,%1$s) %% chuky.LapLai,0) = 0))
                    ) OR (
                            chuky.CanCu in(1,2)
                            AND
                            ifnull((
                                select sum(SoHoatDong)
                                from ONhatTrinhThietBi
                                where Ref_MaTB = btdk.Ref_MaThietBi
                                    and ifnull(Ref_DiemDo,0) = ifnull(chuky.Ref_ChiSo,0)
                                    and Ngay >= ifnull(
                                        (select NgayYeuCau
                                        from OPhieuBaoTri as lastin
                                        where 
                                            lastin.Ref_MoTa = btdk.IOID
                                            AND lastin.Ref_ChuKy = chuky.IOID
                                            
                                        order by NgayYeuCau DESC LIMIT 1)
                                    ,btdk.NgayBatDau)
                            ),0)
                            + (DATEDIFF(%1$s,now()) * ifnull(diemdo.SoHoatDongNgay,0)) >= chuky.GiaTri
                            AND
                            ifnull((
                                select sum(SoHoatDong)
                                from ONhatTrinhThietBi
                                where Ref_MaTB = btdk.Ref_MaThietBi
                                    and ifnull(Ref_DiemDo,0) = ifnull(chuky.Ref_ChiSo,0)
                                    and Ngay >= ifnull(
                                        (select NgayYeuCau
                                        from OPhieuBaoTri as lastin
                                        where 
                                            lastin.Ref_MoTa = btdk.IOID
                                            AND lastin.Ref_ChuKy = chuky.IOID
                                            
                                        order by NgayYeuCau DESC LIMIT 1)
                                    ,btdk.NgayBatDau)
                            ),0)
                            + (DATEDIFF(%1$s,now()) * ifnull(diemdo.SoHoatDongNgay,0)) %% chuky.GiaTri < diemdo.SoHoatDongNgay
                		)
		            )
	            AND (%1$s >= btdk.NgayBatDau OR IFNULL(btdk.NgayBatDau,\'\') = \'\')
	            AND (%1$s <= btdk.NgayKetThuc OR IFNULL(btdk.NgayKetThuc,\'\') = \'\')
                %2$s	        
            '
            , $this->_o_DB->quote($filterdate)
            , $where
            , $this->_user->user_dept_id . ',' . $this->_user->user_dept_list
            , $date->format('d')
            , $date->format('W')
            , $date->format('m')
            , $date->format('Y')
            , $date->format('w')
            , $this->_user->user_id);
            // echo '<pre>'; echo $sql;die;

        $sql .= ' ORDER BY ';

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'STT')) {
            $sql .= ' dstb.STT,  ';
        }

        $sql .= ' case when ifnull(btdk.`Ref_DVBT`, 0) = 0 then 1000000 else btdk.`Ref_DVBT` end, btdk.MaThietBi';
        $sql .= ' LIMIT 10000';
        return $this->_o_DB->fetchAll($sql);
    }

    public function getAllPlansByDate($filterdate,$locIOID = 0,$dvbt = 0, $eqGroupIOID=0, $eqTypeIOID=0, $eqIOID=0, $all = true)//lấy cả kế hoạch đã tạo phiếu và dừng hoạt động
    {
        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';
    	$date    = date_create($filterdate);

        // Filter
        $where   = '';
    	$where  .= $locName?sprintf('
            AND IFNULL(dstb.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)'
            , $locName->lft, $locName->rgt):'';
        $where  .= $eqTypes?sprintf('
            and IFNULL(dstb.Ref_LoaiThietBi, 0) IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d) '
            ,$eqTypes->lft, $eqTypes->rgt):'';
        $where  .= $dvbt?sprintf(' and IFNULL(btdk.Ref_DVBT, 0) = %1$d ', $dvbt):'';
        $where  .= $eqGroupIOID?sprintf(' and IFNULL(dstb.Ref_NhomThietBi, 0) = %1$d ', $eqGroupIOID):'';
        $where  .= $eqIOID?sprintf(' and IFNULL(dstb.IOID, 0) = %1$d ', $eqIOID):'';


        if(Qss_Lib_System::formSecure('M720'))
        {
            $where .= sprintf(' 
			    AND (
                    IFNULL(btdk.Ref_MaThietBi, 0) IN (
                        SELECT ODanhSachThietBi.IOID
                        FROM ODanhSachThietBi 
                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt			 
                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID 
                        WHERE UID = %1$d
                    )
                    OR IFNULL(btdk.Ref_MaThietBi, 0) = 0
                )
			',$this->_user->user_id);
        }

        if(Qss_Lib_System::formSecure('M125'))
        {
            $where .= sprintf('
	            AND (btdk.Ref_DVBT in (
	                SELECT IOID FROM ODonViSanXuat
                    inner join qsrecordrights on ODonViSanXuat.IFID_M125 = qsrecordrights.IFID
                    WHERE UID = %1$d) OR IFNULL(btdk.Ref_DVBT, 0) = 0)
            ', $this->_user->user_id);
        }

        $sql = sprintf('
            SELECT
	            btdk.*, dstb.*, chuky.*
	            , ifnull(btdk.SoPhut, 0) AS XuLy
	            , kv1.IOID AS Ref_KhuVuc, dstb.MaKhuVuc AS MaKhuVucTheoDS, kv1.Ten AS TenKhuVucTheoDS
	            , kv1.lft AS lft_KhuVuc, kv1.rgt AS rgt_KhuVuc
	            , chuky.IOID as ChuKyIOID
	            , pbt.SoPhieu
	            , pbt.NgayYeuCau as NgayYCBT
	            , pbt.NgayBatDau AS NgayBatDauBaoTri
	            , khuvucngunghoatdong.IOID as khuvuchoatdong
	            , pbt.IOID as PBT
            FROM OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
            LEFT JOIN ODanhSachThietBi AS dstb on dstb.`IOID` = btdk.`Ref_MaThietBi`
            LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = dstb.IFID_M705
            	AND IFNULL(chuky.CanCu, 0) IN (1,2)  -- Phai la loai dinh ky chi so moi join
                  AND IFNULL(chuky.Ref_ChiSo, 0) = diemdo.IOID -- Phai do cung 1 loai chi so  moi join
                  AND IFNULL(btdk.Ref_BoPhan, 0) = IFNULL(diemdo.Ref_BoPhan, 0) -- Phai cung 1 bo phan moi join 
                  AND IFNULL(chuky.GiaTri, 0) > 0 -- Chỉ số phải có giá trị mới join
            LEFT JOIN OKhuVuc as kv1 on kv1.IOID = dstb.Ref_MaKhuVuc
            LEFT JOIN OPhieuBaoTri AS pbt ON 
                pbt.Ref_MoTa = btdk.IOID
                AND pbt.Ref_ChuKy = chuky.IOID 
                AND ((chuky.KyBaoDuong = \'D\' AND ((chuky.LapLai = 1 and pbt.NgayYeuCau = %1$s) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 DAY) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 DAY))))
                    or (chuky.KyBaoDuong = \'W\' AND ((chuky.LapLai = 1 and %5$d = WEEK(pbt.NgayYeuCau, 3) AND  %7$d = YEAR(pbt.NgayYeuCau)) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 WEEK) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 WEEK))))
                    or (chuky.KyBaoDuong = \'M\' AND ((chuky.LapLai = 1 and %6$d = MONTH(pbt.NgayYeuCau) AND  %7$d = YEAR(pbt.NgayYeuCau)) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 MONTH) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 MONTH))))
                    or (chuky.KyBaoDuong = \'Y\' AND  ((chuky.LapLai = 1 and %7$d = YEAR(pbt.NgayYeuCau)) or  (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%1$s, INTERVAL -(chuky.LapLai-1)/2 YEAR) and DATE_ADD(%1$s, INTERVAL (chuky.LapLai-1)/2 YEAR)))))
            LEFT JOIN (SELECT kv3.IOID, lft, rgt FROM OKhuVuc AS kv3 WHERE kv3.NgungHoatDong = 1)
                as khuvucngunghoatdong on kv1.lft >= khuvucngunghoatdong.lft and kv1.rgt <= khuvucngunghoatdong.rgt
            WHERE
                ifnull(dstb.TrangThai , 0) = 0 AND btdk.DeptID in (%3$s)
            	AND (( ifnull(chuky.CanCu,0) =0
                        AND (
                        (chuky.KyBaoDuong = \'D\' AND IFNULL(TIMESTAMPDIFF(DAY, btdk.NgayBatDau ,%1$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'W\' AND chuky.Thu =%8$d
                            AND IFNULL(TIMESTAMPDIFF(WEEK, btdk.NgayBatDau ,%1$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'M\' AND (chuky.Ngay =%4$d or (LAST_DAY(%1$s) = %1$s and chuky.Ngay > %4$d))
                            AND IFNULL(TIMESTAMPDIFF(MONTH, date_add(btdk.NgayBatDau,INTERVAL -day(btdk.NgayBatDau) DAY),%1$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'Y\' AND (chuky.Ngay =%4$d or (LAST_DAY(%1$s) = %1$s and chuky.Ngay > %4$d))
                            AND chuky.Thang =%6$d
                            AND IFNULL(TIMESTAMPDIFF(YEAR, date_add(btdk.NgayBatDau,INTERVAL -day(btdk.NgayBatDau) DAY) ,%1$s) %% chuky.LapLai,0) = 0))
                    ) OR (
                            chuky.CanCu in(1,2)
                            AND
                            ifnull((
                                select sum(SoHoatDong)
                                from ONhatTrinhThietBi
                                where Ref_MaTB = btdk.Ref_MaThietBi
                                    and ifnull(Ref_DiemDo,0) = ifnull(chuky.Ref_ChiSo,0)
                                    and Ngay >= ifnull(
                                        (select NgayYeuCau
                                        from OPhieuBaoTri as lastin
                                        where 
                                            lastin.Ref_MoTa = btdk.IOID
                                            AND lastin.Ref_ChuKy = chuky.IOID
                                            
                                        order by NgayYeuCau DESC LIMIT 1)
                                    ,btdk.NgayBatDau)
                            ),0)
                            + (DATEDIFF(%1$s,now()) * ifnull(diemdo.SoHoatDongNgay,0)) >= chuky.GiaTri
                            AND
                            ifnull((
                                select sum(SoHoatDong)
                                from ONhatTrinhThietBi
                                where Ref_MaTB = btdk.Ref_MaThietBi
                                    and ifnull(Ref_DiemDo,0) = ifnull(chuky.Ref_ChiSo,0)
                                    and Ngay >= ifnull(
                                        (select NgayYeuCau
                                        from OPhieuBaoTri as lastin
                                        where 
                                            lastin.Ref_MoTa = btdk.IOID
                                            AND lastin.Ref_ChuKy = chuky.IOID
                                            
                                        order by NgayYeuCau DESC LIMIT 1)
                                    ,btdk.NgayBatDau)
                            ),0)
                            + (DATEDIFF(%1$s,now()) * ifnull(diemdo.SoHoatDongNgay,0)) %% chuky.GiaTri < diemdo.SoHoatDongNgay
                		)
		            )
	            AND (%1$s >= btdk.NgayBatDau OR IFNULL(btdk.NgayBatDau,\'\') = \'\')
	            AND (%1$s <= btdk.NgayKetThuc OR IFNULL(btdk.NgayKetThuc,\'\') = \'\')
                %2$s
	        ORDER BY case when ifnull(btdk.`Ref_DVBT`, 0) = 0 then 1000000 else btdk.`Ref_DVBT` end, btdk.MaThietBi
            LIMIT 1000'
            , $this->_o_DB->quote($filterdate)
            , $where
            , $this->_user->user_dept_id . ',' . $this->_user->user_dept_list
            , $date->format('d')
            , $date->format('W')
            , $date->format('m')
            , $date->format('Y')
            , $date->format('w'));
            // echo '<pre>'; echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }
    /**
     *
     * Get raw data
     */
    public function getActivePlans($locIOID = 0,$eqTypeIOID=0,$eqGroupIOID=0,$mainTypeIOID=0, $equipIOID = 0, $workcenter= 0)
    {
        $where   = '';
        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):false;
        $where  .= $locName?sprintf(' and dstb.Ref_MaKhuVuc IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)',$locName->lft, $locName->rgt):'';
        $where  .= $eqGroupIOID?sprintf(' and dstb.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';
        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):false;
        $where  .= $eqTypes?sprintf(' and (dstb.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqTypes->lft, $eqTypes->rgt):'';
        $where  .= $mainTypeIOID?sprintf(' and btdk.Ref_LoaiBaoTri = %1$d ', $mainTypeIOID):'';
        $where  .= $equipIOID?sprintf(' and IFNULL(dstb.IOID, 0) = %1$d ', $equipIOID):'';
        $where  .= $workcenter?sprintf(' and IFNULL(btdk.Ref_DVBT, 0) = %1$d ', $workcenter):'';

        $sql = sprintf('
            SELECT 
                btdk.*
                , chuky.KyBaoDuong as MaKy
                , chuky.Thu as GiaTri
                , chuky.*
                , chuky.Thu AS NgayThu
                , chuky.IOID AS ChuKyIOID
                , kv1.lft AS lft_KhuVuc, kv1.rgt AS rgt_KhuVuc
	            , chuky.IOID as ChuKyIOID
            FROM OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
            LEFT JOIN ODanhSachThietBi AS dstb on dstb.`IOID` = btdk.`Ref_MaThietBi`
            LEFT JOIN OKhuVuc as kv1 on kv1.IOID = dstb.Ref_MaKhuVuc
            LEFT JOIN (SELECT kv3.IOID, lft,rgt FROM   OKhuVuc AS kv3 WHERE  kv3.NgungHoatDong = 1) as khuvucngunghoatdong 
                ON kv1.lft >= khuvucngunghoatdong.lft and kv1.rgt <= khuvucngunghoatdong.rgt
            WHERE khuvucngunghoatdong.IOID is null AND ifnull(dstb.TrangThai , 0) = 0 AND btdk.DeptID in (%2$s) %1$s
            ORDER BY case when ifnull(btdk.`Ref_DVBT`, 0) = 0 then 1000000 else btdk.`Ref_DVBT` end, btdk.MaThietBi
            LIMIT 100000'
            ,$where , $this->_user->user_dept_list);
        // echo '<pre>'; echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

	public function getActivePlanByEquipment($ioid)
	{
	    $sql = sprintf('
            SELECT
                btdk.*
                , chuky.KyBaoDuong as MaKy
                , chuky.ChuKy
                , chuky.IOID as ChuKyIOID
                , chuky.LapLai
                , case when ifnull(chuky.CanCu,0) = 0 then
                	case 
                		when chuky.KyBaoDuong = \'D\' then 1
                		when chuky.KyBaoDuong = \'W\' then 7
                		when chuky.KyBaoDuong = \'M\' then 30
                		when chuky.KyBaoDuong = \'Y\' then 365 
                	end * chuky.LapLai
                else chuky.GiaTri / diemdo.SoHoatDongNgay
                end as khoangcach
               , DATEDIFF(now(),btdk.NgayBatDau) as khoangcachbatdau
               , (select group_concat(concat(cv.MoTa,\' \',cv.BoPhan) SEPARATOR \'\n\') from OCongViecBT as cv where cv.IFID_M724 = btdk.IFID_M724) as CongViec
            FROM OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri as chuky on chuky.IFID_M724 = btdk.IFID_M724
            INNER JOIN ODanhSachThietBi as dstb on dstb.IOID = btdk.Ref_MaThietBi            
	        LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = dstb.IFID_M705
	        	AND IFNULL(chuky.CanCu, 0) IN (1,2)  -- Phai la loai dinh ky chi so moi join
                  AND IFNULL(chuky.Ref_ChiSo, 0) = diemdo.IOID -- Phai do cung 1 loai chi so  moi join
                  AND IFNULL(btdk.Ref_BoPhan, 0) = IFNULL(diemdo.Ref_BoPhan, 0) -- Phai cung 1 bo phan moi join 
                  AND IFNULL(chuky.GiaTri, 0) > 0 -- Chỉ số phải có giá trị mới join
            WHERE dstb.IOID =  %1$d'
	        , $ioid);
        // echo '<pre>'; print_r($sql); die;
	    return $this->_o_DB->fetchAll($sql);	    
	}

	public function getAllMaintenanceNeedUpdate($planIOID)
	{
		$sql = sprintf('
				SELECT ck.*
				FROM OBaoTriDinhKy as dk 
				inner join OChuKyBaoTri as ck on dk.IFID_M724 = ck.IFID_M724 
				where ifnull(dk.IOID,0) = %1$d 
				having count(distinct ck.IFID_M724) = 1'
            , $planIOID
        );
		return $this->_o_DB->fetchAll($sql);
	}

	public function getPreventiveByWorkOrder($ifid)
    {
        $sql = sprintf('
            select btdk.*,ck.*, ifnull(dk.Chon,0) as Chon, ky.MaKy as Ky,btdk.IOID as PIOID,dk.IOID as AIOID
            from OBaoTriDinhKy as btdk
            inner join OChuKyBaoTri as ck on btdk.IFID_M724 = ck.IFID_M724
            inner join OKy as ky on ky.IOID = ck.Ref_KyBaoDuong
            inner join OPhieuBaoTri as pbt on pbt.Ref_MaThietBi = btdk.Ref_MaThietBi
            inner join ODanhSachThietBi as dstb on dstb.IOID = btdk.Ref_MaThietBi
            left join OCauTrucThietBi as ct on ct.IFID_M705 = dstb.IFID_M705 and ct.IOID = ifnull(pbt.Ref_BoPhan,0)
            left join OThucHienDinhKy as dk on dk.Ref_LoaiBaoTri = btdk.IOID
            where pbt.IFID_M759=%1$d
                and (
                    ifnull(pbt.Ref_BoPhan,0) = 0
                    or btdk.Ref_BoPhan in
                    (
                        select IOID
                        from OCauTrucThietBi
                        where IFID_M705 = dstb.IFID_M705
                            and lft>=ct.lft and rgt <= ct.rgt
                    )
                )'
        	,$ifid
        );
       	//echo '<pre>'; //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay vat tu thay the tu ke hoach bao tri
     * @param int $locIOID Location IOID
     * @param int $eqGroupIOID Equip Group IOID
     * @param int $eqTypeIOID Equip Type IOID
     */
    public function getPlanMaterialGroupByEquip($mSDate, $mEDate, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0)
    {
        $mSolar = new Qss_Model_Calendar_Solar();
        $common = new Qss_Model_Extra_Extra();
        $loc    = $locIOID?$common->getTableFetchOne('OKhuVuc', array('IOID' => $locIOID)):FALSE;
        $eqType = $eqTypeIOID?$common->getTableFetchOne('OLoaiThietBi', array('IOID' => $eqTypeIOID)):FALSE;
        $where  = 'WHERE ifnull(DinhKy.NgungHoatDong,0)=0 ';
        $where  = 'WHERE 1=1';
        $where .= ($eqGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d', $eqGroupIOID):'';
        $where .= ($loc)?sprintf(' AND IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft>= %1$d AND  rgt <= %2$d)', $loc->lft, $loc->rgt):'';
        $where .= ($eqType)?sprintf(' AND IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft>= %1$d AND  rgt <= %2$d)', $eqType->lft, $eqType->rgt):'';

        $planSql = sprintf('
            SELECT
                DinhKy.IFID_M724         
               , DinhKy.MaThietBi, DinhKy.TenThietBi
                , IFNULL(DinhKy.NgayBatDau, "") AS NgayBaoTriGanNhat
                , ChuKy.IOID AS PeriodIOID
                , ChuKy.KyBaoDuong, ChuKy.LapLai
                , ChuKy.Thu
                , ChuKy.Ngay
                , ChuKy.Thang
                , VatTu.Ref_MaVatTu
                , VatTu.MaVatTu
                , VatTu.TenVatTu                
                , MatHang.DonViTinh              
                , (IFNULL(VatTu.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS SoLuong                
            FROM OBaoTriDinhKy AS DinhKy
            INNER JOIN OChuKyBaoTri AS ChuKy ON DinhKy.IFID_M724 = ChuKy.IFID_M724
            INNER JOIN OVatTu AS VatTu ON DinhKy.IFID_M724 = VatTu.IFID_M724
            INNER JOIN OSanPham AS MatHang ON VatTu.Ref_MaVatTu = MatHang.IOID
            INNER JOIN ODonViTinhSP AS DonViTinh ON VatTu.Ref_DonViTinh = DonViTinh.IOID 
            LEFT JOIN ODanhSachThietBi AS ThietBi ON DinhKy.Ref_MaThietBi = ThietBi.IOID
            %1$s -- AND TRIM(VatTu.MaVatTu) IN("GIE-01") 
            ORDER BY DinhKy.IFID_M724, ChuKy.IOID, VatTu.IOID, DinhKy.NgayBatDau
        ', $where);
        // echo '<pre>'; print_r($planSql); die;

        $oPlans    = $this->_o_DB->fetchAll($planSql);
        $oldPlan   = '';
        $oldPeriod = '';
        $arrItems  = array();
        $countDay  = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'D', false);
        $SoLanCuaChuKy = 0;
        $tempArrItems = array();

        foreach ($oPlans as $plan) {


            // Nếu sang một chu kỳ khác thì tính lại số lần xảy ra của chu kỳ ấy
            // Số lần xảy ra phải dựa vào khoảng thời gian chuyền vào và ngày bảo trì đầu tiên/gần nhất
            if($oldPlan !== $plan->IFID_M724 || $oldPeriod !== $plan->PeriodIOID) {
                switch ($plan->KyBaoDuong) {
                    case 'D':// Bao tri theo ngay
                        if($plan->LapLai == 1) {
                            $SoLanCuaChuKy = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'D');
                        }
                        else {
                            // Nếu lặp lớn hơn 1, phải dựa vào ngày bảo trì gần nhất để tìm ngày bảo trì (KH) đầu tiên
                            // trong khoảng thời gian đang lọc. Lấy thời gian cuối trừ đi ngày vừa tìm được ra số ngày,
                            // rồi lấy số ngày này chia cho số lần lặp sẽ ra được số lần bảo trì theo kế hoạch trong
                            // khoảng thời gian (Làm tròn lên).
                            $SoLanCuaChuKy = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;// $plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;
                            $tempDiffEnd                = Qss_Lib_Date::diffTime($plan->NgayBaoTriGanNhat, $mSDate, 'D', false);

                            if($tempDiffEnd > 1) {
                                $soLanXayRa   = $plan->LapLai?ceil($tempDiffEnd/(int)$plan->LapLai):0;
                                $tongSoLanLap = $plan->LapLai * $soLanXayRa;



                                $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$tongSoLanLap.' days'));
                            }

                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                                && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                $countDay2     = Qss_Lib_Date::diffTime($tempNgayDauTienTrongKhoang, $mEDate, 'D');
                                $SoLanCuaChuKy = ceil($countDay2 / $plan->LapLai);
                            }
                        }
                    break;
                    case 'W':// Bao tri theo tuan
                        // Với trường hợp hàng tuần lặp lại bằng 1, ta chỉ cần đếm xem trong khoảng thời gian có bao
                        // nhiêu ngày đó là được. Khi lặp lại nhiều hơn ta phải dựa vào ngày đầu tiên để tìm được ngày
                        // đầu tiên trong khoảng với mỗi bước nhảy ngày = (lặp lại * 7) + 1 với +1 là cộng ngày đầu tiên.
                        // Khi ra được ngày bảo trì dầu tiên trong khoảng, ta lấy cuối trừ ngày này rồi chia cho 7 để
                        // ra số tuần. Rồi lấy số tuần chia cho lặp lại để lấy được số lần xảy ra. Cả hai phép tính trên
                        // đều làm tròn lên.
                        if($plan->LapLai == 1) {
                            $countWeekdays = Qss_Lib_Extra::countWeekdays($mSDate, $mEDate);
                            $SoLanCuaChuKy = @(int)$countWeekdays[$plan->Thu];
                        }
                        else {
                            $SoLanCuaChuKy = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;//$plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;

                            while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) == -1) {
                                $soNgayCongThem  = ($plan->LapLai * 7) + 1; // Với +1 là ngày đầu tiên.
                                $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$soNgayCongThem.' days'));
                            }

                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                                && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                $countDay2     = Qss_Lib_Date::diffTime($tempNgayDauTienTrongKhoang, $mEDate, 'D', false);
                                $SoTuan        = ceil($countDay2 / 7);
                                $SoLanCuaChuKy = ceil($SoTuan/ $plan->LapLai);
                            }
                        }
                    break;
                    case 'M':// Bao tri theo thang
                        // Nếu lập lại bằng 1 chỉ cần xem trong khoảng thời gian có bao nhiêu tháng, sau đó nếu ngày đầu tiên
                        // vượt quá ngày bảo trì thì trừ đi 1 hoặc ngày cuối cùng nhỏ hơn ngày bảo trì thì cũng trừ đi 1
                        // Còn nếu lặp lại lớn hơn 1 thì phải dựa vào ngày bảo trì gần nhất để tìm lần bảo trì đàu tiên trong
                        // khoảng thời gian đã chọn, rồi loop cộng ngày theo dạng ngày  lấy ra cộng với sô lần lặp cho tháng.
                        // @Lưu ý: Cần kiểm tra ngày có tồn tại không, nếu ngày không tồn tại trong tháng cần lùi ngày về ngày
                        // cuối của tháng.
                        if($plan->LapLai == 1) {
                            $countMonth   = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'MO');
                            $SoLanCuaChuKy = $countMonth;
                            $micrStart    = strtotime($mSDate);
                            $micrEnd      = strtotime($mEDate);
                            $dayOfStart   = date('d', $micrStart);
                            $monthOfStart = date('m', $micrStart);
                            $yearOfStart  = date('Y', $micrStart);
                            $dayOfEnd     = date('d', $micrEnd);
                            $monthOfEnd   = date('m', $micrEnd);
                            $yearOfEnd    = date('Y', $micrEnd);

                            if(!checkdate($monthOfStart, $plan->Ngay, $yearOfStart)) {
                                $dayOfStart = date('d', strtotime(date('t-'.$monthOfStart.'-'.$yearOfStart)));
                            }

                            if(!checkdate($monthOfEnd, $plan->Ngay, $yearOfEnd)) {
                                $dayOfEnd = date('d', strtotime(date('t-'.$monthOfEnd.'-'.$yearOfEnd)));
                            }

                            if((int)$dayOfStart > (int)$plan->Ngay
                                || ((int)$dayOfEnd < (int)$plan->Ngay) && $monthOfStart != $monthOfEnd) {
                                $SoLanCuaChuKy--;
                            }

                            $SoLanCuaChuKy = ($SoLanCuaChuKy > 0)?$SoLanCuaChuKy:0;
                        }
                        else {
                            $SoLanCuaChuKy              = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;//$plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;

                            while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) == -1) {
                                $microNgayGanNhat = strtotime($tempNgayDauTienTrongKhoang);
                                $monthOfGanNhat   = date('m', $microNgayGanNhat);
                                $yearOfGanNhat    = date('Y', $microNgayGanNhat);

                                $nextYear         = (int)$yearOfGanNhat;
                                $nextMonth        = (int)$monthOfGanNhat + (int)$plan->LapLai;
                                if($nextMonth > 12)
                                {
                                    $nextYear++;
                                    $nextMonth = $nextMonth - 12;
                                }

                                if(!checkdate($nextMonth, $plan->Ngay, $nextYear)) {
                                    $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime(date('t-'.$nextMonth.'-'.$nextYear)));
                                }
                                else {
                                    $tempNgayDauTienTrongKhoang = $nextYear. '-' . $nextMonth . '-'. $plan->Ngay;
                                }
                            }

                            $limit = 0;
                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                             && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                // Lặp xem phiếu bảo trì xảy ra ở những ngày nào, rồi cộng vào trong số lần của chu kỳ
                                while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1) {
                                    if($limit > 100) break;
                                    $limit++;



                                    $microNgayGanNhat = strtotime($tempNgayDauTienTrongKhoang);
                                    $monthOfGanNhat   = date('m', $microNgayGanNhat);
                                    $yearOfGanNhat    = date('Y', $microNgayGanNhat);
                                    $nextYear         = (int)$yearOfGanNhat;
                                    $nextMonth        = (int)$monthOfGanNhat + (int)$plan->LapLai;
                                    if ($nextMonth >= 12) // Sang năm tiếp theo cần cộng năm lên 1
                                    {
                                        $nextYear++;
                                        $nextMonth = $nextMonth - 12;
                                    }

//                                    echo $tempNgayDauTienTrongKhoang.' - (int)$plan->LapLai:'.(int)$plan->LapLai
//                                        .' - $nextMonth:'.$nextMonth. ' - $nextYear:'.$nextYear.'<br>';

                                    $nextMonth  = $nextMonth < 10?'0'.(int)$nextMonth:$nextMonth;
                                    $plan->Ngay = $plan->Ngay < 10?'0'.(int)$plan->Ngay:$plan->Ngay;

                                    if (!checkdate($nextMonth, $plan->Ngay, $nextYear)) {
                                        $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime(date('t-' . $nextMonth . '-' . $nextYear)));
                                    } else {
                                        $tempNgayDauTienTrongKhoang = $nextYear . '-' . $nextMonth . '-' . $plan->Ngay;
                                    }

                                    // if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1)
                                    {
                                        $SoLanCuaChuKy++;
                                    }
                                }
                            }


                        }
                    break;
                    case 'Y':// Bao tri theo nam
                        // Neu lap lai 1 lan thi lam giong theo thang
                        // Neu lap lai nhieu lan cung lam giong theo thang



                        if($plan->LapLai == 1) {
                            $countYear     = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'Y');
                            $SoLanCuaChuKy = $countYear;
                            $micrStart    = strtotime($mSDate);
                            $micrEnd      = strtotime($mEDate);
                            $yearOfStart  = date('Y', $micrStart);
                            $yearOfEnd    = date('Y', $micrEnd);

                            $tempCompareStart = $plan->Ngay . '-' .$plan->Thang.'-'.$yearOfStart;
                            $tempCompareEnd   = $plan->Ngay . '-' .$plan->Thang.'-'.$yearOfEnd;

                            if(!checkdate($plan->Thang, $plan->Ngay, $yearOfStart)) {
                                $tempCompareStart = date('t-'.$plan->Thang.'-'.$yearOfStart);
                            }

                            if(!checkdate($plan->Thang, $plan->Ngay, $yearOfEnd)) {
                                $tempCompareEnd = date('t-'.$plan->Thang.'-'.$yearOfEnd);
                            }

                            if(Qss_Lib_Date::compareTwoDate($mSDate, $tempCompareStart) == 1
                                || Qss_Lib_Date::compareTwoDate($mEDate, $tempCompareEnd) == -1) {
                                $SoLanCuaChuKy--;
                            }

                            $SoLanCuaChuKy = ($SoLanCuaChuKy > 0)?$SoLanCuaChuKy:0;
                        }
                        else {

                            $SoLanCuaChuKy = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;//$plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;

//                            if($plan->Ref_MaVatTu == 2665) {
//                                echo '$tempNgayDauTienTrongKhoang:' . $tempNgayDauTienTrongKhoang . '<br/>';
//                            }

                            //$tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$plan->LapLai.' years'));
							//@todo: Tại sao lại mark vào đoạn dưới
                            while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) == -1) {
                                $soNgayCongThem  = ($plan->LapLai * 365) + 1; // Với +1 là ngày đầu tiên.
                                $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$plan->LapLai.' years'));
                            }

//                            if($plan->Ref_MaVatTu == 2665)
//                            {
//                                echo '$tempNgayDauTienTrongKhoang:'.$tempNgayDauTienTrongKhoang.'<br/>';
//                                echo '$mEDate:'.$mEDate.'<br/>';
//                                echo '-------'.'<br/>';
//                            }

                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                                && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                $countYear     = Qss_Lib_Date::diffTime($tempNgayDauTienTrongKhoang, $mEDate, 'Y');
                                $SoNam         = ceil($countYear / 365);
                                $SoLanCuaChuKy = ceil($SoNam/ $plan->LapLai);
                            }
                        }
                    break;
                    default:
                        $SoLanCuaChuKy = 0;
                    break;
                }
            }

            $plan->SoLanChuKy = $SoLanCuaChuKy;

            if($SoLanCuaChuKy > 0)
            {
                if(!isset($arrItems[$plan->Ref_MaVatTu]))
                {
                    $arrItems[$plan->Ref_MaVatTu] = $plan;
                    $arrItems[$plan->Ref_MaVatTu]->TongSoLuongDuKien = 0;
                    $arrItems[$plan->Ref_MaVatTu]->TongYeuCauMua     = 0; // xử lý ở bên dưới
                    $arrItems[$plan->Ref_MaVatTu]->TongTonKho        = 0; // xử lý ở bên dưới
                }

                if(!isset($stt)) { $stt = 0 ;}

                $stt += ($SoLanCuaChuKy * $plan->SoLuong);

//                echo ($SoLanCuaChuKy * $plan->SoLuong).'<br/>';
//                echo $stt.'<br/>';
//                echo '---------------'.'<br/>';

                // Cộng dồn sản phẩm giống nhau lại
                $arrItems[$plan->Ref_MaVatTu]->TongSoLuongDuKien += $plan->SoLuong * $SoLanCuaChuKy;
            }

            $oldPlan   = $plan->IFID_M724;
            $oldPeriod = $plan->PeriodIOID;
        }

        // die;


//echo '<pre>'; print_r($arrItems); die;
//        die;

        // Lấy mảng IOID vật tư để lấy tồn kho và yêu cầu mua sắm
        foreach($arrItems as $key=>$item)
        {
            if($item->TongSoLuongDuKien == 0) {
                unset($arrItems[$key]);
            }
            else {
                $tempArrItems[] = $item->Ref_MaVatTu;
            }
        }

        $tempArrItems[] = 0;

        $sql = sprintf('
            SELECT 
                MatHang.IOID
                , IFNULL(YeuCauMuaSam.TongYeuCauConLai, 0) AS TongYeuCauConLai
                , IFNULL(Kho.SoLuongHC, 0) AS SoLuongHC
            FROM OSanPham AS MatHang
            LEFT JOIN (
                SELECT 
                  SUM(IF( IFNULL(ODanhSachKho.IOID, 0) != 0, IFNULL(OKho.SoLuongHC,0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0), 0)) AS  SoLuongHC
                  , OSanPham.IOID AS Ref_MaSanPham
				FROM OKho
                INNER JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID AND ODanhSachKho.LoaiKho = %2$s
				INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP 
				INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND ODonViTinhSP.IOID = OKho.Ref_DonViTinh
				WHERE OSanPham.IOID IN (%1$s)
				GROUP BY OKho.Ref_MaSP            
            ) AS Kho ON MatHang.IOID = Kho.Ref_MaSanPham            
            LEFT JOIN
            (
                SELECT 
                    YeuCau.Ref_MaSP
                    , SUM(IFNULL(YeuCau.TongYeuCauMua, 0) - IFNULL(NhapKho.TongNhapKho, 0)) AS TongYeuCauConLai
                FROM
                (
                    SELECT
                        DanhSach.Ref_MaSP                        
                        , YeuCauMS.IOID AS Ref_SoYeuCau
                        , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongYeuCauMua
                    FROM OYeuCauMuaSam AS YeuCauMS
                    INNER JOIN ODSYeuCauMuaSam AS DanhSach ON YeuCauMS.IFID_M412 = DanhSach.IFID_M412
                    INNER JOIN qsiforms AS iform ON YeuCauMS.IFID_M412 = iform.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID                                                                                                                                               
                    WHERE DanhSach.Ref_MaSP IN (%1$s) AND iform.Status IN (3) 
                    GROUP BY YeuCauMS.IOID, DanhSach.Ref_MaSP 
                ) AS YeuCau
                LEFT JOIN
                (
                    SELECT
                        DanhSach.Ref_MaSanPham AS Ref_MaSP
                        , DanhSach.Ref_SoYeuCau
                        , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongNhapKho
                    FROM ONhapKho AS NhapKho
                    INNER JOIN ODanhSachNhapKho AS DanhSach ON NhapKho.IFID_M402 = DanhSach.IFID_M402
                    INNER JOIN qsiforms AS iform ON NhapKho.IFID_M402 = iform.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID
                    WHERE DanhSach.Ref_MaSanPham IN (%1$s) AND iform.Status = 2
                    GROUP BY DanhSach.Ref_SoYeuCau, DanhSach.Ref_MaSanPham
                ) AS NhapKho ON YeuCau.Ref_SoYeuCau = NhapKho.Ref_SoYeuCau AND YeuCau.Ref_MaSP = NhapKho.Ref_MaSP  
                GROUP BY YeuCau.Ref_MaSP
            ) AS YeuCauMuaSam ON MatHang.IOID = YeuCauMuaSam.Ref_MaSP 
            WHERE MatHang.IOID IN (%1$s)
        ', implode(',', $tempArrItems), $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));

        // echo '<pre>'; print_r($sql); die;

        $dat = $this->_o_DB->fetchAll($sql);

        foreach ($dat as $item) {
            $arrItems[$item->IOID]->TongYeuCauMua += $item->TongYeuCauConLai;
            $arrItems[$item->IOID]->TongTonKho    += $item->SoLuongHC;
        }

        // echo '<pre>'; print_r($arrItems); die;

        return $arrItems;
    }

    public function getPlanMaterial($mSDate, $mEDate, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0)
    {
        $mSolar = new Qss_Model_Calendar_Solar();
        $common = new Qss_Model_Extra_Extra();
        $loc    = $locIOID?$common->getTableFetchOne('OKhuVuc', array('IOID' => $locIOID)):FALSE;
        $eqType = $eqTypeIOID?$common->getTableFetchOne('OLoaiThietBi', array('IOID' => $eqTypeIOID)):FALSE;
        $where  = 'WHERE ifnull(DinhKy.NgungHoatDong,0)=0 ';
        $where  = 'WHERE 1=1';
        $where .= ($eqGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d', $eqGroupIOID):'';
        $where .= ($loc)?sprintf(' AND IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft>= %1$d AND  rgt <= %2$d)', $loc->lft, $loc->rgt):'';
        $where .= ($eqType)?sprintf(' AND IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft>= %1$d AND  rgt <= %2$d)', $eqType->lft, $eqType->rgt):'';
        $retval = array();

        $planSql = sprintf('
            SELECT
               DinhKy.IFID_M724 ,
               DinhKy.MaThietBi, DinhKy.TenThietBi,
               ThietBi.IOID AS EQIOID,
               IFNULL(DinhKy.NgayBatDau, "") AS NgayBaoTriGanNhat,
                ifnull(VatTu.Ref_ViTri, 0) AS CIOID,
                MatHang.IOID AS IIOID,
                ThietBi.MaThietBi,
                ThietBi.TenThietBi,
                VatTu.ViTri,
                VatTu.BoPhan,
                VatTu.Ref_MaVatTu,
                VatTu.MaVatTu,
                VatTu.TenVatTu,                   
                MatHang.MaSanPham,
                MatHang.TenSanPham,
                MatHang.DonViTinh, 
                (ifnull(VatTu.SoLuong, 0) * ifnull(DonViTinh.HeSoQuyDoi, 0))AS SoLuong,
                0 AS TonKho,
                ChuKy.IOID AS PeriodIOID,
                ChuKy.IOID AS ChuKyIOID,
                ChuKy.KyBaoDuong as MaKy,
                ChuKy.KyBaoDuong,
                ChuKy.Ngay,
                ChuKy.Thang,
                ChuKy.Thu AS NgayThu,  
                ChuKy.Thu,                  
                ChuKy.LapLai,
                DinhKy.NgayBatDau,
                DinhKy.NgayKetThuc
            FROM OBaoTriDinhKy AS DinhKy
            INNER JOIN OChuKyBaoTri AS ChuKy ON DinhKy.IFID_M724 = ChuKy.IFID_M724
            INNER JOIN OVatTu AS VatTu ON DinhKy.IFID_M724 = VatTu.IFID_M724
            INNER JOIN OSanPham AS MatHang ON VatTu.Ref_MaVatTu = MatHang.IOID
            INNER JOIN ODonViTinhSP AS DonViTinh ON VatTu.Ref_DonViTinh = DonViTinh.IOID 
            LEFT JOIN ODanhSachThietBi AS ThietBi ON DinhKy.Ref_MaThietBi = ThietBi.IOID
            %1$s -- AND TRIM(VatTu.MaVatTu) IN("GIE-01") 
            ORDER BY DinhKy.IFID_M724, ChuKy.IOID, VatTu.IOID, DinhKy.NgayBatDau
        ', $where);
        // echo '<pre>'; print_r($planSql); die;

        $oPlans    = $this->_o_DB->fetchAll($planSql);
        $oldPlan   = '';
        $oldPeriod = '';
        $arrItems  = array();
        $countDay  = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'D', false);
        $SoLanCuaChuKy = 0;
        $tempArrItems = array();

        foreach ($oPlans as $plan) {


            // Nếu sang một chu kỳ khác thì tính lại số lần xảy ra của chu kỳ ấy
            // Số lần xảy ra phải dựa vào khoảng thời gian chuyền vào và ngày bảo trì đầu tiên/gần nhất
            if($oldPlan !== $plan->IFID_M724 || $oldPeriod !== $plan->PeriodIOID) {
                switch ($plan->KyBaoDuong) {
                    case 'D':// Bao tri theo ngay
                        if($plan->LapLai == 1) {
                            $SoLanCuaChuKy = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'D');
                        }
                        else {
                            // Nếu lặp lớn hơn 1, phải dựa vào ngày bảo trì gần nhất để tìm ngày bảo trì (KH) đầu tiên
                            // trong khoảng thời gian đang lọc. Lấy thời gian cuối trừ đi ngày vừa tìm được ra số ngày,
                            // rồi lấy số ngày này chia cho số lần lặp sẽ ra được số lần bảo trì theo kế hoạch trong
                            // khoảng thời gian (Làm tròn lên).
                            $SoLanCuaChuKy = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;// $plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;
                            $tempDiffEnd                = Qss_Lib_Date::diffTime($plan->NgayBaoTriGanNhat, $mSDate, 'D', false);

                            if($tempDiffEnd > 1) {
                                $soLanXayRa   = $plan->LapLai?ceil($tempDiffEnd/(int)$plan->LapLai):0;
                                $tongSoLanLap = $plan->LapLai * $soLanXayRa;



                                $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$tongSoLanLap.' days'));
                            }

                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                                && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                $countDay2     = Qss_Lib_Date::diffTime($tempNgayDauTienTrongKhoang, $mEDate, 'D');
                                $SoLanCuaChuKy = ceil($countDay2 / $plan->LapLai);
                            }
                        }
                        break;
                    case 'W':// Bao tri theo tuan
                        // Với trường hợp hàng tuần lặp lại bằng 1, ta chỉ cần đếm xem trong khoảng thời gian có bao
                        // nhiêu ngày đó là được. Khi lặp lại nhiều hơn ta phải dựa vào ngày đầu tiên để tìm được ngày
                        // đầu tiên trong khoảng với mỗi bước nhảy ngày = (lặp lại * 7) + 1 với +1 là cộng ngày đầu tiên.
                        // Khi ra được ngày bảo trì dầu tiên trong khoảng, ta lấy cuối trừ ngày này rồi chia cho 7 để
                        // ra số tuần. Rồi lấy số tuần chia cho lặp lại để lấy được số lần xảy ra. Cả hai phép tính trên
                        // đều làm tròn lên.
                        if($plan->LapLai == 1) {
                            $countWeekdays = Qss_Lib_Extra::countWeekdays($mSDate, $mEDate);
                            $SoLanCuaChuKy = @(int)$countWeekdays[$plan->Thu];
                        }
                        else {
                            $SoLanCuaChuKy = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;//$plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;

                            while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) == -1) {
                                $soNgayCongThem  = ($plan->LapLai * 7) + 1; // Với +1 là ngày đầu tiên.
                                $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$soNgayCongThem.' days'));
                            }

                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                                && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                $countDay2     = Qss_Lib_Date::diffTime($tempNgayDauTienTrongKhoang, $mEDate, 'D', false);
                                $SoTuan        = ceil($countDay2 / 7);
                                $SoLanCuaChuKy = ceil($SoTuan/ $plan->LapLai);
                            }
                        }
                        break;
                    case 'M':// Bao tri theo thang
                        // Nếu lập lại bằng 1 chỉ cần xem trong khoảng thời gian có bao nhiêu tháng, sau đó nếu ngày đầu tiên
                        // vượt quá ngày bảo trì thì trừ đi 1 hoặc ngày cuối cùng nhỏ hơn ngày bảo trì thì cũng trừ đi 1
                        // Còn nếu lặp lại lớn hơn 1 thì phải dựa vào ngày bảo trì gần nhất để tìm lần bảo trì đàu tiên trong
                        // khoảng thời gian đã chọn, rồi loop cộng ngày theo dạng ngày  lấy ra cộng với sô lần lặp cho tháng.
                        // @Lưu ý: Cần kiểm tra ngày có tồn tại không, nếu ngày không tồn tại trong tháng cần lùi ngày về ngày
                        // cuối của tháng.
                        if($plan->LapLai == 1) {
                            $countMonth   = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'MO');
                            $SoLanCuaChuKy = $countMonth;
                            $micrStart    = strtotime($mSDate);
                            $micrEnd      = strtotime($mEDate);
                            $dayOfStart   = date('d', $micrStart);
                            $monthOfStart = date('m', $micrStart);
                            $yearOfStart  = date('Y', $micrStart);
                            $dayOfEnd     = date('d', $micrEnd);
                            $monthOfEnd   = date('m', $micrEnd);
                            $yearOfEnd    = date('Y', $micrEnd);

                            if(!checkdate($monthOfStart, $plan->Ngay, $yearOfStart)) {
                                $dayOfStart = date('d', strtotime(date('t-'.$monthOfStart.'-'.$yearOfStart)));
                            }

                            if(!checkdate($monthOfEnd, $plan->Ngay, $yearOfEnd)) {
                                $dayOfEnd = date('d', strtotime(date('t-'.$monthOfEnd.'-'.$yearOfEnd)));
                            }

                            if((int)$dayOfStart > (int)$plan->Ngay
                                || ((int)$dayOfEnd < (int)$plan->Ngay) && $monthOfStart != $monthOfEnd) {
                                $SoLanCuaChuKy--;
                            }

                            $SoLanCuaChuKy = ($SoLanCuaChuKy > 0)?$SoLanCuaChuKy:0;
                        }
                        else {
                            $SoLanCuaChuKy              = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;//$plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;

                            while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) == -1) {
                                $microNgayGanNhat = strtotime($tempNgayDauTienTrongKhoang);
                                $monthOfGanNhat   = date('m', $microNgayGanNhat);
                                $yearOfGanNhat    = date('Y', $microNgayGanNhat);

                                $nextYear         = (int)$yearOfGanNhat;
                                $nextMonth        = (int)$monthOfGanNhat + (int)$plan->LapLai;
                                if($nextMonth > 12)
                                {
                                    $nextYear++;
                                    $nextMonth = $nextMonth - 12;
                                }

                                if(!checkdate($nextMonth, $plan->Ngay, $nextYear)) {
                                    $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime(date('t-'.$nextMonth.'-'.$nextYear)));
                                }
                                else {
                                    $tempNgayDauTienTrongKhoang = $nextYear. '-' . $nextMonth . '-'. $plan->Ngay;
                                }
                            }

                            $limit = 0;
                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                                && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                // Lặp xem phiếu bảo trì xảy ra ở những ngày nào, rồi cộng vào trong số lần của chu kỳ
                                while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1) {
                                    if($limit > 100) break;
                                    $limit++;



                                    $microNgayGanNhat = strtotime($tempNgayDauTienTrongKhoang);
                                    $monthOfGanNhat   = date('m', $microNgayGanNhat);
                                    $yearOfGanNhat    = date('Y', $microNgayGanNhat);
                                    $nextYear         = (int)$yearOfGanNhat;
                                    $nextMonth        = (int)$monthOfGanNhat + (int)$plan->LapLai;
                                    if ($nextMonth >= 12) // Sang năm tiếp theo cần cộng năm lên 1
                                    {
                                        $nextYear++;
                                        $nextMonth = $nextMonth - 12;
                                    }

//                                    echo $tempNgayDauTienTrongKhoang.' - (int)$plan->LapLai:'.(int)$plan->LapLai
//                                        .' - $nextMonth:'.$nextMonth. ' - $nextYear:'.$nextYear.'<br>';

                                    $nextMonth  = $nextMonth < 10?'0'.(int)$nextMonth:$nextMonth;
                                    $plan->Ngay = $plan->Ngay < 10?'0'.(int)$plan->Ngay:$plan->Ngay;

                                    if (!checkdate($nextMonth, $plan->Ngay, $nextYear)) {
                                        $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime(date('t-' . $nextMonth . '-' . $nextYear)));
                                    } else {
                                        $tempNgayDauTienTrongKhoang = $nextYear . '-' . $nextMonth . '-' . $plan->Ngay;
                                    }

                                    // if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1)
                                    {
                                        $SoLanCuaChuKy++;
                                    }
                                }
                            }


                        }
                        break;
                    case 'Y':// Bao tri theo nam
                        // Neu lap lai 1 lan thi lam giong theo thang
                        // Neu lap lai nhieu lan cung lam giong theo thang



                        if($plan->LapLai == 1) {
                            $countYear     = Qss_Lib_Date::diffTime($mSDate, $mEDate, 'Y');
                            $SoLanCuaChuKy = $countYear;
                            $micrStart    = strtotime($mSDate);
                            $micrEnd      = strtotime($mEDate);
                            $yearOfStart  = date('Y', $micrStart);
                            $yearOfEnd    = date('Y', $micrEnd);

                            $tempCompareStart = $plan->Ngay . '-' .$plan->Thang.'-'.$yearOfStart;
                            $tempCompareEnd   = $plan->Ngay . '-' .$plan->Thang.'-'.$yearOfEnd;

                            if(!checkdate($plan->Thang, $plan->Ngay, $yearOfStart)) {
                                $tempCompareStart = date('t-'.$plan->Thang.'-'.$yearOfStart);
                            }

                            if(!checkdate($plan->Thang, $plan->Ngay, $yearOfEnd)) {
                                $tempCompareEnd = date('t-'.$plan->Thang.'-'.$yearOfEnd);
                            }

                            if(Qss_Lib_Date::compareTwoDate($mSDate, $tempCompareStart) == 1
                                || Qss_Lib_Date::compareTwoDate($mEDate, $tempCompareEnd) == -1) {
                                $SoLanCuaChuKy--;
                            }

                            $SoLanCuaChuKy = ($SoLanCuaChuKy > 0)?$SoLanCuaChuKy:0;
                        }
                        else {

                            $SoLanCuaChuKy = 0;
                            $tempNgayDauTienTrongKhoang = $plan->NgayBaoTriGanNhat;//$plan->NgayBaoTriGanNhat?$plan->NgayBaoTriGanNhat:$mSDate;

//                            if($plan->Ref_MaVatTu == 2665) {
//                                echo '$tempNgayDauTienTrongKhoang:' . $tempNgayDauTienTrongKhoang . '<br/>';
//                            }

                            $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$plan->LapLai.' years'));

//                            while (Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) <= 0) {
//                                $soNgayCongThem  = ($plan->LapLai * 365) + 1; // Với +1 là ngày đầu tiên.
//                                $tempNgayDauTienTrongKhoang = date('Y-m-d', strtotime($tempNgayDauTienTrongKhoang. ' + '.$plan->LapLai.' years'));
//                            }

//                            if($plan->Ref_MaVatTu == 2665)
//                            {
//                                echo '$tempNgayDauTienTrongKhoang:'.$tempNgayDauTienTrongKhoang.'<br/>';
//                                echo '$mEDate:'.$mEDate.'<br/>';
//                                echo '-------'.'<br/>';
//                            }

                            // Chi xet khi lan bao tri tiep theo ke tu ngay gan nhat con o trong khoang thoi gian dang xet
                            if(Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mEDate) < 1
                                && Qss_Lib_Date::compareTwoDate($tempNgayDauTienTrongKhoang, $mSDate) >= 0) {
                                $countYear     = Qss_Lib_Date::diffTime($tempNgayDauTienTrongKhoang, $mEDate, 'Y');
                                $SoNam         = ceil($countYear / 365);
                                $SoLanCuaChuKy = ceil($SoNam/ $plan->LapLai);
                            }
                        }
                        break;
                    default:
                        $SoLanCuaChuKy = 0;
                        break;
                }
            }

            if($SoLanCuaChuKy > 0)
            {
                if(!isset($stt)) { $stt = 0 ;}

                $stt += ($SoLanCuaChuKy * $plan->SoLuong);

//                echo ($SoLanCuaChuKy * $plan->SoLuong).'<br/>';
//                echo $stt.'<br/>';
//                echo '---------------'.'<br/>';


                $plan->SoLanCuaChuKy   = $SoLanCuaChuKy;
                $plan->SoLuongCuoiCung = $SoLanCuaChuKy * $plan->SoLuong;

                $retval[] = $plan;
            }

            $oldPlan   = $plan->IFID_M724;
            $oldPeriod = $plan->PeriodIOID;
        }
 // die;
        $retval2 = array();

        foreach($retval as $item) {
            $retval2[$item->Ref_MaVatTu][] = $item;
        }

        // echo '<pre>'; print_r($retval2); die;

        return $retval2;
    }
}
