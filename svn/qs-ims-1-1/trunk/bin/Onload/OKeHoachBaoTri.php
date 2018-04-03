<?php
class Qss_Bin_Onload_OKeHoachBaoTri extends Qss_Lib_Onload
{
	protected $_loaiBaoTri;
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
		$user        = Qss_Register::get('userinfo');
		$rights      = Qss_Lib_System::getFormRights('M837', $user->user_group_list);
		$refLoaiBT   = $this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID();
		$loaiBaoTri  = $this->_db->fetchOne(sprintf('select * from OPhanLoaiBaoTri where IOID = %1$d', $refLoaiBT));
		$this->_loaiBaoTri = ($loaiBaoTri)?$loaiBaoTri->LoaiBaoTri:'';

		// Lọc đơn vị bảo trì theo nhân viên (user đăng nhập của nhân viên) - @remove
		if($rights && !($rights & 2))
		{
			$dvbt = $this->_object->getFieldByCode('MaDVBT');
			$dvbt->arrFilters[] = sprintf('
                v.IFID_M125 in (
                    select IFID_M125 from ONhanVien
                    inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
                    where Ref_TenTruyCap = %1$d
                )',
			$user->user_id);
		}

		// Disable bo phan neu bo phan da chon nhieu
		$sql      = sprintf(
            'select count(1) as Total
            from OCongViecKeHoach
            where IFID_M837 = %1$d and ifnull(Ref_ViTri, 0) != 0 ',$this->_object->i_IFID
		);
		$dataSQL1 = $this->_db->fetchOne($sql);

		$sql      = sprintf(
            'select count(1) as Total
            from OVatTuKeHoach
            where IFID_M837 = %1$d and ifnull(Ref_ViTri, 0) != 0 ',$this->_object->i_IFID
		);
		$dataSQL2 = $this->_db->fetchOne($sql);
		$totalCom = @(int)$dataSQL1->Total + @(int)$dataSQL2->Total;

		if($totalCom)
		{
			$this->_object->getFieldByCode('BoPhan')->bReadOnly = true;
		}

		// Hien listbox khi loai la dinh ky va textbox khi khong phai dinh ky
		if($this->_loaiBaoTri == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE)
		{
			$this->_object->getFieldByCode('MoTa')->intInputType = 4;
			$this->_object->getFieldByCode('MoTa')->bEditStatus = true;
		}
		else
		{
			$this->_object->getFieldByCode('MoTa')->intInputType = 1;
			$this->_object->getFieldByCode('MoTa')->bEditStatus = true;

			// Yeu cau chu ky
			$this->_object->getFieldByCode('ChuKy')->bReadOnly = true;
			$this->_object->getFieldByCode('ChuKy')->bRequired = false;
			$this->_object->getFieldByCode('ChuKy')->setValue('');
			$this->_object->getFieldByCode('ChuKy')->setRefIOID(0);
		}
	}

	public function MoTa()
	{
		$makv     = $this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();
		$matb     = $this->_object->getFieldByCode('MaThietBi')->getRefIOID();
		$manophan = $this->_object->getFieldByCode('BoPhan')->getRefIOID();
		if($this->_object->getFieldByCode('MoTa')->intInputType != 1)
		{
			if($matb) // Cho chọn theo thiết bị
			{
				$this->_object->getFieldByCode('MoTa')->arrFilters[] = sprintf('
            v.IFID_M724 in (select IFID_M724 from OBaoTriDinhKy where IFNULL(Ref_MaThietBi, 0) = %1$d and ifnull(Ref_BoPhan,0) = %2$d)
            ', $matb, $manophan);
			}
			elseif($makv) // Cho chon theo khu vuc
			{
				$this->_object->getFieldByCode('MoTa')->arrFilters[] = sprintf('
            v.IFID_M724 in (select IFID_M724 from OBaoTriDinhKy where IFNULL(Ref_MaThietBi, 0) = 0 and ifnull(Ref_MaKhuVuc,0) = %1$d)
            ', $makv);
			}
			else // Chua co loc, khong cho chon
			{
				$this->_object->getFieldByCode('MoTa')->arrFilters[] = sprintf('v.IFID_M724 = 0');
			}
		}
	}

	public function ChuKy()
	{
		$tenCongViec = $this->_object->getFieldByCode('MoTa')->getRefIOID();

		$this->_object->getFieldByCode('ChuKy')->arrFilters[] = sprintf(' v.IFID_M724 in (select IFID_M724 from OBaoTriDinhKy where IOID = %1$d)', $tenCongViec);
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