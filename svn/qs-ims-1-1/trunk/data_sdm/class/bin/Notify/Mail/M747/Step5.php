<?php
class Qss_Bin_Notify_Mail_M747_Step5 extends Qss_Lib_Notify_Mail
{
    const TITLE ='Gửi email khi chuyển tình trạng "Đã từ chối"';

    const TYPE ='TRIGGER';

    public function __doExecute($user,$status,$comment)
    {
        $domain  = $_SERVER['HTTP_HOST'];
        $ccMails = array();
        $toMails = array();
        $own     = $this->_db->fetchOne(sprintf('select UserName, EMail from qsusers where qsusers.UID = %1$d', $this->_form->i_UserID));
        $add     = $this->_db->fetchOne(sprintf('select * from OYeuCauBaoTri where IFID_M747 = %1$d', $this->_form->i_IFID));

        // Lấy mail của chủ bản ghi
        if($own)
        {
            if($own->EMail)
            {
                $toMails[$own->EMail] = $own->UserName;
            }
        }

        // Lấy mail được gắn trong phần cài đặt
        foreach ($this->_maillist as $item)
        {
            if($item->EMail)
            {
                $toMails[$item->EMail] = $item->UserName;
            }
        }

        // Lấy mail được gắn thêm
        if($add)
        {
            if($add->EMail)
            {
                $temp = explode(',', $add->EMail);

                foreach ($temp as $tmp)
                {
                    $tmp = trim($tmp);
                    if($tmp)
                    {
                        $toMails[$tmp] = $tmp;
                    }
                }
            }
        }
		unset($toMails[$user->user_email]);
        if(count($toMails) || count($ccMails))
        {
            $subject = '[QS-M747] Hủy phiếu ';
            $subject .= $this->_params->SoPhieu;
            $subject .= $this->_params->MaThietBi ? ' thiết bị ' : ($this->_params->MaKhuVuc ? ' khu vực ' : ' ');
            $subject .= $this->_params->MaThietBi ? $this->_params->MaThietBi : ($this->_params->MaKhuVuc ? $this->_params->MaKhuVuc : '');
            $subject .= ' ngày ' . Qss_Lib_Date::mysqltodisplay($this->_params->Ngay);

            $body = sprintf('Số phiếu : <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</a><br>',
                $this->_params->SoPhieu,
                $domain,
                $this->_params->IFID_M747,
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



            $body .= sprintf('Mô tả: %1$s<br><br>',$this->_params->MoTa);
            $body .= 'QS-IMS Mailer';
            $this->_sendMail($subject, $toMails, $body,$ccMails);
        }
    }
}
?>