<?php
class Qss_Bin_Onload_OLoaiBaoTriPhuThuoc extends Qss_Lib_Onload
{
    public function TenLoai()
    {
        $field = $this->_object->getFieldByCode('TenLoai');
        $field->arrFilters[] = ' (v.LoaiBaoTri = \'P\' or v.LoaiBaoTri = \'CA\') ';
    }
}