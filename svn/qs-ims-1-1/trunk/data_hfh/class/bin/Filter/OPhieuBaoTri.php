<?php
class Qss_Bin_Filter_OPhieuBaoTri extends Qss_Lib_Filter
{
	public function getJoin()
	{
		return '';
	}
	public function getWhere()
	{
        $makhuvuc = (int) @$this->_params['makhuvuc'];
        $retval   = '';

        if($makhuvuc)
        {
            $retval .= sprintf(' and v.Ref_MaThietBi in (SELECT ODanhSachThietBi.IOID FROM ODanhSachThietBi
						inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID = %1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
                ,$makhuvuc);
            //echo $retval;die;
        }

		return $retval;
	}
}