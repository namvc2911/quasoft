<?php

class Qss_Service_System_Database_CopyField extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$model = new Qss_Model_System_Database();
		$objid  = $params['objid'];
		$object = $model->getObjectByCode($objid);
		$copyobject = $model->getCopyObjectByCode($objid);
		$copyFields = $model->getCopyFieldsByObjectCode($objid);
		if(!count($object))//insert object
		{
			$data = array(
						'ObjectCode'=>$copyobject[0]->ObjectCode
						,'ObjectName'=>$copyobject[0]->ObjectName
						,'ObjectName_en'=>$copyobject[0]->ObjectName_en
						,'OrderField'=>$copyobject[0]->OrderField
						,'OrderType'=>$copyobject[0]->OrderType);
				$model->copyObject($data,false);
		}
		
		//copy object
		foreach ($copyFields as $f)
		{
			if(isset($params['copy_'.$f->FieldCode]) && $params['copy_'.$f->FieldCode])
			{
				$grid = bindec(sprintf('%1$d%2$d%3$d%4$d',
							(@$params['eMobile_' . $f->FieldCode])?1:0,
							(@$params['eGrid_' . $f->FieldCode])?1:0,
							(@$params['Mobile_' . $f->FieldCode])?1:0,
							(@$params['bGrid_' . $f->FieldCode])?1:0));
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
						,'Effect'		=>(int)@$params['effect_' . $f->FieldCode]
						,'ReadOnly'		=>(int)@$params['readonly_' . $f->FieldCode]
						,'InputType'	=>$f->InputType
						,'FieldWidth' 	=>$f->FieldWidth
						,'Search'		=>$f->Search
						,'TValue'		=>$f->TValue
						,'FValue'		=>$f->FValue
						,'Grid'			=>$grid
						,'Required'		=>(int)@$params['required_' . $f->FieldCode]
						,'AFunction'	=>$f->AFunction
						,'Pattern'		=>$f->Pattern
						,'PatternMessage'=>$f->PatternMessage
						,'Regx'			=>$f->Regx
						,'Unique'			=>$f->Unique
						,'Style'			=>$f->Style
						,'FieldName_en'	=>$f->FieldName_en
						,'isRefresh'	=>$f->isRefresh);
					$model->copyField($data,false);
			}
		}
		/*$newobject = new Qss_Model_System_Object();
		$newobject->v_fInit($objid);
		$newobject->createView();*/
	}

}
?>