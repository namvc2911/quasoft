<?php
class Qss_Bin_Calculate_OVatTuPBT_Serial extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
    	$ret = 0;
        $vatTu = (int)$this->_object->getFieldByCode('MaVatTu')->intRefIOID;
        $viTri = (int)$this->_object->getFieldByCode('ViTri')->intRefIOID;
        if($vatTu && $viTri)
        {
             $sql = sprintf('SELECT * FROM OCauTrucThietBi WHERE IOID = %1$d and Ref_MaSP = %2$d'
             		, $viTri
             		, $vatTu);
        	$dat = $this->_db->fetchOne($sql);
            if($dat)
            {
                $ret = (int) $dat->Ref_Serial;
            }
        }
        return $ret;
   }
}
?>