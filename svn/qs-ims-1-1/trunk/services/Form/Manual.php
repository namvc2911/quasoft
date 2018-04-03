<?php
class Qss_Service_Form_Manual extends Qss_Lib_Service
{

	protected $intImported;

	protected $intError;
	
	protected $form;

	public function __doExecute ($formcode,$ifid,$params,$ignore = false,$multilink = true,$deptid = 0)
	{
		$user = Qss_Register::get('userinfo');
		$deptid = $deptid?$deptid:($user?$user->user_dept_id:1);
		$this->intImported = 0;
		$this->intError = 0;
		
		$form = new Qss_Model_Form();
		$fid = $form->getByCode($formcode)->FormCode;
		if (!($ifid 
				&& $deptid 
				&& $form->initData($ifid, $deptid)) 
				&& !$form->init($fid, $deptid, $user?$user->user_id:1) )
		{
			$this->setMessage($this->_translate(145));
			$this->setError();
			return;
		}
		$this->form = $form;
		foreach ($params as $key=>$value)//key is objectid
		{
			$object = $form->o_fGetObjectByCode($key);
			if(!$object ||(!$this->form->i_IFID && !$object->b_Main))
			{
				$this->setMessage('Không tồn tại dữ liệu chính'.$key);
				$this->setError();
				break;
			}
			//$updatelink = true;
			foreach ($value as $k=>$val)
			{
				$object->loadFields();
				$iobject = clone $object;
				$fields = Qss_Lib_Util::cloneData($iobject->a_Fields);
				$this->importRow($val, $iobject, $ignore,$multilink);
				//save IOID link
				if(isset($val['ioidlink']) && isset($val['ifidlink']))
				{
					$this->form->saveIOIDLink($val['ioidlink'],$iobject->i_IOID,$val['ifidlink'],$iobject->i_IFID);
				}
				unset($iobject);
				$object->a_Fields = $fields;//@TODO reset causes reload fields next time
			}
		}
		if($this->intError)
		{
			$this->setError();
		}
		$this->setMessage("{$this->intImported} dòng được cập nhật, {$this->intError} dòng bị lỗi.");
		return $this->form->i_IFID;
	}

