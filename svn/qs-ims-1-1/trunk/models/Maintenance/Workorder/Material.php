<?php
/**
 * Class Qss_Model_Maintenance_Workorder
 * Danh muc vat tu phieu bao tri
 */
class Qss_Model_Maintenance_Workorder_Material extends Qss_Model_Abstract
{
    /**
     * Lấy các vật tư đã sử dụng trong một khoảng thời gian sắp xếp theo mã vật tư
     * @param $startDate
     * @param $endDate
     * @param int $material
     * @param int $loc
     * @param int $eType
     * @param int $eGroup
     * @param int $eq
     * @return mixed
     */
    public function getUsedMaterialsValue(
        $startDate
        , $endDate
        , $material = 0
        , $loc      = 0
        , $eType    = 0
        , $eGroup   = 0
        , $eq       = 0)
    {
        $objLoc  = $loc?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $loc)):'';
        $objType = $eType?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eType)):'';

        $where   = ($material)?sprintf(' AND MatHang.IOID = %1$d  ', $material):'';
        $where  .= $objLoc?sprintf('
            AND(
                (IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
                OR (IFNULL(PhieuBaoTri.Ref_MaKhuVuc,0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
            )'
            , $objLoc->lft, $objLoc->rgt):'';
        $where  .= $objType?sprintf('
            AND (ThietBi.Ref_LoaiThietBi IN(select IOID from OLoaiThietBi where lft>= %1$d and rgt <= %2$d))'
            , $objType->lft, $objType->rgt):'';
        $where  .= ($eGroup)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $eGroup):'';
        $where  .= ($eq)?sprintf(' AND ThietBi.IOID = %1$d  ', $eq):'';

        $sql1 = sprintf('
            SELECT
                MatHang.IOID AS Ref_SanPham
                , MatHang.MaSanPham
                , MatHang.TenSanPham
                , MatHang.DacTinhKyThuat
                , IFNULL(VatTu.Ref_DonViTinh, 0) AS Ref_DonViTinh
                , VatTu.DonViTinh
                , SUM(IFNULL(VatTu.SoLuong, 0)) AS TongSoLuong
                , SUM(IFNULL(DanhSachXuatKho.DonGia, 0)) AS TongDonGia
                , SUM(IFNULL(DanhSachXuatKho.DonGia, 0) * IFNULL(VatTu.SoLuong, 0)) AS TongThanhTien
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN qsiforms as IFormPhieuBaoTri ON PhieuBaoTri.IFID_M759 = IFormPhieuBaoTri.IFID
            INNER JOIN OVatTuPBT AS VatTu ON PhieuBaoTri.IFID_M759 = VatTu.IFID_M759
            INNER JOIN OSanPham AS MatHang ON IFNULL(VatTu.Ref_MaVatTu, 0) = MatHang.IOID
            LEFT JOIN OXuatKho AS XuatKho ON PhieuBaoTri.IOID = XuatKho.Ref_PhieuBaoTri
            LEFT JOIN qsiforms as IFormXuatKho ON XuatKho.IFID_M506 = IFormXuatKho.IFID
            LEFT JOIN ODanhSachXuatKho AS DanhSachXuatKho ON XuatKho.IFID_M506 = DanhSachXuatKho.IFID_M506
                AND IFNULL(VatTu.Ref_MaVatTu, 0) = IFNULL(DanhSachXuatKho.Ref_MaSP, 0)
                AND IFNULL(VatTu.Ref_DonViTinh, 0) = IFNULL(DanhSachXuatKho.Ref_DonViTinh, 0)
                AND IFormXuatKho.Status = 2
            LEFT JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
            WHERE (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                AND IFormPhieuBaoTri.Status = 4 %3$s
            GROUP BY MatHang.MaSanPham, IFNULL(VatTu.Ref_DonViTinh, 0)'
        , $this->_o_DB->quote($startDate)
        , $this->_o_DB->quote($endDate)
        , $where);

        // echo '<pre>'; print_r($sql1); die;
        return $this->_o_DB->fetchAll($sql1);

    }

    /**
     * Lấy vật tư phiếu bảo trì sử dụng gộp theo phiếu
     * @param $startDate
     * @param $endDate
     * @param int $material
     * @param int $loc
     * @param int $eType
     * @param int $eGroup
     * @return mixed
     */
    public function getMaterialByRangeTimeGroupByWorkOrder(
        $startDate
        , $endDate
        , $material = 0
        , $loc      = 0
        , $eType    = 0
        , $eGroup   = 0
        , $equipIOID = 0)
    {
        // Tinh gia mat hang vao mot bang tam, de join lay gia o query chinh
        $startdate = date_create($startDate);
        $enddate   = date_create($endDate);

        $sqltemp   = sprintf('
            CREATE TEMPORARY TABLE tmp
            (
                Ref_MaSanPham int NOT NULL,
        		Thang int NOT NULL,
        		Gia int NOT NULL
            )
            ENGINE = MEMORY'
        );
        $this->_o_DB->execute($sqltemp);

        while ($startdate < $enddate)
        {
            $tablename = 'tblcost'.$startdate->format('m').$startdate->format('Y');

            if($this->_o_DB->tableExists($tablename))
            {
                $sqltemp = sprintf('insert into tmp select Ref_MaSanPham,Gia,%2$d from %1$s',$tablename,$startdate->format('m'));
                $this->_o_DB->execute($sqltemp);
            }

            $startdate = Qss_Lib_Date::add_date($startdate, 1,'month');
        }

        // Lay vat tu theo phieu bao tri trong mot khoang thoi gian
        $where  = '';
        $loc1   = $loc?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $loc)):'';
        $where .= $loc1?sprintf(' AND (dstb.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc1->lft, $loc1->rgt):'';
        $eType1 = $eType?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eType)):'';
        $where .= $eType1?sprintf(' AND (dstb.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $eType1->lft, $eType1->rgt):'';
        $where .= ($eGroup)?sprintf(' AND dstb.Ref_NhomThietBi = %1$d  ', $eGroup):'';
        $where .= ($material)?sprintf(' AND Ref_MaVatTu = %1$d  ', $material):'';
        $where .= ($equipIOID)?sprintf(' AND dstb.IOID = %1$d  ', $equipIOID):'';



        $sql1 = sprintf('
            SELECT
                pbt.IFID_M759,
                pbt.SoPhieu,
                ((ifnull(vt.SoLuong, 0)) * ifnull(tmp.Gia,ifnull(OSanPham.GiaMua, 0))) AS ThanhTien,
                ifnull(tmp.Gia,ifnull(OSanPham.GiaMua, 0)) AS GiaVon,
                vt.BoPhan,
                vt.ViTri,
                vt.DonViTinh,
                vt.MaVatTu,
                vt.TenVatTu,
                vt.ThuocTinh,
                sum(ifnull(vt.SoLuong, 0)) AS SoLuong,
                IFNULL(vt.Ngay, pbt.NgayBatDau) AS NgaySuDungVatTu,
                dstb.MaThietBi,
                dstb.TenThietBi,
                OSanPham.DacTinhKyThuat

            FROM OPhieuBaoTri AS pbt
            INNER JOIN OVatTuPBT AS vt ON pbt.IFID_M759 = vt.IFID_M759
            INNER JOIN OSanPham ON vt.Ref_MaVatTu = OSanPham.IOID
            INNER JOIN ODanhSachThietBi AS dstb ON dstb.IOID = pbt.Ref_MaThietBi
            LEFT JOIN tmp on tmp.Ref_MaSanPham = OSanPham.IOID and tmp.Thang = month(pbt.Ngay)
            WHERE (pbt.Ngay BETWEEN %1$s AND %2$s) %3$s
            GROUP BY pbt.IFID_M759, IFNULL(vt.Ref_ViTri, 0), Ref_MaVatTu
            ORDER BY pbt.NgayBatDau DESC, pbt.IFID_M759'
            , $this->_o_DB->quote($startDate)
            , $this->_o_DB->quote($endDate)
            , $where);

        // echo '<pre>'; print_r($sql1); die;
        return $this->_o_DB->fetchAll($sql1);

    }

    public function getSerialHistory($startDate, $endDate, $item = 0, $serial = 0)
    {
//        SELECT *
//        FROM ODanhSachThietBi AS ThietBi
//            INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
//    AND IFNULL(CauTruc.Ref_Serial, 0) != 0
//            LEFT JOIN (
//        SELECT *
//        FROM ODanhSachThietBi AS ThietBi
//                INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
//                INNER JOIN OPhieuBaoTri AS Phieu ON ThietBi.IOID = Phieu.Ref_MaThietBi
//                INNER JOIN qsiforms AS iFormPhieu ON Phieu.IFID_M759 = iFormPhieu.IFID
//                INNER JOIN OVatTuPBT AS VatTu ON Phieu.IFID_M759 = VatTu.IFID_M759
//    AND CauTruc.IOID = IFNULL(VatTu.Ref_ViTri, 0)
//                WHERE (IFNULL(VatTu.Ref_Serial, 0) != 0 OR IFNULL(VatTu.Ref_SerialKhac, 0) != 0)
//                AND iFormPhieu.Status = 4
//                AND IFNULL(VatTu.HinhThuc, 0) IN (0, 1)
//            ) AS PhieuThayThe

        $where = ($item)?sprintf(' AND VatTu.Ref_MaVatTu = %1$d ', $item):'';
        $where.= ($serial)?sprintf(' AND (IFNULL(VatTu.Ref_Serial, 0) = %1$d OR IFNULL(VatTu.Ref_SerialKhac, 0) = %1$d) ', $serial):'';

        $sql = sprintf('
            SELECT *
            FROM
            (
                SELECT
                    ThietBi.IOID AS Ref_ThietBi
                    , ThietBi.MaThietBi
                    , ThietBi.TenThietBi
                    , IF(IFNULL(VatTu.Ngay, "") != "", VatTu.Ngay, Phieu.NgayBatDau) AS NgayCaiDatThaoDo
                    , CauTruc.IOID AS Ref_ViTri
                    , CauTruc.ViTri
                    , CauTruc.BoPhan
                    , VatTu.Serial
                    , IFNULL(VatTu.Ref_Serial, 0) AS Ref_Serial
                    , TrangThai.SoSerial AS SerialKhac
                    , IFNULL(TrangThai.IOID, 0) AS Ref_SerialKhac
                    , VatTu.MaVatTu
                    , VatTu.TenVatTu
                FROM ODanhSachThietBi AS ThietBi
                INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                INNER JOIN OPhieuBaoTri AS Phieu ON ThietBi.IOID = Phieu.Ref_MaThietBi
                INNER JOIN qsiforms AS iFormPhieu ON Phieu.IFID_M759 = iFormPhieu.IFID
                INNER JOIN OVatTuPBT AS VatTu ON Phieu.IFID_M759 = VatTu.IFID_M759
                    AND CauTruc.IOID = IFNULL(VatTu.Ref_ViTri, 0)
                LEFT JOIN OThuocTinhChiTiet AS TrangThai ON IFNULL(VatTu.SerialKhac, "") = IFNULL(TrangThai.SoSerial, "")
                    AND IFNULL(VatTu.Ref_MaVatTu, 0) = IFNULL(TrangThai.Ref_MaSanPham, 0)
                WHERE (IFNULL(VatTu.Ref_Serial, 0) != 0 OR IFNULL(VatTu.Ref_SerialKhac, 0) != 0)
                    -- AND iFormPhieu.Status = 4
                    AND IFNULL(VatTu.HinhThuc, 0) IN (0, 1)
                    AND (IF(IFNULL(VatTu.Ngay, "") != "", VatTu.Ngay, Phieu.NgayBatDau) BETWEEN %1$s AND %2$s)
                    %3$s
                ORDER BY IF(IFNULL(VatTu.Ngay, "") != "", VatTu.Ngay, Phieu.NgayBatDau), Phieu.IFID_M759
            ) AS CoPhieuBaoTriThayTheLapMoi

            UNION

            SELECT
                ThietBi.IOID AS Ref_ThietBi
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , NULL AS NgayCaiDatThaoDo
                , CauTruc.IOID AS Ref_ViTri
                , CauTruc.ViTri
                , CauTruc.BoPhan
                , CauTruc.Serial
                , IFNULL(CauTruc.Ref_Serial, 0) AS Ref_Serial
                , NULL AS SerialKhac
                , 0 AS Ref_SerialKhac
                , CauTruc.MaSP AS MaVatTu
                , CauTruc.TenSP AS TenVatTu
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
            LEFT JOIN OPhieuBaoTri AS Phieu ON ThietBi.IOID = Phieu.Ref_MaThietBi
            LEFT JOIN OVatTuPBT AS VatTu ON Phieu.IFID_M759 = VatTu.IFID_M759
                AND CauTruc.IOID = IFNULL(VatTu.Ref_ViTri, 0)
            WHERE IFNULL(CauTruc.Ref_Serial, 0) != 0
                AND (IFNULL(Phieu.IFID_M759, 0) = 0 OR
                (IFNULL(VatTu.Ref_Serial, 0) = 0 AND IFNULL(VatTu.Ref_SerialKhac, 0) = 0))
            GROUP BY ThietBi.IFID_M705, CauTruc.IOID


        ', $this->_o_DB->quote($startDate)
        , $this->_o_DB->quote($endDate)
        , $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
	public function getLifeCycle(
        $start
        , $eqGroup = 0
        , $eqType = 0
        , $eqID = 0
        , $itemID = 0
        , $componentID = 0)
    {
        $db     = $this->_o_DB;
        $where  = '';
        $where .= $eqGroup?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d', $eqGroup):'';
        $types  = $eqType?$db->fetchOne(sprintf(' select * from OLoaiThietBi where IOID = %1$d ', $eqType)):'';
        $where .= $types?sprintf(' AND ThietBi.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d ) ', $types->lft, $types->rgt):'';
        $where .= $eqID?sprintf(' AND ThietBi.IOID = %1$d ', $eqID):'';
        $where .= $itemID?sprintf(' AND VatTu.Ref_MaVatTu = %1$d ', $itemID):'';
        $eq     = $eqID?$this->_o_DB->fetchOne(sprintf(' select * FROM ODanhSachThietBi AS ThietBi WHERE IOID = %1$d ', $eqID)):'';
        $eqIFID = $eq?$eq->IFID_M705:0;
        $coms   = ($eqID && $componentID)?$db->fetchOne(sprintf(' select * from OCauTrucThietBi where IOID = %1$d AND IFID_M705 = %2$d ', $componentID, $eqIFID)):'';
        $where .= $coms?sprintf(' AND CauTruc.IOID IN  (select IOID from OCauTrucThietBi where lft>= %1$d and  rgt <= %2$d AND IFID_M705 = %3$d) ', $coms->lft, $coms->rgt, $eqIFID):'';

        $sql = sprintf('
            SELECT 
                TuoiTho.*
                , max(Duration) as MaxDuration
                , min(Duration) as MinDuration
                , IF((IFNULL(TuoiTho.SoLan, 1) - 1) > 1, sum(IFNULL(Duration, 0)/(IFNULL(TuoiTho.SoLan, 1) - 1)), 0) as AvgDuration
                , Group_concat(Duration Separator ",") as ts	
            FROM
            (
                SELECT
                    ThietBi.IOID AS Ref_MaThietBi
                    , ThietBi.MaThietBi
                    , ThietBi.TenThietBi
                    , CauTruc.IOID AS Ref_ViTri
                    , CauTruc.BoPhan
                    , CauTruc.ViTri
                    , CauTruc.SoNgayCanhBao
                    , CauTruc.lft AS lft_ViTri
                    , MatHang.IOID AS Ref_MaVatTu
                    , MatHang.MaSanPham
                    , MatHang.TenSanPham	
                    , LanCuoi.SoLan
                    , LanCuoi.NgayThayTheCuoi
                    , LanCuoi.TuoiThoLanCuoi
                    , DATEDIFF(
                        IFNULL(VatTu.Ngay, PhieuBaoTri.NgayBatDau) ,
                        (
                            SELECT MAX(IFNULL(vt.Ngay, t.NgayBatDau)) 
                            FROM OPhieuBaoTri as t
                            INNER JOIN OVatTuPBT AS vt ON t.IFID_M759 = vt.IFID_M759
                            WHERE 
                                IFNULL(vt.Ngay, t.NgayBatDau) < IFNULL(VatTu.Ngay, PhieuBaoTri.NgayBatDau) 
                                and Ref_MaThietBi = PhieuBaoTri.Ref_MaThietBi
                                and IFNULL(Ref_ViTri, 0) = IFNULL(VatTu.Ref_ViTri, 0)
                                and Ref_MaVatTu = VatTu.Ref_MaVatTu
                        )   
                    ) AS Duration
                FROM OVatTuPBT AS VatTu
                INNER JOIN OSanPham AS MatHang ON VatTu.Ref_MaVatTu = MatHang.IOID
                INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON VatTu.IFID_M759 = PhieuBaoTri.IFID_M759
                INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705 AND IFNULL(VatTu.Ref_ViTri, 0) = CauTruc.IOID
                INNER JOIN (
                    SELECT 
                        ThietBi.IOID AS Ref_MaThietBi
                        , IFNULL(CauTruc.IOID, 0) AS Ref_ViTri
                        , MatHang.IOID AS Ref_MaVatTu
                        , MAX(IFNULL(VatTu.Ngay, PhieuBaoTri.NgayBatDau)) AS NgayThayTheCuoi
                        , COUNT(1) AS SoLan
                        , DATEDIFF(
                            MAX(IFNULL(VatTu.Ngay, PhieuBaoTri.NgayBatDau)) ,
                            (
                                SELECT MAX(IFNULL(vt.Ngay, t.NgayBatDau)) 
                                FROM OPhieuBaoTri as t
                                INNER JOIN OVatTuPBT AS vt ON t.IFID_M759 = vt.IFID_M759
                                WHERE 
                                    IFNULL(vt.Ngay, t.NgayBatDau) < MAX(IFNULL(VatTu.Ngay, PhieuBaoTri.NgayBatDau))            
                                    and IFNULL(vt.Ngay, t.NgayBatDau) >= %2$s
                                    and Ref_MaThietBi = PhieuBaoTri.Ref_MaThietBi
                                    and IFNULL(Ref_ViTri, 0) = IFNULL(VatTu.Ref_ViTri, 0)
                                    and Ref_MaVatTu = VatTu.Ref_MaVatTu
                            )   
                        ) AS TuoiThoLanCuoi
                    FROM OVatTuPBT AS VatTu
                    INNER JOIN OSanPham AS MatHang ON VatTu.Ref_MaVatTu = MatHang.IOID
                    INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON VatTu.IFID_M759 = PhieuBaoTri.IFID_M759
                    INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
                    INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                    LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705 AND IFNULL(VatTu.Ref_ViTri, 0) = CauTruc.IOID
                    WHERE IFNULL(VatTu.Ngay, PhieuBaoTri.NgayBatDau) >= %2$s AND IFNULL(MatHang.PhuTung, 0) = 1 AND qsiforms.Status IN (3, 4) %1$s
                    GROUP BY ThietBi.IOID, CauTruc.IOID, MatHang.IOID
                ) AS LanCuoi ON LanCuoi.Ref_MaThietBi = ThietBi.IOID AND LanCuoi.Ref_ViTri = IFNULL(CauTruc.IOID, 0) AND LanCuoi.Ref_MaVatTu = MatHang.IOID
            
            
                WHERE ( IFNULL(VatTu.Ngay, PhieuBaoTri.NgayBatDau) BETWEEN %2$s AND %3$s) AND IFNULL(MatHang.PhuTung, 0) = 1 AND qsiforms.Status IN (3, 4) %1$s
                
            ) AS TuoiTho
            GROUP BY TuoiTho.Ref_MaThietBi, TuoiTho.Ref_ViTri, TuoiTho.Ref_MaVatTu
            ORDER BY TuoiTho.TenThietBi, TuoiTho.lft_ViTri, TuoiTho.TenSanPham
        '
            ,  $where, $db->quote($start), $db->quote(date('Y-m-d')));
         //echo '<pre>'; print_r($sql); die;
        return $db->fetchAll($sql);
    }
}