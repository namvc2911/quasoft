<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Web_Comment_Save extends Qss_Lib_Service
{

	public function __doExecute ($a_Data)
	{
		$form = new Qss_Model_Form();
		$ifid = (int) $a_Data['ifid'];
		$deptid = (int) $a_Data['deptid'];
		$objid = (int) $a_Data['objid'];
		$captcha = (string) $a_Data['captcha'];

		if ( $ifid && $deptid && $form->initData($ifid, $deptid) )
		{
			$object = new Qss_Model_Object();
			$object->v_fInit($objid, $form->FormCode);
			if ( $object->initData($ifid, $deptid, $ioid) )
			{
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
						Qss_Session::set('captcha','');
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
		}
		else
		{
			$this->setMessage('Bản ghi không tồn tại.');
			$this->setError();
		}
	}
}
?>