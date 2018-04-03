<?php
class Qss_Bin_Onload_OThanhToan extends Qss_Lib_Onload
{
	public function __doExecute()
	{
		 parent::__doExecute();
		 $loaitien = $this->_object->getFieldByCode('LoaiTien')->getValue();
		 if($loaitien)
		 {
		 	$this->_object->getFieldByCode('SoTienDaTT')->setRefIOID($loaitien);
		 }
	}
    public function SoDonHang()
    {
        $partnerIOID = $this->_object->getFieldByCode('MaNCC')->intRefIOID;
        
        $this->_object->getFieldByCode('SoDonHang')->arrFilters[] = sprintf(' 
            v.IOID in 
            (
                SELECT ifnull(ODonMuaHang.IOID, 0) AS IOID
                FROM ODonMuaHang 
                INNER JOIN qsiforms ON ODonMuaHang.IFID_M401 = qsiforms.IFID
                WHERE qsiforms.Status = 2 AND Ref_MaNCC = %1$d
            )
        ', $partnerIOID);
    }    
    
    public function SoHoaDon()
    {
        $poIOID = $this->_object->getFieldByCode('SoDonHang')->intRefIOID;
    
        $this->_object->getFieldByCode('SoHoaDon')->arrFilters[] = sprintf('
            v.IOID in
            (
                SELECT ifnull(OHoaDonMuaHang.IOID, 0) AS IOID
                FROM OHoaDonMuaHang
                WHERE OHoaDonMuaHang.Ref_SoDonHang = %1$d
            )
        ', $poIOID);
    }    
}