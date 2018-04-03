<?php
class Qss_Bin_Notify_Mail_M706 extends Qss_Lib_Notify_Mail
{
    public $cc = array();
    public $to = array();
    public $domain = '';

	public function __doExecute()
    {
        $this->domain = $_SERVER['HTTP_HOST'];
        $this->setCCList();

        $this->sendOrdersInComming();  // Qua han o buoc 1
        $this->sendOrdersOverTime();   // Qua han o buoc 2
        $this->sendOrdersInNextWeek(); // Thiet bi ve trong tuan toi
    }

    public function setCCList()
    {
        foreach ($this->_maillist as $item)
        {
            if($item->EMail && !in_array($item->EMail, $this->cc))
            {
                $this->cc[$item->EMail] = $item->UserName;
            }
        }
    }

    public function setToList($data)
    {
        if(count($data))
        {
            foreach ($data as $item)
            {
                if($item->EMail && !in_array($item->EMail, $this->to))
                {
                    $this->to[$item->EMail] = $item->UserName;
                }
            }

            if(count($this->to) == 0)
            {
                $this->to = $this->cc;
                $this->cc = array();
            }
        }
    }

    /**
     * Gui mail cho cac phieu da den han dieu dong (Dang o buoc 1, sap den ngay dieu dong)
     */
    public function sendOrdersInComming()
    {
        $subject = '[QS-IMS MAILER] DANH SÁCH ĐIỀU ĐỘNG QUÁ HẠN ÁP DỤNG ĐIỀU ĐỘNG';
        $mail    = array();
        $sMail   = '';
        $to      = array();
        $i       = 0;

        $sql = sprintf('
				   SELECT
				    qsiforms.*,
				    ddtb.*,
				    qsusers.*,
  		            ODanhSachNhanVien.Email AS Email2
				   FROM OLichThietBi AS ddtb
		           INNER JOIN qsiforms ON qsiforms.IFID = ddtb.IFID_M706
				   INNER JOIN qsusers ON qsusers.UID = qsiforms.UID
		           INNER JOIN ODanhSachNhanVien ON qsusers.UID = ODanhSachNhanVien.Ref_TenTruyCap
				   WHERE qsiforms.Status = 1 and qsusers.isActive = 1
		           and ddtb.NgayBatDau <= %1$s
				  ', $this->_db->quote(date('Y-m-d')));
        $dataSql = $this->_db->fetchAll($sql);

        if(count($dataSql) && count($this->to))
        {
            foreach($dataSql as $item)
            {
                if(!isset($to[$item->EMail]))
                {
                    $to[$item->EMail] = $item->UserName;
                }

                if(!isset($mail[$item->EMail]))
                {
                    $mail[$item->EMail] = array();
                }

                $mail[$item->EMail][$i]['SoPhieu']     = $item->SoPhieu;
                $mail[$item->EMail][$i]['NgayBatDau']  = $item->NgayBatDau;
                $mail[$item->EMail][$i]['NgayKetThuc'] = $item->NgayKetThuc;
                $mail[$item->EMail][$i]['DeptID']      = $item->DeptID;
                $mail[$item->EMail][$i]['IFID']        = $item->IFID_M706;
                $i++;
            }

            if(count($mail))
            {
                foreach($to as $email=>$user)
                {
                    if(isset($mail[$email]))
                    {
                        $sMail  = '';
                        $sMail .= '<h1>DANH SÁCH ĐIỀU ĐỘNG QUÁ HẠN ÁP DỤNG ĐIỀU ĐỘNG</h1>';
                        $sMail .= '<br/>';
                        $sMail .= '<table cellpadding="0" cellspacing="0" border="1">';
                        $sMail .= '<tr>';
                        $sMail .= '<th> SỐ PHIẾU </th>';
                        $sMail .= '<th> NGÀY BẮT ĐẦU </th>';
                        $sMail .= '<th> NGÀY KẾT THÚC </th>';
                        $sMail .= '</tr>';

                        foreach($mail[$email] as $item)
                        {
                            $sMail .= '<tr>';
                            $sMail .= '<td><a target="_blank" href="http://'.$this->domain.'/user/form/edit?ifid='.$item['IFID'].'&deptid='.$item['DeptID'].'">'.$item['SoPhieu'].'</a></td>';
                            $sMail .= '<td>' . Qss_Lib_Date::mysqltodisplay($item->NgayBatDau) . '</td>';
                            $sMail .= '<td>' . Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc) . '</td>';
                            $sMail .= '</tr>';
                        }

                        $sMail .= '</table>';
                        $sMail .= '<br/>';
                        $sMail .= '<p style="text-align:right" >  <b>QS-IMS Mailer</b> </p>';
                        $this->_sendMail($subject, array($email=>$user), $sMail, $this->cc);
                    }
                }
            }
        }
    }

