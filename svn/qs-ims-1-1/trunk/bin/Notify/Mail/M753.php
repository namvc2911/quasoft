<?php
class Qss_Bin_Notify_Mail_M753 extends Qss_Lib_Notify_Mail
{
	public function __doExecute()
	{
		$date       = date_create();
		$date       = Qss_Lib_Date::add_date($date, 7);
		$toMails    = array();
		$ccMails    = array();
        $arrToDonVi = array();
        $arrCCDonVi = array();
        $arrName    = array();

		foreach ($this->_maillist as $item)
		{
			if($item->EMail)
			{
                $toMails[$item->EMail] = $item->UserName;
			}
		}

		$sql = sprintf(
            'select
                ODonViSanXuat.*
                , ONhanVien.QuanLy
                , qsusers.EMail
                , qsusers.UserName
            from ODonViSanXuat
            inner join ONhanVien ON ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125
            inner join ODanhSachNhanVien ON ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
            inner join qsusers ON qsusers.UID =  ODanhSachNhanVien.Ref_TenTruyCap
            where qsusers.isActive = 1
            order by ODonViSanXuat.IFID_M125'
        );
		$dataSQL    = $this->_db->fetchAll($sql);


		foreach ($dataSQL as $item)
		{
			if(!isset($arrToDonVi[$item->IOID]))
			{
				$arrToDonVi[$item->IOID] = array();
			}

			if(!isset($arrCCDonVi[$item->IOID]))
			{
				$arrCCDonVi[$item->IOID] = array();
			}

			if($item->QuanLy)
			{
				$arrCCDonVi[$item->IOID][$item->EMail] = $item->UserName;
			}
			else
			{
				$arrToDonVi[$item->IOID][$item->EMail] = $item->UserName;
			}
			$arrName[$item->IOID] = $item->Ten;
		}

		foreach ($arrName as $key=>$value)
		{
			$arrTos  = array_merge($toMails,$arrToDonVi[$key]);
			$arrCcs  = array_merge($ccMails,$arrCCDonVi[$key]);
			$content = $this->getContentByWC($key,$value,$arrTos);

			if($content)
			{
				$subject = 'Thiết bị cần HC/KĐ vào ngày '. $date->format('d-m-Y');
				$body    = 'Chào anh/chị' . '<br><br>';
				$body   .= $content.'<br>';
				$body   .= 'QS-IMS Mailer';
				$this->_sendMail($subject, $arrTos, $body,$arrCcs);
			}
		}
	}

    /**
     * Lay cac ke hoach hieu chinh/kiem dinh
     * @param $wc
     * @param $name
     * @param $to
     * @return string
     */
	private function getContentByWC($wc,$name,$to)
	{
		$date     = date_create();
        $haveData = false;
        $i        = 1;
        $content  = '';
        $content .= '<table border = "1">';
        $content .= sprintf('<th>Mã TB</th><th>Tên TB</th><th>Loại TB</th><th>Khu Vực</th><th>Ngày HC/KĐ</th>');
		$sql    = sprintf('select
                                dstb.MaThietBi,
                                dstb.LoaiThietBi,
                                khuvuc.Ten AS TenKhuVuc,
                                dstb.TenThietBi AS TenThietBi2,
                                NgayKiemDinhTiepTheo
                                from OHieuChuanKiemDinh as hc
                                inner join ODanhSachThietBi AS dstb on dstb.IOID = hc.Ref_MaThietBi
                                inner join OKhuVuc as khuvuc on khuvuc.IOID = ifnull(dstb.Ref_MaKhuVuc, 0)
                                inner join OThietBi as kvtb on kvtb.Ref_Ma = khuvuc.IOID
                                inner join ODonViSanXuat as dvsx on dvsx.IFID_M125 = kvtb.IFID_M125 
                                where
                                dvsx.IOID = %1$d and
                                DATE(NgayKiemDinhTiepTheo) between NOW() and (NOW() + INTERVAL 7 DAY)',
						$wc);

        $dataSQL = $this->_db->fetchAll($sql);

        if(count($dataSQL))
        {
        	$haveData = true;
            foreach ($dataSQL as $item)
            {
            	$content .= '<tr>';
                $content .= sprintf('<td>%1$s</td><td>%5$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td>',
                                $item->MaThietBi,
                                $item->LoaiThietBi,
                                $item->TenKhuVuc,
                                Qss_Lib_Date::mysqltodisplay($item->NgayKiemDinhTiepTheo),
                                $item->TenThietBi2);
                $content .= '</tr>';
			}
		}
        $content .= '</table>';
		return $haveData?$content:'';
	}
}
?>