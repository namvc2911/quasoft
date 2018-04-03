<?php

class Qss_Service_Form_Approve extends Qss_Lib_WService
{

	public function __doExecute (Qss_Model_Form $form, $stepno, $user, $comment)
	{
		$approvers = $step->getApproverByIFID($ifid);
		$this->html->approvers = $approvers;
		$ok = false;
		foreach ($approvers as $item)
		{
			if($step->intStepType == 1 || $step->intStepType == 2)//yêu cầu tất cả được duyệt, có StepNo là đã được duyệt
			{
				$ok = true;
				if(!$item->StepNo)
				{
					$ok = false;
					break;
				}
			}
			elseif($step->intStepType == 3)//Yêu cầu cái cuối cùng
			{
				if(!$item->StepNo)
				{
					$ok = false;
				}
				else
				{
					$ok = true;
				}
			}
			elseif($step->intStepType == 4)//Chỉ cần 1 nhóm duyệt
			{
				if($item->StepNo)
				{
					$ok = true;
					break;
				}
			}
		}
	}
}
?>