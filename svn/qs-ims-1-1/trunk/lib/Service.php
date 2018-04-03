<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Service extends Qss_Service_Abstract
{

	/**
	 *
	 * @param Qss_Model_Object $object
	 * @param $data
	 * @return boolean
	 */
	public function b_fValidate (Qss_Model_Object &$object, $data)
	{
		$ret = true;
		$fields = $object->loadFields();
		$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
		if(!class_exists($classname))
		{
			$classname = 'Qss_Lib_Onload';
		}
		$onload = new $classname(null,$object);
		foreach ($fields as $key => $f)
		{
			//$onload->{$f->FieldCode}();Bỏ đi, mà không biết ở đâu load lần nữa khi refresh
			$name = $f->ObjectCode . '_' . $f->FieldCode;
			if(isset($data[$name])  && (!$f->bReadOnly || $f->szDefaultVal == 'AUTO'))//nếu readonly mà auto
			{
				if (is_array($data[$name]))
				{
					$val = 0;
					foreach ($data[$name] as $bit)
					{
						$val = $val | $bit;
					}
					$f->setValue($val);
				}
				elseif ( ($f->intFieldType == 8 || $f->intFieldType == 9))
				{
					$f->setValue($data[$name]);
				}
				elseif($f->intFieldType == 7 )
				{
					$f->setValue(($data[$name]?true:false));
				}
				else
				{
					
		
					if(is_int($data[$name]) 
					&&($f->RefFieldCode 
					|| $f->intFieldType == 14 
					|| $f->intFieldType == 16))
					{
						$f->setRefIOID($data[$name]);
						$f->setValue('');
					}
					else 
					{
												
						$f->setValue($data[$name]);
					}
				}
			}
		}

					
		$arrDuplicate = array();
		foreach ($fields as $key => $f)
		{
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
					&&  !$f->intIOID && !$f->getValue())//
			{
				if($f->RefFieldCode)
				{
					//$filter = $object->getFilter($f);
					$user = Qss_Register::get('userinfo');
					$f->setDefaultLookupValue($user);//,$filter
				}
				elseif($f->intFieldType == 10)
				{
					$f->setValue(date('d-m-Y'));
				}
				elseif($f->intFieldType == 4)
				{
					$f->setValue(date('H:i'));
				}
				elseif($f->intFieldType == 17)
				{
					$f->setValue(date('Y-m-d H:i:s'));
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
				$errorMessage = '';
				$ret = false;
				foreach ($arrDuplicate as $item)
				{
					if($errorMessage != '')
					{
						$errorMessage .= ' + ';
					}
					$errorMessage .= $item->szFieldName . ': "' . $item->getValue() . '"';
				}
				$this->setMessage("{$errorMessage} đã tồn tại");
			}
		}
		/*if(count($arrKeys) && $arrKeys[key($arrKeys)] && count(array_unique($arrKeys)) == 1)
		{
			
		}*/
		return $ret;

	}

	/**
	 *
	 * @param Qss_Model_Object $object
	 * @return check require
	 */
	public function b_fCheckRequire (Qss_Model_Object $object)
	{
		$ret = true;
		foreach ($object->loadFields() as $key => $f)
		{
			$f instanceof Qss_Model_Field;
			if ( $f->bRequired && ($f->getValue() === '' || $f->getValue() === null))
			{
				$this->setMessage("{$f->szFieldName} yêu cầu bắt buộc!");
				if($ret)
				{
					$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
				}
				$ret = false;
			}
			$value = $f->getValue();
			if($f->bEditStatus && $value)
			{
				if($f->intInputType == 12)
				{
					/*if(!$this->checkAttributes($object, $f))
					{
						if($ret)
						{
							$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
						}
						$ret = false;
					}*/
					continue;
				}
				switch ($f->intFieldType)
				{
					case 5://int
						if(!Qss_Validation::isInt($value))
						{
							$this->setMessage("{$f->szFieldName} phải là dạng số!");
							if($ret)
							{
								$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
							}
							$ret = false;
						}
						break;
					case 6://float
						if(!Qss_Validation::isFloat($value))
						{
							$this->setMessage("{$f->szFieldName} phải là dạng thập phân!");
							if($ret)
							{
								$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
							}
							$ret = false;
						}
						break;
					case 12://mail
						if(!Qss_Validation::isEmail($value))
						{
							$this->setMessage("{$f->szFieldName} phải là địa chỉ email!");
							if($ret)
							{
								$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
							}
							$ret = false;
						}
						break;
					case 11://Money
						if(!Qss_Validation::isPatternValid('/^[0-9.,-]+$/', $value))
						{
							$this->setMessage("{$f->szFieldName} phải là tiền tệ!");
							if($ret)
							{
								$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
							}
							$ret = false;
						}
						break;
					case 10://Date
						if(!Qss_Validation::isPatternValid('/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/',  $value))
						{
							$this->setMessage("{$f->szFieldName} phải là ngày tháng dạng dd-mm-yyyy!");
							if($ret)
							{
								$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
							}
							$ret = false;
						}
						break;
				}
				if($f->szPattern)
				{
					if(!Qss_Validation::isPatternValid($f->szPattern,  $value))
					{
						$this->setMessage($f->szPatternMessage);
						if($ret)
						{
							$this->setStatus("{$f->ObjectCode}_{$f->FieldCode}");
						}
						$ret = false;
					}
				}
			}
		}
		return $ret;
	}
	public function setFieldCalculate(&$object,$auto = true)
	{
		$ret = false;
		foreach ($object->loadFields() as $key => $f)
		{
			$classname = 'Qss_Bin_Calculate_'.$object->ObjectCode.'_'.$f->FieldCode;
			if ( class_exists($classname) && $f->szRegx == Qss_Lib_Const::FIELD_REG_RECALCULATE)
			{
				$autocal = new $classname($object);
				$f->setValue($autocal->__doExecute());
				$ret = true;
			}
			elseif ( $f->szRegx == Qss_Lib_Const::FIELD_REG_AUTO && $auto )
			{
				$object->setRefValue($f);
				$ret = true;
			}
		}
		return $ret;
	}
	public function sendMail($subject,$address,$body,$ccadress = array())
	{
		if(is_array($address) && count($address))
		{
			$to = '';
			$cc = '';
			$mail = new Qss_Model_Mail();
			foreach ($address as $email=>$name)
			{
				//$mail->addTo($email, $name);
				if($to)
				{
					$to .= ',';
				}
				$to .= $email;
			}
			foreach ($ccadress as $email=>$name)
			{
				//$mail->addCc($email, $name);
				if($cc)
				{
					$cc .= ',';
				}
				$cc .= $email;
			}
			/*$mail->setSubject($subject);
			$mail->setBody($body);
			@$mail->send();*/
			$mail->logMail($subject, $body, $to, $cc, null, null);
		}
	}
	private function checkAttributes($object,$attrfield)
	{
		$ret = true;
		$model 		   		  = new Qss_Model_Extra_Products();
		$new   		   		  = $object->getFieldByCode($attrfield->szTValue)->getValue();
		$attributes    		  = $attrfield->intRefIOID;
		if($attributes)
		{
			$next = $model->checkAttributesAvailable($new, $attributes);
			
			if(!$next)
			{
				$ret = false;
				$this->setMessage('Bạn phải cập nhật lại thuộc tính sản phẩm.');/*'Bạn phải cập nhật lại thuộc tính sản phẩm.'*/
			}
		}
		elseif(!$this->isError())
		{
			$requires  	   = $model->checkAttributeRequires($new);
			if($requires)
			{
				$ret = false;
				$this->setMessage('Thuộc tính sản phẩm bắt buộc.');/*'Thuộc tính sản phẩm bắt buộc.'*/
			}
		}
		return $ret;
	}
}
?>