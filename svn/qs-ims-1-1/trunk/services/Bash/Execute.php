<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Bash_Execute extends Qss_Lib_Service
{
	protected $_fields;

	protected $_form;
	/*current object*/
	protected $_object;

	public function __doExecute (Qss_Model_Form $form,$id)
	{
		$db = Qss_Db::getAdapter('main');
		$db->beginTransaction();
		$this->_form = $form;
		$bashmodel = new Qss_Model_Bash();
		$bash = $bashmodel->getByID($id);
		$frommainobject = $form->o_fGetMainObject();
		$bhid = 0;
		if($bash)// && $form->FormCode == $bash->FormCode
		{	
			//validation
			
			//$ifid = $form->i_IFID;
			//$tofid = $bash->ToFormCode;
			//$toform = new Qss_Model_Form();
			//$toform->init($tofid, $form->i_DepartmentID,$form->i_UserID);
			//$tomainobject = $toform->o_fGetMainObject();
			//$history = $bashmodel->getHistory($id,$frommainobject->i_IFID);
			if($bash->Type <= 4 && $bash->Class)
			{
				$classname = 'Qss_Bin_Bash_'.$bash->FormCode . '_' .$bash->Class;
				if(!class_exists($classname))
				{
					$classname = 'Qss_Bin_Bash_'.$bash->Class;
				}
				if(class_exists($classname))
				{
					
					$bash = new $classname($form);
					$bash->init();
					$bash->__doExecute();

					$this->setMessage($bash->getMessage());
					if($bash->isError())
					{
						$this->setError();

					}
				}
				else
				{
					$this->setError();
					$this->setMessage('Không tồn tại file bash ' . $bash->Class . '!');
				}
			}
		}
		if(!$this->isError())
		{
			$db->commit();
		}
		else 
		{
			$db->rollback();
		}
	}
	private function transferObject($form,&$toobject)
	{
		if ( !$toobject || !$this->b_fTransfer($toobject) || !$this->b_fCheckRequire($toobject) )
		{
			$this->setError();
			//echo 'fromobjecttosubobjects';
		}
		if(!$this->isError())
		{
			if($toobject->b_fSave())
			{
				$classname = 'Qss_Bin_Trigger_'.$toobject->ObjectCode;
				if(class_exists($classname))
				{
					$trigger = new $classname($form);
					$trigger->init();
					if($ioid)
					{
						$trigger->onInserted($toobject);
					}
					else
					{
						$trigger->onUpdated($toobject);
					}
				}
			}
		}
	}
	private function b_fTransfer(&$object)
	{
		$ret = true;
		$fields = $object->loadFields();
		foreach ($fields as $key => $f)
		{
			$val = $this->getFieldValue($f->FieldCode);
			if ( $f->intInputType == 3 
				|| $f->intInputType == 4 
				|| $f->intInputType == 11 
				|| $f->intInputType == 12)
			{
				if(is_int($val) && ($f->intFieldType == 11 
					|| $f->RefFieldCode 
					|| $f->intFieldType == 14 
					|| $f->intFieldType == 16))
				{
					$f->setRefIOID($val);
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
		$arrDuplicate = array();
		$arrKeys = null;
		foreach ($fields as $key => $f)
		{
			$classname = 'Qss_Bin_Calculate_'.$object->ObjectCode.'_'.$f->FieldCode;
			if ( class_exists($classname) && $f->bReadOnly )
			{
				$autocal = new $classname($object);
				$f->setValue($autocal->__doExecute());
			}
			elseif ( $f->szRegx == Qss_Lib_Const::FIELD_REG_AUTO )
			{
				$object->setRefValue($f);
			}
			if ( $f->bUnique )
			{
				$dupIOID = $object->b_fCheckDuplicate($f);
				if(!is_array($arrKeys))
				{
					$arrKeys = $dupIOID;
				}
				else
				{
					$arrKeys = array_intersect($arrKeys,$dupIOID);
				}
				$arrDuplicate[] = $f->szFieldName;
			}
			elseif ( $f->intFieldType != 14 && $f->intFieldType != 15 && $f->szDefaultVal == 'AUTO' && !$f->getValue() )
			{
				// $f->setValue($f->getMaxValue($f->getMaxValue($object->FormCode,$object->i_IFID) + 1);
                $f->setValue($f->getMaxValue($object->FormCode,$object->i_IFID) + 1);
			}
		}
		if(count($arrKeys) && $arrKeys[key($arrKeys)] && count(array_unique($arrKeys)) == 1)
		{
			$errorMessage = '';
			$ret = false;
			foreach ($arrDuplicate as $item)
			{
				if($errorMessage != '')
				{
					$errorMessage .= ' + ';
				}
				$errorMessage .= $item;
			}
			$this->setMessage("{$errorMessage} đã tồn tại");
		}
		return $ret;
	}
	private function getFieldValue($fieldid)
	{
		foreach ($this->_fields as $item)
		{
			if($item->ToFieldID == $fieldid)
			{
				if($item->Regx)
				{
					return $this->_form->getObjectByCode($item->ObjectCode,$item->FieldCode)->v_fGetCalculate($item->Regx);
				}
				else
				{
					return $this->_form->getFieldByCode($item->ObjectCode,$item->FieldCode)->getValue();
				}
			}
		}
	}
	private function saveHistory($bhid,$id,$userid,$ifid,$toifid,$ioid)
	{
		$bashmodel = new Qss_Model_Bash();
		$retval = 0;
		$data = array();
		$data['BHID'] = $bhid;
		$data['BID'] = $id;
		$data['UID'] = $userid;
		$data['LastRun'] = date('Y-m-d H:i:s');
		$data['IFID'] = $ifid;
		$data['ToIFID'] = $toifid;
		$data['IOID'] = $ioid;
		if($this->isError())
		{
			$data['Error'] = 1;
			$data['Message'] = $this->getMessage(Qss_Service_Abstract::TYPE_TEXT);
			$retval = $bashmodel->saveHistory($data);
		}
		else
		{
			$data['Error'] = 0;
			$data['Message'] = $this->getMessage(Qss_Service_Abstract::TYPE_TEXT);
			$retval = $bashmodel->saveHistory($data);
			//$toform->updateReader(Qss_Register::get('userinfo')->user_id);
		}
		return $retval;
	}
	private function saveSubHistory($bhid,$fromioid,$toioid)
	{
		$bashmodel = new Qss_Model_Bash();
		$data = array();
		$data['BHID'] = $bhid;
		$data['FromIOID'] = $fromioid;
		$data['TOIOID'] = $toioid;
		$bashmodel->saveSubHistory($data);
	}
}
?>