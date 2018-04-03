<?php
class Qss_Bin_Onload_ODanhSachThietBi extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();

		$activeSoYeuCau = Qss_Lib_System::fieldActive('ODanhSachThietBi', 'SoYeuCau');
        $activeDuAnMua  = Qss_Lib_System::fieldActive('ODanhSachThietBi', 'DuAnMua');
        $activeLoai     = Qss_Lib_System::fieldActive('ODanhSachThietBi', 'Loai');

        $this->_object->getFieldByCode('NgayNgung')->bRequired = false;
        $this->_object->getFieldByCode('NgayNgung')->bReadOnly = true;

		$trangThai = $this->_object->getFieldByCode('TrangThai')->getValue();

		if(in_array($trangThai, Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP))
		{
            $this->_object->getFieldByCode('NgayNgung')->bReadOnly = false;
			$this->_object->getFieldByCode('NgayNgung')->bRequired = true;
		}
        else
        {
            $this->_object->getFieldByCode('NgayNgung')->setValue('');
        }

        // Hiển thị required theo loại, với mua thuê thì yêu cầu dự án mua và số yêu cầu
        if($activeLoai) {
            $loai = $this->_object->getFieldByCode('Loai')->getValue();

            if($activeSoYeuCau) {
                if($loai == 1 || $loai == 2) {
                    $this->_object->getFieldByCode('SoYeuCau')->bRequired = true;
                }
                else {
                    $this->_object->getFieldByCode('SoYeuCau')->bRequired = false;
                }
            }

            if($activeDuAnMua) {
                if($loai == 1 || $loai == 2) {
                    $this->_object->getFieldByCode('DuAnMua')->bRequired = true;
                }
                else {
                    $this->_object->getFieldByCode('DuAnMua')->bRequired = false;
                }
            }
        }
	}
	public function MaKhuVuc()
	{
		$user = Qss_Register::get('userinfo');
		$this->_object->getFieldByCode('MaKhuVuc')->arrFilters[] = sprintf(' 
						v.IFID_M720 in(select IFID  from qsrecordrights  
			WHERE UID = %1$d)',$user->user_id);
	}

    /**
     * Lọc số yêu cầu theo dự án mua, loại thiết bị có trong yêu cầu, tình trạng đã duyệt
     */
	public function SoYeuCau() {
        $activeSoYeuCau = Qss_Lib_System::fieldActive('ODanhSachThietBi', 'SoYeuCau');
        $activeDuAnMua  = Qss_Lib_System::fieldActive('ODanhSachThietBi', 'DuAnMua');

	    if($activeSoYeuCau && $activeDuAnMua){
            $DuAnMua     = $this->_object->getFieldByCode('DuAnMua')->getRefIOID();
            $LoaiThietBi = $this->_object->getFieldByCode('LoaiThietBi')->getRefIOID();

            $this->_object->getFieldByCode('SoYeuCau')->arrFilters[] = sprintf('
                v.IOID IN (
                    SELECT OYeuCauTrangThietBiVatTu.IOID
                    FROM OYeuCauTrangThietBiVatTu
                    INNER JOIN qsiforms ON OYeuCauTrangThietBiVatTu.IFID_M751 = qsiforms.IFID
                    INNER JOIN OYeuCauTrangThietBi ON OYeuCauTrangThietBiVatTu.IFID_M751 = OYeuCauTrangThietBi.IFID_M751
                    WHERE IFNULL(Ref_DuAn, 0) = %1$d 
                        AND qsiforms.Status = 3 -- approved
                        AND OYeuCauTrangThietBi.Ref_LoaiThietBi = %2$d
                    GROUP BY OYeuCauTrangThietBiVatTu.IFID_M751
                )
            ', $DuAnMua, $LoaiThietBi);
        }
    }

    public function DuAnMua() {
        if(Qss_Lib_System::formSecure('M803') && Qss_Lib_System::fieldActive('ODanhSachThietBi', 'SoYeuCau')
            && Qss_Lib_System::fieldActive('ODanhSachThietBi', 'DuAnMua'))
        {
            $user   = Qss_Register::get('userinfo');
            $this->_object->getFieldByCode('DuAnMua')->arrFilters[] = sprintf(' 
                v.IFID_M803 in (
                    SELECT IFID 
                    FROM qsrecordrights 
                    WHERE FormCode = "M803" and UID = %1$d
                )'
                ,$user->user_id);
        }
    }

}