<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Web_Feedback_Save extends Qss_Lib_Service
{

	public function __doExecute ($a_Data)
	{
		$retval = 0;
		$form = new Qss_Model_Form();
		$fid = $a_Data['fid'];
		$deptid = $a_Data['deptid'];
		$captcha = (string) $a_Data['captcha'];
		if ( $form->init($fid, $deptid, 0) )
		{
			$object = $form->o_fGetMainObject();
			$object->loadFields();
			if ( $object && $this->b_fValidate($object, $a_Data) && $this->b_fCheckRequire($object) )
			{
				if($captcha == '' || Qss_Session::get('captcha') != $captcha)
				{
					$this->setMessage('Mời bạn nhập lại chữ trên ảnh.');
					$this->setError();
				}
				else
				{
					$object->b_fSave();
					$retval = $object->i_IFID;
					$dept = new Qss_Model_Admin_Department();
					$dept->init($deptid);
					$mail = new Qss_Model_Mail();
					$to = $dept->szMail;
					$subject= 'Phản hồi';
					$body = 'Chào bạn!
		Bạn đã nhận được phản hồi trên trang web
		
		QS-IMS system';
					//$mail->setBody($text);
					//$mail->send();
					$mail->logMail($subject, $body, $to, null, null, null);
				}
			}
			else
			{
				$this->setError();
			}
		}
		else
		{
			$this->setMessage('Bản ghi không tồn tại.');
			$this->setError();
		}
		return $retval;
	}
}
?>