    /**
     * Gui mail cho phieu da het han dieu dong (Dang o buoc 2, ngay hien tai da qua hoac bang ngay ket thuc dieu dong)
     */
    public function sendOrdersOverTime()
    {
        $subject = '[QS-IMS MAILER] DANH SÁCH ĐIỀU ĐỘNG QUÁ HẠN KẾT THÚC ĐIỀU ĐỘNG';
        $mail    = array();
        $sMail   = '';
        $to      = array();
        $i       = 0;

        $sql = sprintf('
            SELECT
            qsiforms.*,
            ddtb.*,
            qsusers.*,
            ODanhSachNhanVien.Email AS Email2
            FROM OLichThietBi AS ddtb
            INNER JOIN qsiforms ON qsiforms.IFID = ddtb.IFID_M706
            INNER JOIN qsusers ON qsusers.UID = qsiforms.UID
            INNER JOIN ODanhSachNhanVien ON qsusers.UID = ODanhSachNhanVien.Ref_TenTruyCap
            WHERE
                qsiforms.Status = 2
                and qsusers.isActive = 1
                and ddtb.NgayKetThuc <= %1$s
            ', $this->_db->quote(date('Y-m-d'))
        );
        $dataSql = $this->_db->fetchAll($sql);

        // $this->setToList($dataSql);

        if(count($dataSql) && count($this->to))
        {
            foreach($dataSql as $item)
            {
                if(!isset($to[$item->EMail]))
                {
                    $to[$item->EMail] = $item->UserName;
                }

                if(!isset($mail[$item->EMail]))
                {
                    $mail[$item->EMail] = array();
                }

                $mail[$item->EMail][$i]['SoPhieu']     = $item->SoPhieu;
                $mail[$item->EMail][$i]['NgayBatDau']  = $item->NgayBatDau;
                $mail[$item->EMail][$i]['NgayKetThuc'] = $item->NgayKetThuc;
                $mail[$item->EMail][$i]['DeptID']      = $item->DeptID;
                $mail[$item->EMail][$i]['IFID']        = $item->IFID_M706;
                $i++;
            }

            if(count($mail))
            {
                foreach($to as $email=>$user)
                {
                    if(isset($mail[$email]))
                    {
                        $sMail  = '';
                        $sMail .= '<h1>DANH SÁCH ĐIỀU ĐỘNG ĐẾN HẠN KẾT THÚC ĐIỀU ĐỘNG</h1>';
                        $sMail .= '<br/>';
                        $sMail .= '<table cellpadding="0" cellspacing="0" border="1">';
                        $sMail .= '<tr>';
                        $sMail .= '<th> SỐ PHIẾU </th>';
                        $sMail .= '<th> NGÀY BẮT ĐẦU </th>';
                        $sMail .= '<th> NGÀY KẾT THÚC </th>';
                        $sMail .= '</tr>';

                        foreach($mail[$email] as $item)
                        {
                            $sMail .= '<tr>';
                            $sMail .= '<td><a target="_blank" href="http://'.$this->domain.'/user/form/edit?ifid='.$item['IFID'].'&deptid='.$item['DeptID'].'">'.$item['SoPhieu'].'</a></td>';
                            $sMail .= '<td>' . Qss_Lib_Date::mysqltodisplay($item->NgayBatDau) . '</td>';
                            $sMail .= '<td>' . Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc) . '</td>';
                            $sMail .= '</tr>';
                        }

                        $sMail .= '</table>';
                        $sMail .= '<br/>';
                        $sMail .= '<p style="text-align:right" >  <b>QS-IMS Mailer</b> </p>';
                        $this->_sendMail($subject, array($email=>$user), $sMail, $this->cc);
                    }
                }
            }
        }

    }

    /**
     * Gui mail cho cac phieu co thiet bi ve trong tuan ke tiep
     */
    public function sendOrdersInNextWeek()
    {
        $subject = '[QS-IMS MAILER] DANH SÁCH THIẾT BỊ SẼ ĐƯỢC ĐIỀU ĐỘNG TRONG TUẦN KẾ TIẾP';
        $mail    = '';
        $start   = date('Y-m-d', strtotime('next monday'));
        $end     = date('Y-m-d', strtotime('next sunday'));

        $sql = sprintf('
            SELECT
                ddtb.*,
                qsusers.*,
                dsddtb.MaThietBi,
                dsddtb.TenThietBi
            FROM OLichThietBi AS ddtb
            INNER JOIN ODanhSachDieuDongThietBi AS dsddtb ON ddtb.IFID_M706 = dsddtb.IFID_M706
            INNER JOIN ODanhSachThietBi AS dstb ON dsddtb.Ref_MaThietBi = dstb.IOID
            INNER JOIN OKhuVuc AS khuvuc ON dstb.Ref_MaKhuVuc = khuvuc.IOID
            INNER JOIN OKhuVuc AS khuvuccon ON khuvuc.lft <= khuvuccon.lft AND khuvuc.rgt >= khuvuccon.rgt
            INNER JOIN OThietBi AS DonViKhuVuc ON khuvuccon.IOID = DonViKhuVuc.Ref_Ma
            INNER JOIN ODonViSanXuat AS DonVi ON DonVi.IFID_M125 = DonViKhuVuc.IFID_M125
            INNER JOIN ODonViSanXuat AS DonViCon ON DonVi.lft <= DonViCon.lft AND DonVi.rgt >= DonViCon.rgt
            INNER JOIN ONhanVien AS DonViNhanVien ON DonViCon.IFID_M125 = DonViNhanVien.IFID_M125
            INNER JOIN ODanhSachNhanVien AS NhanVien ON DonViNhanVien.Ref_MaNV = NhanVien.IOID
            INNER JOIN qsusers ON qsusers.UID = NhanVien.Ref_TenTruyCap
            WHERE qsusers.isActive = 1 /*AND (ddtb.NgayBatDau BETWEEN %1$s AND %2$s)*/
            ORDER BY  ddtb.NgayBatDau, ddtb.SoPhieu
        ', $this->_db->quote($start), $this->_db->quote($end));

        $dataSql = $this->_db->fetchAll($sql);

        $this->setToList($dataSql);

        if(count($dataSql) && count($this->to))
        {
            $mail .= '<h1> DANH SÁCH THIẾT BỊ SẼ ĐƯỢC ĐIỀU ĐỘNG TRONG TUẦN KẾ TIẾP </h1>';
            $mail .= '<br/>';
            $mail .= '<table cellpadding="0" cellspacing="0" border="1">';
            $mail .= '<tr>';
            $mail .= '<th> TÊN THIẾT BỊ </th>';
            $mail .= '<th> MÃ THIẾT BỊ </th>';
            $mail .= '<th> SỐ PHIẾU </th>';
            $mail .= '<th> NGÀY BẮT ĐẦU </th>';
            $mail .= '<th> NGÀY KẾT THÚC </th>';
            $mail .= '</tr>';

            foreach($dataSql as $item)
            {
                $mail .= '<tr>';
                $mail .= '<td>' . $item->TenThietBi . '</td>';
                $mail .= '<td>' . $item->MaThietBi . '</td>';
                $mail .= '<td><a target="_blank" href="http://'.$this->domain.'/user/form/edit?ifid='.$item->IFID_M706.'&deptid='.$item->DeptID.'">'.$item->SoPhieu.'</a></td>';
                $mail .= '<td>' . Qss_Lib_Date::mysqltodisplay($item->NgayBatDau) . '</td>';
                $mail .= '<td>' . Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc) . '</td>';
                $mail .= '</tr>';
            }

            $mail .= '</table>';
            $mail .= '<br/>';
            $mail .= '<p style="text-align:right" >  <b>QS-IMS Mailer</b> </p>';
            $this->_sendMail($subject, $this->to, $mail, $this->cc);
        }
    }
}
?>