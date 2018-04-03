<?php
class Qss_Bin_Notify_Mail_M412 extends Qss_Lib_Notify_Mail
{
	public function __doExecute()
	{
		
	}
	public function changeStatus($user,$status,$comment)
	{
		//khi nhận hàng gửi cho người yêu cầu
		/*$toMails = array();
		$ccMails = array();
		
		$domain = $_SERVER['HTTP_HOST'];
		$phieuyc = '';
		$sql = sprintf('
				   SELECT 
				    qsiforms.*,
				    ncvt.*,
				    qsusers.*
				   FROM ONhuCauMuaHang AS ncmh
				   INNER JOIN qsioidlink AS `link` ON ncmh.IOID = link.ToIOID AND ncmh.IFID_M716 = link.ToIFID
				   INNER JOIN ONhuCauVatTu AS ncvt ON link.FromIOID = ncvt.IOID AND link.FromIFID = ncvt.FromIFID
				   INNER JOIN qsiforms on qsiforms.IFID = ncvt.IFID_M709
				   INNER JOIN qsusers on qsusers.UID = qsiforms.UID
				   WHERE ncmh.IFID_M716 = \'%1$d\' and qsusers.isActive = 1
				  ', $this->_form->i_IFID);
  		$dataSQL = $this->_db->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$toMails[$item->EMail] = $item->UserName;
			$phieuyc .= sprintf('<p>Phiếu yêu cầu vật tư <strong> <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</strong>',
						$item->SoPhieu,
						$domain,
						$item->IFID,
						$item->DepartmentID);
		}
		
		if(count($toMails))
		{
			$body = sprintf('Chào bạn!
				 <p>%1$s đã cập nhật tình trạng <strong>%2$s</strong> %2$s</p>
				 <strong>"%6$s"</strong>', 
				 $user->user_desc,
				 $status,
				 $this->_form->sz_Name, 
				 $comment,
				 $phieuyc);
			$this->_sendMail($this->_form->sz_Name, $toMails, $body,$ccMails);
		}
			*/
	}
}
?>