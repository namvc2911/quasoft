<?php

class Qss_Service_Form_Sharing extends Qss_Service_Abstract
{

	public function __doExecute ($form,$params)
	{
		$form->v_fAddSharing($params);
		$form->updateReader(Qss_Register::get('userinfo')->user_id);
	}

}
?>