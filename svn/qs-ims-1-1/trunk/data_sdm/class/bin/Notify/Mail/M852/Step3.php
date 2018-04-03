<?php
class Qss_Bin_Notify_Mail_M852_Step3 extends Qss_Lib_Notify_Mail
{
	const TITLE ='Gửi email xác nhận đã xử lý';
	
	const TYPE ='TRIGGER';

    const INACTIVE = true;
	
	public function __doExecute($user,$status,$comment)
	{
		$domain = $_SERVER['HTTP_HOST'];
			$sql = sprintf('select qsusers.EMail,qsusers.UserName
							from qsusers 
							inner join qsrecordrights on qsrecordrights.UID = qsusers.UID
							inner join ODuAn on ODuAn.IFID_M803 = qsrecordrights.IFID  
							where FormCode = "M803" and ODuAn.IOID = %1$d AND IFNULL(qsusers.isActive, 0) = 1',
						$this->_params->Ref_DuAn);
			$wcs = $this->_db->fetchAll($sql);
			$arrToDonVi = array();
			$arrCCDonVi = array();
			foreach ($wcs as $item)
			{
				$arrToDonVi[$item->EMail] = $item->UserName;
			}
			foreach ($this->_maillist as $item)
			{
			    if($item->EMail)
			    {
			        $arrToDonVi[$item->EMail] = $item->UserName;
			    }
			}

			// unset($arrToDonVi[$user->user_email]);

			if(count($arrToDonVi) || count($arrCCDonVi))
			{
				$subject = '['. $this->_params->DuAn .'] Đã xử lý '. $this->_params->SoPhieu;

                $body  = '<h1>Đã xử lý '. $this->_params->SoPhieu.'</h1>';
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

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Người thực hiện:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= $this->_params->GiaoCho;
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Mô tả:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Util::textToHtml($this->_params->MoTa);
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Hạn hoàn thành:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Date::mysqltodisplay($this->_params->NgayKetThucKeHoach);
                $body .= '</td>';
                $body .= '</tr>';

                $body .= '<tr style="border-bottom: 1px #ccc solid;">';
                $body .= '<th style="text-align: left; width: 20%;" valign="top">';
                $body .= 'Ngày hoàn thành:';
                $body .= '</th>';
                $body .= '<td style="text-align: left;" valign="top">';
                $body .= Qss_Lib_Date::mysqltodisplay($this->_params->NgayKetThuc).' '.$this->_params->ThoiGianKetThuc;
                $body .= '</td>';
                $body .= '</tr>';


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

				$this->_sendMail($subject, $arrToDonVi, $body,$arrCCDonVi);	
			}
	}
}
?>