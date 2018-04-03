<?php
class Qss_Bin_Notify_Mail_M759_Inserted extends Qss_Lib_Notify_Mail
{
	const TYPE = 'TRIGGER';
	
	const TITLE = 'Gửi email khi tạo phiếu đề xuất yêu cầu công việc';

	public function __doExecute()
	{
        $domain              = $_SERVER['HTTP_HOST'];
        $loaiBaoTriCoGuiMail = false;
        $ccMails             = array();
        $toMails             = array();

        if($this->_params->Ref_LoaiBaoTri) // Kiem tra xem co gui mail khi thay doi hay khong?
        {
            $mLoaiBaoTri = Qss_Model_Db::Table('OPhanLoaiBaoTri');
            $mLoaiBaoTri->where(sprintf(' IOID = %1$d ', $this->_params->Ref_LoaiBaoTri));
            $loaiBaoTri = $mLoaiBaoTri->fetchOne();

            if($loaiBaoTri && $loaiBaoTri->GuiEmailKhiTaoMoi)
            {
                $loaiBaoTriCoGuiMail = true;
            }
        }

        // Tien hanh gui mail khi loai bao tri cho phep gui mail
        if($loaiBaoTriCoGuiMail)
        {
            // Lay danh sach mail tu don vi de gui mail
            $mEmailList = Qss_Model_Db::Table('qsusers');
            $mEmailList->select('ODonViSanXuat.*, qsusers.EMail, qsusers.UserName');
            $mEmailList->join(' inner join ODanhSachNhanVien on qsusers.UID = ODanhSachNhanVien.Ref_TenTruyCap ');
            $mEmailList->join(' inner join ONhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV ');
            $mEmailList->join(' inner join ODonViSanXuat on ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125 ');
            $mEmailList->where(sprintf('ODonViSanXuat.IOID = %1$d', $this->_params->Ref_MaDVBT));
            $mEmailList->where($mEmailList->ifnullNumber('qsusers.isActive', 1));

            foreach ($mEmailList->fetchAll() as $item) // Gửi cho nhân viên trong đơn vị
            {
                if($item->EMail)
                {
                    if(isset($item->QuanLy) && $item->QuanLy)
                    {
                        $ccMails[$item->EMail] = $item->UserName;
                    }
                    else
                    {
                        $toMails[$item->EMail] = $item->UserName;
                    }
                }
            }

            foreach ($this->_maillist as $item) // Gửi cho nhân viên được gắn thêm trong phần gửi mail
            {
                if($item->EMail)
                {
                    $toMails[$item->EMail] = $item->UserName;
                }
            }

            $user = Qss_Register::get('userinfo');
			// unset($toMails[$user->user_email]);

            if(count($toMails) || count($ccMails))
            {
                $subject  = '[QS-M759] Cảnh báo ';
                $subject .= $this->_params->LoaiBaoTri;
                $subject .= $this->_params->MaThietBi?' thiết bị ':($this->_params->MaKhuVuc?' khu vực ':' ');
                $subject .= $this->_params->MaThietBi?$this->_params->MaThietBi:($this->_params->MaKhuVuc?$this->_params->MaKhuVuc:'');
                $subject .= ' ngày '.date('d-m-Y');

                $body = sprintf('Số phiếu : <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</a><br>',
                    $this->_params->SoPhieu,
                    $domain,
                    $this->_params->IFID_M759,
                    1);

                if($this->_params->MaThietBi)
                {
                    // Lay khu vuc hien tai
                    $mLocation = Qss_Model_Db::Table('OKhuVuc');
                    $mLocation->select('OKhuVuc.Ten AS TenKhuVuc');
                    $mLocation->join(' inner join ODanhSachThietBi on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc ');
                    $mLocation->where(sprintf(' ODanhSachThietBi.IOID = %1$d ', $this->_params->Ref_MaThietBi));
                    $khuvuc = $mLocation->fetchOne();

                    $body .= sprintf('Thiết bị: %1$s: %2$s<br>',$this->_params->MaThietBi,$this->_params->TenThietBi);

                    if($khuvuc)
                    {
                        $body .= sprintf('Khu vực: %1$s<br>',$khuvuc->TenKhuVuc);
                    }
                }
                elseif($this->_params->MaKhuVuc)
                {
                    $body .= sprintf('Khu vực: %1$s<br>',$this->_params->MaKhuVuc);
                }

                $body .= sprintf('Mô tả: %1$s<br><br>',$this->_params->MoTa);
                $body .= 'QS-IMS Mailer';
                $this->_sendMail($subject, $toMails, $body,$ccMails);
            }
        }
	}
}
?>