<?php
class Qss_Bin_Notify_Mail_M078_Inserted extends Qss_Lib_Notify_Mail
{
	const TYPE = 'TRIGGER';
	
	const TITLE = 'Gửi email khi đăng ký làm thêm';

	public function __doExecute()
	{
        $domain              = $_SERVER['HTTP_HOST'];
        $loaiBaoTriCoGuiMail = false;
        $ccMails             = array();
        $toMails             = array();

        if($this->_params->EMail) // Kiem tra xem có phải đk online hoặc cần gửi email ko
        {
            // Lay danh sach mail tu don vi de gui mail
            $mEmailList = Qss_Model_Db::Table('ODanhSachNhanVien');
            $mEmailList->select('*');
            $mEmailList->join(' inner join qsusers on qsusers.UID = ODanhSachNhanVien.Ref_TenTruyCap ');
            $mEmailList->where(sprintf('(ODanhSachNhanVien.IOID in (select Ref_NguoiQuanLy1 
            		from ODanhSachNhanVien where IOID = %1$d) or ODanhSachNhanVien.IOID in (select Ref_NguoiQuanLy2 
            		from ODanhSachNhanVien where IOID = %1$d))'
            		,$this->_params->Ref_MaNhanVien));
            $mEmailList->where($mEmailList->ifnullNumber('qsusers.isActive', 1));
            $mails = $mEmailList->fetchAll();
            foreach ($mails as $item) // Gửi cho nhân viên trong đơn vị
            {
                if($this->_params->EMail)
                {
                  	$subject  = '[QS-M078] Đăng ký làm thêm ';
	                $subject .= $this->_params->TenNhanVien;
	                $subject .= ' ('.$this->_params->MaNhanVien.')';
	                $subject .= ' ngày '.date('d-m-Y');
	
	                $body = sprintf('<a href="http://%1$s/user/form/edit?ifid=%2$d&deptid=%3$d">Xem chi tiết</a><br>',
	                    $domain,
	                    $this->_params->IFID_M078,
	                    1);
					$body .= sprintf('%1$s đăng ký làm thêm "%2$s" <br>
									Ngày: %3$s<br>
									Số giờ đăng ký: %5$s<br>
									Lý do: %6$s<br>'
								,$this->_params->TenNhanVien
								,$this->_params->HinhThucTangCa
								,0
								,$this->_params->NgayKetThuc
								,$this->_params->GioDangKy
								,$this->_params->LyDoTangCa);
	                $body .= sprintf('%1$s <br><br>',$this->_params->MoTa);
	                $body .= 'QS-IMS Mailer';
	                $this->_sendMail($subject, array($item->EMail=>$item->UserName), $body,$ccMails);
                }
            }
        }
	}
}
?>