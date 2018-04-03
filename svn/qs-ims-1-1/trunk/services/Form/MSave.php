<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Form_MSave extends Qss_Lib_Service
{

	public function __doExecute ($data)
	{
		/* The validation should be here. This will add to message property that
		 * we can access in caller e.g: $this->services->form->save->message */
		$form = new Qss_Model_Form();
		$fid = $data['fid'];
		$ifids = explode(',',$data['ifids']);
		$deptids = explode(',',$data['deptids']);
		$user = Qss_Register::get('userinfo');
		$error = false;
		$import = new Qss_Model_Import_Form($fid);
		foreach($ifids as $key=>$ifid)
		{
			if($form->initData($ifid, $deptids[$key]))
			{
				$rights = $form->i_fGetRights($user->user_group_list);
				if ( ($rights & 4) )
				{
					$arrUpdate = array();
					$fields = $form->o_fGetMainObject()->loadFields();
					$mainData = array();
					foreach ($fields as $f)
					{
						if($f->bGrid & 16)
						{
							$check = @$data['multi_'.$f->ObjectCode.'_'.$f->FieldCode];
							if($check)//có sửa
							{
								$f->szDefaultVal = '';
								//nếu readonly báo lỗi luôn
								if($f->bReadOnly)
								{
									$error = true;
									$this->setMessage($f->szFieldName.' không được sửa!');
									break;
								}
								$value = @$data[$f->ObjectCode.'_'.$f->FieldCode];
								if (is_array($value))
								{
									$val = 0;
									foreach ($value as $bit)
									{
										$val = $val | $bit;
									}
									$f->setValue($val);
								}
								elseif ( ($f->intFieldType == 8 || $f->intFieldType == 9))
								{
									$f->setValue($value);
								}
								elseif($f->intFieldType == 7 )
								{
									$f->setValue(($value?true:false));
								}
								else
								{
									if(is_int($value)
									&&($f->RefFieldCode
									|| $f->intFieldType == 14
									|| $f->intFieldType == 16))
									{
										$f->setRefIOID($value);
										$f->setValue('');
									}
									else
									{
										$f->setValue($value);
									}
								}
								if(!$this->checkRequired($f))
								{
									$error = true;
									break;
								}
								$mainData[$f->FieldCode] = $f->getValue(false);
							}
						}
					}
					$mainData['ifid'] = $ifid;
					$arrUpdate[$form->o_fGetMainObject()->ObjectCode][0] = $mainData;
					$objects = $form->a_fGetSubObjects();
					foreach($objects as $object)
					{
						if(!($object->bPublic & 1))
						{
							$fields = $object->loadFields();
							$subData = array();
							foreach ($fields as $f)
							{
								if($f->bGrid & 16)
								{
									$check = @$data['multi_'.$f->ObjectCode.'_'.$f->FieldCode];
									if($check)//có sửa
									{
										$f->szDefaultVal = '';
										//nếu readonly báo lỗi luôn
										if($f->bReadOnly)
										{
											$error = true;
											$this->setMessage($f->szFieldName.' không được sửa!');
											break;
										}
										$value = @$data[$f->ObjectCode.'_'.$f->FieldCode];
										if (is_array($value))
										{
											$val = 0;
											foreach ($value as $bit)
											{
												$val = $val | $bit;
											}
											$f->setValue($val);
										}
										elseif ( ($f->intFieldType == 8 || $f->intFieldType == 9))
										{
											$f->setValue($value);
										}
										elseif($f->intFieldType == 7 )
										{
											$f->setValue(($value?true:false));
										}
										else
										{
											if(is_int($value)
											&&($f->RefFieldCode
											|| $f->intFieldType == 14
											|| $f->intFieldType == 16))
											{
												$f->setRefIOID($value);
												$f->setValue('');
											}
											else
											{
												$f->setValue($value);
											}
										}
										if(!$this->checkRequired($f))
										{
											$error = true;
											break;
										}
										$subData[$f->FieldCode] = $f->getValue(false);
									}
								}
							}
							$table = new Qss_Model_Db_Table($object->ObjectCode);
							$table->where(sprintf('IFID_%1$s = %2$d',$form->FormCode,$ifid));
							$table->select('IOID');
							$ioidData = $table->fetchAll();
							foreach ($ioidData as $item)
							{
								$subData['ioid'] = $item->IOID;
								$subData['ifid'] = $ifid;
								$arrUpdate[$object->ObjectCode][] = $subData;
							}
						}
					}
					if(count($arrUpdate))
					{
						//print_r($arrUpdate);die;
						$import->setData($arrUpdate);
					}
				}
				else
				{
					$this->setMessage('Một trong số bản ghi được chọn không cho phép sửa!');
					$error = true;
					break;
				}
			}
			else
			{
				$this->setMessage('Không tìm thấy bản ghi');
				$error = true;
				break;
			}
		}
		if(!$error)
		{
			$import->generateSQL();
			if($import->isError())
			{
				$error = true;
				$this->setMessage('Cập nhật không thành công do có ít nhất một bản ghi không thể cập nhật');
				print_r($import->getImportRows());
			}
		}
		if($error)
		{
			$this->setError();
		}
	}
	public function checkRequired($f)
	{
		$ret = true;
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
		if($value)
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
		return $ret;
	}
}
?>