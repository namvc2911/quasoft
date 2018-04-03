<?php
class Qss_Bin_Onload_OHoaDonMuaHang extends Qss_Lib_Onload
{
    public function SoDonHang()
    {
        $partnerIOID = $this->_object->getFieldByCode('NCC')->intRefIOID;
        
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
}