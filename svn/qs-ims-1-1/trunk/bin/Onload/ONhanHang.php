<?php
class Qss_Bin_Onload_ONhanHang extends Qss_Lib_Onload
{
    public function DoiTac()
    {
        $PO = $this->_object->getFieldByCode('PO')->getRefIOID();
		if($PO)
		{
	        $this->_object->getFieldByCode('DoiTac')->arrFilters[] = sprintf('
	            v.IOID in
	            (
	                SELECT ODoiTac.IOID
	                FROM ODonMuaHang
	                INNER JOIN ODoiTac ON ODonMuaHang.Ref_MaNCC = ODoiTac.IOID
	                WHERE ODonMuaHang.IOID = %1$d
	            )
	        ', $PO);
		}
    }

    public function PO()
    {
    	$DT = $this->_object->getFieldByCode('DoiTac')->getRefIOID();
        $this->_object->getFieldByCode('PO')->arrFilters[] = sprintf(' 
            v.IOID in 
            (
                SELECT ifnull(ODonMuaHang.IOID, 0) AS IOID
                FROM ODonMuaHang 
                INNER JOIN qsiforms ON ODonMuaHang.IFID_M401 = qsiforms.IFID
                WHERE qsiforms.Status = 2 and Ref_MaNCC = %1$d
            )
        ',$DT);
    }    
}