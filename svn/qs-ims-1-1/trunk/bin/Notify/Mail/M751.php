<?php
class Qss_Bin_Notify_Mail_M751 extends Qss_Lib_Notify_Mail
{
	public function __doExecute()
	{
        $today   = date('Y-m-d');
        $domain  = $_SERVER['HTTP_HOST'];
        $subject  = 'Yêu cầu điều động thiết bị đến hạn';
        $toMails  = array();
        $ccMails  = array();     
        $newUser  = '';
        $newReq   = '';
        $bodyTemp = '';
        $newReqDetail = '';
        
        
        /* Lay tat ca yeu cau chua duoc chuyen thanh dieu dong thiet bi */
	    $sql = sprintf('

	        SELECT 
	           `nv`.*
	           , `user`.UserID
	           , `user`.EMail AS Email2
	           , ifnull(`user`.UID, 0) AS UID
	           , `yctb`.IFID_M751 AS YCIFID
	           , `yctb`.SoPhieu AS DocNo
	           , `yctb`.IOID AS YCTBIOID
               , `ycdd`.LoaiThietBi AS EqType
	           , `ycdd`.DonViTinh AS UOM
	           , `ycdd`.SoLuong AS Qty
	           , `ycdd`.IOID AS YCDDIOID
	           , `ycdd`.NgayBatDau AS Start
	           , `ycdd`.NgayKetThuc AS End
	        FROM OYeuCauTrangThietBiVatTu AS `yctb`
	        INNER JOIN OYeuCauTrangThietBi AS `ycdd` ON `ycdd`.IFID_M751 =  `yctb`.IFID_M751
	        INNER JOIN qsiforms AS `ifo` ON `ycdd`.IFID_M751 = `ifo`.IFID
	        INNER JOIN qsusers AS `user` ON `user`.UID = `ifo`.UID
	        INNER JOIN ODanhSachNhanVien AS `nv` ON `nv`.Ref_TenTruyCap = `user`.UID
	        LEFT JOIN 
	        (
	           SELECT `ldd`.*, `dstb`.Ref_LoaiThietBi
	           FROM OLichThietBi AS `ldd`
	           INNER JOIN ODanhSachThietBi AS `dstb` ON `ldd`.Ref_MaThietBi = `dstb`.IOID
	        )
	        AS `ddtb` ON `yctb`.IOID = `ddtb`.Ref_PhieuYeuCau  AND `ddtb`.Ref_LoaiThietBi = `ycdd`.Ref_LoaiThietBi	        
	        WHERE 
	           `user`.isActive = 1 and
	           `ycdd`.NgayBatDau <= %1$s
	           AND `ddtb`.IOID is null
	           AND `ifo`.Status not in (3, 4)

	        ORDER BY `user`.UID, `yctb`.IOID, `ycdd`.IOID
        ', $this->_db->quote($today));

	    $dataSQL  = $this->_db->fetchAll($sql);

	    
	    foreach ($this->_maillist as $item)
	    {
	        if($item->EMail)
	        {
	            $ccMails[$item->EMail] = $item->UserName;
	        }
	    }	    
	    
	    foreach($dataSQL as $item)
	    {
	        // Ghi lai va reset noi dung mail khi chuyen sang mot user moi
	        if($newUser != $item->UID) // Chuyen sang mot user moi
	        {
	            // gui mail cua cac user thiet lap truoc do
	            if(count($toMails) && isset($bodyTemp))
	            {
	                $this->_sendMail($subject, $toMails, $bodyTemp, $ccMails);
	            }
	            
	            $toMails  = array(); // reset
	            //$ccMails  = array(); // reset	            
	            $bodyTemp = 'Chào bạn! <br/>'; // reset noi dung gui mail
	            $newReq   = ''; 
	            $toMails[$item->Email]  = $item->UserID;
	            
	            
	            if($item->Email != $item->Email2)
	            {
	                $toMails[$item->Email2]  = $item->UserID;
	            }
	        }
	        
	        // Lay so phieu
	        if($item->YCTBIOID != $newReq)
	        {
	            $bodyTemp .= sprintf('
	            + Yêu cầu điều động thiết bị số %1$s  của bạn đã đến hạn điều động.
	            <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=1">Xem chi tiết</a><br/>'
	                , $item->DocNo, $domain, $item->YCIFID);	          
	            // , $item->EqType, $item->Qty, $item->UOM  
	        }
	        
	        
	        if($item->YCDDIOID != $newReqDetail)
	        {
    	        // Ghi lai loai thiet bi yeu cau
    	        $bodyTemp .= sprintf(' - Loại thiết bị "%1$s" yêu cầu %2$s (%3$s)'
    	            , $item->EqType, $item->Qty, $item->UOM);
    	        
    	        if($item->Start && $item->End)
    	        {
    	            $bodyTemp .= sprintf(' từ %1$s đến %2$s'
    	            ,  Qss_Lib_Date::mysqltodisplay($item->Start), Qss_Lib_Date::mysqltodisplay($item->End));
    	        }
    	        
    	        $bodyTemp .= '<br/>';
	        }
	        
	        // Ghi nhan ban ghi hien tai de so sanh yeu cau dieu dong va nhan vien
	        $newReq       = $item->YCTBIOID;
	        $newReqDetail = $item->YCDDIOID;
	        $newUser      = $item->UID;
	    }
	    
	    // Gui mail cua user cuoi 
	    if(count($toMails) && isset($bodyTemp))
	    {
	        $this->_sendMail($subject, $toMails, $bodyTemp, $ccMails);
	    }	    
	}
	
	public function changeStatus($user,$status,$comment)
	{
	    $sql = sprintf('
	        select ODanhSachNhanVien.*, qsusers.UserID, qsusers.EMail AS Email2
			from  qsusers
            left join ODanhSachNhanVien ON qsusers.UID = ODanhSachNhanVien.Ref_TenTruyCap
			where qsusers.UID = %1$d'
        , $this->_form->i_UserID);
	    
	    $dataSQL = $this->_db->fetchOne($sql);	  

	    $toMails = array();
	    $ccMails = array();
	    
	    if($dataSQL)
	    {
    		// gui thu khi chuyen tinh trang
    	    $domain = $_SERVER['HTTP_HOST'];
    	    $toMails[$dataSQL->Email] = $dataSQL->UserID;
    	    if($dataSQL->Email != $dataSQL->Email2)
    	    {
    	        $toMails[$dataSQL->Email2] = $dataSQL->UserID;
    	    }
    	    
    	    // send mail
    	    $body = sprintf('Chào bạn!
    				 <p>Yêu cầu điều động thiết bị của bạn đã được cập nhật tình trạng
    				 <a href="http://%1$s/user/form/edit?ifid=%2$d&deptid=%3$d">Xem chi tiết</a>
    				 </p>',
    	        $domain,
    	        $this->_form->i_IFID,
    	        $this->_form->i_DepartmentID);
    	    $this->_sendMail($this->_form->sz_Name, $toMails, $body,$ccMails);
	    }
	}
}
?>