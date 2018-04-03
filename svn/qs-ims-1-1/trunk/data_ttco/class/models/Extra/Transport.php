<?php
class Qss_Model_Extra_Transport extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function filterByDept($field)
    {
        return sprintf(' %1$s in (%2$s)', $field, $this->_user->user_dept_list);
    }

    public function fitlerByLocation($field, $locationIOID)
    {
        $filter = '';
        if($locationIOID)
        {
            $sqlLocName = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID);
            $locName    = $this->_o_DB->fetchOne($sqlLocName);

            if ($locName)
            {
                $filter = sprintf('
						%3$s IN (
							SELECT
								IOID
							FROM
								OKhuVuc
							WHERE
								lft >= %1$d
							AND rgt <= %2$d
						)
				', $locName->lft, $locName->rgt, $field);
            }
        }

        return $filter;
    }


    public function filterByEqType($field, $eqtypeIOID)
    {
        $filter = '';
        if($eqtypeIOID)
        {
            $sqlEqtypeName = sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eqtypeIOID);
            $EqtypeName    = $this->_o_DB->fetchOne($sqlEqtypeName);

            if ($EqtypeName)
            {
                $filter = sprintf('
						%3$s IN (
							SELECT
								IOID
							FROM
								OLoaiThietBi
							WHERE
								lft >= %1$d
							AND rgt <= %2$d
						)
				', $EqtypeName->lft, $EqtypeName->rgt, $field);
            }
        }

        return $filter;
    }

    public function getTransportEquips($locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' WHERE %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT
                tb.*
            FROM ODanhSachThietBi AS tb
                %1$s
        ',  $filterSql);
        return  $this->_o_DB->fetchAll($sql);
    }

    public function getLuyKeHoatDongDenThang($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT
              tb.IOID  as EQIOID
              , tb.TenThietBi
              , tb.MaThietBi
              , sum(
                  CASE WHEN ifnull(Km,0) >0
                  THEN ifnull(Km,0)
                  ELSE ifnull(Gio,0)
                  END
              ) AS LuyKeHoatDong

            FROM ODanhSachThietBi AS tb
            LEFT JOIN OHoatDongThietBiVanTai AS hdtbvt on tb.IOID = hdtbvt.Ref_MaThietBi
            WHERE
                MONTH(hdtbvt.Ngay) <= %1$d
                AND YEAR(hdtbvt.Ngay) <= %2$d
                %3$s
            GROUP BY tb.IOID
        ', $month, $year,  $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }



    public function countMaintTypeOfEquip($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('

                SELECT
                    tb.IOID AS EQIOID
                    ,pbt.LoaiBaoTri AS MainType
                    ,count(1) AS Num
                FROM ODanhSachThietBi AS tb
                LEFT JOIN OPhieuBaoTri AS pbt ON pbt.Ref_MaThietBi = tb.IOID
                WHERE
                    MONTH(pbt.NgayBatDau) = %1$d
                    AND YEAR(pbt.NgayBatDau) = %2$d
                    %3$s
                GROUP BY tb.IOID, pbt.Ref_LoaiBaoTri

        ', $month, $year, $filterSql);


        return  $this->_o_DB->fetchAll($sql);
    }


    public function getActiveByMonth($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT
              tb.IOID  as EQIOID
              , tb.TenThietBi
              , tb.MaThietBi
              , sum(
                  CASE WHEN ifnull(Km,0) >0
                  THEN ifnull(Km,0)
                  ELSE ifnull(Gio,0)
                  END
              ) AS TongHoatDong
              , sum(ifnull(Gio,0)) AS TongGio
              , sum(ifnull(Km,0)) AS TongKm
              , sum(ifnull(Tan,0)) AS TongTan
              , sum(ifnull(TKm,0)) AS TongTKm
              , sum(ifnull(Xang,0)) AS TongXang
              , sum(ifnull(Gadoan,0)) AS TongGadoan
              , sum(ifnull(Mo,0)) AS TongMo

            FROM ODanhSachThietBi AS tb
            LEFT JOIN OHoatDongThietBiVanTai AS hdtbvt on tb.IOID = hdtbvt.Ref_MaThietBi
            WHERE
                MONTH(hdtbvt.Ngay) = %1$d
                AND YEAR(hdtbvt.Ngay) = %2$d
                %3$s
            GROUP BY tb.IOID
        ', $month, $year,  $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }

    public function getHoatDongDongCoCuoiCung($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('


            SELECT
              tb.IOID  as EQIOID
              , tb.TenThietBi
              , tb.MaThietBi
              , sum(
                  CASE WHEN ifnull(hdtbvt.Km,0) >0
                  THEN ifnull(hdtbvt.Km,0)
                  ELSE ifnull(hdtbvt.Gio,0)
                  END
              ) AS TongHoatDong
              , hdtbvt.SoDongCo
            FROM OHoatDongThietBiVanTai as hdtbvt
            INNER JOIN ODanhSachThietBi AS tb on tb.IOID = hdtbvt.Ref_MaThietBi
            INNER JOIN
            (
                SELECT *
                FROM
                (
                    SELECT hdtbvt.*, tb.IOID AS EQIOID
                    FROM OHoatDongThietBiVanTai as hdtbvt
                    INNER JOIN ODanhSachThietBi AS tb on tb.IOID = hdtbvt.Ref_MaThietBi
                    WHERE
                      MONTH(hdtbvt.Ngay) <= %1$d
                      AND YEAR(hdtbvt.Ngay) <= %2$d
                      %3$s
                    ORDER BY tb.IOID, hdtbvt.Ngay DESC
                ) AS table1
                GROUP BY EQIOID
            ) AS tabl1 ON hdtbvt.Ref_SoDongCo = tabl1.Ref_SoDongCo AND tabl1.EQIOID =  hdtbvt.Ref_MaThietBi
            WHERE
              MONTH(hdtbvt.Ngay) = %1$d
              AND YEAR(hdtbvt.Ngay) = %2$d
              %3$s
        ', $month, $year, $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }


    public function getLuyKeHoatDongDongCoCuoiCung($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('


            SELECT
              tb.IOID  as EQIOID
              , tb.TenThietBi
              , tb.MaThietBi
              , sum(
                  CASE WHEN ifnull(hdtbvt.Km,0) >0
                  THEN ifnull(hdtbvt.Km,0)
                  ELSE ifnull(hdtbvt.Gio,0)
                  END
              ) AS TongHoatDong
              , sum(
                  CASE WHEN
                      hdtbvt.Ngay > scl.StartDate
                  THEN
                      (CASE WHEN ifnull(hdtbvt.Km,0) >0
                      THEN ifnull(hdtbvt.Km,0)
                      ELSE ifnull(hdtbvt.Gio,0)
                      END)
                  ELSE
                    0
                  END

              ) AS SauSCL
              , hdtbvt.SoDongCo
            FROM OHoatDongThietBiVanTai as hdtbvt
            INNER JOIN ODanhSachThietBi AS tb on tb.IOID = hdtbvt.Ref_MaThietBi
            INNER JOIN
            (
                SELECT *
                FROM
                (
                    SELECT hdtbvt.*, tb.IOID AS EQIOID
                    FROM OHoatDongThietBiVanTai as hdtbvt
                    INNER JOIN ODanhSachThietBi AS tb on tb.IOID = hdtbvt.Ref_MaThietBi
                    WHERE
                      MONTH(hdtbvt.Ngay) <= %1$d
                      AND YEAR(hdtbvt.Ngay) <= %2$d
                      %3$s
                    ORDER BY tb.IOID, hdtbvt.Ngay DESC
                ) AS table1
                GROUP BY EQIOID
            ) AS tabl1 ON hdtbvt.Ref_SoDongCo = tabl1.Ref_SoDongCo AND tabl1.EQIOID =  hdtbvt.Ref_MaThietBi

            LEFT JOIN
            (
                SELECT *
                FROM
                (
                    SELECT
                        tb.IOID AS EQIOID
                        , tb.TenThietBi
                        , tb.MaThietBi
                        , ifnull(pbt.NgayBatDau, \'\') AS `StartDate`
                        , ifnull(pbt.Ngay, \'\') AS `EndDate`
                    FROM ODanhSachThietBi AS tb
                    LEFT JOIN OPhieuBaoTri AS pbt ON pbt.Ref_MaThietBi = tb.IOID
                    WHERE
                        pbt.LoaiBaoTri = %4$s
                        %3$s
                    ORDER BY pbt.Ref_MaThietBi, pbt.NgayBatDau DESC
                ) AS table1
                GROUP BY EQIOID
            ) AS scl ON scl.EQIOID = tabl1.EQIOID

            WHERE
              MONTH(hdtbvt.Ngay) <= %1$d
              AND YEAR(hdtbvt.Ngay) <= %2$d
              %3$s
        ', $month, $year, $filterSql, $this->_o_DB->quote(Qss_Lib_Ttco::SUA_CHUA_LON));

        return  $this->_o_DB->fetchAll($sql);
    }

    public function getHoatDongSauLoaiBaoTri($month, $year, $maintType, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('

            SELECT table2.*
              , sum(
                  CASE WHEN ifnull(hdtbvt.Km,0) >0
                  THEN ifnull(hdtbvt.Km,0)
                  ELSE ifnull(hdtbvt.Gio,0)
                  END
              ) AS TongHoatDong
            FROM OHoatDongThietBiVanTai as hdtbvt
            INNER JOIN
            (
                SELECT *
                FROM
                (
                    SELECT
                        tb.IOID AS EQIOID
                        , tb.TenThietBi
                        , tb.MaThietBi
                        , ifnull(pbt.NgayBatDau, \'\') AS `StartDate`
                        , ifnull(pbt.Ngay, \'\') AS `EndDate`
                    FROM ODanhSachThietBi AS tb
                    LEFT JOIN OPhieuBaoTri AS pbt ON pbt.Ref_MaThietBi = tb.IOID
                    WHERE
                        pbt.LoaiBaoTri = %1$s
                        %2$s
                    ORDER BY pbt.Ref_MaThietBi, pbt.NgayBatDau DESC
                ) AS table1
                GROUP BY EQIOID
            ) AS table2 ON hdtbvt.Ref_MaThietBi = table2.EQIOID
                AND (ifnull(table2.StartDate, \'\')  = \'\' OR hdtbvt.Ngay >= table2.StartDate)
            GROUP BY table2.EQIOID

        ', $this->_o_DB->quote($maintType), $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }

    public function getLastMaintainByType($month, $year, $maintType, $locationIOID = 0, $eqtypeIOID = 0)
    {

        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT *
            FROM
            (
                SELECT
                    tb.IOID AS EQIOID
                    , tb.TenThietBi
                    , tb.MaThietBi
                    , ifnull(pbt.NgayBatDau, \'\') AS `StartDate`
                    , ifnull(pbt.Ngay, \'\') AS `EndDate`
                FROM ODanhSachThietBi AS tb
                LEFT JOIN OPhieuBaoTri AS pbt ON pbt.Ref_MaThietBi = tb.IOID
                WHERE
                    pbt.LoaiBaoTri = %1$s
                    %2$s
                ORDER BY pbt.Ref_MaThietBi, pbt.NgayBatDau DESC
            ) AS table1
            GROUP BY EQIOID
        ', $this->_o_DB->quote($maintType), $filterSql);

        return  $this->_o_DB->fetchAll($sql);

    }

    public function getLuyKeSanLuong($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT
              tb.IOID  as EQIOID
              , tb.TenThietBi
              , tb.MaThietBi
              , sum(ifnull(Tan,0)) AS LuyKeTongTan
            FROM ODanhSachThietBi AS tb
            LEFT JOIN OHoatDongThietBiVanTai AS hdtbvt on tb.IOID = hdtbvt.Ref_MaThietBi
            WHERE
                MONTH(hdtbvt.Ngay) = %1$d
                AND YEAR(hdtbvt.Ngay) = %2$d
                %3$s
            GROUP BY tb.IOID
        ', $month, $year,  $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }

    public function getTieuHaoNhienLieu($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT
              tb.IOID  as EQIOID
              , thbp.ViTri
              , sum(ifnull(thbp.TieuHao,0)) AS TieuHao
            FROM OHoatDongThietBiVanTai AS hdtbvt
            INNER JOIN ODanhSachThietBi AS tb on tb.IOID = hdtbvt.Ref_MaThietBi
            INNER JOIN OTieuHaoBoPhan AS thbp ON hdtbvt.IFID_M776 = thbp.IFID_M776
            WHERE
                MONTH(hdtbvt.Ngay) = %1$d
                AND YEAR(hdtbvt.Ngay) = %2$d
                %3$s
            GROUP BY tb.IOID, thbp.ViTri
        ', $month, $year,  $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }

    public function getQuyetToan($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT
              tb.IOID  as EQIOID
              , chitiet.LoaiBaoTri
              , sum(ifnull(chitiet.GiaTri,0)) AS GiaTri
            FROM OQuyetToanSuaChua AS quyettoan
            INNER JOIN OChiTietQuyetToanSuaChua AS chitiet ON quyettoan.IFID_M774 = chitiet.IFID_M774
            INNER JOIN ODanhSachThietBi AS tb on tb.IOID = chitiet.Ref_MaThietBi
            WHERE
                quyettoan.Thang = %1$d
                AND quyettoan.Nam = %2$d
                %3$s
            GROUP BY tb.IOID, chitiet.LoaiBaoTri
            ORDER BY tb.IOID, chitiet.LoaiBaoTri
        ', $month, $year,  $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }

    public function getLuyKeQuyetToan($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $filter    = array();
        $filterLoc = $this->fitlerByLocation('tb.Ref_MaKhuVuc', $locationIOID);
        $filterEqt = $this->filterByEqType('tb.Ref_LoaiThietBi', $eqtypeIOID);
        $filterDept = $this->filterByDept('tb.DeptID');

        if($filterLoc) $filter[]  = $filterLoc;
        if($filterEqt) $filter[]  = $filterEqt;
        if($filterDept) $filter[]  = $filterDept;

        $filterSql = count($filter)?sprintf(' AND %1$s ', implode(' AND ', $filter)):'';

        $sql = sprintf('
            SELECT
              tb.IOID  as EQIOID
              , chitiet.LoaiBaoTri
              , sum(ifnull(chitiet.GiaTri,0)) AS GiaTri
            FROM OQuyetToanSuaChua AS quyettoan
            INNER JOIN OChiTietQuyetToanSuaChua AS chitiet ON quyettoan.IFID_M774 = chitiet.IFID_M774
            INNER JOIN ODanhSachThietBi AS tb on tb.IOID = chitiet.Ref_MaThietBi
            WHERE
                quyettoan.Thang <= %1$d
                AND quyettoan.Nam <= %2$d
                %3$s
            GROUP BY tb.IOID
        ', $month, $year,  $filterSql);

        return  $this->_o_DB->fetchAll($sql);
    }
}