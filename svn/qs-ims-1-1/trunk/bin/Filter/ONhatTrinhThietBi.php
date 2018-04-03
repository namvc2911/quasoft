<?php
class Qss_Bin_Filter_ONhatTrinhThietBi extends Qss_Lib_Filter
{
	public function getWhere()
	{
		$retval = '';
		$makhuvuc = (int) @$this->_params['makhuvuc'];
		if($makhuvuc)
		{
			$retval .= sprintf(' and v.Ref_MaTB in (SELECT ODanhSachThietBi.IOID FROM ODanhSachThietBi
						inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID = %1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
					,$makhuvuc);
		}
		$ca = (int) @$this->_params['nameShift'];
		if($ca)
		{
			$retval .= sprintf(' and v.Ref_Ca =%1$d'
					,$ca);
		}
		
		$date        = @$this->_params['nameDate']?Qss_Lib_Date::displaytomysql(@$this->_params['nameDate']):'';

        if($date)
        {
            $retval .= sprintf(' AND (v.Ngay = %1$s)', $this->_db->quote($date));
        }
        return $retval;
	}
	
}