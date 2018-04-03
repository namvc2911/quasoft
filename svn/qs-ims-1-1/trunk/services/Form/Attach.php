<?php

class Qss_Service_Form_Attach extends Qss_Service_Abstract
{
/**/
	public function __doExecute ($uid,$form,$params)
	{
		$docid = (int) $params['document_id'];
		if($docid)
		{
			$document = new Qss_Model_Document();
			$retval = $document->getCheckRights($uid,$docid);
			if($retval <= 0 && $retval > $rights)
			{
				$this->setError();
				$this->setMessage('Bạn không có quyền sử dụng tài liệu này');
				return false;
			}	
		}
		$reference = $params['reference'];
		if(!$docid && !$reference)
		{
			$this->setError();
			$this->setMessage('Hồ sơ yêu cầu phải có hoặc file đính kèm hoặc bản cứng');
			return false;
		}
		$form->attach($params); 
	}

}
?>