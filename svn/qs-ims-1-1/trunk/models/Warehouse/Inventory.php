<?php

class Qss_Model_Warehouse_Inventory extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
    
    /**
     * Lay so luong ton kho tu bang ton kho voi so luong  da chuyen ve dvt co so
     * @param type $itemIOID
     * @param type $attrIOID
     * @param type $uomIOID
     * @param type $stockIOID
     */
    public function getSimpleInventory($itemIOID = 0, $attrIOID = 0, $stockIOID = 0)
    {

        $sql = 'SELECT';
        $sql .= ' sanpham.`MaSanPham` AS ItemCode';
        $sql .= ' , sanpham.`TenSanPham` AS ItemName';
        $sql .= ' , sanpham.`IOID` AS RefItem';
        $sql .= ' , sanpham.`DonViTinh` AS UOM';
        $sql .= ' , SUM(ifnull(kho.`SoLuongHC`, 0) * ifnull(donvitinh.`HeSoQuyDoi`, 0)) AS Qty';
        $sql .= ' , '.(($stockIOID)?'IFNULL(HanMuc.SoLuongThoiThieu,0)':'IFNULL(sanpham.SLToiThieu, 0)').' AS Min '; // Han muc luu tru
        $sql .= ' FROM OSanPham AS sanpham ';
        $sql .= ' LEFT JOIN OKho AS kho ON kho.`Ref_MaSP` = sanpham.`IOID`  '.($stockIOID?sprintf(' AND kho.IOID = %1$d ', $stockIOID):'');
        $sql .= ' LEFT JOIN ODonViTinhSP AS donvitinh ON sanpham.`IFID_M113` = donvitinh.`IFID_M113` AND kho.`Ref_DonViTinh` = donvitinh.`IOID`';
        $sql .= ($stockIOID)?sprintf(' LEFT JOIN OHanMucLuuTru AS HanMuc ON sanpham.`IFID_M113` = HanMuc.`IFID_M113` AND HanMuc.Ref_MaKho = %1$d', $stockIOID):'';
        $sql .= ' WHERE 1=1';
        $sql .= $itemIOID?sprintf(' AND sanpham.IOID = %1$d ', $itemIOID):'';
        $sql .= $attrIOID?sprintf(' AND kho.Ref_ThuocTinh = %1$d ', $attrIOID):'';
        $sql .= ' GROUP BY sanpham.IOID, kho.Ref_ThuocTinh';

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }  
    
    /**
     * 
     * @param type $itemIOID
     * @param type $attrIOID
     * @param type $qty
     * @param type $stockIOID
     * @return boolean
     */
    public function checkEnoughInventory($itemIOID, $attrIOID, $qty, $stockIOID = 0)
    {
        if(!$itemIOID) return false;
        $item = $this->getSimpleInventory($itemIOID , $attrIOID , $stockIOID );
        return count($item)?( (($item->{0}->Qty) > $qty)? true: false):false;
    }
    
    /**
     * Lay ton kho  cua vat tu phieu bao tri
     * @param type $IFID_M759
     */
    public function getInventoryForItemsOfMaintainOrder($IFID_M759)
    {
        $sql = sprintf(' 
            SELECT 
                vattubaotri.*
                , vattubaotri.Qty AS InsQty
                , ifnull(tonkho.Qty, 0) AS InvQty
                , CASE WHEN vattubaotri.Qty < tonkho.Qty THEN 1 ELSE 0 END AS `Enough`
            FROM
            /* VAT TU */
            (
                SELECT 
                    sanpham.`MaSanPham` AS ItemCode,
                    sanpham.`TenSanPham` AS ItemName,  
                    vattu.ThuocTinh AS Attr,
                    sanpham.`IOID` AS RefItem,
                    ifnull(vattu.`Ref_ThuocTinh`, 0) AS RefAttr,
                    (ifnull(vattu.`SoLuong`, 0) * ifnull(donvitinh.`HeSoQuyDoi`, 0)) AS Qty
                FROM OVatTuPBT AS vattu
                INNER JOIN OSanPham AS sanpham ON vattu.`Ref_MaVatTu` = sanpham.`IOID`
                INNER JOIN ODonViTinhSP AS donvitinh ON sanpham.`IFID_M113` = donvitinh.`IFID_M113`
                AND vattu.`Ref_DonViTinh` = donvitinh.`IOID`                
                WHERE vattu.IFID_M759 = %1$d
            ) AS vattubaotri
            LEFT JOIN 
            /* KHO */
            (
            SELECT 
                sanpham.`MaSanPham` AS ItemCode,
                sanpham.`TenSanPham` AS ItemName,
                kho.ThuocTinh AS Attr,
                sanpham.`IOID` AS RefItem,
                ifnull(kho.`Ref_ThuocTinh`,0) AS RefAttr,
                (ifnull(kho.`SoLuongHC`, 0) * ifnull(donvitinh.`HeSoQuyDoi`, 0)) AS Qty
            FROM OKho AS kho
            INNER JOIN OSanPham AS sanpham ON kho.`Ref_MaSP` = sanpham.`IOID`
            INNER JOIN ODonViTinhSP AS donvitinh ON sanpham.`IFID_M113` = donvitinh.`IFID_M113`
                AND kho.`Ref_DonViTinh` = donvitinh.`IOID`            
            WHERE kho.`Ref_MaSP` IN (
                SELECT `Ref_MaVatTu` FROM OVatTuPBT  WHERE IFID_M759 = %1$d
            )
            GROUP BY kho.`Ref_MaSP`, kho.`Ref_ThuocTinh`
            ) AS tonkho
            ON vattubaotri.RefItem = tonkho.RefItem AND vattubaotri.RefAttr = tonkho.RefAttr
        ', $IFID_M759);
        return $this->_o_DB->fetchAll($sql);        
    }
}