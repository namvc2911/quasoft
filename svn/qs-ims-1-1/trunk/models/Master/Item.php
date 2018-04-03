<?php
class Qss_Model_Master_Item extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function getItemGroups()
    {
        $sql = sprintf('
            SELECT * FROM ONhomSanPham ORDER BY TenNhom
        ');
        return $this->_o_DB->fetchAll($sql);
    }

    public function countItems()
    {
        $sql     = sprintf('select count(1) AS Total from OSanPham');
        $dataSql =  $this->_o_DB->fetchOne($sql);
        return $dataSql?$dataSql->Total:0;
    }

    public function getItems()
    {
        $sql = sprintf('
            select OSanPham.*, ODonViTinhSP.IOID AS Ref_DonViTinh, SoLuongHC
            from OSanPham
            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113
                AND OSanPham.Ref_DonViTinh = ODonViTinhSP.Ref_DonViTinh
            left join (select OKho.Ref_MaSP, sum(SoLuongHC) as SoLuongHC from OKho 
            inner join ODanhSachKho on ODanhSachKho.IOID = OKho.Ref_Kho 
            where ODanhSachKho.LoaiKho = "VATTU"
            group by OKho.Ref_MaSP) as T on T.Ref_MaSP = OSanPham.IOID
            order by MaSanPham
        ');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getItemsLimit($page, $perpage)
    {
        $offset = ($page - 1) * $perpage;
        $limit  = $perpage;

        $sql = sprintf('
            select OSanPham.*, ODonViTinhSP.IOID AS Ref_DonViTinh
            from OSanPham
            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND OSanPham.Ref_DonViTinh = ODonViTinhSP.Ref_DonViTinh
            order by MaSanPham limit %1$d, %2$d', $offset, $limit);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getItemsByGroup($groupIOID)
    {
        $sql = sprintf('select * from OSanPham where Ref_NhomSP = %1$d order by MaSanPham', $groupIOID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getItemByIOID($ioid)
    {
        $sql = sprintf('select * from OSanPham where IOID = %1$d', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getItemByCodeOrName($search)
    {
        if($search)
        {
            $search = "%{$search}%";

            $sql = sprintf('
                select * from OSanPham where MaSanPham like %1$s OR TenSanPham Like %1$s ORDER BY MaSanPham LIMIT 100
            ',$this->_o_DB->quote($search));
        }
        else
        {
            $sql = sprintf(' select * from OSanPham ORDER BY MaSanPham  LIMIT 100');
        }

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipTypeUseList($itemIOID)
    {
        $sql = sprintf('
            SELECT OLoaiThietBi.*
                , IFNULL(OVatTuThayTheTheoLoai.IOID, 0) AS ExIOID
            FROM OLoaiThietBi
            LEFT JOIN OVatTuThayTheTheoLoai ON 
                OLoaiThietBi.IFID_M770 = IFNULL(OVatTuThayTheTheoLoai.IFID_M770, 0) 
                AND OVatTuThayTheTheoLoai.Ref_MaSanPham = %1$d             
            GROUP BY OLoaiThietBi.IFID_M770
        ', $itemIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function deleteItemsForEquipType($ifid, $ioid)
    {
        $sql = sprintf('
            DELETE
            FROM OVatTuThayTheTheoLoai
            WHERE IFID_M770 = %1$d AND IOID = %2$d
        ', $ifid, $ioid);
        // echo $sql; die;
        $this->_o_DB->execute($sql);
    }

    public function getInventoryOfItems()
    {
        $sql = sprintf('
            SELECT 
                OSanPham.IOID
                , OSanPham.MaSanPham
                , OSanPham.TenSanPham
                , ODonViTinhSP.IOID AS Ref_DonViTinh
                , ODonViTinhSP.DonViTinh
                , IFNULL(OKho.SoLuongHC, 0) AS SoLuongHC
            FROM OSanPham
            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND OSanPham.Ref_DonViTinh = ODonViTinhSP.Ref_DonViTinh
            LEFT JOIN (
                SELECT SUM( IF( IFNULL(ODanhSachKho.IOID, 0) != 0, IFNULL(OKho.SoLuongHC,0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0), 0) ) AS  SoLuongHC, OSanPham.IOID AS Ref_MaSanPham
				FROM OKho
                INNER JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID AND ODanhSachKho.LoaiKho = %1$s
				INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP 
				INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND ODonViTinhSP.IOID = OKho.Ref_DonViTinh
				GROUP BY OKho.Ref_MaSP            
            ) AS OKho ON OSanPham.IOID = OKho.Ref_MaSanPham
        ', $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));


        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $sql .= sprintf('WHERE IFNULL(MaTam, 0) = 0 ');
        }

//        if($minimumByStock != 0)
//        {
//            $sql = sprintf('
//            select
//                OSanPham.IOID
//                , OSanPham.MaSanPham
//                , OSanPham.TenSanPham
//                , ODonViTinhSP.IOID AS Ref_DonViTinh
//                , ODonViTinhSP.DonViTinh
//                , IFNULL(HanMuc.SoLuongThoiThieu, 0) AS SLToiThieu
//                , IFNULL(OKho.SoLuongHC, 0) AS SoLuongHC
//            from OSanPham
//            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113
//                AND OSanPham.Ref_DonViTinh = ODonViTinhSP.Ref_DonViTinh
//            LEFT JOIN (
//                SELECT SUM( IF( IFNULL(ODanhSachKho.IOID, 0) != 0, IFNULL(OKho.SoLuongHC,0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0), 0) ) AS  SoLuongHC, OSanPham.IOID
//				FROM OKho
//                INNER JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID AND ODanhSachKho.LoaiKho = %1$s
//				INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP
//				INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND ODonViTinhSP.IOID = OKho.Ref_DonViTinh
//				WHERE IFNULL(OKho.Ref_Kho, 0) = %2$d
//				GROUP BY OKho.Ref_MaSP
//            ) AS OKho ON OSanPham.IOID = OKho.IOID
//            LEFT JOIN (SELECT * FROM OHanMucLuuTru  WHERE IFNULL(Ref_MaKho, 0) = %2$d) AS HanMuc ON
//                OSanPham.IFID_M113 = HanMuc.IFID_M113
//                AND HanMuc.Ref_MaKho = OKho.IOID
//            ORDER BY OSanPham.MaSanPham
//        ', $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL), $minimumByStock);
//        }
//        else
//        {
//            $sql = sprintf('
//            select
//                OSanPham.IOID
//                , OSanPham.MaSanPham
//                , OSanPham.TenSanPham
//                , ODonViTinhSP.IOID AS Ref_DonViTinh
//                , ODonViTinhSP.DonViTinh
//                , IFNULL(OSanPham.SLToiThieu, 0) AS SLToiThieu
//                , SUM( IF( IFNULL(ODanhSachKho.IOID, 0) != 0, IFNULL(OKho.SoLuongHC,0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0), 0) ) AS  SoLuongHC
//            from OSanPham
//            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113
//                AND OSanPham.Ref_DonViTinh = ODonViTinhSP.Ref_DonViTinh
//            LEFT JOIN OKho ON OSanPham.IOID = OKho.Ref_MaSP AND ODonViTinhSP.IOID = OKho.Ref_DonViTinh
//            LEFT JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID AND ODanhSachKho.LoaiKho = %1$s
//            GROUP BY OKho.Ref_MaSP
//            ORDER BY OSanPham.MaSanPham
//        ', $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));
//        }


        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    public function getUOMByIOID($ioid)
    {
    	$sql = sprintf('
            SELECT 
                ODonViTinhSP.*
            FROM ODonViTinhSP
            INNER JOIN OSanPham ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 
            Where OSanPham.IOID = %1$d'
    		,$ioid);
    	return $this->_o_DB->fetchAll($sql);
    }   
}