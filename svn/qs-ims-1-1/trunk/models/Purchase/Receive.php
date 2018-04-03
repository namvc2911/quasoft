<?php 

class Qss_Model_Purchase_Receive extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
        $this->_user = Qss_Register::get('userinfo');
    }
    
    public function getReceiveByPO($receiveIFID)
    {
        $sql = sprintf('
            SELECT 
                dsdonhang.*
                ,sum( ifnull(dsnhanhang.SoLuong, 0) ) AS SoLuongDaNhan
            FROM ONhanHang AS nhanhang
            INNER JOIN ODonMuaHang AS donhang ON nhanhang.Ref_PO = donhang.IOID
            INNER JOIN ODSDonMuaHang AS dsdonhang ON donhang.IFID_M401 = dsdonhang.IFID_M401
            LEFT JOIN
            (
                SELECT dsnhanhang2.*
                FROM ONhanHang AS nhanhang2
                INNER JOIN ODanhSachNhanHang AS dsnhanhang2 ON nhanhang2.IFID_M408 = dsnhanhang2.IFID_M408
                INNER JOIN qsiforms ON nhanhang2.IFID_M408 = qsiforms.IFID
            ) AS dsnhanhang ON 
                dsdonhang.Ref_MaSP = dsnhanhang.Ref_MaMatHang
                AND ifnull(dsdonhang.Ref_DonViTinh, 0) = ifnull(dsnhanhang.Ref_DonViTinh, 0)
                AND ifnull(dsdonhang.Ref_ThuocTinh, 0) = ifnull(dsnhanhang.Ref_ThuocTinh, 0)
                AND ifnull(dsdonhang.Ref_SoYeuCau, 0)  = ifnull(dsnhanhang.Ref_SoYeuCau, 0)
            WHERE nhanhang.IFID_M408 = %1$d
            GROUP BY dsdonhang.IOID
        ', $receiveIFID);
        
        // echo '<pre>'; print_r($sql); die; 
        return $this->_o_DB->fetchAll($sql);
    }
}