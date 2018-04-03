<?php
class Qss_Bin_Validation_M412 extends Qss_Lib_Validation
{
	
	public function onRead()
    {
        parent::init();
        $user = Qss_Register::get('userinfo');
        $this->_form->read($user->user_id);
    }
}