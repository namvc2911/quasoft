<?php
class Qss_Bin_Filter_ODSNhuCauMuaHang extends Qss_Lib_Filter
{
    public function MaSP()
    {
        $soyeucau = @$this->_params['soyeucau'];

        if($soyeucau)
        {
            $sql = sprintf('
                SELECT chitiet.Ref_MaSP AS ItemIOID
                FROM ONhuCauVatTu AS yeucaumuahang
                INNER JOIN ODSNhuCauVatTu AS chitiet ON yeucaumuahang.IFID_M709 = chitiet.IFID_M709
                WHERE yeucaumuahang.SoPhieu = %1$s
            ', $this->_db->quote($soyeucau));

            $data  = $this->_db->fetchAll($sql);
            $items = array();

            foreach($data as $dat)
            {
                $items[] = $dat->ItemIOID;
            }

            if(count($items))
            {
                $retval = sprintf(' and v.Ref_MaSP in (%1$s)', implode(',', $items));
                return $retval;
            }
        }
    }
}