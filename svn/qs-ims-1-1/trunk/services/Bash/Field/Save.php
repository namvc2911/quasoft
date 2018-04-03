<?php

class Qss_Service_Bash_Field_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		if(!$params['tofieldid'] && !$params['regx'])
		{
			$this->setError();
			$this->setMessage('Bạn phải nhập lấy từ hoặc hàm tự động tính toán');
			return;
		}
		$bash = new Qss_Model_Bash();
		$bash->saveField($params);
	}

}
?>