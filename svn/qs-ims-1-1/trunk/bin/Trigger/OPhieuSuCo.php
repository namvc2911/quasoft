<?php

/**
 * @author: ThinhTuan
 */
class Qss_Bin_Trigger_OPhieuSuCo extends Qss_Lib_Trigger
{
    public function checkDateRange($object)
    {
        $this->_checkDateRange($object->getFieldByCode('NgayBatDau')->getValue(), $object->getFieldByCode('Ngay')->getValue(), $this->_translate(1));
    }
    
	public function onInserted($object)
	{
		//send email if breakdown
		/*parent::init();
		$sql = sprintf('select OKhuVuc.Ten AS TenKhuVuc from ODanhSachThietBi
					inner join OKhuVuc on ODanhSachThietBi.Ref_MaKhuVuc = OKhuVuc.IOID
					where ODanhSachThietBi.IOID = %1$d',
			$this->_params->Ref_MaThietBi);
		$khuvuc = $this->_db->fetchOne($sql);
		$domain = $_SERVER['HTTP_HOST'];
		$sql = sprintf('select ONhanVien.QuanLy, qsusers.EMail,qsusers.UserName
						from ODonViSanXuat 
						inner join ONhanVien on ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
						inner join qsusers on qsusers.UID =  ODanhSachNhanVien.Ref_TenTruyCap
						where qsusers.isActive = 1
						order by ODonViSanXuat.IFID_M125',
					$this->_params->Ref_MaDVBT);//ODonViSanXuat.IOID = %1$d and 
		$wcs = $this->_db->fetchAll($sql);
		$arrToDonVi = array();
		$arrCCDonVi = array();
		foreach ($wcs as $item)
		{
			if($item->QuanLy)
			{
				$arrCCDonVi[$item->EMail] = $item->UserName;
			}
			else
			{
				$arrToDonVi[$item->EMail] = $item->UserName;
			}
		}
		if(count($arrToDonVi) || count($arrCCDonVi))
		{
			$subject = 'Cảnh báo sự cố thiết bị '. $this->_params->MaThietBi . ' ngày ' . date('d-m-Y');
			$body = sprintf('Số phiếu : <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</a><br>',
					$this->_params->SoPhieu,
					$domain, 
					 $this->_params->IFID_M707,
					 1);
			$body .= sprintf('Thiết bị: %1$s: %2$s<br>',$this->_params->MaThietBi,$this->_params->TenThietBi);
			$body .= sprintf('Khu vực: %1$s<br>',$khuvuc->TenKhuVuc);
			$body .= sprintf('Mô tả: %1$s<br><br>',$this->_params->MoTa);
			$body .= 'QS-IMS Mailer';
			$this->_sendMail($subject, $arrToDonVi, $body,$arrCCDonVi);	
		}*/
	}
	
	public function onUpdate($object)
	{
		parent::init();
        //$this->checkDateRange($object);
		//$this->checkDateInTimeOfWorkOrder($object);
	}

	public function onInsert($object)
	{
		parent::init();
        //$this->checkDateRange($object);
		//$this->checkDateInTimeOfWorkOrder($object);
	}



}
