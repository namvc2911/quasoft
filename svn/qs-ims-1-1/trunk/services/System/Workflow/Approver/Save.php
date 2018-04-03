<?php

class Qss_Service_System_Workflow_Approver_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$user = Qss_Register::get('userinfo');
		$wfid = $params['wfid'];
		$sid = (int) $params['sid'];
		$said = (int) $params['said'];
		$name = $params['name'];
		$step  = new Qss_Model_System_Step($wfid);
		if($sid)
		{
			$step->v_fInit($sid);
			$fid = $step->FormCode;
			$form = new Qss_Model_Form();
			$form->init($fid, $user->user_dept_id, $user->user_id);
			$arrCondition = array();
			$mainobjects = $form->o_fGetMainObjects();
			foreach ($mainobjects as $object)
			{
				$fields = $object->loadFields();
				foreach ($fields as $item)
				{
					if(isset($params['field_'.$item->FieldCode]))
					{
						if($item->intFieldType == 7)
						{
							$value = (bool) @$params['value_'.$item->FieldCode];
						}
						else 
						{
							$value = $params['value_'.$item->FieldCode];
						}
						$arrCondition[$item->FieldCode] = $value;
					}
				}
			}
			$subobjects = $form->a_fGetSubObjects();
			foreach ($subobjects as $object)
			{
				$fields = $object->loadFields();
				foreach ($fields as $item)
				{
					if(isset($params['field_'.$item->FieldCode]))
					{
						if($item->intFieldType == 7)
						{
							$value = (bool) @$params['value_'.$item->FieldCode];
						}
						else 
						{
							$value = $params['value_'.$item->FieldCode];
						}
						$arrCondition[$item->FieldCode] = $value;
					}
				}
			}
			if(count($arrCondition))
			{
				$condition = Qss_Json::encode($arrCondition);
			}
			else
			{
				$condition = '';
			}
			$orderno = (int)$params['orderno'];
			$step->saveApprover($said,$orderno, $name,$condition);
		}
	}

}
?>