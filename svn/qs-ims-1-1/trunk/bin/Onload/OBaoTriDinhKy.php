<?php
class Qss_Bin_Onload_OBaoTriDinhKy extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();

        // Disable bo phan neu bo phan da chon nhieu trong vat tu hoac cong viec
        $sql      = sprintf('
            select count(1) as Total
            from OCongViecBT
            where IFID_M724 = %1$d and ifnull(Ref_ViTri, 0) != 0 '
        ,$this->_object->i_IFID);
		$dataSQL1 = $this->_db->fetchOne($sql);
        
        $sql      = sprintf('
            select count(1) as Total
            from OVatTu
            where IFID_M724 = %1$d and ifnull(Ref_ViTri, 0) != 0 '
        ,$this->_object->i_IFID);
		$dataSQL2 = $this->_db->fetchOne($sql);    
        
        $totalCom = @(int)$dataSQL1->Total + @(int)$dataSQL2->Total;
        
        if($totalCom)
        {
            $this->_object->getFieldByCode('BoPhan')->bReadOnly = true;
        }
	}

	public function DVBT()
	{
		$field = $this->_object->getFieldByCode('DVBT');
		$field->arrFilters[] = ' (v.BaoTri = 1)';
	}

	public function MaThietBi()
    {
		$makhuvuc = $this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();
		if($makhuvuc)
		{
			$this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID=%1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
				,$makhuvuc);
		}

        if(Qss_Lib_System::formSecure('M720'))
        {
            $user   = Qss_Register::get('userinfo');
            $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' (ifnull(v.Ref_MaKhuVuc,0)=0 or v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID 
						WHERE UID = %1$d))'
                ,$user->user_id);
        }
    }	
}