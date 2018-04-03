<?php

class Qss_Model_Maintenance_Viwasupcoplan extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPlansOrderByLocationAndType(
        $filterdate, $locIOID = 0,$eqTypeIOID=0,$eqGroupIOID=0,$mainTypeIOID=0, $equipIOID = 0, $workcenter = 0, $start = '', $end = '')
    {
        $date    = date_create($filterdate);
        $where   = '';
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
        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):false;
        $where  .= $locName?sprintf(' and dstb.Ref_MaKhuVuc IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)',$locName->lft, $locName->rgt):'';
        $where  .= $eqGroupIOID?sprintf(' and dstb.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';
        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):false;
        $where  .= $eqTypes?sprintf(' and (dstb.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqTypes->lft, $eqTypes->rgt):'';
        $where  .= $mainTypeIOID?sprintf(' and btdk.Ref_LoaiBaoTri = %1$d ', $mainTypeIOID):'';
        $where  .= $equipIOID?sprintf(' and IFNULL(dstb.IOID, 0) = %1$d ', $equipIOID):'';
        $oWC     = $workcenter?$this->_o_DB->fetchOne(sprintf('SELECT * FROM ODonViSanXuat WHERE IOID = %1$d', $workcenter)):'';
        $where  .= ($oWC)?sprintf('and IFNULL(btdk.`Ref_DVBT`, 0) IN (SELECT IOID FROM ODonViSanXuat WHERE lft >= %1$d AND rgt <= %2$d)', $oWC->lft, $oWC->rgt):'';

        $sql = sprintf('
            SELECT 
                btdk.*
                , chuky.*
                , btdk.IOID AS BaoDuongIOID
                , chuky.IOID AS ChuKyIOID
                , KhuVucCha.Ten AS TenKhuVuc
                , KhuVucCha.IOID AS KhuVucIOID
                , LoaiThietBi.TenLoai
                , LoaiThietBi.IOID AS LoaiThietBiIOID
            FROM OBaoTriDinhKy AS btdk
            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
            INNER JOIN ODanhSachThietBi AS dstb on dstb.`IOID` = btdk.`Ref_MaThietBi`
            LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = dstb.IFID_M705
            	 AND IFNULL(chuky.CanCu, 0) IN (1,2)  -- Phai la loai dinh ky chi so moi join
                  AND IFNULL(chuky.Ref_ChiSo, 0) = IFNULL(diemdo.IOID, 0) -- Phai do cung 1 loai chi so  moi join
                  AND IFNULL(btdk.Ref_BoPhan, 0) = IFNULL(diemdo.Ref_BoPhan, 0) -- Phai cung 1 bo phan moi join 
                  AND IFNULL(chuky.GiaTri, 0) > 0 -- Chỉ số phải có giá trị mới join
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(dstb.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OKhuVuc as kv1 on kv1.IOID = dstb.Ref_MaKhuVuc
            LEFT JOIN OKhuVuc AS KhuVucCha On kv1.lft >= KhuVucCha.lft AND kv1.rgt <= KhuVucCha.rgt AND IFNULL(KhuVucCha.Ref_TrucThuoc, 0) = 0
            LEFT JOIN (SELECT kv3.IOID, lft,rgt FROM   OKhuVuc AS kv3 WHERE kv3.NgungHoatDong = 1) as khuvucngunghoatdong ON kv1.lft >= khuvucngunghoatdong.lft and kv1.rgt <= khuvucngunghoatdong.rgt
            WHERE khuvucngunghoatdong.IOID is null AND ifnull(dstb.TrangThai , 0) = 0 AND btdk.DeptID in (%2$s) %1$s
            
            
            
            	AND (( ifnull(chuky.CanCu,0) =0
                        AND (
                        (chuky.KyBaoDuong = \'D\' AND IFNULL(TIMESTAMPDIFF(DAY, btdk.NgayBatDau ,%3$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'W\' AND chuky.Thu =%8$d
                            AND IFNULL(TIMESTAMPDIFF(WEEK, btdk.NgayBatDau ,%3$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'M\' AND (chuky.Ngay =%4$d or (LAST_DAY(%3$s) = %3$s and chuky.Ngay > %4$d))
                            AND IFNULL(TIMESTAMPDIFF(MONTH, date_add(btdk.NgayBatDau,INTERVAL -day(btdk.NgayBatDau) DAY),%3$s) %% chuky.LapLai,0) = 0)
                        OR (chuky.KyBaoDuong = \'Y\' AND (chuky.Ngay =%4$d or (LAST_DAY(%3$s) = %3$s and chuky.Ngay > %4$d))
                            AND chuky.Thang =%6$d
                            AND IFNULL(TIMESTAMPDIFF(YEAR, date_add(btdk.NgayBatDau,INTERVAL -day(btdk.NgayBatDau) DAY) ,%3$s) %% chuky.LapLai,0) = 0))
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
                            + (DATEDIFF(%3$s,now()) * ifnull(diemdo.SoHoatDongNgay,0)) >= chuky.GiaTri
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
                            + (DATEDIFF(%3$s,now()) * ifnull(diemdo.SoHoatDongNgay,0)) %% chuky.GiaTri < diemdo.SoHoatDongNgay
                		)
		            )
	            AND (%3$s >= btdk.NgayBatDau OR IFNULL(btdk.NgayBatDau,\'\') = \'\')
	            AND (%3$s <= btdk.NgayKetThuc OR IFNULL(btdk.NgayKetThuc,\'\') = \'\')
            
            
            
            '
            ,$where , $this->_user->user_dept_list, $this->_o_DB->quote($filterdate)
            , $date->format('d'), $date->format('W'), $date->format('m'), $date->format('Y'), $date->format('w'));

        $sql .= ' ORDER BY KhuVucCha.lft, LoaiThietBi.lft ';

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'STT')) {
            $sql .= ' , dstb.STT ';
        }

        $sql .= ' , dstb.TenThietBi, dstb.MaThietBi ';

        $sql .= ' LIMIT 1000000 ';

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}