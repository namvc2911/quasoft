<?php
class Qss_View_Instance_Form_GridEdit extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, $currentpage = 1, $limit = 20, $orderfield = 0, $groupby=0)
	{
		$object = $form->o_fGetMainObject();
		$this->html->user = Qss_Register::get('userinfo');
		if($object->i_PageCount && $currentpage > $object->i_PageCount)
		{
			$currentpage = 1;
		}
		$this->html->currentpage = $currentpage;
		$this->html->objects = $object->a_fGetIOIDBySQL($sql[0], $currentpage, $limit);
		$fcount = 0;
		foreach($form->o_fGetMainObjects() as $item)
		{
			$fcount += $item->getGridFieldCount();
		}
		$this->html->gridFieldCount = $fcount + 1;
		$this->html->mainobjects = $form->o_fGetMainObjects();
		$this->html->o_Object = $object;
		$stepmodel = new Qss_Model_System_Step($form->i_WorkFlowID);
		$steps = $stepmodel->getAll();
		$arrname = array();
		$arrcolor = array();
		$arrformrights = array();
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$isApprove = false;
		foreach ($steps as $step)
		{
			if($step->StepType || @$step->QuickStep)
			{
				$isApprove = true;
			}
			$arrname[$step->StepNo] = $step->{"Name".$lang};
			$arrcolor[$step->StepNo] = $step->Color;
			$arrformrights[$step->StepNo] = $step->FormRights;
		}
		$this->html->arrname = $arrname;
		$this->html->arrcolor = $arrcolor;
		$this->html->arrformrights = $arrformrights;
		$this->html->isApprove = $isApprove;
	}
}

?>