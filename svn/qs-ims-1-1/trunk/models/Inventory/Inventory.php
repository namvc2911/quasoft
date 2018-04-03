<?php

/**
 * Function List
 *
 * getItemConfigByInventory($inventoryIFID): Lấy cấu hình sản phẩm theo dòng tồn kho
 *
 */

/**
 * Class Qss_Model_Inventory_Inventory
 */
class Qss_Model_Inventory_Inventory extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getStockStatusOfInput($inputIFID, $inputLineIOID = 0)
    {
        $where = ($inputLineIOID)?sprintf(' IFNULL(DSNhapKho.IOID, 0) = %1$d ', $inputLineIOID):'';

        $sql = sprintf('
            Select TrangThai.*, IFNULL(DSNhapKho.IOID,0) AS InputLineIOID
            FROM OThuocTinhChiTiet AS TrangThai
            LEFT JOIN ODanhSachNhapKho AS DSNhapKho ON TrangThai.IFID_M402 = DSNhapKho.IFID_M402
              AND TrangThai.Ref_MaSanPham = IFNULL(DSNhapKho.Ref_MaSanPham, 0)
              AND TrangThai.Ref_DonViTinh = IFNULL(DSNhapKho.Ref_DonViTinh, 0)
              AND IFNULL(TrangThai.Ref_MaThuocTinh, 0) = IFNULL(DSNhapKho.Ref_ThuocTinh, 0)
            WHERE TrangThai.IFID_M402 = %1$d %2$s
        ', $inputIFID, $where);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getStockStatusOfOutput($outputIFID, $outputLineIOID = 0)
    {
        $where = ($outputLineIOID)?sprintf(' IFNULL(DSXuatKho.IOID, 0) = %1$d ', $outputLineIOID):'';

        $sql = sprintf('
            Select TrangThai.*, IFNULL(DSXuatKho.IOID,0) AS OutputLineIOID
            FROM OThuocTinhChiTiet AS TrangThai
            LEFT JOIN ODanhSachXuatKho AS DSXuatKho ON TrangThai.IFID_M506 = DSXuatKho.IFID_M506
              AND TrangThai.Ref_MaSanPham = IFNULL(DSXuatKho.Ref_MaSP, 0)
              AND TrangThai.Ref_DonViTinh = IFNULL(DSXuatKho.Ref_DonViTinh, 0)
              AND IFNULL(TrangThai.Ref_MaThuocTinh, 0) = IFNULL(DSXuatKho.Ref_ThuocTinh, 0)
            WHERE TrangThai.IFID_M506 = %1$d %2$s
        ', $outputIFID, $where);
        return $this->_o_DB->fetchAll($sql);
    }


    public function getInputStockStatus($ifid)
    {
        $sql = sprintf(' select * FROM OThuocTinhChiTiet WHERE IFID_M402 = %1$d', $ifid);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getOutputStockStatus($ifid)
    {
        $sql = sprintf(' select * FROM OThuocTinhChiTiet WHERE IFID_M506 = %1$d', $ifid);
        return $this->_o_DB->fetchAll($sql);
    }

    /* --- MASTER DATA --- */

    public function getBinListByWarehouse($warehouseIOID)
    {
        $sql = sprintf('
            SELECT Bin.*,
            (SELECT count(*) FROM OBin AS u WHERE u.lft <= Bin.lft AND u.rgt >= Bin.rgt AND u.IFID_M601 = Bin.IFID_M601) AS LEVEL
            FROM ODanhSachKho AS Kho
            INNER JOIN OBin AS Bin ON Kho.IFID_M601 = Bin.IFID_M601
            WHERE Kho.IOID = %1$d
            ORDER BY Bin.lft
        ', $warehouseIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getWarehouses()
    {
        if(Qss_Lib_System::fieldActive('ODanhSachKho', 'TrucThuoc'))
        {
            $sql = sprintf('SELECT * FROM ODanhSachKho ORDER BY lft');
        }
        else
        {
            $sql = sprintf('SELECT * FROM ODanhSachKho ORDER BY MaKho');
        }
        return $this->_o_DB->fetchAll($sql);
    }

    public function getWarehouseByIOID($warehouseIOID)
    {
        $sql = sprintf('SELECT * FROM ODanhSachKho WHERE IOID = %1$d', $warehouseIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getInputTypes()
    {
        $sql = sprintf('SELECT *  FROM OLoaiNhapKho ORDER BY Ten');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInputTypeByCode($code)
    {
        $sql = sprintf('SELECT * FROM OLoaiNhapKho WHERE Loai = %1$s', $this->_o_DB->quote($code));
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOutputTypes()
    {
        $sql = sprintf('SELECT * FROM OLoaiXuatKho ORDER BY Ten');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInputByIFID($ifid)
    {
        $sql = sprintf('
            SELECT NhapKho.*
            FROM ONhapKho AS NhapKho
            WHERE NhapKho.IFID_M402 = %1$d
        ', $ifid);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function getInputLineByInputIFID($inputIFID)
    {
        $sql = sprintf('
            SELECT DSNhapKho.*, NhapKho.IOID AS InputIOID, iform.Status
            FROM ONhapKho AS NhapKho
            INNER JOIN qsiforms AS iform ON NhapKho.IFID_M402 = iform.IFID
            INNER JOIN ODanhSachNhapKho AS DSNhapKho ON NhapKho.IFID_M402 = DSNhapKho.IFID_M402
            WHERE NhapKho.IFID_M402 = %1$d
        ', $inputIFID);
        return $this->_o_DB->fetchAll($sql);
    }

//    public function getItemsNotInInput($inputIFID)
//    {
//        $sql = sprintf('
//            select OSanPham.*, ODonViTinhSP.IOID AS Ref_DonViTinh
//            from OSanPham
//            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113
//                AND OSanPham.Ref_DonViTinh = ODonViTinhSP.Ref_DonViTinh
//            LEFT JOIN (
//                SELECT DSNhapKho.*
//                FROM ONhapKho AS NhapKho
//                INNER JOIN ODanhSachNhapKho AS DSNhapKho ON NhapKho.IFID_M402 = DSNhapKho.IFID_M402
//                WHERE NhapKho.IFID_M402 = %1$d
//            ) AS `Input` ON  OSanPham.IOID = Input.Ref_MaSanPham AND  ODonViTinhSP.IOID = Input.Ref_DonViTinh
//            WHERE ifnull(Input.IOID, 0) = 0
//            order by OSanPham.MaSanPham
//        ', $inputIFID);
//        // echo '<pre>'; print_r($sql); die;
//        return $this->_o_DB->fetchAll($sql);
//    }

    public function getOutputTypeByIOID($ioid)
    {
        $sql = sprintf('SELECT * FROM OLoaiXuatKho WHERE IOID = %1$d', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOutputByIOID($outputIOID)
    {
        $sql = sprintf('SELECT * FROM OXuatKho WHERE IOID = %1$d', $outputIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOutputByIFID($outputIFID)
    {
        $sql = sprintf('
            SELECT OXuatKho.* , qsworkflowsteps.Color, qsworkflowsteps.Name, qsiforms.Status
            FROM OXuatKho 
            INNER JOIN qsiforms ON qsiforms.IFID = OXuatKho.IFID_M506
            INNER JOIN qsworkflows  ON qsworkflows.FormCode = qsiforms.FormCode
			INNER JOIN qsworkflowsteps ON qsworkflowsteps.WFID = qsworkflows.WFID 
			    AND qsiforms.Status = qsworkflowsteps.StepNo             
            WHERE IFID_M506 = %1$d'
        , $outputIFID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOutputLineByOuputIOID($outputIOID)
    {
        $sql = sprintf('
            SELECT DanhSachXK.*, SanPham.IFID_M113
            FROM OXuatKho AS XuatKho
            INNER JOIN ODanhSachXuatKho AS DanhSachXK ON XuatKho.IFID_M506 = DanhSachXK.IFID_M506
            INNER JOIN OSanPham AS SanPham ON DanhSachXK.Ref_MaSP = SanPham.IOID
            WHERE XuatKho.IOID = %1$d
        ', $outputIOID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getOutputLineByOuputLineIOID($outputLineIOID)
    {
        $sql = sprintf('
            SELECT DanhSachXK.*, SanPham.IFID_M113
            FROM  ODanhSachXuatKho AS DanhSachXK 
            INNER JOIN OSanPham AS SanPham ON DanhSachXK.Ref_MaSP = SanPham.IOID
            WHERE DanhSachXK.IOID = %1$d
        ', $outputLineIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOutputLineByOuputIFID($outputIFID)
    {
        $sql = sprintf('
            SELECT DanhSachXK.*, SUM( IFNULL(Kho.SoLuongHC, 0) ) AS TonKho
            FROM OXuatKho AS XuatKho
            INNER JOIN ODanhSachXuatKho AS DanhSachXK ON XuatKho.IFID_M506 = DanhSachXK.IFID_M506
            LEFT JOIN OKho AS Kho ON XuatKho.Ref_Kho = Kho.Ref_Kho
                AND DanhSachXK.Ref_MaSP = Kho.Ref_MaSP
                AND DanhSachXK.Ref_DonViTinh = Kho.Ref_DonViTinh
                AND IFNULL(DanhSachXK.Ref_ThuocTinh,0) = IFNULL(Kho.Ref_ThuocTinh,0)
            WHERE XuatKho.IFID_M506 = %1$d
            GROUP BY DanhSachXK.Ref_MaSP, DanhSachXK.Ref_DonViTinh, IFNULL(DanhSachXK.Ref_ThuocTinh,0)
        ', $outputIFID);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getBinConfigOfInputLine($inputIFID, $inputLineIOID = 0)
    {
        $where = '';
        $where.= $inputLineIOID?sprintf(' AND NhapKho.IOID = %1$d ', $inputLineIOID):'';

        $sql = sprintf('
            SELECT
                TrangThai.*
                , TrangThai.IOID AS StockStatusIOID
                , NhapKho.IOID AS InputLineIOID
                , Nhap.Ref_Kho AS RefWarehouse
                , IFNULL(MatHang.QuanLyTheoMa, 0) AS QuanLyTheoMa
            FROM ODanhSachNhapKho AS NhapKho
            INNER JOIN OSanPham AS MatHang ON NhapKho.Ref_MaSanPham = MatHang.IOID
            INNER JOIN ONhapKho AS Nhap ON NhapKho.IFID_M402 = Nhap.IFID_M402
            INNER JOIN OThuocTinhChiTiet AS TrangThai ON NhapKho.IFID_M402 = TrangThai.IFID_M402
                AND Nhap.Ref_Kho = TrangThai.Ref_Kho
                AND NhapKho.Ref_MaSanPham = TrangThai.Ref_MaSanPham
                AND NhapKho.Ref_DonViTinh = TrangThai.Ref_DonViTinh
                -- AND IFNULL(NhapKho.Ref_ThuocTinh, 0) = IFNULL(TrangThai.Ref_MaThuocTinh, 0)
            WHERE NhapKho.IFID_M402 = %1$d
                %2$s
            ORDER BY NhapKho.IFID_M402, NhapKho.IOID, TrangThai.IOID
        ', $inputIFID, $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getStockStatusByItem($stockIOID, $itemIOID, $uomIOID)
    {
        $sql = sprintf('
            SELECT 
                0 AS OutputLineIOID
                , %1$d  AS RefWarehouse
                , OThuocTinhChiTiet.*
                , IFNULL(OThuocTinhChiTiet.Ref_Bin, 0) AS Ref_Bin
                , IFNULL(OThuocTinhChiTiet.SoLuong, 0) AS TonKho
                , 0 AS SoLuong
                , IFNULL(OThuocTinhChiTiet.IOID, 0) AS StockStatusIOID
                , OThuocTinhChiTiet.SoSerial AS SoSerialTrangThai            
            FROM OThuocTinhChiTiet
            WHERE 
                IFNULL(Ref_Kho, 0) = %1$d 
                AND IFNULL(IFID_M602, 0) != 0
                AND IFNULL(Ref_MaSanPham, 0) = %2$d
                AND IFNULL(Ref_DonViTinh, 0) = %3$d
        ', $stockIOID, $itemIOID, $uomIOID);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function getBinConfigOfOutputLine($outputIFID, $outputLineIOID = 0)
    {
        $where = '';
        $where.= $outputLineIOID?sprintf(' AND XuatKho.IOID = %1$d ', $outputLineIOID):'';

        $sql = sprintf('
            SELECT
                IFNULL(XuatKho.IOID, 0) AS OutputLineIOID
                , IFNULL(Xuat.Ref_Kho, 0) AS RefWarehouse
                , TrangThaiKho.*
                , IFNULL(TrangThaiKho.Ref_Bin, 0) AS Ref_Bin
                , IFNULL(TrangThaiKho.SoLuong, 0) AS TonKho
                , IFNULL(TrangThai.SoLuong, 0) AS SoLuong
                , IFNULL(TrangThai.IOID, 0) AS StockStatusIOID
                , TrangThai.SoSerial AS SoSerialTrangThai
            FROM ODanhSachXuatKho AS XuatKho

            INNER JOIN OSanPham AS MatHang ON XuatKho.Ref_MaSP = MatHang.IOID

            INNER JOIN OXuatKho AS Xuat ON XuatKho.IFID_M506 = Xuat.IFID_M506

            -- Join Ton Kho
            INNER JOIN OThuocTinhChiTiet AS TrangThaiKho ON IFNULL(TrangThaiKho.IFID_M602, 0) != 0
                AND Xuat.Ref_Kho = TrangThaiKho.Ref_Kho
                AND XuatKho.Ref_MaSP = TrangThaiKho.Ref_MaSanPham
                AND XuatKho.Ref_DonViTinh = TrangThaiKho.Ref_DonViTinh
                -- AND IFNULL(XuatKho.Ref_ThuocTinh, 0) = IFNULL(TrangThaiKho.Ref_MaThuocTinh, 0)

            -- Join Dang chon
            LEFT JOIN OThuocTinhChiTiet AS TrangThai ON XuatKho.IFID_M506 = TrangThai.IFID_M506
                AND IFNULL(TrangThaiKho.Ref_MaSanPham, 0) = IFNULL(TrangThai.Ref_MaSanPham, 0)
                AND IFNULL(TrangThaiKho.Ref_DonViTinh, 0) = IFNULL(TrangThai.Ref_DonViTinh, 0)
                AND IFNULL(TrangThaiKho.Ref_Bin, 0) = IFNULL(TrangThai.Ref_Bin, 0)
                AND IFNULL(TrangThaiKho.SoLo, \'\') = IFNULL(TrangThai.SoLo, \'\')
                AND IFNULL(TrangThaiKho.SoSerial, \'\') = IFNULL(TrangThai.SoSerial, \'\')
                -- AND IFNULL(TrangThaiKho.Ref_MaThuocTinh, 0) = IFNULL(TrangThai.Ref_MaThuocTinh, 0)

            WHERE XuatKho.IFID_M506 = %1$d
                %2$s
            ORDER BY XuatKho.IFID_M506, XuatKho.IOID, TrangThai.IOID
        ', $outputIFID, $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lây danh sach nhap kho, xuat kho, su dung cua vat tu dua theo cac phieu bao tri
     * @param string $mStart
     * @param string $mEnd
     * @param int $locationIOID
     * @param int $equipGroupIOID
     * @param int $equipTypeIOID
     * @param int $equipIOID
     * @return mixed
     */
    public function getInOutOfWorkOrders(
        $mStart           = ''
        , $mEnd           = ''
        , $locationIOID   = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID  = 0
        , $equipIOID      = 0
        , $orderIOID      = 0
    )
    {
        // @note: Dieu kien loc duoc su dung trong ca ba sub query, can luu y dieu nay
        $where  = ''; // init
        $where .= ($mStart && $mEnd)?sprintf(' AND PhieuBT.NgayYeuCau BETWEEN %1$s AND %2$s ', $this->_o_DB->quote($mStart), $this->_o_DB->quote($mEnd)):'';
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        if($loc)
        {
            $where .= sprintf('
                AND (
                    ( ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
                    OR ( ifnull(PhieuBT.Ref_KhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
                )
                '
            , $loc->lft, $loc->rgt);
        }
        $where  .= ($equipGroupIOID)?sprintf(' AND ifnull(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $equipGroupIOID):'';
        $where  .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where  .= ($equipIOID)?sprintf(' AND ifnull(ThietBi.IOID, 0) = %1$d ', $equipIOID):'';
        $where  .= ($orderIOID)?sprintf(' AND ifnull(PhieuBT.IOID, 0) = %1$d ', $orderIOID):'';

        $sql = sprintf('
            SELECT
                PhieuBT.*
                , PhieuBT.SoLuong AS SoLuongSuDung
                , ifnull(PhieuBT.SoLuongDuKien, 0) AS SoLuongDuKien
                , ifnull(XuatKho.SoLuong, 0) AS SoLuongXuatKho
                , ifnull(NhapKho.SoLuong, 0) AS SoLuongNhapKho
                , (ifnull(XuatKho.SoLuong, 0) - 
                (case when HinhThuc = 0 and HinhThuc = 1 then PhieuBT.SoLuong 
                when HinhThuc = 2 and HinhThuc = 5 then -PhieuBT.SoLuong when HinhThuc=3 and HinhThuc=4 then 0 end   
                + ifnull(NhapKho.SoLuong, 0))) AS SoLuongThuaThieu
                , IF( IFNULL(TrangThaiXuatKho.RefWorkOrder, 0) != 0, 1, 0) AS TrueSerial

            /* Lay phieu bao tri */
            FROM
            (
                SELECT
                  PhieuBT.IOID AS RefWorkOrder
                  , PhieuBT.MaThietBi AS ThietBiKhuVuc
                  , PhieuBT.SoPhieu
                  , PhieuBT.NgayYeuCau
                  , IFNULL(MatHang.QuanLyTheoMa, 0) AS QuanLyTheoMa
                  , VatTu.*

                FROM OPhieuBaoTri AS PhieuBT
                LEFT JOIN ODanhSachThietBi AS ThietBi ON ifnull(PhieuBT.Ref_MaThietBi, 0) = ThietBi.IOID
                LEFT JOIN OKhuVuc AS KhuVuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
                INNER JOIN OVatTuPBT AS VatTu ON PhieuBT.IFID_M759 = VatTu.IFID_M759
                INNER JOIN OSanPham AS MatHang On VatTu.Ref_MaVatTu = MatHang.IOID
                WHERE 1=1 %1$s
                GROUP BY PhieuBT.IFID_M759, VatTu.Ref_MaVatTu, VatTu.Ref_DonViTinh, ifnull(VatTu.Ref_ThuocTinh, 0)
                ORDER BY PhieuBT.NgayYeuCau DESC, PhieuBT.IFID_M759, VatTu.Ref_ViTri, VatTu.MaVatTu, VatTu.DonViTinh
            ) AS PhieuBT
            /* Lay xuat kho bao tri */
            LEFT JOIN
            (
                SELECT XuatKho.Ref_PhieuBaoTri AS RefWorkOrder, XuatKho.IOID AS Ref_PhieuXuatKho, DanhSachXK.*
                FROM OXuatKho AS XuatKho
                INNER JOIN OLoaiXuatKho AS LoaiXK ON XuatKho.Ref_LoaiXuatKho = LoaiXK.IOID
                INNER JOIN ODanhSachXuatKho AS DanhSachXK ON XuatKho.IFID_M506 = DanhSachXK.IFID_M506
                WHERE
                    ifnull(XuatKho.Ref_PhieuBaoTri, 0) IN (
                        SELECT PhieuBT.IOID
                        FROM OPhieuBaoTri AS PhieuBT
                        LEFT JOIN ODanhSachThietBi AS ThietBi ON ifnull(PhieuBT.Ref_MaThietBi, 0) = ThietBi.IOID
                        LEFT JOIN OKhuVuc AS KhuVuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
                        WHERE 1=1 %1$s
                    )
                    AND (LoaiXK.Loai = \'BAOTRI\')
                GROUP BY XuatKho.Ref_PhieuBaoTri, DanhSachXK.Ref_MaSP, DanhSachXK.Ref_DonViTinh, ifnull(DanhSachXK.Ref_ThuocTinh,0)
            ) AS XuatKho ON
                PhieuBT.RefWorkOrder = XuatKho.RefWorkOrder
                AND PhieuBT.Ref_MaVatTu = XuatKho.Ref_MaSP
                AND PhieuBT.Ref_DonViTinh = XuatKho.Ref_DonViTinh
                AND ifnull(PhieuBT.Ref_ThuocTinh, 0) = ifnull(XuatKho.Ref_ThuocTinh, 0)
            /* Lay nhap kho bao tri */
            LEFT JOIN
            (
                SELECT NhapKho.Ref_PhieuXuatKho, DanhSachNK.*
                FROM ONhapKho AS NhapKho
                INNER JOIN OLoaiNhapKho AS LoaiNK ON NhapKho.Ref_LoaiNhapKho = LoaiNK.IOID
                INNER JOIN ODanhSachNhapKho AS DanhSachNK ON NhapKho.IFID_M402 = DanhSachNK.IFID_M402
                WHERE
                    ifnull(NhapKho.Ref_PhieuXuatKho, 0) IN (
                        SELECT IOID
                        FROM OXuatKho
                        WHERE IFNULL(Ref_PhieuBaoTri, 0) IN
                        (
                            SELECT PhieuBT.IOID
                            FROM OPhieuBaoTri AS PhieuBT
                            LEFT JOIN ODanhSachThietBi AS ThietBi ON ifnull(PhieuBT.Ref_MaThietBi, 0) = ThietBi.IOID
                            LEFT JOIN OKhuVuc AS KhuVuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
                            WHERE 1=1 %1$s
                        )
                    )
                    AND LoaiNK.Loai = \'TRALAI\'
                GROUP BY NhapKho.Ref_PhieuXuatKho, DanhSachNK.Ref_MaSanPham, DanhSachNK.Ref_DonViTinh, ifnull(DanhSachNK.Ref_ThuocTinh,0)
            ) AS NhapKho ON
                XuatKho.Ref_PhieuXuatKho = NhapKho.Ref_PhieuXuatKho
                AND PhieuBT.Ref_MaVatTu = NhapKho.Ref_MaSanPham
                AND PhieuBT.Ref_DonViTinh = NhapKho.Ref_DonViTinh
                AND ifnull(PhieuBT.Ref_ThuocTinh, 0) = ifnull(NhapKho.Ref_ThuocTinh, 0)

            LEFT JOIN
            (
                SELECT
                    XuatKho.Ref_PhieuBaoTri AS RefWorkOrder
                    , XuatKho.IOID AS Ref_PhieuXuatKho
                    , DanhSachXK.Ref_MaSP
                    , DanhSachXK.Ref_DonViTinh
                    , DanhSachXK.Ref_ThuocTinh
                    , TrangThai.SoSerial
                FROM OXuatKho AS XuatKho
                INNER JOIN OLoaiXuatKho AS LoaiXK ON XuatKho.Ref_LoaiXuatKho = LoaiXK.IOID
                INNER JOIN ODanhSachXuatKho AS DanhSachXK ON XuatKho.IFID_M506 = DanhSachXK.IFID_M506
                INNER JOIN OThuocTinhChiTiet AS TrangThai ON XuatKho.IFID_M506 = TrangThai.IFID_M506
                WHERE
                    ifnull(XuatKho.Ref_PhieuBaoTri, 0) IN (
                        SELECT PhieuBT.IOID
                        FROM OPhieuBaoTri AS PhieuBT
                        LEFT JOIN ODanhSachThietBi AS ThietBi ON ifnull(PhieuBT.Ref_MaThietBi, 0) = ThietBi.IOID
                        LEFT JOIN OKhuVuc AS KhuVuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
                        WHERE 1=1 %1$s
                    )
                    AND (LoaiXK.Loai = \'BAOTRI\')
                    AND TrangThai.SoLuong = 1
                GROUP BY XuatKho.Ref_PhieuBaoTri, DanhSachXK.Ref_MaSP, DanhSachXK.Ref_DonViTinh, ifnull(DanhSachXK.Ref_ThuocTinh,0)
            ) AS TrangThaiXuatKho ON
                PhieuBT.RefWorkOrder = TrangThaiXuatKho.RefWorkOrder
                AND IFNULL(PhieuBT.SerialKhac, "") = IFNULL(TrangThaiXuatKho.SoSerial, "")
                AND PhieuBT.Ref_MaVatTu = TrangThaiXuatKho.Ref_MaSP
                AND PhieuBT.Ref_DonViTinh = TrangThaiXuatKho.Ref_DonViTinh
                AND ifnull(PhieuBT.Ref_ThuocTinh, 0) = ifnull(TrangThaiXuatKho.Ref_ThuocTinh, 0)

        ', $where);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @param array $itemIOIDs
     * @param int $warehouseIOID
     * @param array $alertType
     * @return mixed
     * @use-in: M174, M608
     */
    public function getCurrentInventory($itemIOIDs = array(), $warehouseIOID = 0, $alertType = array())
    {
        $where   = ''; // init
        $where  .= ($itemIOIDs)?sprintf(' AND MatHang.IOID IN (%1$s) ', implode(',', $itemIOIDs)):'';
        $where  .= ($warehouseIOID)?sprintf(' AND TonKho.Ref_Kho = %1$d ', $warehouseIOID):'';
        $where2  = ($alertType)?sprintf(' AND PhanLoai in (%1$s) ', implode(',', $alertType)):'';
        $where3  = ($warehouseIOID)?sprintf(' AND HanMuc.Ref_MaKho = %1$d ', $warehouseIOID):'';
        $joinDanhSach = '';

        if(Qss_Lib_System::fieldActive('ODSYeuCauMuaSam', 'MaKho'))
        {
            $joinDanhSach = sprintf('AND (TonKho.Ref_MaKho = 0 OR TonKho.Ref_MaKho = DanhSach.Ref_MaKho)');
        }

        $sql = sprintf('


        SELECT *
        FROM 
        (      
            SELECT TonKho.*, DanhSach.NgayYeuCau         
            , IF(DanhSach.NgayYeuCau, IF(DATEDIFF(CURDATE(), DanhSach.NgayYeuCau) >= 10, "red bold", ""), "") AS dateClass
            FROM 
            (
                SELECT
                    TonKho.*,
                    CASE WHEN SoLuongHienCoTheoCoSo > SoLuongToiDaTheoCoSo AND SoLuongToiDaTheoCoSo != 0 THEN 1
                    WHEN SoLuongHienCoTheoCoSo < SoLuongToiThieuTheoCoSo AND SoLuongToiThieuTheoCoSo != 0 THEN 2
                    WHEN SoLuongHienCoTheoCoSo = 0 THEN 5                
                    ELSE 4
                    END AS PhanLoai
                FROM
                (
                    SELECT
                        MatHang.IOID AS ItemIOID
                        , TonKho.Ref_Kho
                        , ifnull(TonKho.Ref_ThuocTinh, 0) AS Ref_ThuocTinh
                        , MatHang.MaSanPham
                        , MatHang.TenSanPham
                        , MatHang.DonViTinh AS DonViTinhCoSo
                        , DonViTinhCoSo.IOID AS RefDonViTinhCoSo
                        , sum(ifnull(TonKho.SoLuongHC, 0) * ifnull(DonViTinh.HeSoQuyDoi,1)) AS SoLuongHienCoTheoCoSo
                        , ifnull(MatHang.SLToiThieu, 0) AS SoLuongToiThieuTheoCoSo
                        , ifnull(MatHang.SLToiDa, 0) AS SoLuongToiDaTheoCoSo
                        , 0 AS Ref_MaKho
                        , NULL AS MaKho
                    FROM OSanPham  AS MatHang
                    INNER JOIN ODonViTinhSP AS DonViTinhCoSo ON
                        MatHang.IFID_M113 = DonViTinhCoSo.IFID_M113
                        AND ifnull(MatHang.Ref_DonViTinh, 0) = ifnull(DonViTinhCoSo.Ref_DonViTinh, 0)
                    LEFT JOIN OKho AS TonKho ON TonKho.Ref_MaSP = MatHang.IOID
                    LEFT JOIN ODonViTinhSP AS DonViTinh ON
                        MatHang.IFID_M113 = DonViTinh.IFID_M113
                        AND ifnull(TonKho.Ref_DonViTinh, 0) = ifnull(DonViTinh.IOID, 0)                
                    WHERE 1=1 %1$s
                    GROUP BY MatHang.IOID
                              
                    UNION 
                      
                    SELECT
                        MatHang.IOID AS ItemIOID
                        , TonKho.Ref_Kho
                        , ifnull(TonKho.Ref_ThuocTinh, 0) AS Ref_ThuocTinh
                        , MatHang.MaSanPham
                        , MatHang.TenSanPham
                        , MatHang.DonViTinh AS DonViTinhCoSo
                        , DonViTinhCoSo.IOID AS RefDonViTinhCoSo
                        , sum(ifnull(TonKho.SoLuongHC, 0) * ifnull(DonViTinh.HeSoQuyDoi,1)) AS SoLuongHienCoTheoCoSo
                        , ifnull(HanMuc.SoLuongThoiThieu, 0) AS SoLuongToiThieuTheoCoSo
                        , ifnull(HanMuc.SoLuongToiDa, 0) AS SoLuongToiDaTheoCoSo
                        , HanMuc.Ref_MaKho AS Ref_MaKho
                        , HanMuc.MaKho AS MaKho                    
                    FROM OSanPham  AS MatHang
                    INNER JOIN ODonViTinhSP AS DonViTinhCoSo ON
                        MatHang.IFID_M113 = DonViTinhCoSo.IFID_M113
                        AND ifnull(MatHang.Ref_DonViTinh, 0) = ifnull(DonViTinhCoSo.Ref_DonViTinh, 0)
                    INNER JOIN OHanMucLuuTru AS HanMuc ON MatHang.IFID_M113 = HanMuc.IFID_M113                    
                    LEFT JOIN OKho AS TonKho ON TonKho.Ref_MaSP = MatHang.IOID AND HanMuc.Ref_MaKho = TonKho.Ref_Kho
                    LEFT JOIN ODonViTinhSP AS DonViTinh ON
                        MatHang.IFID_M113 = DonViTinh.IFID_M113
                        AND ifnull(TonKho.Ref_DonViTinh, 0) = ifnull(DonViTinh.IOID, 0)                
                    WHERE 1=1 %4$s
                    GROUP BY MatHang.IOID, HanMuc.Ref_MaKho             
                ) AS TonKho
                HAVING  1=1 %2$s
            ) AS TonKho
            LEFT JOIN ODSYeuCauMuaSam AS DanhSach ON TonKho.ItemIOID = DanhSach.Ref_MaSP %3$s
            ORDER BY DanhSach.NgayYeuCau DESC, TonKho.ItemIOID, TonKho.Ref_MaKho
            LIMIT 18446744073709551615 
        )  AS T
        GROUP BY T.ItemIOID, T.Ref_MaKho
        ORDER BY T.MaSanPham, T.MaKho                        
        ', $where, $where2, $joinDanhSach, $where3);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function checkTransactionExists($maspioid)
    {
        $sql = sprintf('
            SELECT 1 as transaction
            FROM ODanhSachNhapKho WHERE Ref_MaSanPham = %1$d
            union all
            SELECT 1 as transaction
            FROM ODanhSachXuatKho WHERE Ref_MaSP = %1$d
        ', $maspioid);
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * @param int $stockIOID
     * @param bool|true $inTrue
     * @param bool|true $outTrue
     * @param int $inputType
     * @param int $outputType
     * @param int $itemIOID
     * @return mixed
     */
    public function getTransaction(
        $inTrue = true
        , $outTrue = true
        , $startDate = ''
        , $endDate   = ''
        , $stockIOID = 0
        , $inputTypeIOID = 0
        , $outputTypeIOID = 0
        , $itemIOID = 0
        , $page = 0
        , $display = 0)
    {

        $limit         = ''; // Phan trang
        $filterInput   = ''; // Loc nhap kho
        $filterOutput  = ''; // Loc xuat kho

        // Loc theo nhap/xuat
        $filterInput  .= !$inTrue?sprintf(' AND 1=0 '):'';
        $filterOutput .= !$outTrue?sprintf(' AND 1=0 '):'';

        // Loc theo thoi gian
        $filterInput  .= ($inTrue && $stockIOID)?sprintf(' AND DSKho.IOID = %1$d ', $stockIOID):'';
        $filterOutput .= ($outTrue && $stockIOID)?sprintf(' AND DSKho.IOID = %1$d ', $stockIOID):'';

        // Loc theo kho
        $filterInput  .= ($inTrue && $startDate && $endDate)?sprintf(' AND (NhapKho.NgayChungTu between %1$s And %2$s) ', $this->_o_DB->quote($startDate),$this->_o_DB->quote($endDate)):'';
        $filterOutput .= ($inTrue && $startDate && $endDate)?sprintf(' AND (XuatKho.NgayChungTu between %1$s And %2$s) ', $this->_o_DB->quote($startDate),$this->_o_DB->quote($endDate)):'';

        // Loc theo loai
        $filterInput  .= ($inTrue && $inputTypeIOID)?sprintf(' AND Loai.IOID = %1$d ', $inputTypeIOID):'';
        $filterOutput .= ($outTrue && $outputTypeIOID)?sprintf(' AND Loai.IOID = %1$d ', $outputTypeIOID):'';

        // Loc theo mat hang
        $filterInput  .= ($inTrue && $itemIOID)?sprintf(' AND DSNhapKho.Ref_MaSanPham = %1$d ', $itemIOID):'';
        $filterOutput .= ($outTrue && $itemIOID)?sprintf(' AND DSXuatKho.Ref_MaSP = %1$d ', $itemIOID):'';

        // Phan trang
        if($page && $display)
        {
            $start   = ceil(($page - 1) * $display);
            $limit   = " limit {$start}, {$display}";
        }



        $sql = sprintf('
        SELECT
            GiaoDichKho.*
            , MatHang.DacTinhKyThuat
        FROM
            (
                (
                    SELECT
                        NhapKho.NgayChungTu
                        , NhapKho.SoChungTu
                        , DSNhapKho.MaSanPham
                        , DSNhapKho.TenSanPham
                        , DSNhapKho.DonViTinh
                        , DSNhapKho.Ref_MaSanPham
                        , NhapKho.Ref_Kho
                        , DSNhapKho.Ref_ThuocTinh
                        , DSNhapKho.ThuocTinh
                        , IFNULL(DSNhapKho.SoLuong,0) as SoLuong
                        , 1 AS NhapXuat
                        , Loai.Ten AS LoaiNhapXuat
                        , DSNhapKho.DonGia
                        , DSNhapKho.ThanhTien
                        , NhapKho.IFID_M402 AS IFID
                    FROM ODanhSachNhapKho AS DSNhapKho
                    INNER JOIN ONhapKho AS NhapKho ON DSNhapKho.IFID_M402 = NhapKho.IFID_M402
                    INNER JOIN qsiforms AS IForm ON NhapKho.IFID_M402 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSNhapKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On NhapKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiNhapKho AS Loai ON NhapKho.Ref_LoaiNhapKho = Loai.IOID
                    WHERE IForm.Status = 2 %1$s
                    -- GROUP BY DSNhapKho.Ref_MaSanPham, ifnull(DSNhapKho.Ref_ThuocTinh,0), Loai.IFID_M613
                )
                UNION ALL
                -- AS NhapKho
                -- ON MatHang.IOID = NhapKho.Ref_MaSanPham
                -- LEFT JOIN
                (
                    SELECT
                        XuatKho.NgayChungTu
                        , XuatKho.SoChungTu
                        , DSXuatKho.MaSP AS MaSanPham
                        , DSXuatKho.TenSP AS TenSanPham
                        , DSXuatKho.DonViTinh
                        , DSXuatKho.Ref_MaSP AS Ref_MaSanPham
                        , XuatKho.Ref_Kho
                        , DSXuatKho.Ref_ThuocTinh
                        , DSXuatKho.ThuocTinh
                        , IFNULL(DSXuatKho.SoLuong,0) as SoLuong
                        , 0 AS NhapXuat
                        , Loai.Ten AS LoaiNhapXuat
                        , DSXuatKho.DonGia
                        , DSXuatKho.ThanhTien
                        , XuatKho.IFID_M506 AS IFID
                    FROM ODanhSachXuatKho AS DSXuatKho
                    INNER JOIN OXuatKho AS XuatKho ON DSXuatKho.IFID_M506 = XuatKho.IFID_M506
                    INNER JOIN qsiforms AS IForm ON XuatKho.IFID_M506 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSXuatKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On XuatKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiXuatKho AS Loai ON XuatKho.Ref_LoaiXuatKho = Loai.IOID
                    WHERE IForm.Status = 2 %2$s
                    -- GROUP BY DSXuatKho.Ref_MaSP, ifnull(DSXuatKho.Ref_ThuocTinh,0), Loai.IFID_M614
                )
                -- AS XuatKho
                -- ON XuatKho.Ref_MaSanPham = MatHang.IOID
            ) AS GiaoDichKho
            LEFT JOIN OSanPham AS MatHang On GiaoDichKho.Ref_MaSanPham = MatHang.IOID
            ORDER BY NgayChungTu, SoChungTu, LoaiNhapXuat, MaSanPham
            %3$s
        ', $filterInput, $filterOutput, $limit);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function countTransaction(
        $inTrue = true
        , $outTrue = true
        , $startDate = ''
        , $endDate   = ''
        , $stockIOID = 0
        , $inputTypeIOID = 0
        , $outputTypeIOID = 0
        , $itemIOID = 0)
    {
        $filterInput   = ''; // Loc nhap kho
        $filterOutput  = ''; // Loc xuat kho

        // Loc theo nhap/xuat
        $filterInput  .= !$inTrue?sprintf(' AND 1=0 '):'';
        $filterOutput .= !$outTrue?sprintf(' AND 1=0 '):'';

        // Loc theo thoi gian
        $filterInput  .= ($inTrue && $stockIOID)?sprintf(' AND DSKho.IOID = %1$d ', $stockIOID):'';
        $filterOutput .= ($outTrue && $stockIOID)?sprintf(' AND DSKho.IOID = %1$d ', $stockIOID):'';

        // Loc theo kho
        $filterInput  .= ($inTrue && $startDate && $endDate)?sprintf(' AND (NhapKho.NgayChungTu between %1$s And %2$s) ', $this->_o_DB->quote($startDate),$this->_o_DB->quote($endDate)):'';
        $filterOutput .= ($inTrue && $startDate && $endDate)?sprintf(' AND (XuatKho.NgayChungTu between %1$s And %2$s) ', $this->_o_DB->quote($startDate),$this->_o_DB->quote($endDate)):'';

        // Loc theo loai
        $filterInput  .= ($inTrue && $inputTypeIOID)?sprintf(' AND Loai.IOID = %1$d ', $inputTypeIOID):'';
        $filterOutput .= ($outTrue && $outputTypeIOID)?sprintf(' AND Loai.IOID = %1$d ', $outputTypeIOID):'';

        // Loc theo mat hang
        $filterInput  .= ($inTrue && $itemIOID)?sprintf(' AND DSNhapKho.Ref_MaSanPham = %1$d ', $itemIOID):'';
        $filterOutput .= ($outTrue && $itemIOID)?sprintf(' AND DSXuatKho.Ref_MaSP = %1$d ', $itemIOID):'';


        $sql = sprintf('
        SELECT
            Count(1) AS Total
        FROM
            (
                (
                    SELECT
                        NhapKho.NgayChungTu
                        , NhapKho.SoChungTu
                        , DSNhapKho.MaSanPham
                        , DSNhapKho.TenSanPham
                        , DSNhapKho.DonViTinh
                        , DSNhapKho.Ref_MaSanPham
                        , NhapKho.Ref_Kho
                        , DSNhapKho.Ref_ThuocTinh
                        , DSNhapKho.ThuocTinh
                        , IFNULL(DSNhapKho.SoLuong,0) as SoLuong
                        , 1 AS NhapXuat
                        , Loai.Ten AS LoaiNhapXuat
                        , DSNhapKho.DonGia
                        , DSNhapKho.ThanhTien
                        , NhapKho.IFID_M402 AS IFID
                    FROM ODanhSachNhapKho AS DSNhapKho
                    INNER JOIN ONhapKho AS NhapKho ON DSNhapKho.IFID_M402 = NhapKho.IFID_M402
                    INNER JOIN qsiforms AS IForm ON NhapKho.IFID_M402 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSNhapKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On NhapKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiNhapKho AS Loai ON NhapKho.Ref_LoaiNhapKho = Loai.IOID
                    WHERE IForm.Status = 2 %1$s
                    -- GROUP BY DSNhapKho.Ref_MaSanPham, ifnull(DSNhapKho.Ref_ThuocTinh,0), Loai.IFID_M613
                )
                UNION ALL
                -- AS NhapKho
                -- ON MatHang.IOID = NhapKho.Ref_MaSanPham
                -- LEFT JOIN
                (
                    SELECT
                        XuatKho.NgayChungTu
                        , XuatKho.SoChungTu
                        , DSXuatKho.MaSP AS MaSanPham
                        , DSXuatKho.TenSP AS TenSanPham
                        , DSXuatKho.DonViTinh
                        , DSXuatKho.Ref_MaSP AS Ref_MaSanPham
                        , XuatKho.Ref_Kho
                        , DSXuatKho.Ref_ThuocTinh
                        , DSXuatKho.ThuocTinh
                        , IFNULL(DSXuatKho.SoLuong,0) as SoLuong
                        , 0 AS NhapXuat
                        , Loai.Ten AS LoaiNhapXuat
                        , DSXuatKho.DonGia
                        , DSXuatKho.ThanhTien
                        , XuatKho.IFID_M506 AS IFID
                    FROM ODanhSachXuatKho AS DSXuatKho
                    INNER JOIN OXuatKho AS XuatKho ON DSXuatKho.IFID_M506 = XuatKho.IFID_M506
                    INNER JOIN qsiforms AS IForm ON XuatKho.IFID_M506 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSXuatKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On XuatKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiXuatKho AS Loai ON XuatKho.Ref_LoaiXuatKho = Loai.IOID
                    WHERE IForm.Status = 2 %2$s
                    -- GROUP BY DSXuatKho.Ref_MaSP, ifnull(DSXuatKho.Ref_ThuocTinh,0), Loai.IFID_M614
                )
                -- AS XuatKho
                -- ON XuatKho.Ref_MaSanPham = MatHang.IOID
            ) AS GiaoDichKho
            LEFT JOIN OSanPham AS MatHang On GiaoDichKho.Ref_MaSanPham = MatHang.IOID

        ', $filterInput, $filterOutput);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }


    /**
     * Lấy trạng thái lưu trữ trong kho của các sản phẩm liên quan đến serial, các sản phẩm đã đánh rồi đánh lại
     * cần ghi
     * @return mixed
     */
    public function getStockStatusByInventory($stockIFID)
    {
        $sql = sprintf('
            SELECT
                TrangThaiKho.*
                , TonKho.*
                , TrangThaiKho.IOID
                , IFNULL(TrangThaiKho.SoLuong, 0) AS SoLuong
                , IFNULL(TonKho.SoLuongHC, 0) AS SoLuongHC
            FROM OKho AS TonKho
            INNER JOIN OSanPham AS MatHang ON TonKho.Ref_MaSP = MatHang.IOID
            LEFT JOIN OThuocTinhChiTiet AS TrangThaiKho ON TonKho.IFID_M602 = TrangThaiKho.IFID_M602
            WHERE
                TonKho.IFID_M602 = %1$d
                AND IFNULL(MatHang.QuanLyTheoMa, 0) = 1
            ORDER BY TrangThaiKho.Kho, TrangThaiKho.MaSanPham, TrangThaiKho.SoSerial
        ', $stockIFID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Chỉ dùng cho nút đánh lại serial
     * @param $params
     */
    public function updateSerial($params)
    {
        if(!isset($params['ioid']) || !count($params['ioid'])) { return; }

        $i         = 0;
        $sqlInsert = '';
        $sqlUpdate = '';
        $reject    = (!isset($params['reject']) || !$params['reject'])?false:true; // Loai bo cac serial da danh tu truoc

        // b1 - xoa trang thai truoc do
        $deleteSql = sprintf('delete from OThuocTinhChiTiet where ifnull(IFID_M602, 0) = %1$d ', $params['ifid']);

        if($reject)
        {
            $deleteSql .= sprintf(' and IFNULL(SoSerial,  \'\') = \'\' ');
        }

        foreach($params['ioid'] as $ioid)
        {
            $params['new_serial'][$i] = trim($params['new_serial'][$i]);

            if($params['new_serial'][$i])
            {
                // Cap nhat lai ban ghi da co
                if($ioid)
                {
                    $sqlUpdate .= ($sqlUpdate != '')?',':'';

                    $sqlUpdate .= sprintf(
                        '(%15$d, %17$d, %16$d, %1$s , %2$d, %3$s, %4$d, %5$s, %6$d, %7$s, %8$d, %9$s, %10$s, %11$d, %12$s, %13$d, %14$s)'
                        , $this->_o_DB->quote($params['kho'][$i]) //1
                        , $params['ref_kho'][$i] //2
                        , $this->_o_DB->quote($params['bin'][$i]) //3
                        , $params['ref_bin'][$i] //4
                        , $this->_o_DB->quote($params['masanpham'][$i]) //5
                        , $params['ref_masanpham'][$i] //6
                        , $this->_o_DB->quote($params['tensanpham'][$i]) //7
                        , $params['ref_masanpham'][$i] //8
                        , $this->_o_DB->quote($params['new_serial'][$i]) //9
                        , $this->_o_DB->quote($params['dactinhkythuat'][$i]) // 10
                        , $params['ref_masanpham'][$i]  // 11
                        , $this->_o_DB->quote($params['donvitinh'][$i]) //12
                        , $params['ref_donvitinh'][$i] //13
                        , 1 //14
                        , $params['aifid'][$i] // 15
                        , $params['deptid'][$i] // 16
                        , $params['ioid'][$i] // 17
                    );
                }
                else // Tao ban ghi moi
                {
                    $sqlInsert .= ($sqlInsert != '')?',':'';

                    // 1. Kho 2.Ref_Kho 3.Bin 4.Ref_Bin 5.MaSanPham 6.Ref_MaSanPham 7.TenSanPham 8.Ref_TenSanPham 9.SoSerial
                    // 10.DacTinhKyThuat 11.Ref_DacTinhKyThuat 12.DonViTinh 13.Ref_DonViTinh 14.SoLuong 15. ifid
                    $sqlInsert .= sprintf(
                        '(%15$d, %16$d, %1$s , %2$d, %3$s, %4$d, %5$s, %6$d, %7$s, %8$d, %9$s, %10$s, %11$d, %12$s, %13$d, %14$s)'
                        , $this->_o_DB->quote($params['kho'][$i]) //1
                        , $params['ref_kho'][$i] //2
                        , $this->_o_DB->quote($params['bin'][$i]) //3
                        , $params['ref_bin'][$i] //4
                        , $this->_o_DB->quote($params['masanpham'][$i]) //5
                        , $params['ref_masanpham'][$i] //6
                        , $this->_o_DB->quote($params['tensanpham'][$i]) //7
                        , $params['ref_masanpham'][$i] //8
                        , $this->_o_DB->quote($params['new_serial'][$i]) //9
                        , $this->_o_DB->quote($params['dactinhkythuat'][$i]) // 10
                        , $params['ref_masanpham'][$i]  // 11
                        , $this->_o_DB->quote($params['donvitinh'][$i]) //12
                        , $params['ref_donvitinh'][$i] //13
                        , 1 //14
                        , $params['aifid'][$i] // 15
                        , $params['deptid'][$i] // 16
                    );
                }
            }
            $i++;
        }

        if($deleteSql)
        {
            $this->_o_DB->execute($deleteSql);
        }

        if($sqlInsert)
        {
            // 1. Kho 2.Ref_Kho 3.Bin 4.Ref_Bin 5.MaSanPham 6.Ref_MaSanPham 7.TenSanPham 8.Ref_TenSanPham 9.SoSerial
            // 10.DacTinhKyThuat 11.Ref_DacTinhKyThuat 12.DonViTinh 13.Ref_DonViTinh 14.SoLuong 15. ifid
            $sql = sprintf('
                    insert into OThuocTinhChiTiet (IFID_M602, DeptID, Kho,Ref_Kho,Bin,Ref_Bin,MaSanPham,Ref_MaSanPham,TenSanPham,Ref_TenSanPham,SoSerial,DacTinhKyThuat, Ref_DacTinhKyThuat, DonViTinh,Ref_DonViTinh,SoLuong)values %1$s;'
                ,$sqlInsert);
            // echo '<pre>'; print_r($sql);die;
            $this->_o_DB->execute($sql);
        }

        if($sqlUpdate)
        {
            // 1. Kho 2.Ref_Kho 3.Bin 4.Ref_Bin 5.MaSanPham 6.Ref_MaSanPham 7.TenSanPham 8.Ref_TenSanPham 9.SoSerial
            // 10.DacTinhKyThuat 11.Ref_DacTinhKyThuat 12.DonViTinh 13.Ref_DonViTinh 14.SoLuong 15. ifid
            $sql = sprintf('
                    insert into OThuocTinhChiTiet (IFID_M602, IOID, DeptID, Kho,Ref_Kho,Bin,Ref_Bin,MaSanPham,Ref_MaSanPham,TenSanPham,Ref_TenSanPham,SoSerial,DacTinhKyThuat, Ref_DacTinhKyThuat, DonViTinh,Ref_DonViTinh,SoLuong)values %1$s;'
                ,$sqlUpdate);
            // echo '<pre>'; print_r($sql);die;
            $this->_o_DB->execute($sql);
        }
    }

    /**
     * Lấy cấu hình sản phẩm theo dòng tồn kho <Lưu ý: Một dòng tồn kho chỉ bao gồm 1 mặt hàng>
     * @param int $inventoryIFID Tồn kho IFID
     * @return mixed
     */
    public function getItemConfigByInventory($inventoryIFID)
    {
        $sql = sprintf('
            SELECT
                MatHang.*
                , MatHang.DonViTinh AS DonViTinhChinh
                , TonKho.DonViTinh
                , TonKho.SoLuongHC
                , TonKho.Kho
            FROM OKho AS TonKho
            INNER JOIN OSanPham AS MatHang ON TonKho.Ref_MaSP = MatHang.IOID
            WHERE TonKho.IFID_M602 = %1$d
        ', $inventoryIFID);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchOne($sql);
    }

    public function getItemConfigByInput($inputIOID)
    {
        $sql = sprintf('
            SELECT
                MatHang.*
                , MatHang.DonViTinh AS DonViTinhChinh
                , DanhSach.DonViTinh
                , DanhSach.SoLuong
                , NhapKho.Kho

            FROM ODanhSachNhapKho AS DanhSach
            INNER JOIN ONhapKho AS NhapKho ON DanhSach.IFID_M402 = NhapKho.IFID_M402
            INNER JOIN OSanPham AS MatHang ON DanhSach.Ref_MaSanPham = MatHang.IOID
            WHERE DanhSach.IOID = %1$d
        ', $inputIOID);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchOne($sql);
    }

    public function getItemConfigByOutput($outputIOID)
    {
        $sql = sprintf('
            SELECT
                MatHang.*
                , MatHang.DonViTinh AS DonViTinhChinh
                , DanhSach.DonViTinh
                , DanhSach.SoLuong
                , XuatKho.Kho
            FROM ODanhSachXuatKho AS DanhSach
            INNER JOIN OXuatKho AS XuatKho ON DanhSach.IFID_M506 = XuatKho.IFID_M506
            INNER JOIN OSanPham AS MatHang ON DanhSach.Ref_MaSP = MatHang.IOID
            WHERE DanhSach.IOID = %1$d
        ', $outputIOID);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchOne($sql);
    }

    public function getInputByPO($receiveIFID)
    {
        $sql = sprintf('
            SELECT
                dsdonhang.*
                ,sum( ifnull(dsnhanhang.SoLuong, 0) ) AS SoLuongDaNhan
            FROM ONhapKho AS nhanhang
            INNER JOIN ODonMuaHang AS donhang ON IFNULL(nhanhang.Ref_SoDonHang, 0) = donhang.IOID
            INNER JOIN ODSDonMuaHang AS dsdonhang ON donhang.IFID_M401 = dsdonhang.IFID_M401
            LEFT JOIN
            (
                SELECT dsnhanhang2.*
                FROM ONhapKho AS nhanhang2
                INNER JOIN ODanhSachNhapKho AS dsnhanhang2 ON nhanhang2.IFID_M402 = dsnhanhang2.IFID_M402
                INNER JOIN qsiforms ON nhanhang2.IFID_M402 = qsiforms.IFID
                WHERE nhanhang2.IFID_M402 = %1$d
            ) AS dsnhanhang ON
                dsdonhang.Ref_MaSP = dsnhanhang.Ref_MaSanPham
                AND ifnull(dsdonhang.Ref_DonViTinh, 0) = ifnull(dsnhanhang.Ref_DonViTinh, 0)
                AND ifnull(dsdonhang.Ref_ThuocTinh, 0) = ifnull(dsnhanhang.Ref_ThuocTinh, 0)
                AND ifnull(dsdonhang.Ref_SoYeuCau, 0)  = ifnull(dsnhanhang.Ref_SoYeuCau, 0)
            WHERE nhanhang.IFID_M402 = %1$d
            GROUP BY dsdonhang.IOID
        ', $receiveIFID);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function countOutputNeedApprove()
    {
        $sql = sprintf('
            SELECT count(1) AS Total
            FROM OXuatKho 
            INNER JOIN qsiforms AS IFormXuatKho ON OXuatKho.IFID_M506 = IFormXuatKho.IFID
            WHERE IFormXuatKho.Status = 4           
        ');

        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->Total:0;
    }

    public function countOutputByUser($userID)
    {
        $sql = sprintf('
            SELECT count(1) AS Total
            FROM OXuatKho 
            INNER JOIN qsiforms AS IFormXuatKho ON OXuatKho.IFID_M506 = IFormXuatKho.IFID
            WHERE IFormXuatKho.UID = %1$d           
        ', $userID);

        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->Total:0;
    }

    public function countOutputsByUser($userID)
    {
        $sql = sprintf('
            SELECT count(1) AS Total
            FROM OXuatKho   
            INNER JOIN qsiforms ON qsiforms.IFID = OXuatKho.IFID_M506
            INNER JOIN ODanhSachXuatKho ON OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506	
            WHERE qsiforms.Status not in (1, 3) AND qsiforms.UID = %1$d
            GROUP BY OXuatKho.IFID_M506
            ORDER BY OXuatKho.NgayChungTu DESC, OXuatKho.NgayChuyenHang DESC, ODanhSachXuatKho.MaSP            
        ', $userID);

        // echo "<pre>"; print_r($sql); die;
        $dataSql = $this->_o_DB->fetchOne($sql);
        return $dataSql?$dataSql->Total: 0;
    }

    public function getOutputsByUser($userID, $page = 0, $perpage = 0, $excludeStatus = array())
    {
        // Phan trang
        $startLimit = ceil(($page - 1) * $perpage);
        $limit      = ($startLimit>=0)?" limit {$startLimit}, {$perpage}":'';
        $where      = count($excludeStatus)?sprintf(' WHERE qsiforms.Status not in (%1$s) ', implode(',', $excludeStatus)):'';

        $sql = sprintf('
            SELECT 
                OXuatKho.*

                , GROUP_CONCAT(
                    CONCAT("<tr><td>", ODanhSachXuatKho.MaSP, "</td><td>"
                        , ODanhSachXuatKho.TenSP, "</td><td>", ODanhSachXuatKho.DonViTinh
                        , "</td><td>", IFNULL(ODanhSachXuatKho.SoLuong, 0), "</td></tr>") SEPARATOR ""
                ) AS VatTuYeuCauDangTable   
                , qsiforms.Status, qsworkflowsteps.Name, qsworkflowsteps.Color, IFNULL(qsstepapprover.SAID, 0) AS SAID
                , ODanhSachNhanVien.MaNhanVien 
                , ODanhSachNhanVien.TenNhanVien 
            FROM OXuatKho            
            INNER JOIN qsiforms ON qsiforms.IFID = OXuatKho.IFID_M506
            INNER JOIN qsworkflows  ON qsworkflows.FormCode = qsiforms.FormCode
			INNER JOIN qsworkflowsteps ON qsworkflowsteps.WFID = qsworkflows.WFID 
			    AND qsiforms.Status = qsworkflowsteps.StepNo        
            INNER JOIN ODanhSachXuatKho ON OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506		
            LEFT JOIN ODanhSachNhanVien ON ODanhSachNhanVien.IOID IN
                 (
                    SELECT
                        MIN(IOID) AS IOID
                    FROM ODanhSachNhanVien 
                    WHERE Ref_TenTruyCap = %1$d                    
                 )
            
            LEFT JOIN qsstepapprover ON qsworkflowsteps.SID = qsstepapprover.SID
            LEFT JOIN qsstepapproverrights ON qsstepapprover.SAID = qsstepapproverrights.SAID	
                AND qsstepapproverrights.UID = %1$d
            %3$s
            GROUP BY OXuatKho.IFID_M506
            ORDER BY OXuatKho.NgayChungTu DESC, OXuatKho.NgayChuyenHang DESC, ODanhSachXuatKho.MaSP
            %2$s
        ', $userID, $limit, $where);

        // echo "<pre>"; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getWarehouseByWorkcenter($Ref_MaDVBT)
    {
        $sql = sprintf('
                select ODanhSachKho.*
				FROM ODanhSachKho
				LEFT JOIN ODonViSanXuat ON ODonViSanXuat.Ref_KhoVatTu = ODanhSachKho.IOID and ODonViSanXuat.IOID = %1$d
				WHERE LoaiKho=%2$s
				ORDER BY ODonViSanXuat.IOID desc
				limit 1'
            , $Ref_MaDVBT
            , $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOutputsByWorkOrder($workOrderIOID)
    {
        $sql = sprintf('
                select OXuatKho.*, IFNULL(qsiforms.Status, 1) AS Status
				FROM OXuatKho
				INNER JOIN OPhieuBaoTri ON IFNULL(OXuatKho.Ref_PhieuBaoTri, 0) = OPhieuBaoTri.IOID 
				INNER JOIN qsiforms ON OXuatKho.IFID_M506 = qsiforms.IFID			
				WHERE OPhieuBaoTri.IOID = %1$d AND OPhieuBaoTri.IOID != 0'
            , $workOrderIOID);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInventoryOfItem($itemIOID, $loaiKho = '', $coSoLuongKhacKhong = false)
    {
        $where = '';
        if($loaiKho != '')
        {
            $where .= sprintf(' AND ODanhSachKho.LoaiKho = %1$s ', $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));
        }

        if($coSoLuongKhacKhong)
        {
            $where .= sprintf(' AND IFNULL(OKho.SoLuongHC, 0) != 0 ');
        }

        $sql = sprintf('
            SELECT OKho.*, ODanhSachKho.LoaiKho
            FROM OKho 
            LEFT JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID 
            WHERE Ref_MaSP = %1$d %2$s
            ORDER BY Kho
        ', $itemIOID, $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}