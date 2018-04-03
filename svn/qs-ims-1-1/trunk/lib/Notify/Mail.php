<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Notify_Mail extends Qss_Lib_Notify
{
	
	protected $_step;
	
	public function init($step = null)
	{
		parent::init();
		$this->_step = $step;
		//$this->_maillist = array();
		$model = new Qss_Model_Notify_Mail();
		$dataSQL = $model->getNotifyMembers(get_class ($this));
		foreach($dataSQL as $item)
		{
			$row = new stdClass();
			$row->EMail = $item->EMail;
			$row->UserName = $item->UserName;
			$this->_maillist[] = $row;	
		}
		$dataSQL = $model->getNotifyGroups(get_class ($this));
		foreach($dataSQL as $item)
		{
			$row = new stdClass();
			$row->EMail = $item->EMail;
			$row->UserName = $item->UserName;
			$this->_maillist[] = $row;	
		}
		$dataSQL = $model->getMails(get_class ($this));
		if($dataSQL)
		{
			$extra = $dataSQL->Extra;
			if($extra)
			{
				$mails = explode(',',$extra);
				foreach ($mails as $item)
				{
					$row = new stdClass();
					$row->EMail = $item;
					$row->UserName = $item;
					$this->_maillist[] = $row;	
				}
			}	
		}

//		if($this->_step)
//		{
//			$dataSQL = $step->getApproverUsers();
//			foreach($dataSQL as $item)
//			{
//				$row = new stdClass();
//				$row->EMail = $item->EMail;
//				$row->UserName = $item->UserName;
//				$this->_maillist[] = $row;
//			}
//		}
	}
	public function approve($user,$level)
	{
		$toMails = array();
		$ccMails = array();
		
		$domain   = $_SERVER['HTTP_HOST'];
		$phieuyc  = '';
		$phieuyc .= "<p>{$this->_form->sz_Name}";
        $phieuyc .= "<a href=\"http://{$domain}/user/form/edit?ifid={$this->_form->i_IFID}&deptid={$this->_form->i_DepartmentID}\">Xem chi tiết</a></strong> </p>";

		foreach ($this->_maillist as $item)
		{
		    if($item->EMail)
		    {
		        $toMails[$item->EMail] = $item->UserName;
		    }
		}
		if(count($toMails))
		{
			$body = sprintf('Chào bạn!
				 <p>%1$s vừa <strong>%2$s</strong> %3$s</p>
				 <strong>"%4$s"</strong>',
				 $user->user_desc,
				 $level->Name,
				 $this->_form->sz_Name, 
				 $phieuyc);
			$this->_sendMail($level->Name, $toMails, $body,$ccMails);
		}
	}
}
?>