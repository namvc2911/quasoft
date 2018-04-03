<?php

/**
 * Class Qss_Model_Warehouse_Inout
 * Function Menu
 * + F1: getInputByTime($startDate, $endDate, $vendorIOIDArr)
 * Lay danh sach nhap kho cua nhung phieu da nhap kho trong mot khoang thoi gian.
 *
 * + F2: getLastInputOfItem($inputType)
 * Lay dong nhap kho moi nhat cua san pham theo loai nhap kho.
 *
 * + F3: getTotalOutputVal($startDate,$endDate,$itemIOID,$inputType)
 * Lay tong so luong nhap (da quy ra he so co so) va gia tri tien nhap cua mot san pham
 *
 */

class Qss_Model_Warehouse_Inout extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * @param $startDate
     * @param $endDate
     * @param array $vendorIOIDArr
     * @return mixed
     */
	public function getInputByTime(
		$startDate
		, $endDate
		, $vendorIOIDArr = array()
		)
		//, $stockIOIDArr = array()
	{
		$vendorIOIDStr = count($vendorIOIDArr)?sprintf(' AND nh.Ref_MaNCC in (%1$s) ', implode(',', $vendorIOIDArr)):'';
//		$stockIOIDStr  = count($stockIOIDArr)?sprintf(' AND nh.Ref_Kho in (%1$s) ', implode(',', $stockIOIDArr)):'';
		
		
		$sql = sprintf('
			SELECT 
				nh.IOID AS InputIOID
				, nh.MaNCC AS VendorCode
				, nh.TenNCC AS VendorName
				, ifnull(nh.Ref_MaNCC, 0) AS VendorIOID
				, nh.NgayChungTu AS DocDate
				, nh.SoChungTu AS DocNo
				, ds.MaSanPham AS ItemCode
				, ds.TenSanPham AS ItemName
				, ds.DonViTinh AS UOM
				, ds.SoLuong AS QTY
				, ifnull(ds.DonGia, 0) AS UnitPrice
				, ifnull(ds.ThanhTien, 0) AS Total
			FROM ONhapKho AS nh
			INNER JOIN qsiforms AS qsi ON nh.IFID_M402 = qsi.IFID
			LEFT JOIN ODanhSachNhapKho AS ds ON nh.IFID_M402 = ds.IFID_M402 
			WHERE (nh.NgayChungTu between %1$s and %2$s)
			AND ifnull(qsi.Status, 0) = 2
			AND ifnull(nh.Ref_MaNCC, 0) <> 0
			%3$s
			ORDER BY nh.Ref_MaNCC, nh.NgayChungTu
			
		', $this->_o_DB->quote($startDate)
		, $this->_o_DB->quote($endDate)
		, $vendorIOIDStr);
		//, $stockIOIDStr
		return $this->_o_DB->fetchAll($sql);
	}

    /**
     * @param $itemIOID
     * @param string $inputType
     * @return mixed
     */
    public function getLastInputOfItem($itemIOID, $inputType = Qss_Lib_Extra_Const::INPUT_TYPE_PURCHASE)
    {
        $inputTypeFilter = $inputType?sprintf(' and  lnh.Loai =  %1$s ', $this->_o_DB->quote($inputType)):'';
        $sql             = sprintf('
            select cm.*,sp.GiaMua
            from OSanPham as sp  
            left join ODanhSachNhapKho  as cm on sp.IOID = cm.Ref_MaSanPham
            left join ONhapKho as nh ON  cm.IFID_M402 = nh.IFID_M402
            left join OLoaiNhapKho as lnh ON nh.Ref_LoaiNhapKho = lnh.IOID %2$s
            WHERE sp.IOID =  %1$d 
            GROUP BY  nh.NgayChungTu DESC
            limit 1'
        , $itemIOID
        , $inputTypeFilter);
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $itemIOID
     * @param string $inputType
     * @return mixed
     */
    public function getTotalOutputVal(
        $startDate,
        $endDate,
        $itemIOID = 0,
        $inputType = Qss_Lib_Extra_Const::INPUT_TYPE_PURCHASE
    )
    {
        $item = ($itemIOID)?sprintf(' AND ds.Ref_MaSanPham = %1$s', $itemIOID):'';
        $type = ($inputType)?sprintf(' AND lnk.Loai = %1$s', $this->_o_DB->quote($inputType)):'';

        $sql = sprintf('
			SELECT
			sum(ifnull(ds.SoLuong, 0) * ifnull(dvtsp.HeSoQuyDoi, 0)) AS TotalQty,
			sum(ifnull(ds.ThanhTien,0)) AS TotalVal
			FROM ODanhSachNhapKho AS ds
			INNER JOIN OSanPham AS sp ON ds.Ref_MaSanPham = sp.IOID
			INNER JOIN ODonViTinhSP AS dvtsp ON sp.IFID_M113 = dvtsp.IFID_M113
				AND dvtsp.IOID = ds.Ref_DonViTinh
			INNER JOIN ONhapKho AS nk ON ds.IFID_M402 = nk.IFID_M402
			INNER JOIN OLoaiNhapKho AS lnk ON nk.Ref_LoaiNhapKho = lnk.IOID
			WHERE (nk.NgayChungTu BETWEEN %1$s AND %2$s)
			%3$s
			%4$s
			GROUP BY ds.Ref_MaSanPham
		'
            , $this->_o_DB->quote($startDate)
            , $this->_o_DB->quote($endDate)
            , $item
            , $type);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }
    
    
    public function getInOutDataGroupByInOutType(
        $start
        , $end
        , $itemIOIDs= array()
        , $stockIOID = 0
    )    
    {
//        // Loc kho theo hinh cay  hoac loc theo binh thuong neu ko cau hinh kho theo dang cay
//        $filterStock = '';
//        if(Qss_Lib_Extra::checkFieldExists('OKhuVuc', 'lft'))
//        {
//            $stockSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d ', $stockIOID);
//            $stock    = $this->_o_DB->fetchOne($stockSql);
//
//            if($stock)
//            {
//                $filterStock = sprintf(' AND (dskho.lft >= %1$d AND dskho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
//            }
//        }
//        else
//        {
//            $filterStock = $stockIOID?sprintf(' AND giaodich.Ref_Kho = %1$d ',$stockIOID):'';
//        }
//
//        $filterItems = count($itemIOIDs)?sprintf(' AND giaodich.Ref_MaSanPham in (%1$s) ',implode(', ', $itemIOIDs)):'';

        $filterInput   = ''; // Loc nhap kho
        $filterOutput  = ''; // Loc xuat kho
        $filterItem    = ''; // Loc query chinh

        // Loc xuat nhap theo kho
        if(Qss_Lib_System::fieldActive('ODanhSachKho', 'TrucThuoc'))
        {
            $stockSql = sprintf('SELECT * FROM ODanhSachKho WHERE IOID = %1$d ', $stockIOID);
            $stock    = $this->_o_DB->fetchOne($stockSql);

            if($stock)
            {
                $filterInput  .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
                $filterOutput .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
                $filterItem   .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
            }
        }
        else
        {
            $filterInput  .= $stockIOID?sprintf(' AND NhapKho.Ref_Kho = %1$d ',$stockIOID):'';
            $filterOutput .= $stockIOID?sprintf(' AND XuatKho.Ref_Kho = %1$d ',$stockIOID):'';
            $filterItem   .= $stockIOID?sprintf(' AND DSKho.IOID = %1$d ',$stockIOID):'';
        }

        // Loc theo danh sach mat hang truyen vao
        $filterInput  .= count($itemIOIDs)?sprintf(' AND DSNhapKho.Ref_MaSanPham in (%1$s) ',implode(', ', $itemIOIDs)):'';
        $filterOutput .= count($itemIOIDs)?sprintf(' AND DSXuatKho.Ref_MaSP in (%1$s) ',implode(', ', $itemIOIDs)):'';
        $filterItem   .= count($itemIOIDs)?sprintf(' AND MatHang.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';


        $sql = sprintf('
            SELECT
                MatHang.IOID as RefItem,
                MatHang.MaSanPham AS ItemCode,
                MatHang.TenSanPham AS ItemName,
                MatHang.`DonViTinh` AS UOM,
                GiaoDichKho.Ref_ThuocTinh AS RefAttr, -- Chua su dung
                GiaoDichKho.ThuocTinh AS Attr, -- Chua su dung
                sum(case when IFNULL(GiaoDichKho.NhapXuat,0) = 1 then ifnull(GiaoDichKho.SoLuong, 0) end) as Input,
                sum(case when IFNULL(GiaoDichKho.NhapXuat,0) = 0 then ifnull(GiaoDichKho.SoLuong, 0) end) as Output,
                GiaoDichKho.RefType,
                GiaoDichKho.TypeIFID,
                SUM(GiaoDichKho.NormalReturn) AS NormalReturn,
                SUM(GiaoDichKho.BreakReturn) AS BreakReturn

            FROM OSanPham AS MatHang
            INNER JOIN
            (
                (
                    SELECT
                        DSNhapKho.Ref_MaSanPham
                        , NhapKho.Ref_Kho
                        , DSNhapKho.Ref_ThuocTinh
                        , DSNhapKho.ThuocTinh
                        , (IFNULL(DSNhapKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) as SoLuong
                        , (IFNULL(DSNhapKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0) * IFNULL(DonGia,0)) AS ThanhTien
                        , Loai.IOID AS RefType
                        , Loai.IFID_M613 AS TypeIFID
                        , 1 AS NhapXuat
                        , NhapKho.NgayChungTu
                        , (
                            case when
                                ifnull(Loai.`Loai`, \'\') = %6$s
                                and ifnull(DSKho.`LoaiKho`, \'\') != %7$s
                            THEN (IFNULL(DSNhapKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0))
                            ELSE 0 END
                        ) AS NormalReturn

                        , (
                          case when
                              ifnull(Loai.`Loai`, \'\') = %6$s
                              and ifnull(DSKho.`LoaiKho`, \'\') = %7$s
                          THEN (IFNULL(DSNhapKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0))
                          ELSE 0 END
                        ) AS BreakReturn
                    FROM ODanhSachNhapKho AS DSNhapKho
                    INNER JOIN ONhapKho AS NhapKho ON DSNhapKho.IFID_M402 = NhapKho.IFID_M402
                    INNER JOIN qsiforms AS IForm ON NhapKho.IFID_M402 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSNhapKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On NhapKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiNhapKho AS Loai ON NhapKho.Ref_LoaiNhapKho = Loai.IOID
                    WHERE IForm.Status = 2
                        AND (NhapKho.NgayChungTu BETWEEN %1$s AND %2$s) %3$s
                    -- GROUP BY DSNhapKho.Ref_MaSanPham, ifnull(DSNhapKho.Ref_ThuocTinh,0), Loai.IFID_M613
                )
                UNION ALL
                -- AS NhapKho
                -- ON MatHang.IOID = NhapKho.Ref_MaSanPham
                -- LEFT JOIN
                (
                    SELECT
                        DSXuatKho.Ref_MaSP AS Ref_MaSanPham
                        , XuatKho.Ref_Kho
                        , DSXuatKho.Ref_ThuocTinh
                        , DSXuatKho.ThuocTinh
                        , (IFNULL(DSXuatKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) as SoLuong
                        , (IFNULL(DSXuatKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0) * IFNULL(DonGia,0)) AS ThanhTien
                        , Loai.IOID AS RefType
                        , Loai.IFID_M614 AS TypeIFID
                        , 0 AS NhapXuat
                        , XuatKho.NgayChungTu
                        , 0 AS NormalReturn
                        , 0 AS BreakReturn
                    FROM ODanhSachXuatKho AS DSXuatKho
                    INNER JOIN OXuatKho AS XuatKho ON DSXuatKho.IFID_M506 = XuatKho.IFID_M506
                    INNER JOIN qsiforms AS IForm ON XuatKho.IFID_M506 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSXuatKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On XuatKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiXuatKho AS Loai ON XuatKho.Ref_LoaiXuatKho = Loai.IOID
                    WHERE IForm.Status = 2
                        AND (XuatKho.NgayChungTu BETWEEN %1$s AND %2$s) %4$s
                    -- GROUP BY DSXuatKho.Ref_MaSP, ifnull(DSXuatKho.Ref_ThuocTinh,0), Loai.IFID_M614
                )
                -- AS XuatKho
                -- ON XuatKho.Ref_MaSanPham = MatHang.IOID
            ) AS GiaoDichKho ON MatHang.IOID = GiaoDichKho.Ref_MaSanPham
            INNER JOIN ODanhSachKho AS DSKho ON GiaoDichKho.Ref_Kho = DSKho.IOID
            WHERE (GiaoDichKho.NgayChungTu between %1$s and %2$s) %5$s
            GROUP BY GiaoDichKho.`Ref_MaSanPham`, IFNULL(GiaoDichKho.`Ref_ThuocTinh`, 0), GiaoDichKho.TypeIFID'
			, $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
			, $filterInput
            , $filterOutput
            , $filterItem
            , $this->_o_DB->quote(Qss_Lib_Extra_Const::INPUT_TYPE_RETURN)
            , $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_DRAFT));
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }    
    
    public function getInOutData(
        $start
        , $end
        , $itemIOIDs= array()
        , $stockIOID = 0
    )    
    {
//        $filterStock = $stockIOID?sprintf(' AND giaodich.Ref_Kho = %1$d ',$stockIOID):'';
//        $filterItems = count($itemIOIDs)?sprintf(' AND giaodich.Ref_MaSanPham in (%1$s) ',implode(', ', $itemIOIDs)):'';

        $filterInput   = ''; // Loc nhap kho
        $filterOutput  = ''; // Loc xuat kho
        $filterItem    = ''; // Loc query chinh

        // Loc xuat nhap theo kho
        if(Qss_Lib_System::fieldActive('ODanhSachKho', 'TrucThuoc'))
        {
            $stockSql = sprintf('SELECT * FROM ODanhSachKho WHERE IOID = %1$d ', $stockIOID);
            $stock    = $this->_o_DB->fetchOne($stockSql);

            if($stock)
            {
                $filterInput  .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
                $filterOutput .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
                $filterItem   .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
            }
        }
        else
        {
            $filterInput  .= $stockIOID?sprintf(' AND NhapKho.Ref_Kho = %1$d ',$stockIOID):'';
            $filterOutput .= $stockIOID?sprintf(' AND XuatKho.Ref_Kho = %1$d ',$stockIOID):'';
            $filterItem   .= $stockIOID?sprintf(' AND DSKho.IOID = %1$d ',$stockIOID):'';
        }

        // Loc theo danh sach mat hang truyen vao
        $filterInput  .= count($itemIOIDs)?sprintf(' AND DSNhapKho.Ref_MaSanPham in (%1$s) ',implode(', ', $itemIOIDs)):'';
        $filterOutput .= count($itemIOIDs)?sprintf(' AND DSXuatKho.Ref_MaSP in (%1$s) ',implode(', ', $itemIOIDs)):'';
        $filterItem   .= count($itemIOIDs)?sprintf(' AND MatHang.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';


        $sql = sprintf('
            SELECT
                MatHang.IOID as RefItem,
                MatHang.MaSanPham AS ItemCode,
                MatHang.TenSanPham AS ItemName,
                MatHang.`DonViTinh` AS UOM,
                GiaoDichKho.Ref_ThuocTinh AS RefAttr, -- Chua su dung
                GiaoDichKho.ThuocTinh AS Attr, -- Chua su dung
                sum(case when IFNULL(GiaoDichKho.NhapXuat,0) = 1 then ifnull(GiaoDichKho.SoLuong, 0) end) as Input,
                sum(case when IFNULL(GiaoDichKho.NhapXuat,0) = 0 then ifnull(GiaoDichKho.SoLuong, 0) end) as Output
            FROM OSanPham AS MatHang
            INNER JOIN
            (
                (
                    SELECT
                        DSNhapKho.Ref_MaSanPham
                        , NhapKho.Ref_Kho
                        , DSNhapKho.Ref_ThuocTinh
                        , DSNhapKho.ThuocTinh
                        , (IFNULL(DSNhapKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) as SoLuong
                        , 1 AS NhapXuat
                        , NhapKho.NgayChungTu
                    FROM ODanhSachNhapKho AS DSNhapKho
                    INNER JOIN ONhapKho AS NhapKho ON DSNhapKho.IFID_M402 = NhapKho.IFID_M402
                    INNER JOIN qsiforms AS IForm ON NhapKho.IFID_M402 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSNhapKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On NhapKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiNhapKho AS Loai ON NhapKho.Ref_LoaiNhapKho = Loai.IOID
                    WHERE IForm.Status = 2
                        AND (NhapKho.NgayChungTu BETWEEN %1$s AND %2$s) %3$s
                    -- GROUP BY DSNhapKho.Ref_MaSanPham, ifnull(DSNhapKho.Ref_ThuocTinh,0), Loai.IFID_M613
                )
                UNION ALL
                -- AS NhapKho
                -- ON MatHang.IOID = NhapKho.Ref_MaSanPham
                -- LEFT JOIN
                (
                    SELECT
                        DSXuatKho.Ref_MaSP AS Ref_MaSanPham
                        , XuatKho.Ref_Kho
                        , DSXuatKho.Ref_ThuocTinh
                        , DSXuatKho.ThuocTinh
                        , (IFNULL(DSXuatKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) as SoLuong
                        , 0 AS NhapXuat
                        , XuatKho.NgayChungTu
                    FROM ODanhSachXuatKho AS DSXuatKho
                    INNER JOIN OXuatKho AS XuatKho ON DSXuatKho.IFID_M506 = XuatKho.IFID_M506
                    INNER JOIN qsiforms AS IForm ON XuatKho.IFID_M506 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSXuatKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On XuatKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiXuatKho AS Loai ON XuatKho.Ref_LoaiXuatKho = Loai.IOID
                    WHERE IForm.Status = 2
                        AND (XuatKho.NgayChungTu BETWEEN %1$s AND %2$s) %4$s
                    -- GROUP BY DSXuatKho.Ref_MaSP, ifnull(DSXuatKho.Ref_ThuocTinh,0), Loai.IFID_M614
                )
                -- AS XuatKho
                -- ON XuatKho.Ref_MaSanPham = MatHang.IOID
            ) AS GiaoDichKho ON MatHang.IOID = GiaoDichKho.Ref_MaSanPham
            INNER JOIN ODanhSachKho AS DSKho ON GiaoDichKho.Ref_Kho = DSKho.IOID
            WHERE (GiaoDichKho.NgayChungTu between %1$s and %2$s) %5$s
            GROUP BY GiaoDichKho.`Ref_MaSanPham`, IFNULL(GiaoDichKho.`Ref_ThuocTinh`, 0)'
			, $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $filterInput
            , $filterOutput
            , $filterItem);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getInOutDataGroupByStockAndItemGroup(
        $start
        , $end
        , $itemIOIDs= array()
        , $stockIOID = 0
    )    
    {
//        $filterStock = $stockIOID?sprintf(' AND giaodich.Ref_Kho = %1$d ',$stockIOID):'';
//        $filterItems = count($itemIOIDs)?sprintf(' AND giaodich.Ref_MaSanPham in (%1$s) ',implode(', ', $itemIOIDs)):'';

        $filterInput   = ''; // Loc nhap kho
        $filterOutput  = ''; // Loc xuat kho
        $filterItem    = ''; // Loc query chinh

        // Loc xuat nhap theo kho
        if(Qss_Lib_System::fieldActive('ODanhSachKho', 'TrucThuoc'))
        {
            $stockSql = sprintf('SELECT * FROM ODanhSachKho WHERE IOID = %1$d ', $stockIOID);
            $stock    = $this->_o_DB->fetchOne($stockSql);

            if($stock)
            {
                $filterInput  .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
                $filterOutput .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
                $filterItem   .= sprintf(' AND (DSKho.lft >= %1$d AND DSKho.rgt <= %2$d) ',$stock->lft, $stock->rgt);
            }
        }
        else
        {
            $filterInput  .= $stockIOID?sprintf(' AND NhapKho.Ref_Kho = %1$d ',$stockIOID):'';
            $filterOutput .= $stockIOID?sprintf(' AND XuatKho.Ref_Kho = %1$d ',$stockIOID):'';
            $filterItem   .= $stockIOID?sprintf(' AND DSKho.IOID = %1$d ',$stockIOID):'';
        }

        // Loc theo danh sach mat hang truyen vao
        $filterInput  .= count($itemIOIDs)?sprintf(' AND DSNhapKho.Ref_MaSanPham in (%1$s) ',implode(', ', $itemIOIDs)):'';
        $filterOutput .= count($itemIOIDs)?sprintf(' AND DSXuatKho.Ref_MaSP in (%1$s) ',implode(', ', $itemIOIDs)):'';
        $filterItem   .= count($itemIOIDs)?sprintf(' AND MatHang.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';


        $sql = sprintf('
            SELECT
                DSKho.`MaKho` AS StockCode,
                DSKho.`TenKho` AS StockName,
                NhomSP.TenNhom AS GroupName,
                ifnull(NhomSP.`IOID`, 0) AS RefGroup,
                ifnull(GiaoDichKho.`Ref_Kho`, 0) AS RefStock,
                MatHang.IOID as RefItem,
                MatHang.MaSanPham AS ItemCode,
                MatHang.TenSanPham AS ItemName,
                MatHang.`DonViTinh` AS UOM,
                GiaoDichKho.Ref_ThuocTinh AS RefAttr, -- Chua su dung
                GiaoDichKho.ThuocTinh AS Attr, -- Chua su dung
                sum(case when IFNULL(GiaoDichKho.NhapXuat,0) = 1 then ifnull(GiaoDichKho.SoLuong, 0) end) as Input,
                sum(case when IFNULL(GiaoDichKho.NhapXuat,0) = 0 then ifnull(GiaoDichKho.SoLuong, 0) end) as Output
            FROM OSanPham AS MatHang
            INNER JOIN
            (
                (
                    SELECT
                        DSNhapKho.Ref_MaSanPham
                        , NhapKho.Ref_Kho
                        , DSNhapKho.Ref_ThuocTinh
                        , DSNhapKho.ThuocTinh
                        , (IFNULL(DSNhapKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) as SoLuong
                        , 1 AS NhapXuat
                        , NhapKho.NgayChungTu
                    FROM ODanhSachNhapKho AS DSNhapKho
                    INNER JOIN ONhapKho AS NhapKho ON DSNhapKho.IFID_M402 = NhapKho.IFID_M402
                    INNER JOIN qsiforms AS IForm ON NhapKho.IFID_M402 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSNhapKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On NhapKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiNhapKho AS Loai ON NhapKho.Ref_LoaiNhapKho = Loai.IOID
                    WHERE IForm.Status = 2
                        AND (NhapKho.NgayChungTu BETWEEN %1$s AND %2$s) %3$s
                    -- GROUP BY DSNhapKho.Ref_MaSanPham, ifnull(DSNhapKho.Ref_ThuocTinh,0), Loai.IFID_M613
                )
                UNION ALL
                -- AS NhapKho
                -- ON MatHang.IOID = NhapKho.Ref_MaSanPham
                -- LEFT JOIN
                (
                    SELECT
                        DSXuatKho.Ref_MaSP AS Ref_MaSanPham
                        , XuatKho.Ref_Kho
                        , DSXuatKho.Ref_ThuocTinh
                        , DSXuatKho.ThuocTinh
                        , (IFNULL(DSXuatKho.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) as SoLuong
                        , 0 AS NhapXuat
                        , XuatKho.NgayChungTu
                    FROM ODanhSachXuatKho AS DSXuatKho
                    INNER JOIN OXuatKho AS XuatKho ON DSXuatKho.IFID_M506 = XuatKho.IFID_M506
                    INNER JOIN qsiforms AS IForm ON XuatKho.IFID_M506 = IForm.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DSXuatKho.Ref_DonViTinh = DonViTinh.IOID
                    INNER JOIN ODanhSachKho AS DSKho On XuatKho.Ref_Kho = DSKho.IOID
                    INNER JOIN OLoaiXuatKho AS Loai ON XuatKho.Ref_LoaiXuatKho = Loai.IOID
                    WHERE IForm.Status = 2
                        AND (XuatKho.NgayChungTu BETWEEN %1$s AND %2$s) %4$s
                    -- GROUP BY DSXuatKho.Ref_MaSP, ifnull(DSXuatKho.Ref_ThuocTinh,0), Loai.IFID_M614
                )
                -- AS XuatKho
                -- ON XuatKho.Ref_MaSanPham = MatHang.IOID
            ) AS GiaoDichKho ON MatHang.IOID = GiaoDichKho.Ref_MaSanPham
            LEFT JOIN ODanhSachKho AS DSKho ON GiaoDichKho.Ref_Kho = DSKho.IOID
            LEFT JOIN ONhomSanPham AS NhomSP ON MatHang.`Ref_NhomSP` = NhomSP.`IOID`
            WHERE (GiaoDichKho.NgayChungTu between %1$s and %2$s) %5$s
            GROUP BY GiaoDichKho.`Ref_Kho`, GiaoDichKho.`Ref_MaSanPham`, IFNULL(GiaoDichKho.`Ref_ThuocTinh`, 0)
            ORDER BY GiaoDichKho.`Ref_Kho`, NhomSP.lft, MatHang.`MaSanPham`'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $filterInput
            , $filterOutput
            , $filterItem);
        return $this->_o_DB->fetchAll($sql);
    }    
    
    
    /*
     *==========================================================================
     * Join với bảng cost của tháng
     * Nếu tồn tại bảng cost của tháng năm (dựa vào ngày bắt đầu)
     *    Join với bảng cost của tháng
     * Nếu không tồn tại
     *    Lùi tháng về 1 join voi bang cua cost
     *    Neu co join voi bang cost
     *    Khong co thi khong join nua lay gia tu mat hang 
     * 
     *==========================================================================
     */
    /**
     * 
     * @param type $start
     * @param type $end
     * @param type $itemIOIDs
     * @param type $stockIOID
     * @param type $hasQty
     * @param type $hasTransaction
     * @return type
     */
    public function getCostTable(
        $date
        , $itemIOIDs= array()
        , $stockIOID = 0
    )
	{
        $month     = (int)date('m', strtotime($date));
        $year      = (int)date('Y', strtotime($date));
        $costTable = sprintf('tblcost%1$s%2$s',str_pad($month, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
        
        if($this->_o_DB->tableExists($costTable))
        {
            // Ton tai bang dau ki cung thang
            $filterStock = $stockIOID?sprintf(' AND cost.Ref_Kho = %1$d ',$stockIOID):'';
            $filterItems = count($itemIOIDs)?sprintf(' AND sanpham.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';
            
            $sql = sprintf('
                SELECT 
                    sanpham.IOID AS RefItem,
                    ifnull(cost.Ref_ThuocTinh, 0) AS RefAttr,
                    ifnull(cost.ThuocTinh, \'\') AS Attr,
                    sanpham.`MaSanPham` AS ItemCode,
                    sanpham.`TenSanPham` AS ItemName,  
                    sanpham.`DonViTinh` AS UOM,  
                    ifnull(cost.Ref_Kho, 0) AS RefStock,
                    cost.Gia AS Price,
                    cost.TonKhoDK AS FirstQty,
                    cost.Nhap AS Input,
                    cost.Xuat AS Output,
                    cost.TonKhoCK AS LastQty,
                    1 AS HasCostTable
                FROM OSanPham AS sanpham
                LEFT JOIN %1$s AS cost ON sanpham.IOID = cost.Ref_MaSanPham
                WHERE 1=1 %2$s %3$s
                ORDER BY sanpham.MaSanPham
            ', $costTable, $filterItems, $filterStock);
        }
        else
        {
            
            $month--;
            if($month == 0)
            {
                $month = 12;
                $year--;
            }
            $costTable   = sprintf('tblcost%1$s%2$s',str_pad($month, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
            $filterStock = $stockIOID?sprintf(' AND cost.Ref_Kho = %1$d ',$stockIOID):'';
            $filterItems = count($itemIOIDs)?sprintf(' AND sanpham.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';
            
            if($this->_o_DB->tableExists($costTable))
            {
            // Lay bang tinh gia cua thang truoc neu khong co cua thang nay
            $sql = sprintf('
                SELECT 
                    sanpham.IOID AS RefItem,
                    ifnull(cost.Ref_ThuocTinh, 0) AS RefAttr,
                    ifnull(cost.ThuocTinh, \'\') AS Attr,
                    sanpham.`MaSanPham` AS ItemCode,
                    sanpham.`TenSanPham` AS ItemName,
                    sanpham.`DonViTinh` AS UOM,  
                    ifnull(cost.Ref_Kho, 0) AS RefStock,
                    cost.Gia AS Price,
                    cost.TonKhoDK AS FirstQty,
                    cost.Nhap AS InQty,
                    cost.Xuat AS OutQty,
                    cost.TonKhoCK AS LastQty,
                    2 AS HasCostTable
                FROM OSanPham AS sanpham
                LEFT JOIN %1$s AS cost ON sanpham.IOID = cost.Ref_MaSanPham                
                WHERE 1=1 %2$s %3$s
                ORDER BY sanpham.MaSanPham
            ', $costTable, $filterItems, $filterStock);
            }   
            else
            {
                // Lay truc tiep tu kho va mat hang neu khong co 
                $filterStock = $stockIOID?sprintf(' AND kho.Ref_Kho = %1$d ',$stockIOID):'';
                $filterItems = count($itemIOIDs)?sprintf(' AND sanpham.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';
                
                $sql = sprintf('
                    SELECT 
                        sanpham.IOID AS RefItem,
                        sanpham.`MaSanPham` AS ItemCode,
                        sanpham.`TenSanPham` AS ItemName,
                        sanpham.`DonViTinh` AS UOM,  
                        ifnull(kho.`Ref_ThuocTinh`, 0) AS RefAttr,
                        ifnull(kho.ThuocTinh, \'\') AS Attr,
                        ifnull(kho.`Ref_Kho`, 0) AS RefStock,
                        (ifnull(sanpham.`GiaMua`, 0) * ifnull(donvitinh1.`HeSoQuyDoi`, 0)) AS Price,
                        sum( ifnull(kho.`SoLuongKhoiTao`, 0) * ifnull(donvitinh1.`HeSoQuyDoi`, 0)) AS FirstQty,
                        0 AS InQty,
                        0 AS OutQty,
                        sum( ifnull(kho.`SoLuongKhoiTao`, 0) * ifnull(donvitinh1.`HeSoQuyDoi`, 0)) AS LastQty,
                        0 AS HasCostTable
                    FROM OSanPham AS sanpham
                    LEFT JOIN OKho AS kho ON sanpham.IOID = kho.`Ref_MaSP` 
                    LEFT JOIN ODonViTinhSP AS donvitinh1 ON sanpham.`IFID_M113` = donvitinh1.`IFID_M113` 
                        AND kho.`Ref_DonViTinh` = donvitinh1.IOID
                    WHERE 1=1 %2$s %3$s
                    GROUP BY sanpham.IOID , kho.`Ref_ThuocTinh`
                    ORDER BY sanpham.MaSanPham
                ', $costTable, $filterItems, $filterStock);               
            }
        }
		return $this->_o_DB->fetchAll($sql);        
	}
    
    
    public function getCostTableOrderByStockAndItemGroup(
        $date
        , $itemIOIDs= array()
        , $stockIOID = 0
    )
	{
        $month     = (int)date('m', strtotime($date));
        $year      = (int)date('Y', strtotime($date));
        $costTable = sprintf('tblcost%1$s%2$s',str_pad($month, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));

        
        if($this->_o_DB->tableExists($costTable))
        {
            // Ton tai bang dau ki cung thang
            $filterStock = $stockIOID?sprintf(' AND cost.Ref_Kho = %1$d ',$stockIOID):'';
            $filterItems = count($itemIOIDs)?sprintf(' AND sanpham.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';
            
            $sql = sprintf('
                SELECT 
                    danhsachkho.`MaKho` AS StockCode,
                    danhsachkho.`TenKho` AS StockName,
                    nhom.`TenNhom` AS GroupName,                
                    ifnull(nhom.`IOID`, 0) AS RefGroup,
                    sanpham.IOID AS RefItem,
                    ifnull(cost.Ref_ThuocTinh, 0) AS RefAttr,
                    ifnull(cost.ThuocTinh, \'\') AS Attr,
                    sanpham.`MaSanPham` AS ItemCode,
                    sanpham.`TenSanPham` AS ItemName,  
                    sanpham.`DonViTinh` AS UOM,  
                    ifnull(cost.Ref_Kho, 0) AS RefStock,
                    cost.TonKhoDK AS FirstQty,
                    cost.Nhap AS Input,
                    cost.Xuat AS Output,
                    cost.TonKhoCK AS LastQty,
                    1 AS HasCostTable
                FROM OSanPham AS sanpham
                LEFT JOIN %1$s AS cost ON sanpham.IOID = cost.Ref_MaSanPham
                LEFT JOIN ONhomSanPham AS nhom ON sanpham.`Ref_NhomSP` = nhom.`IOID`
                LEFT JOIN ODanhSachKho AS danhsachkho ON cost.`Ref_Kho` = danhsachkho.IOID
                WHERE cost.`Ref_Kho` != 0  %2$s %3$s
                ORDER BY cost.`Ref_Kho`,CASE WHEN ifnull(nhom.`IOID`, 0) = 0 THEN 1000000 ELSE nhom.lft END, sanpham.`MaSanPham`
            ', $costTable, $filterItems, $filterStock);
        }
        else
        {
            
            $month--;
            if($month == 0)
            {
                $month = 12;
                $year--;
            }
            $costTable   = sprintf('tblcost%1$s%2$s',str_pad($month, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
            $filterStock = $stockIOID?sprintf(' AND cost.Ref_Kho = %1$d ',$stockIOID):'';
            $filterItems = count($itemIOIDs)?sprintf(' AND sanpham.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';
            
            if($this->_o_DB->tableExists($costTable))
            {
            // Lay bang tinh gia cua thang truoc neu khong co cua thang nay
            $sql = sprintf('
                SELECT 
                    danhsachkho.`MaKho` AS StockCode,
                    danhsachkho.`TenKho` AS StockName,
                    nhom.`TenNhom` AS GroupName,
                    ifnull(nhom.`IOID`, 0) AS RefGroup,
                    sanpham.IOID AS RefItem,
                    ifnull(cost.Ref_ThuocTinh, 0) AS RefAttr,
                    ifnull(cost.ThuocTinh, \'\') AS Attr,
                    sanpham.`MaSanPham` AS ItemCode,
                    sanpham.`TenSanPham` AS ItemName,
                    sanpham.`DonViTinh` AS UOM,  
                    ifnull(cost.Ref_Kho, 0) AS RefStock,
                    cost.TonKhoDK AS FirstQty,
                    cost.Nhap AS InQty,
                    cost.Xuat AS OutQty,
                    cost.TonKhoCK AS LastQty,
                    2 AS HasCostTable
                FROM OSanPham AS sanpham
                LEFT JOIN %1$s AS cost ON sanpham.IOID = cost.Ref_MaSanPham   
                LEFT JOIN ONhomSanPham AS nhom ON sanpham.`Ref_NhomSP` = nhom.`IOID`
                LEFT JOIN ODanhSachKho AS danhsachkho ON cost.`Ref_Kho` = danhsachkho.IOID
                WHERE cost.`Ref_Kho` != 0  %2$s %3$s
                ORDER BY cost.`Ref_Kho`,CASE WHEN ifnull(nhom.`IOID`, 0) = 0 THEN 1000000 ELSE nhom.lft END, sanpham.`MaSanPham`
            ', $costTable, $filterItems, $filterStock);
            }   
            else
            {
                // Lay truc tiep tu kho va mat hang neu khong co 
                $filterStock = $stockIOID?sprintf(' AND kho.Ref_Kho = %1$d ',$stockIOID):'';
                $filterItems = count($itemIOIDs)?sprintf(' AND sanpham.IOID in (%1$s) ',implode(', ', $itemIOIDs)):'';
                
                $sql = sprintf('
                    SELECT 
                        danhsachkho.`MaKho` AS StockCode,
                        danhsachkho.`TenKho` AS StockName,
                        nhom.`TenNhom` AS GroupName,                    
                        ifnull(nhom.`IOID`, 0) AS RefGroup,
                        sanpham.IOID AS RefItem,
                        sanpham.`MaSanPham` AS ItemCode,
                        sanpham.`TenSanPham` AS ItemName,
                        sanpham.`DonViTinh` AS UOM,  
                        ifnull(kho.`Ref_ThuocTinh`, 0) AS RefAttr,
                        ifnull(kho.ThuocTinh, \'\') AS Attr,
                        ifnull(kho.`Ref_Kho`, 0) AS RefStock,
                        sum( ifnull(kho.`SoLuongKhoiTao`, 0) * ifnull(donvitinh1.`HeSoQuyDoi`, 0)) AS FirstQty,
                        0 AS InQty,
                        0 AS OutQty,
                        sum( ifnull(kho.`SoLuongKhoiTao`, 0) * ifnull(donvitinh1.`HeSoQuyDoi`, 0)) AS LastQty,
                        0 AS HasCostTable
                    FROM OSanPham AS sanpham
                    INNER JOIN OKho AS kho ON sanpham.IOID = kho.`Ref_MaSP` 
                    LEFT JOIN ODonViTinhSP AS donvitinh1 ON sanpham.`IFID_M113` = donvitinh1.`IFID_M113` 
                        AND kho.`Ref_DonViTinh` = donvitinh1.IOID
                    LEFT JOIN ONhomSanPham AS nhom ON sanpham.`Ref_NhomSP` = nhom.`IOID`
                    LEFT JOIN ODanhSachKho AS danhsachkho ON kho.`Ref_Kho` = danhsachkho.IOID
                    WHERE 1=1 %2$s %3$s
                    GROUP BY kho.`Ref_Kho`, sanpham.IOID , kho.`Ref_ThuocTinh`
                    ORDER BY  kho.`Ref_Kho`, CASE WHEN ifnull(nhom.`IOID`, 0) = 0 THEN 1000000 ELSE nhom.lft END, sanpham.`MaSanPham`
                ', $costTable, $filterItems, $filterStock);               
            }
        }
		return $this->_o_DB->fetchAll($sql);        
	}

    public function getOutputByType($start, $end, $warehouse = 0, $outputType = array())
    {
        $typeIOIDStr = count($outputType)?sprintf(' AND nh.Ref_LoaiXuatKho in (%1$s) ', implode(',', $outputType)):'';
        $stockIOIDStr = ($warehouse)?sprintf(' AND nh.Ref_Kho = %1$d ', $warehouse ):'';


        $sql = sprintf('
			SELECT
				nh.IOID AS InputIOID
				, nh.LoaiXuatKho AS VendorCode
				, nh.LoaiXuatKho AS VendorName
				, ifnull(nh.Ref_LoaiXuatKho, 0) AS VendorIOID
				, nh.NgayChungTu AS DocDate
				, nh.SoChungTu AS DocNo
				, ds.MaSP AS ItemCode
				, ds.TenSP AS ItemName
				, ds.DonViTinh AS UOM
				, ds.SoLuong AS QTY
				, ifnull(ds.DonGia, 0) AS UnitPrice
				, ifnull(ds.ThanhTien, 0) AS Total
			FROM OXuatKho AS nh
			INNER JOIN qsiforms AS qsi ON nh.IFID_M506 = qsi.IFID
			LEFT JOIN ODanhSachXuatKho AS ds ON nh.IFID_M506 = ds.IFID_M506
			WHERE (nh.NgayChungTu between %1$s and %2$s)
			AND ifnull(qsi.Status, 0) = 2
			%3$s
			%4$s
			ORDER BY nh.Ref_LoaiXuatKho, nh.NgayChungTu

		', $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $typeIOIDStr
            , $stockIOIDStr);

        return $this->_o_DB->fetchAll($sql);
    }

    public function getMaintainReturnInputCostByEquip($start, $end, $equipIOID)
    {
        $sql = sprintf
        ('
            SELECT
                ds.*
                , nh.NgayChungTu
                , nh.NgayChuyenHang
                , nh.SoChungTu
                , ifnull(ds.Ref_ViTri, 0) AS Ref_ViTri
                , ifnull(ds.DonGia, 0) AS DonGia
                , ifnull(ds.ThanhTien, 0) AS ThanhTien
            FROM ONhapKho AS nh
            INNER JOIN qsiforms AS ifo ON nh.IFID_M402 = ifo.IFID
            INNER JOIN OLoaiNhapKho AS l ON nh.Ref_LoaiNhapKho = l.IOID
            INNER JOIN ODanhSachNhapKho AS ds ON nh.IFID_M402 = ds.IFID_M402
            INNER JOIN ODanhSachThietBi AS tb ON ds.Ref_MaThietBi = tb.IOID
            WHERE
                tb.IOID = %1$d
                and (nh.NgayChungTu BETWEEN %2$s AND %3$s)
                and (l.Loai = %4$s)
                and ifo.Status = 2
            ORDER BY ds.Ref_MaThietBi, ds.Ref_ViTri, ds.Ref_MaSanPham, nh.NgayChungTu, nh.SoChungTu '
            , $equipIOID
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $this->_o_DB->quote(Qss_Lib_Extra_Const::INPUT_TYPE_RETURN)
        );
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getMaintainOutputCostByEquip($start, $end, $equipIOID)
    {
        $sql = sprintf
        ('
            SELECT
                ds.*
                , ch.NgayChungTu
                , ch.NgayChuyenHang
                , ch.SoChungTu
                , ifnull(ds.Ref_ViTri, 0) AS Ref_ViTri
                , ifnull(ds.DonGia, 0) AS DonGia
                , ifnull(ds.ThanhTien, 0) AS ThanhTien
            FROM OXuatKho AS ch
            INNER JOIN qsiforms AS ifo ON ch.IFID_M506 = ifo.IFID
            INNER JOIN OLoaiXuatKho AS l ON ch.Ref_LoaiXuatKho = l.IOID
            INNER JOIN ODanhSachXuatKho AS ds ON ch.IFID_M506 = ds.IFID_M506
            INNER JOIN ODanhSachThietBi AS tb ON ds.Ref_MaThietBi = tb.IOID
            WHERE
                tb.IOID = %1$d
                and (ch.NgayChungTu BETWEEN %2$s AND %3$s)
                and l.Loai in = %4$s)
                and ifo.Status = 2
            ORDER BY ds.Ref_MaThietBi, ds.Ref_ViTri, ds.Ref_MaSP, ch.NgayChungTu, ch.SoChungTu '
            , $equipIOID
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $this->_o_DB->quote(Qss_Lib_Extra_Const::OUTPUT_TYPE_MAINTAIN)
        );

        return $this->_o_DB->fetchAll($sql);
    }

    public function getOutputByWorkorder($workOrderIOID)
    {
        $sql = sprintf('
            SELECT OXuatKho.* 
            FROM OXuatKho
            INNER JOIN qsiforms ON OXuatKho.IFID_M506 = qsiforms.IFID
            WHERE Ref_PhieuBaoTri = %1$d AND qsiforms.Status != 3
            LIMIT 1'
        , $workOrderIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getInputWithPOInfo($inputIOID)
    {
        $sql = sprintf('
            SELECT ONhapKho.SoChungTu, ONhapKho.NguoiGiao, ONhapKho.Kho, ODonMuaHang.SoDonHang, ODonMuaHang.NgayDatHang, ODonMuaHang.TenNCC
            FROM ONhapKho
            LEFT JOIN ODonMuaHang ON IFNULL(ONhapKho.Ref_SoDonHang, 0) = ODonMuaHang.IOID 
            WHERE ONhapKho.IOID = %1$d'
            , $inputIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getInputLineWithPOInfo($inputIOID)
    {
        $sql = sprintf('
            SELECT ODanhSachNhapKho.*, IFNULL(ODSDonMuaHang.SoLuong, 0) AS SoLuongDatMua
            FROM ODanhSachNhapKho
            INNER JOIN ONhapKho ON ODanhSachNhapKho.IFID_M402 = ONhapKho.IFID_M402 
            LEFT JOIN ODonMuaHang ON IFNULL(ONhapKho.Ref_SoDonHang, 0) = ODonMuaHang.IOID 
            LEFT JOIN ODSDonMuaHang ON ODonMuaHang.IFID_M401 = ODSDonMuaHang.IFID_M401 
                AND ODanhSachNhapKho.Ref_MaSanPham = ODSDonMuaHang.Ref_MaSP
                AND ODanhSachNhapKho.Ref_DonViTinh = ODSDonMuaHang.Ref_DonViTinh
            WHERE ONhapKho.IOID = %1$d'
            , $inputIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * @module Bao cao tong hop theo doi tuong
     * Tong hop chi phi thong qua xuat kho gop theo tung khu vuc
     * @param date $filter['start']  (R) yeu cau, ngay bat dau (yyyy-mm-dd)
     * @param date $filter['end'] (R) yeu cau, ngay ket thuc (yyyy-mm-dd)
     * @param array $filter['eqs'] (R) yeu cau, mang danh muc thiet bi ([0]=>eq_id)
     * @todo can loc theo phieu xuat kho da ket thuc
     */
    public function getVatTuByLocation($start, $end, $location = 0)
    {
        $where = '';

        if ($location)
        {
            $mTable = Qss_Model_Db::Table('OKhuVuc');
            $mTable->where(sprintf('IOID = %1$d', $location));
            $locName = $mTable->fetchOne();
            if ($locName)
            {
                $where .= sprintf('
					and DSTB.Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)'
                    , $locName->lft, $locName->rgt);
            }
        }

        $sql = sprintf('SELECT dsxk.*,KhuVuc.MaKhuVuc AS MaNhom, sum(IFNULL(dsxk.SoLuong,0)) AS SoLuongVatTu,
                          sum(IFNULL(dsxk.ThanhTien,0)) AS ThanhTien
                          FROM ODanhSachXuatKho AS dsxk
                          INNER JOIN OXuatKho AS xk ON xk.IFID_M506 = dsxk.IFID_M506
                          INNER JOIN OPhieuBaoTri AS Phieu ON xk.Ref_PhieuBaoTri = Phieu.IOID
					      INNER JOIN ODanhSachThietBi AS dstb ON Phieu.Ref_MaThietBi = dstb.IOID
					      LEFT JOIN OKhuVuc AS KhuVuc ON KhuVuc.IOID = dstb.Ref_MaKhuVuc
					      WHERE
                            (xk.NgayChungTu BETWEEN %1$s AND %2$s)
                            %3$s
                            GROUP BY IFNULL(dstb.Ref_MaKhuVuc, 0), dsxk.Ref_MaSP, dsxk.Ref_DonViTinh
                            ORDER BY KhuVuc.lft, dsxk.TenSP'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $where
        );
        //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getVatTuByUnit($start, $end, $workcenter = 0)
    {
        $where = '';
        $where .= $workcenter?sprintf(' AND IFNULL(dvsx.IOID, 0) = %1$d', $workcenter):'';

        $sql = sprintf('SELECT dsxk.*, sum(IFNULL(dsxk.SoLuong,0)) AS SoLuongVatTu,
                          dvsx.Ten AS TenNhom,dvsx.Ma AS MaNhom,
                          sum(IFNULL(dsxk.ThanhTien,0)) AS ThanhTien
                          FROM ODanhSachXuatKho AS dsxk
                          INNER JOIN OXuatKho AS xk ON xk.IFID_M506 = dsxk.IFID_M506
                          INNER JOIN OPhieuBaoTri AS Phieu ON xk.Ref_PhieuBaoTri = Phieu.IOID
					      INNER JOIN ODanhSachThietBi AS dstb ON Phieu.Ref_MaThietBi = dstb.IOID
					      INNER JOIN ODonViSanXuat AS dvsx ON xk.Ref_DonViThucHien = dvsx.IOID
					      WHERE
                            (xk.NgayChungTu BETWEEN %1$s AND %2$s)
                            %3$s
                            GROUP BY xk.Ref_DonViThucHien , dsxk.Ref_MaSP, dsxk.Ref_DonViTinh
                            ORDER BY dvsx.Ten'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $where
        );
        // echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getVatTuByEquipment($start, $end, $equipment = 0)
    {
        $where = '';
        $where .= $equipment?sprintf(' AND IFNULL(Phieu.Ref_MaThietBi, 0) = %1$d', $equipment):'';

        $sql = sprintf('SELECT dsxk.*, sum(IFNULL(dsxk.SoLuong,0)) AS SoLuongVatTu,
                          dstb.MaThietBi AS MaNhom,dstb.TenThietBi AS TenNhom,
                          sum(IFNULL(dsxk.ThanhTien,0)) AS ThanhTien
                          FROM ODanhSachXuatKho AS dsxk
                          INNER JOIN OXuatKho AS xk ON xk.IFID_M506 = dsxk.IFID_M506
                          INNER JOIN OPhieuBaoTri AS Phieu ON xk.Ref_PhieuBaoTri = Phieu.IOID
					      INNER JOIN ODanhSachThietBi AS dstb ON Phieu.Ref_MaThietBi = dstb.IOID
					      INNER JOIN OLoaiThietBi AS ltb ON dstb.Ref_LoaiThietBi = ltb.IOID
			              INNER JOIN ONhomThietBi AS ntb ON dstb.Ref_NhomThietBi = ntb.IOID
					      WHERE
                            (xk.NgayChungTu BETWEEN %1$s AND %2$s)
                            %3$s
                            GROUP BY dstb.IOID, dsxk.Ref_MaSP, dsxk.Ref_DonViTinh
                            ORDER BY dstb.MaThietBi'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $where
        );
        //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getVatTuByTrungTamChiPhi($start, $end, $costcenter = 0)
    {
        $where = '';
        $where .= $costcenter?sprintf(' AND IFNULL(chiphi.IOID, 0) = %1$d', $costcenter):'';

        $sql = sprintf('SELECT dsxk.*, sum(IFNULL(dsxk.SoLuong,0)) AS SoLuongVatTu,
                          chiphi.Ma AS MaNhom, chiphi.Ten AS TenNhom,
                          sum(IFNULL(dsxk.ThanhTien,0)) AS ThanhTien
                          FROM ODanhSachXuatKho AS dsxk
                          INNER JOIN OXuatKho AS xk ON xk.IFID_M506 = dsxk.IFID_M506
                          INNER JOIN OPhieuBaoTri AS Phieu ON xk.Ref_PhieuBaoTri = Phieu.IOID
					      INNER JOIN ODanhSachThietBi AS dstb ON Phieu.Ref_MaThietBi = dstb.IOID
					      INNER JOIN OTrungTamChiPhi AS chiphi ON dstb.Ref_TrungTamChiPhi = chiphi.IOID
					      WHERE
                            (xk.NgayChungTu BETWEEN %1$s AND %2$s)
                            %3$s
                            GROUP BY chiphi.IOID, dsxk.Ref_MaSP, dsxk.Ref_DonViTinh
                            ORDER BY chiphi.Ma'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $where
        );
        // echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInventoryOfItem($refItem, $refOUM = 0, $refStock = 0)
    {
        $where  = ($refOUM != 0)?sprintf(' AND IFNULL(TonKho.Ref_DonViTinh, 0) = %1$d', $refOUM):'';
        $where .= ($refStock != 0)?sprintf(' AND IFNULL(TonKho.Ref_Kho, 0) = %1$d', $refStock):'';

        $sql = sprintf('
            SELECT 
                SUM((IFNULL(TonKho.SoLuongHC, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 1))) AS TonKho
            FROM OKho AS TonKho
            INNER JOIN OSanPham AS MatHang ON TonKho.Ref_MaSP = MatHang.IOID
            INNER JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                AND TonKho.Ref_DonViTinh = DonViTinh.IOID
            WHERE IFNULL(TonKho.Ref_MaSP, 0) = %1$d %2$s
            GROUP BY TonKho.Ref_MaSP',
        $refItem, $where);

        // echo '<pre>'; print_r($sql); die;
        $dataSQL = $this->_o_DB->fetchOne($sql);

        return $dataSQL?$dataSQL->TonKho:0;
    }
}