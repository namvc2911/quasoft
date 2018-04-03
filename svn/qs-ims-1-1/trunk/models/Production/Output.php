<?php

/**
 * @author: ThinhTuan
 * @component: Production
 */
class Qss_Model_Production_Output extends Qss_Model_Abstract {

    public function __construct() {
        parent::__construct();
    }
    
    /// * Function: mergeLinesWithShifts - trong day chuyen voi ca
    public function getProductQuantity($malenhsx,$condoan,$donvithuchien)
    {
        $sql = sprintf('select * from OThongKeSanLuong
					where MaLenhSX = %1$s and Ref_CongDoan = %2$d and Ref_DonViThucHien = %3$d',
        		$this->_o_DB->quote($malenhsx)
        		, $condoan
        		, $donvithuchien);
        return $this->_o_DB->fetchOne($malenhsx,$condoan,$donvithuchien);
    }
 	public function getByProductQuantity($ifid)
    {
        $sql = sprintf('select * from OSanLuong as phupham
        			inner join OThongKeSanLuong as chinhpham on chinhpham.IFID_M717 = phupham.IFID_M717 
					where chinhpham.MaLenhSX = %1$s and chinhpham.Ref_CongDoan = %2$d and chinhpham.Ref_DonViThucHien = %3$d'
        		, $this->_o_DB->quote($malenhsx)
        		, $condoan
        		, $donvithuchien);
        return $this->_o_DB->fetchOne($sql);
    }
	public function getDefectProductQuantity($malenhsx,$condoan,$donvithuchien)
    {
        $sql = sprintf('select * from OSanPhamLoi as phupham
        			inner join OThongKeSanLuong as chinhpham on chinhpham.IFID_M717 = phupham.IFID_M717 
					where chinhpham.MaLenhSX = %1$s and chinhpham.Ref_CongDoan = %2$d and chinhpham.Ref_DonViThucHien = %3$d'
        		, $this->_o_DB->quote($malenhsx)
        		, $condoan
        		, $donvithuchien);
        return $this->_o_DB->fetchOne($sql);
    }
	
}

?>