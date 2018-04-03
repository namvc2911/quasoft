<?php
/**
 * Save form service 
 * 
 * @author HuyBD
 *
 */
class Qss_Service_Form_Refresh extends Qss_Lib_Service
{

	public function __doExecute ($a_Data)
	{
		/* The validation should be here. This will add to message property that 
		 * we can access in caller e.g: $this->services->form->save->message */
		$retval = 0;
		$form = new Qss_Model_Form();
		$fid = @$a_Data['fid'];
		$ifid = @$a_Data['ifid'];
		$deptid = @$a_Data['deptid'];
		$user = Qss_Register::get('userinfo');
		if ( $ifid && $form->initData($ifid, $deptid) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			if ( ($rights & 15) )
			{
				$object = $form->o_fGetMainObject();
				$object->initData($form->i_IFID, $form->i_DepartmentID, 0);
				if ( $object && $this->b_fValidate($object, $a_Data))
				{
					//$retval = $object->i_IFID;
					$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
					if(!class_exists($classname))
					{
						$classname = 'Qss_Lib_Onload';
					}
					$onload = new $classname($form,$object);
					$onload->__doExecute();
					$fields = $object->loadFields();
					foreach ($fields as $key => $field)
					{
						$onload->{$field->FieldCode}();
						//check lại nếu có lọc
						if(count($field->arrFilters) && !$field->checkLookup())
						{
							$field->setRefIOID(0);
							$field->setValue('');
						}
						else if ( $field->szRegx == Qss_Lib_Const::FIELD_REG_AUTO )
						{
							$object->setRefValue($field);
						}
					}
					$object->setTableData();
				}
				else
				{
					$this->setError();
				}
			}
			else
			{
				$this->setMessage($this->_translate(146));
				$this->setError();
			}
		}
		elseif ( $form->init($fid, $deptid, $user->user_id) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			if ( ($rights & 15) )
			{
				$object = $form->o_fGetMainObject();

				if ( $object && $this->b_fValidate($object, $a_Data) )
				{
					//$retval = $object;
					$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
					if(!class_exists($classname))
					{
						$classname = 'Qss_Lib_Onload';
					}
					$onload = new $classname($form,$object);
					$onload->__doExecute();
					$fields = $object->loadFields();
					foreach ($fields as $key => $field)
					{
						$onload->{$field->FieldCode}();
						//check lại nếu có lọc
						if($field->intInputType == 3
							&& $field->intInputType == 4
							&& count($field->arrFilters) 
							&& !$field->checkLookup())
						{
							$field->setRefIOID(0);
							$field->setValue('');
						}
						else if ( $field->szRegx == Qss_Lib_Const::FIELD_REG_AUTO )
						{
							$object->setRefValue($field);
						}
					}
					$object->setTableData();
				}
				else
				{
					$this->setError();
				}
			}
			else
			{
				$this->setMessage($this->_translate(146));
				$this->setError();
			}
		}
		else
		{
			$this->setMessage($this->_translate(145));
			$this->setError();
		}
		return $form;
	}
}
?>