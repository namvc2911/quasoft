<?php
class Qss_Bin_Notify_Mail_M077_Inserted extends Qss_Lib_Notify_Mail
{
	const TYPE = 'TRIGGER';
	
	const TITLE = 'Gửi email khi yêu cầu nghỉ phép';

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
            $model = Qss_Model_Db::Table('OPhanLoaiNghi');
            $model->where(sprintf('IOID=%1$d',$this->_params->Ref_LoaiNgayNghi));
            $dataSQL = $model->fetchOne();
            $mails = $mEmailList->fetchAll();
            foreach ($mails as $item) // Gửi cho nhân viên trong đơn vị
            {
                if($this->_params->EMail)
                {
                  	$subject  = '[QS-M077] Yêu cầu nghỉ phép ';
	                $subject .= $this->_params->LoaiNgayNghi . ': ';
	                $subject .= $this->_params->TenNhanVien;
	                $subject .= ' ('.$this->_params->MaNhanVien.')';
	                $subject .= ' ngày '.date('d-m-Y');
	
	                $body = sprintf('<a href="http://%1$s/user/form/edit?ifid=%2$d&deptid=%3$d">Xem chi tiết</a><br>',
	                    $domain,
	                    $this->_params->IFID_M077,
	                    1);
					$body .= sprintf('%1$s yêu cầu nghỉ "%2$s" <br>
									Từ ngày: %3$s<br>
									Đến ngày: %4$s<br>
									Số giờ nghỉ: %5$s<br>
									Lý do: %6$s<br>'
								,$this->_params->TenNhanVien
								,$this->_params->LoaiNgayNghi
								,$this->_params->NgayBatDau
								,$this->_params->NgayKetThuc
								,$this->_params->SoGioNghi
								,$this->_params->MoTa);
	                $body .= sprintf('%1$s <br><br>',$this->_params->MoTa);
	                //check xem loại nghỉ không duyệt tự động thì thêm cái duyệt qua email
	                if(!$dataSQL->TuDongDuyet)
	                {
	                	//Thêm duyệt vào đây
	                	$hash  = Qss_Util::hmac_md5($item->UserID.$item->Password,$this->_params->IFID_M077);
	                	$body .= sprintf('<a href="http://%1$s/ws/mail/status?ifid=%2$d&status=2&uid=%3$s&sid=%4$s">'
	                			,$domain
	                			,$this->_params->IFID_M077
	                			,$item->UserID
	                			,$hash);
	                	$body .= sprintf('<span>Duyệt nghỉ</span></a><br><br>');
	                }
	                $body .= 'QS-IMS Mailer';
	                $this->_sendMail($subject, array($item->EMail=>$item->UserName), $body,$ccMails);
                }
            }
        }
	}
}
?>