	function importRow ($params, Qss_Model_Object $object, $ignore,$multilink)
	{
		$ioid = (int) @$params['ioid'];//
		if(!$ioid)
		{
			if(!$multilink)
			{
				//check if have link already, check ignore then throw error
				$ioidlink = (int) @$params['ioidlink'];
				$ifidlink = (int) @$params['ifidlink'];
				if($ioidlink && $ifidlink)
				{
					$datalink = $this->form->getIOIDLink($ioidlink,$ifidlink);
					if($datalink)
					{
						if($ignore)
						{
							$ioid = $datalink->ToIOID;
						}
						else 
						{
							$this->intError++;
							$this->setMessage($this->form->sz_Name .' '. $this->_translate(1));
							return;
						}
					}
				}
			}
		}
		if($this->form->i_IFID)
		{
			$object->initData($this->form->i_IFID, $this->form->i_DepartmentID, $ioid);
		}
		$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
		if(!class_exists($classname))
		{
			$classname = 'Qss_Lib_Onload';
		}
		$onload = new $classname(null,$object);
		foreach ($object->loadFields() as $f)
		{
			if ( isset($params[$f->FieldCode]) )
			{
				$onload->{$f->FieldCode}();
				$val = $params[$f->FieldCode];
				$val = ($val === null)?'':$val;
				if ( $f->intInputType == 3 || $f->intInputType == 4 || $f->intInputType == 11 || $f->intInputType == 12)
				{
					/*default*/
					if(is_int($val) && ($f->RefFieldCode 
					|| $f->intFieldType == 14 
					|| $f->intFieldType == 16))
					{
						$f->setRefIOID($val);
						$f->setValue('');
					}
					else 
					{
						$f->setValue($val);
					}
				}
				elseif($f->intFieldType == 7 )
				{
					$f->setValue(($val?true:false));
				}
				else
				{
					$f->setValue($val);
				}
			}
		}
		if ( $this->b_fValidate($params, $object,$ignore))
		{
			if($this->b_fCheckRequire($object) )
			{	
				if($object->i_IFID)
				{
					$this->form->i_IFID = $object->i_IFID; 
				}
				$classname = 'Qss_Bin_Trigger_'.$object->ObjectCode;
				if(class_exists($classname))
				{
					$trigger = new $classname($this->form);
					/*$trigger->init();
					if($object->i_IOID)
					{
						$trigger->onUpdate($object);
					}
					else
					{
						$trigger->onInsert($object);
					}
					$this->setMessage($trigger->getMessage());*/
				}
				//print_r($object);
				if((!isset($trigger) || !$trigger->isError()))
				{
					$object->b_fSave();
					if($object->i_IFID)
					{
						$this->form->i_IFID = $object->i_IFID; 
					}
					if(!$this->form->i_IFID)
					{
						$this->form->initData($object->i_IFID, $this->form->i_DepartmentID);
					}
					if(!$object->b_Main)
					{
						$mainObject = $this->form->o_fGetMainObject();
						$mainObject->initData($this->form->i_IFID, $this->form->i_DepartmentID, $mainObject->i_IOID);
						if($this->setFieldCalculate($mainObject,false))
						{
							$mainObject->b_fSave();
						}
					}
					$this->intImported++;
					if(class_exists($classname))
					{
						$trigger = new $classname($this->form);
						$trigger->init();
						if($object->i_IOID)
						{
							$trigger->onUpdated($object);
						}
						else
						{
							$trigger->onInserted($object);
						}
						$this->setMessage($trigger->getMessage());
					}
				}
				else
				{
					$this->intError++;
				}
			}
			else 
			{
				$this->intError++;
			}
		}
		else
		{
			$this->intError++;
		}
	}
	/**
	 *
	 * @param Qss_Model_Object $object
	 * @param $data
	 * @return boolean
	 */
	public function b_fValidate ($params,Qss_Model_Object &$object,$ignore)
	{
		$ret = true;
		$arrDuplicate = array();
		$arrKeys = null;
		$arrIFIDs = array();
		$dupIOID = array();
		$user = Qss_Register::get('userinfo');
		$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
		if(!class_exists($classname))
		{
			$classname = 'Qss_Lib_Onload';
		}
		$onload = new $classname(null,$object);
		$onload->__doExecute();
		$arrDuplicate = array();
		foreach ($object->loadFields() as $key => $f)
		{
			$onload->{$f->FieldCode}();
			$classname = 'Qss_Bin_Calculate_'.$object->ObjectCode.'_'.$f->FieldCode;
			if(class_exists($classname) 
					&& (($f->szRegx == Qss_Lib_Const::FIELD_REG_RECALCULATE || ($f->getValue(false) === '' || $f->getValue(false) == null) && !$object->i_IOID)))
			{
				$autocal = new $classname($object);
				$val = $autocal->__doExecute();
				if(is_int($val) 
					&&($f->RefFieldCode 
					|| $f->intFieldType == 14 
					|| $f->intFieldType == 16))
				{
					$f->setRefIOID($val);
					$f->setValue('');
				}
				else 
				{
					$f->setValue($val);
				}
			}
			elseif ( $f->szRegx == Qss_Lib_Const::FIELD_REG_AUTO )
			{
				$object->setRefValue($f);
			}
			elseif ( $f->intFieldType != 14 
				&& $f->intFieldType != 15 
				&& $f->intFieldType != 16 
				&& $f->szDefaultVal == 'AUTO' 
				&&  !$f->intRefIOID 
				&& !$f->getValue())//!$f->getValue()
			{
				if($f->RefFieldCode)
				{
					//$onload->{$f->FieldCode}();//$filter
					$user = Qss_Register::get('userinfo');
					$f->setDefaultLookupValue($user);
				}
				elseif($f->intFieldType == 10)
				{
					$f->setValue(date('d-m-Y'));
				}
				else 
				{
					$f->setValue($f->getMaxValue($object->FormCode,$object->i_IFID) + 1);
				}
			}
			if ( $f->bUnique )
			{
				$arrDuplicate[] = $f;
			}
		}
		if(count($arrDuplicate))
		{
			$dup = $object->b_fCheckDuplicate($arrDuplicate,$object->i_IOID);
			if($dup)
			{
				if(!$ignore)
				{
					$object->fetchData($dup);	
					foreach ($object->loadFields() as $f)
					{
						if ( isset($params[$f->FieldCode]) )
						{
							$val = $params[$f->FieldCode];
							$val = ($val === null)?'':$val;
							if ( $f->intInputType == 3 
								|| $f->intInputType == 4 
								|| $f->intInputType == 11 
								|| $f->intInputType == 12)
							{
								if(is_int($val) && ($f->RefFieldCode 
								|| $f->intFieldType == 14 
								|| $f->intFieldType == 16))
								{
									$f->setRefIOID($val);
									$f->setValue('');
								}
								else 
								{
									$f->setValue($val);
								}
							}
							elseif($f->intFieldType == 7 )
							{
								$f->setValue(($val?true:false));
							}
							else
							{
								$f->setValue($val);
							}
						}
					}
				}
				else
				{
					$errorMessage = '';
					$ret = false;
					foreach ($arrDuplicate as $item)
					{
						if($errorMessage != '')
						{
							$errorMessage .= ' + ';
						}
						$errorMessage .= $item->ObjectCode;
					}
					$this->setMessage("{$errorMessage} ". $this->_translate(165));
				}
			}
		}
		return $ret;
	}
}
?>