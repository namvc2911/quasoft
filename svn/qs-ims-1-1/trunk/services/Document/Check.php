<?php

class Qss_Service_Document_Check extends Qss_Service_Abstract
{
/**/
	public function __doExecute ($uid,$docid,$rights)
	{
		if(!$docid)
		{
			return true;	
		}
		$document = new Qss_Model_Document();
		$retval = $document->getCheckRights($uid,$docid);
		if($retval > 0 && $retval <= $rights)
		{
			return true;
		}
		else 
		{
			$this->setError();
			$this->setMessage('Bạn không có quyền thực hiện thao tác này');
			return false;
		}
	}

}
?>