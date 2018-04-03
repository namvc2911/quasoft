<?php
/**
 * Create filter form
 * 
 * @author HuyBD
 *
 */
class Qss_View_Instance_Form_Row extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_Form $form, Qss_Model_UserInfo $user)
	{
		$object = $form->o_fGetMainObject();
		$this->html->o_Object = $object;
	}
 }

?>