<?php
class Qss_Service_Form_Remove extends Qss_Lib_Service
{
	public function __doExecute ($formcode,$ifid,$params)
	{
		$user = Qss_Register::get('userinfo');
		$deptid = $user->user_dept_id;
		$intDeleted = 0;
		$intError = 0;
		
		$form = new Qss_Model_Form();
		if ( !$form->initData($ifid, $deptid) )
		{
			$this->setMessage($this->_translate(145));
			$this->setError();
			return;
		}
		if(!$params)
		{
			//delete form
			$arrCrossLinks = $form->getCrossLinks();
			if(!count( (array) $arrCrossLinks))
			{
				$form->b_fDelete();
				$form->updateReader($user->user_id);
				$intDeleted++;
			}
			else
			{
				$this->setError();
				$this->setMessage('Dữ liệu đã được dùng trong các mô đun sau:');
				foreach($arrCrossLinks as $item)
				{
					$this->setMessage($item->Name);
				}
				$intError++;
			}
		}
		else
		{
			foreach ($params as $key=>$value)//key is objectid
			{
				$object = $form->o_fGetObjectByCode($key);
				foreach ($value as $k=>$val)
				{
					if($object->initData($form->i_IFID, $form->i_DepartmentID, $val))
					{
						$arrCrossLinks = $object->getCrossLinks();
						if(!count( (array) $arrCrossLinks))
						{
							$object->b_fDelete();
							$intDeleted++;
						}
						else
						{
							$this->setError();
							$this->setMessage('Dữ liệu đã được dùng trong các mô đun sau:');
							foreach($arrCrossLinks as $item)
							{
								$this->setMessage($item->Name);
							}
							$intError++;
						}
					}
					else
					{
						$intError++;
					}
				}
			}
			if($intError)
			{
				$this->setError();
			}
			else
			{
				$mainObject = $form->o_fGetMainObject();
				$mainObject->initData($ifid, $form->i_DepartmentID, $mainObject->i_IOID);
				if($this->setFieldCalculate($mainObject,false))
				{
					$mainObject->b_fSave();
				}
			}
		}
		$this->setMessage("{$intDeleted} dòng được xóa, {$intError} dòng bị lỗi.");
		return $form->i_IFID;
	}
}
?>