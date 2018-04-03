<?php
class Qss_Bin_Notify_Mail_M747 extends Qss_Lib_Notify_Mail
{
	public function __doExecute()
	{
// 	    $users   = $this->_form->getRefUsers();
// 	    $status  = $this->_form->i_Status;
// 	    echo '<pre>'; print_r($users); die;
// 	    $this->changeStatus($users,$status,$comment);
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
    				 <p>Yêu cầu bảo trì của bạn đã được cập nhật tình trạng
    				 <a href="http://%1$s/user/form/edit?ifid=%2$d&deptid=%3$d">Xem chi tiết</a>
    				 <p>',
    	        $domain,
    	        $this->_form->i_IFID,
    	        $this->_form->i_DepartmentID);
    	    $this->_sendMail($this->_form->sz_Name, $toMails, $body,$ccMails);
	    }
	}
}
?>