<?php
class Qss_Bin_Notify_Mail_M852_Updated extends Qss_Lib_Notify_Mail
{
    const TYPE = 'TRIGGER';

    const TITLE = 'Gửi email khi thay đổi nội dung';
    /**
     * Xử lý gửi mail của "Phiếu bảo trì"
     * 1. Gửi mail hàng ngày về phiếu bảo trì tồn chưa được xử lý.
     * 2. Gửi mail hàng ngày về kế hoạch bảo trì trong ngày.
     */
    public function __doExecute()
    {
//        $to  = array();
//        $new = $this->_params->GiaoCho;
//        $old = $this->_db->fetchOne(sprintf('SELECT * FROM ODanhSachCongViec WHERE IFID_M852 = %1$d', @(int)$this->_form->i_IFID));
//
        if(
            ($this->_form->o_fGetMainObject()->oldData->Ref_GiaoCho != $this->_form->o_fGetMainObject()->getFieldByCode('GiaoCho')->getRefIOID())
            || ($this->_form->o_fGetMainObject()->oldData->Ref_NguoiTao != $this->_form->o_fGetMainObject()->getFieldByCode('NguoiTao')->getRefIOID())
            || ($this->_form->o_fGetMainObject()->oldData->TieuDe != $this->_form->o_fGetMainObject()->getFieldByCode('TieuDe')->getValue())
            || ($this->_form->o_fGetMainObject()->oldData->MoTa != $this->_form->o_fGetMainObject()->getFieldByCode('MoTa')->getValue())
            || ($this->_form->o_fGetMainObject()->oldData->NgayKetThucKeHoach != $this->_form->o_fGetMainObject()->getFieldByCode('NgayKetThucKeHoach')->getValue())
            || ($this->_form->o_fGetMainObject()->oldData->NgayKetThuc != $this->_form->o_fGetMainObject()->getFieldByCode('NgayKetThuc')->getValue())
        )
        {
            $noiDungCu    = '';
            $domain       = $_SERVER['HTTP_HOST'];

            if(($this->_form->o_fGetMainObject()->oldData->Ref_GiaoCho != $this->_form->o_fGetMainObject()->getFieldByCode('GiaoCho')->getRefIOID())) {
                $noiDungCu .= 'Người tạo cũ: ' . $this->_form->o_fGetMainObject()->oldData->GiaoCho;
                $noiDungCu .= '<br/>';
            }


            $nhanVienDuAn = $this->_db->fetchAll(
                sprintf('
                    select qsusers.EMail,qsusers.UserName
                    from qsusers 
                    inner join qsrecordrights on qsrecordrights.UID = qsusers.UID
                    inner join ODuAn on ODuAn.IFID_M803 = qsrecordrights.IFID  
                    where FormCode = "M803" and ODuAn.IOID = %1$d AND IFNULL(qsusers.isActive, 0) = 1', $this->_params->Ref_DuAn
                )
            );

            foreach ($nhanVienDuAn as $nhanVien) {
                $to[$nhanVien->EMail] = $nhanVien->UserName;
            }

            foreach ($this->_maillist as $item) {
                if($item->EMail) {
                    $to[$item->EMail] = $item->UserName;
                }
            }

            $user = Qss_Register::get('userinfo');

            // unset($to[$user->user_email]);

            if(count($to))
            {
                $subject = '['. $this->_params->DuAn .'] Cập nhật nội dung '. $this->_params->SoPhieu;

                $body  = '<h1>Cập nhật nội dung '. $this->_params->SoPhieu.'</h1>';
                $body .= '<table cellpadding="1" cellspacing="0" border="1"  width="100%">';
                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Số phiếu:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= sprintf('<a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</a><br>',
                    $this->_params->SoPhieu,
                    $domain,
                    $this->_params->IFID_M852,
                    1);
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Tiêu đề:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Util::textToHtml($this->_params->TieuDe);
                $body .= '</td>';
                $body .= '</tr>';

                if($this->_form->o_fGetMainObject()->oldData->TieuDe != $this->_form->o_fGetMainObject()->getFieldByCode('TieuDe')->getValue()) {
                    $body .= '<tr style="border-bottom: 1px #ccc solid; color:red;">';
                    $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                    $body .= 'Tiêu đề cũ:';
                    $body .= '</th>';
                    $body .= '<td style="text-align: left;" valign="top">';
                    $body .= Qss_Lib_Util::textToHtml($this->_form->o_fGetMainObject()->oldData->TieuDe);
                    $body .= '</td>';
                    $body .= '</tr>';
                }

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Dự án:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= $this->_params->DuAn;
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Mô-đun:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= $this->_params->Module;
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Người tạo:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= $this->_params->NguoiTao;
                $body .= '</td>';
                $body .= '</tr>';

                if(($this->_form->o_fGetMainObject()->oldData->Ref_NguoiTao != $this->_form->o_fGetMainObject()->getFieldByCode('NguoiTao')->getRefIOID())) {
                    $body .= '<tr style="border-bottom: 1px #ccc solid; color:red;">';
                    $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                    $body .= 'Người tạo cũ:';
                    $body .= '</th>';
                    $body .= '<td style="text-align: left;" valign="top">';
                    $body .= $this->_form->o_fGetMainObject()->oldData->NguoiTao;
                    $body .= '</td>';
                    $body .= '</tr>';
                }

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Người thực hiện:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= $this->_params->GiaoCho;
                $body .= '</td>';
                $body .= '</tr>';

                if(($this->_form->o_fGetMainObject()->oldData->Ref_GiaoCho != $this->_form->o_fGetMainObject()->getFieldByCode('GiaoCho')->getRefIOID())) {
                    $body .= '<tr style="border-bottom: 1px #ccc solid; color:red;">';
                    $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                    $body .= 'Người thực hiện cũ:';
                    $body .= '</th>';
                    $body .= '<td style="text-align: left;" valign="top">';
                    $body .= $this->_form->o_fGetMainObject()->oldData->GiaoCho;
                    $body .= '</td>';
                    $body .= '</tr>';
                }

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Mô tả:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Util::textToHtml($this->_params->MoTa);
                $body .= '</td>';
                $body .= '</tr>';

                if($this->_form->o_fGetMainObject()->oldData->MoTa != $this->_form->o_fGetMainObject()->getFieldByCode('MoTa')->getValue()) {
                    $body .= '<tr style="border-bottom: 1px #ccc solid; color:red;">';
                    $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                    $body .= 'Mô tả cũ:';
                    $body .= '</th>';
                    $body .= '<td style="text-align: left;" valign="top">';
                    $body .= Qss_Lib_Util::textToHtml($this->_form->o_fGetMainObject()->oldData->MoTa);
                    $body .= '</td>';
                    $body .= '</tr>';
                }

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Hạn hoàn thành:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Date::mysqltodisplay($this->_params->NgayKetThucKeHoach);
                $body .= '</td>';
                $body .= '</tr>';

                if($this->_form->o_fGetMainObject()->oldData->NgayKetThucKeHoach != $this->_params->NgayKetThucKeHoach) {
                    $body .= '<tr style="border-bottom: 1px #ccc solid; color:red;">';
                    $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                    $body .= 'Hạn hoàn thành cũ:';
                    $body .= '</th>';
                    $body .= '<td style="text-align: left;" valign="top">';
                    $body .= Qss_Lib_Date::mysqltodisplay($this->_form->o_fGetMainObject()->oldData->NgayKetThucKeHoach);
                    $body .= '</td>';
                    $body .= '</tr>';
                }

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Ngày hoàn thành:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Date::mysqltodisplay($this->_params->NgayKetThuc).' '.$this->_params->ThoiGianKetThuc;
                $body .= '</td>';
                $body .= '</tr>';

                if($this->_form->o_fGetMainObject()->oldData->NgayKetThuc != $this->_params->NgayKetThuc) {
                    $body .= '<tr style="border-bottom: 1px #ccc solid; color:red;">';
                    $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                    $body .= 'Ngày hoàn thành cũ:';
                    $body .= '</th>';
                    $body .= '<td style="text-align: left;" valign="top">';
                    $body .= Qss_Lib_Date::mysqltodisplay($this->_form->o_fGetMainObject()->oldData->NgayKetThuc).' '
                        .$this->_form->o_fGetMainObject()->oldData->ThoiGianKetThuc;
                    $body .= '</td>';
                    $body .= '</tr>';
                }


                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Nhận xét:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Util::textToHtml($this->_params->NhanXet);
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Ngày gửi:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= date('d-m-Y H:i:s');
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '</table>';
                $body .= '<p style="float: right; clear: both; margin-top: 5px;">Quasoft CMMS Mailer</p>';

                $this->_sendMail($subject, $to, $body, array());
            }
        }
    }
}
?>