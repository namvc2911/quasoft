<?php

class Qss_Service_Form_Validate extends Qss_Lib_Service
{

	public function __doExecute (Qss_Model_Form $form)
	{
		if ( 1 || $form->i_UserID == Qss_Register::get('userinfo')->user_id )
		{
			$classname = 'Qss_Bin_Validation_'.$form->FormCode;
			if(class_exists($classname))
			{
				$validation = new $classname($form);
				$validation->onValidated();
				if($validation->isError())
				{
					$this->setError();
					$this->setMessage($validation->getMessage());
				}
			}
			if($form->i_Type == Qss_Lib_Const::FORM_TYPE_PROCESS)
			{
				if(!$form->sz_Class)
				{
					$this->setError();
					$this->setMessage('Bạn chưa cấu hình file chạy tiến trình, liên hệ với quản trị hệ thống!');
				}
				else
				{
					$classname = 'Qss_Bin_Process_' . ucfirst($form->sz_Class);
					if(!class_exists($classname))
					{
						$this->setError();
						$this->setMessage('Không tồn tại file bash ' . $form->sz_Class . '!');
					}
				}
			}
			
			/*Step Validation*/
			//if($form->i_Status)
			{
				$classname = 'Qss_Bin_Validation_'.$form->FormCode.'_'.'Step'.$form->i_Status;
				if(class_exists($classname))
				{
					$validation = new $classname($form);
				}
				else 
				{
					$validation = new Qss_Lib_WValidation($form);
				}
				//$validation->onNext();
				//$validation->onBack();
				$validation->onAlert();
				if($validation->isError())
				{
					$this->setError();
					$this->setMessage($validation->getMessage());
				}
			}
			$form->updateError($this->isError(), $this->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
		else
		{
			$this->setMessage($this->_translate(146));
			$this->setError();
		}
	}
}
?>