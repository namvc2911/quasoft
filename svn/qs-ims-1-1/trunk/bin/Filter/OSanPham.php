<?php
class Qss_Bin_Filter_OSanPham extends Qss_Lib_Filter {
    public function getWhere() {
        $retval = '';

        $nhomSanPham = (int) @$this->_params['nhomsanpham'];

        if($nhomSanPham) {
            $retval = sprintf('
                AND IFNULL( v.Ref_NhomSP, 0) = %1$d
            ', $nhomSanPham);
        }

        return $retval;
    }
}