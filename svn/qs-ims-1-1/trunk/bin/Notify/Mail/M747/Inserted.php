<?php
class Qss_Bin_Notify_Mail_M747_Inserted extends Qss_Lib_Notify_Mail
{
	const TYPE = 'TRIGGER';

	const TITLE = 'Gửi email khi tạo yêu cầu bảo trì';

	public function __doExecute()
	{
		$domain  = $_SERVER['HTTP_HOST'];
		$ccMails = array();
		$toMails = array();
		$own     = $this->_db->fetchOne(sprintf('select UserName, EMail from qsusers where qsusers.UID = %1$d', $this->_form->i_UserID));
		$add     = $this->_db->fetchOne(sprintf('select * from OYeuCauBaoTri where IFID_M747 = %1$d', $this->_form->i_IFID));

		$sql    = sprintf('
					SELECT 
					dsnv1.Email AS EMail
					, nv1.QuanLy
					, dsnv1.TenNhanVien AS UserName
					, dvsx2.MaKhuVuc  /* Khu vuc cua thiet bi (Not parents)*/
					FROM
					(
						SELECT distinct dvsx.IFID_M125, kvtb.MaKhuVuc FROM
						(
							SELECT dstb.IOID AS EIOID, kv.IOID AS LIOID, kv.lft, kv.rgt, kv.MaKhuVuc
							FROM ODanhSachThietBi AS dstb
							INNER JOIN OKhuVuc AS kv on dstb.Ref_MaKhuVuc = kv.IOID
							WHERE dstb.IOID = %1$d
						) AS kvtb
						INNER JOIN OKhuVuc AS kv2 on kv2.lft <= kvtb.lft AND kv2.rgt >= kvtb.rgt
						LEFT JOIN OThietBi AS kvbt on 
							(kvbt.Ref_Ma = kvtb.LIOID OR kvbt.Ref_Ma = kv2.IOID) 
							AND (%2$s >= kvbt.NgayBatDau or ifnull(kvbt.NgayBatDau, \'\') = \'\')
							AND (%2$s <= kvbt.NgayKetThuc or ifnull(kvbt.NgayKetThuc, \'\') = \'\')
						LEFT JOIN ODonViSanXuat AS dvsx ON dvsx.IFID_M125 = kvbt.IFID_M125
						WHERE dvsx.IFID_M125 <> 0
					) AS dvsx2
					INNER JOIN ONhanVien AS nv1 ON dvsx2.IFID_M125 = nv1.IFID_M125
					INNER JOIN ODanhSachNhanVien AS dsnv1 on dsnv1.IOID = nv1.Ref_MaNV
					ORDER BY dvsx2.IFID_M125
					',$this->_params->Ref_MaThietBi
		, $this->_db->quote($this->_params->Ngay));
		$employees = $this->_db->fetchAll($sql);
		if(count($employees))
		{
			foreach ($employees as $item)
			{
				if($item->QuanLy)
				{
					$ccMails[$item->EMail] = $item->UserName;
				}
				else
				{
					$toMails[$item->EMail] = $item->UserName;
				}
			}
		}
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
		$user = Qss_Register::get('userinfo');
		unset($toMails[$user->user_email]);
		if(count($toMails) || count($ccMails))
		{
			$subject = '[M747] Cảnh báo ';
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
			$body .= 'QS-IMS Mailer';
			$this->_sendMail($subject, $toMails, $body,$ccMails);
		}
	}
}
?>