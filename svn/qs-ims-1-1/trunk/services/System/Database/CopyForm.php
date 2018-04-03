<?php

class Qss_Service_System_Database_CopyForm extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$model = new Qss_Model_System_Database();
		$fid  = $params['fid'];
		$form = $model->getFormByCode($fid);
		$copyForm = $model->getCopyFormByCode($fid);
		$objects = $model->getObjects();
		$arrForm = array();
		$arrCopyForm = array();
		$arrObjects = array();
		foreach($form as $item)
		{
			$arrForm[$item->ObjectCode] = $item;
		}
		foreach($copyForm as $item)
		{
			$arrCopyForm[$item->ObjectCode] = $item;
		}
		foreach($objects as $item)
		{
			$arrObjects[] = $item->ObjectCode;
		}
		$arrTemp = array_values($arrCopyForm);
		$copyform = $arrTemp[0];
		//update or insert to qsforms when not install or class != ''
		if(!count($arrForm))//insert 
		{
			$data = array(
					'FormCode'=>$copyform->FormCode
					,'Effected'=>(int)@$params['effect']
					,'class'=>$copyform->class
					,'Name'=>$copyform->Name
					,'ExcelImport'=>$copyform->ExcelImport
					,'Type'=>$copyform->Type
					,'Document'=>$copyform->Document
					,'Name_en'=>$copyform->Name_en);
			$model->copyForm($data,false);
		}
		else
		{
			$data = array(
					'FormCode'=>$copyform->FormCode
					,'Effected'=>(int)@$params['effect']
					,'class'=>$copyform->class);
			$model->copyForm($data);
		}
		//copy object
		foreach ($arrCopyForm as $item)
		{
			if($item->ObjectCode)
			{
				if(!is_array(@$arrForm) || !array_key_exists($item->ObjectCode,$arrForm))//insert qsfobjects
				{
					$data = array(
						'FormCode'=>$copyform->FormCode
						,'ObjectCode'=>$item->ObjectCode
						,'Public'=>(int)@$params[$item->ObjectCode]
						,'ParentObjectCode'=>$item->ParentObjectCode
						,'Main'=>$item->Main
						,'Editing'=>$item->Editing
						,'Track'=>$item->Track
						,'Insert'=>$item->Insert
						,'Deleting'=>$item->Deleting
						,'ObjNo'=>$item->ObjNo);
					$model->copyFormObject($data,false);
				}
				else //update qsfobject
				{
					$data = array(
						'FormCode'=>$copyform->FormCode
						,'ObjectCode'=>$item->ObjectCode
						,'Public'=>(int)@$params[$item->ObjectCode]);
					$model->copyFormObject($data);
				}
			}
			if(!in_array($item->ObjectCode,$arrObjects))//insert object and qsfields
			{
				$data = array(
						'ObjectCode'=>$item->ObjectCode
						,'ObjectName'=>$item->ObjectName
						,'ObjectName_en'=>$item->ObjectName_en
						,'OrderField'=>$item->OrderField
						,'OrderType'=>$item->OrderType);
				$model->copyObject($data,false);
				$copyfields = $model->getCopyFieldsByObjectCode($copyform->ObjectCode);
				foreach ($copyfields as $f)
				{
					$data = array(
						'FieldNo'		=>$f->FieldNo
						,'ObjectCode'	=>$f->ObjectCode
						,'RefFormCode'	=>$f->RefFormCode
						,'RefObjectCode'=>$f->RefObjectCode
						,'RefFieldCode'	=>$f->RefFieldCode
						,'RefDisplayCode'=>$f->RefDisplayCode
						,'FieldCode'	=>$f->FieldCode
						,'FieldName'	=>$f->FieldName
						,'FieldType'	=>$f->FieldType
						,'DefaultVal'	=>$f->DefaultVal
						,'RefLabel'		=>$f->RefLabel
						,'Effect'		=>$f->Effect
						,'DataType'		=>$f->DataType
						,'ReadOnly'		=>$f->ReadOnly
						,'InputType'	=>$f->InputType
						,'FieldWidth' 	=>$f->FieldWidth
						,'Search'		=>$f->Search
						,'TValue'		=>$f->TValue
						,'FValue'		=>$f->FValue
						,'Grid'			=>$f->Grid
						,'Required'		=>$f->Required
						,'AFunction'	=>$f->AFunction
						,'Pattern'		=>$f->Pattern
						,'PatternMessage'=>$f->PatternMessage
						,'Regx'			=>$f->Regx
						,'FieldName_en'	=>$f->FieldName_en
						,'isRefresh'	=>$f->isRefresh);
					$model->copyField($data,false);
				}
			}
		}
	}

}
?>