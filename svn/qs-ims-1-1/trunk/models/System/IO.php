<?php
/**
 * System form model
 *
 * @author HuyBD
 *
 */
class Qss_Model_System_IO extends Qss_Model_Abstract
{
	protected $_arrForm = array();
	protected $_arrObject = array();
	protected $_arrField = array();

	public function __construct ()
	{
		parent::__construct();
	}
	public function export($fid,$no = 0)
	{
		/*Init form*/
		$formmodel = new Qss_Model_Form();
		$form = $formmodel->getByCode($fid);
		$retval = "";
		if ( $form )
		{	
			$retval = "[qss : {$no}]\n
			[qss : {$no} : qsforms]\n
			Name = \"{$form->Name}\"\n
			Name_en = \"{$form->Name_en}\"\n
			FormCode = \"{$form->FormCode}\"\n
			Workflow = {$form->Workflow}\n
			Effected = {$form->Effected}\n
			Type = {$form->Type}\n
			class = \"{$form->class}\"\n
			Document = {$form->Document}\n";
			$sql = sprintf('
				select * from qsfobjects
				inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
				inner join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode
				where qsfobjects.FormCode= "%1$s"
			',$fid);

			$dataSQL = $this->_o_DB->fetchAll($sql);

			// echo '<pre>'; print_r($dataSQL); die;

			$i = 0;
			$retval .= "[qss : {$no} : qsobjects]\n";
			foreach ($dataSQL as $item)
			{
				$retval .= "[qss : {$no} : qsobjects : {$i}]\n
				ObjectCode = \"{$item->ObjectCode}\"\n
				ObjectName = \"{$item->ObjectName}\"\n
				ObjectName_en = \"{$item->ObjectName_en}\"\n";
				$i++;
			}
			$i = 0;
			$retval .= "[qss : {$no} : qsfobjects]\n";
			foreach ($dataSQL as $item)
			{
				$retval .= "[qss : {$no} : qsfobjects : {$i}]\n
				FormCode = \"{$item->FormCode}\"\n
				ObjectCode = \"{$item->ObjectCode}\"\n
				Main = {$item->Main}\n
				ParentObjectCode = \"{$item->ParentObjectCode}\"\n
				Public 	= {$item->Public}\n
				Editing = {$item->Editing}\n
				Track  = {$item->Track}\n
				Insert  = {$item->Insert}\n
				Deleting  = {$item->Deleting}\n
				ObjNo  = {$item->ObjNo}\n";
				$i++;
			}
			$sql = sprintf('
					select * from qsfields
					inner join qsfobjects on qsfobjects.ObjectCode = qsfields.ObjectCode
					inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
					where qsfobjects.FormCode = "%1$s"
			',$fid);

			$dataSQL = $this->_o_DB->fetchAll($sql);

			//echo '<pre>'; print_r($dataSQL); die;

			$i = 0;
			$retval .= "[qss : {$no} : qsfields]\n";
			foreach ($dataSQL as $item)
			{
				$reffid = $item->RefFormCode;
				$refobjid = $item->RefObjectCode;
				$reffieldid = $item->RefFieldCode;
				$refdisplayid = $item->RefDisplayCode;
				if($reffid && $refobjid && $reffieldid)
				{
					$reffid = $this->getCodeByFID($item->RefFormCode);
					$refobjid = $this->getCodeByObjID($item->RefObjectCode);
					$reffieldid = $this->getCodeByFieldID($item->RefFieldCode);
					if($refdisplayid)
					{
						$refdisplayid = $this->getCodeByFieldID($refdisplayid);
					}
				}

				// remove Filter = \"{$item->Filter}\"\n
				$retval .= "[qss : {$no} : 	qsfields : {$i}]\n
				FieldCode = \"{$item->FieldCode}\"\n
				FieldNo = {$item->FieldNo}\n
				ObjectCode = \"{$item->ObjectCode}\"\n
				FieldName = \"{$item->FieldName}\"\n
				FieldName_en = \"{$item->FieldName_en}\"\n
				FieldType = {$item->FieldType}\n
				DefaultVal = \"{$item->DefaultVal}\"\n	
				RefFieldCode = {$reffieldid}\n
				RefDisplayCode = \"{$refdisplayid}\"\n
				RefLabel = \"{$item->RefLabel}\"\n
				Effect = {$item->Effect}\n
				DataType = {$item->DataType}\n
				ReadOnly = {$item->ReadOnly}\n
				RefFormCode = \"{$reffid}\"\n
				RefObjectCode = \"{$refobjid}\"\n
				InputType = {$item->InputType}\n	
				FieldWidth = {$item->FieldWidth}\n	
				Search = {$item->Search}\n
				TValue = \"{$item->TValue}\"\n
				FValue = \"{$item->FValue}\"\n
				Grid = {$item->Grid}\n
				Required = {$item->Required}\n	
				AFunction = \"{$item->AFunction}\"\n	
				Pattern = \"{$item->Pattern}\"\n
				PatternMessage = \"{$item->PatternMessage}\"\n	

				isRefresh = {$item->isRefresh}\n	
				Regx = \"{$item->Regx}\"\n";
				$i++;

			}
		}
		//echo '<pre>'; print_r($retval); die;
		return $retval;
	}
	public function import($param)
	{
		if(isset($param['qss']) && is_array($param['qss']))
		{
			foreach ($param['qss'] as $module)
			{
				/*Check and Save*/
				$arrForm = $module['qsforms'];
				$fid = $this->getFIDByCode($arrForm['FormCode']);
				if($fid)
				{
					$sql = sprintf('update qsforms set %1$s where FormCode = "%2$s"'
					,$this->arrayToUpdate($arrForm),$fid);
					$this->_o_DB->execute($sql);
				}
				else
				{
					$sql = sprintf('replace into qsforms%1$s'
					,$this->arrayToInsert($arrForm));
					$fid = $this->_o_DB->execute($sql);
				}
				/**/
				foreach ($module['qsobjects'] as $object)
				{
					$objid = $this->getObjIDByCode($object['ObjectCode']);
					if($objid)
					{
						$sql = sprintf('update qsobjects set %1$s where ObjectCode = "%2$s"'
						,$this->arrayToUpdate($object),$objid);
						$this->_o_DB->execute($sql);
					}
					else
					{
						$sql = sprintf('replace into qsobjects%1$s'
						,$this->arrayToInsert($object));
						$objid = $this->_o_DB->execute($sql);
					}
				}
				$sql = sprintf('delete from qsfobjects where FormCode = "%1$s"'
				,$fid);
				$this->_o_DB->execute($sql);
				foreach ($module['qsfobjects'] as $fobject)
				{
					$objid = $this->getObjIDByCode($fobject['ObjectCode']);
					if($objid)
					{
						unset($fobject['FormCode']);
						unset($fobject['ObjectCode']);
						$fobject['FormCode'] = $fid;
						$fobject['ObjectCode'] = $objid;
						$sql = sprintf('replace into qsfobjects%1$s'
						,$this->arrayToInsert($fobject));
						$this->_o_DB->execute($sql);
					}
				}
				if(isset($module['qsfields']) && is_array($module['qsfields']))
				{
					foreach ($module['qsfields'] as $field)
					{
						if($field['RefFormCode'] && $field['RefObjectCode'] && $field['RefFieldCode'])
						{
							$field['RefFormCode'] = $this->getFIDByCode($field['RefFormCode']);
							$field['RefObjectCode'] = $this->getObjIDByCode($field['RefObjectCode']);
							$field['RefFieldCode'] = $this->getFieldIDByCode($field['RefFieldCode'],$field['RefObjectCode']);
							if($field['RefDisplayCode'])
							{
								$field['RefDisplayCode'] = $this->getFieldIDByCode($field['RefDisplayCode'],$field['RefObjectCode']);
							}
						}
						/*if($field['RefFID1'] && $field['RefObjID1'] && $field['RefFieldID1'])
						{
							$field['RefFID1'] = $this->getFIDByCode($field['RefFID1']);
							$field['RefObjID1'] = $this->getObjIDByCode($field['RefObjID1']);
							$field['RefFieldID1'] = $this->getFieldIDByCode($field['RefFieldID1'],$field['RefObjID1']);
							if($field['RefDisplayID1'])
							{
								$field['RefDisplayID1'] = $this->getFieldIDByCode($field['RefDisplayID1'],$field['RefObjID1']);
							}
						}*/
						$objid = $field['ObjectCode'];
						if($objid)
						{
							$fieldid = $this->getFieldIDByCode($field['FieldCode'],$objid);
							unset($field['ObjectCode']);
							$field['ObjectCode'] = $objid;
							if($fieldid)
							{
								$sql = sprintf('update qsfields set %1$s where FieldCode = "%2$s" and ObjectCode="%3$s"'
								,$this->arrayToUpdate($field),$fieldid,$field['ObjectCode']);
								$this->_o_DB->execute($sql);
							}
							else
							{
								$sql = sprintf('replace into qsfields%1$s'
								,$this->arrayToInsert($field));
								$fieldid = $this->_o_DB->execute($sql);
							}
						}
					}
				}
			}
		}
	}
	public function getFIDByCode($code)
	{
		if(isset($this->_arrForm[$code]))
		{
			return $this->_arrForm[$code];
		}
		$sql = sprintf('select FormCode
					from qsforms
					where FormCode = "%1$s"',$this->_o_DB->quote($code));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->_arrForm[$code] = $dataSQL->FormCode;
			return $dataSQL->FormCode;
		}
		return 0;
	}
	public function getObjIDByCode($code)
	{
		if(isset($this->_arrObject[$code]))
		{
			return $this->_arrObject[$code];
		}
		$sql = sprintf('select ObjectCode
					from qsobjects
					where ObjectCode = "%1$s"',$this->_o_DB->quote($code));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->_arrObject[$code] = $dataSQL->ObjectCode;
			return $dataSQL->ObjectCode;
		}
		return 0;
	}
	public function getFieldIDByCode($code,$objid)
	{
		if(isset($this->_arrField[$objid][$code]))
		{
			return $this->_arrField[$objid][$code];
		}
		$sql = sprintf('select FieldCode
					from qsfields
					where ObjectCode = "%1$s" and FieldCode = "%2$s"',$objid, $this->_o_DB->quote($code));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->_arrField[$objid][$code] = $dataSQL->sFieldID;
			return $dataSQL->sFieldID;
		}
		return 0;
	}
	public function getCodeByFID($fid)
	{
		$code = array_search($fid,$this->_arrForm);
		if($code !== false)
		{
			return $code;
		}
		$sql = sprintf('select FormCode
					from qsforms
					where FormCode = "%1$s"',$fid);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->_arrForm[$dataSQL->FormCode] = $fid;
			return $dataSQL->FormCode;
		}
		return 0;
	}
	public function getCodeByObjID($objid)
	{
		$code = array_search($objid,$this->_arrObject);
		if($code !== false)
		{
			return $code;
		}
		$sql = sprintf('select ObjectCode
					from qsobjects
					where ObjectCode = "%1$s"',$objid);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->_arrObject[$dataSQL->ObjectCode] = $objid;
			return $dataSQL->ObjectCode;
		}
		return 0;
	}
	public function getCodeByFieldID($fieldid)
	{
		if(isset($this->_arrField1[$fieldid]))
		{
			return $this->_arrField[$fieldid];
		}
		$sql = sprintf('select FieldCode
					from qsfields
					where FieldCode  = "%1$s"',$fieldid);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->_arrField[$fieldid] = $dataSQL->FieldCode;
			return $dataSQL->FieldCode;
		}
		return 0;
	}
}
?>