<?php
class Qss_Bin_Notify_Mail_M759_Comment extends Qss_Lib_Notify_Mail
{
    const TYPE = 'TRIGGER';

    const TITLE = 'Gửi email phản hồi phiếu bảo trì';

    public function __doExecute($user,$comment)
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

            // unset($toMails[$user->user_email]);


            if(count($toMails) || count($ccMails))
            {
                $subject = '[QS-M759] Phản hồi '. $this->_params->SoPhieu;
                $body = sprintf('%1$s:  <strong>"%2$s"</strong><br><br>',
                    $user->user_desc,
                    $comment);
                $body .= sprintf('Mô tả: %1$s<br><br>',$this->_params->MoTa);
                $body .= sprintf('Số phiếu : <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</a><br>',
                    $this->_params->SoPhieu,
                    $domain,
                    $this->_params->IFID_IFID_M759,
                    1);
                $body .= 'Quasoft CMMS Mailer';
                $this->_sendMail($subject, $toMails, $body,$ccMails);
            }
        }
    }
}
?>