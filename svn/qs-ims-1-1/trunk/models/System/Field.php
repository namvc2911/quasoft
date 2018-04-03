<?php
class Qss_Model_System_Field extends Qss_Model_Abstract
{
	public $FieldCode;
	
	public $ObjectCode;

	public $intFieldNo;

	public $szFieldName;

	public $intFieldType;

	public $szDefaultVal;

	public $RefFieldCode;

	public $RefDisplayCode;

	public $szRefLabel;//tỷ lệ độ rộng cột trên grid

	public $bEffect;

	public $intDataType;

	public $bReadOnly;

	public $RefFormCode;

	public $RefObjectCode;

	public $intInputType;

	public $intFieldWidth;

	public $bSearch;

	public $szTValue;

	public $szFValue;

	public $bGrid;//1 hiển thị trên pc, 2 hiển thị mobile

	public $bRequired;

	public $AFunction;

	public $szPattern;

	public $szPatternMessage;

	public $szRegx;
	
	public $jsonData = false;
	
	public $jsonCell = false;
	
	public $isRefresh;
	
	public $bUnique;//1 unique phân biệt 2 có phân biệt hoa thường
	
	public $arrFilters = array();
	
	public $szStyle;
	
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}

	//-----------------------------------------------------------------------
	/**
	* Get all by object id
	*
	* @return array
	*/
	function getFieldByObjectCode($ObjectCode)//a_fGetAllByObjectId 
	{
		$sql = sprintf('select qsfields.*,t.Title from qsfields  
						left join 
						(select qsuiboxs.Title,qsuiboxfields.FieldCode, qsuigroups.ObjectCode,BoxNo
						from qsuiboxfields
						inner join qsuiboxs on qsuiboxs.UIBID = qsuiboxfields.UIBID
						inner join qsuigroups on qsuigroups.UIGID = qsuiboxs.UIGID 
						where qsuigroups.ObjectCode = %1$s) as t on t.FieldCode = qsfields.FieldCode
						where qsfields.ObjectCode = %1$s 
						order by BoxNo,Effect desc,FieldNo',
				$this->_o_DB->quote($ObjectCode));
		return $this->_o_DB->fetchAll($sql);
	}
	
	function  getFieldByFormCode($FormCode) //a_fGetAllByFID
	{
		$sql = sprintf('select * from qsfields 
						inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
						inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode
						where FormCode = %1$s order by qsfields.ObjectCode, FieldNo',
				$this->_o_DB->quote($FormCode));
		return $this->_o_DB->fetchAll($sql);
	}

	//-----------------------------------------------------------------------
	/**
	* Get all system field
	*
	* @return array
	*/
	public function  init($ObjectCode,$FieldCode)//b_fInit
	{
		$dataSQL = $this->getByCode($ObjectCode,$FieldCode);
		if ( $dataSQL )
		{
			$this->FieldCode = $dataSQL->FieldCode;
			$this->ObjectCode  = trim($dataSQL->ObjectCode);
			$this->intFieldNo = trim($dataSQL->FieldNo);
			$this->szFieldName = trim($dataSQL->FieldName);
			$this->szTValue = $dataSQL->TValue;
			$this->szFValue = $dataSQL->FValue;
			$this->szRegx = $dataSQL->Regx;
			$this->szPattern = $dataSQL->Pattern;
			$this->szPatternMessage = $dataSQL->PatternMessage;
			$this->intInputType = $dataSQL->InputType;
			$this->intFieldType = $dataSQL->FieldType;
			$this->bGrid = $dataSQL->Grid;
			$this->bEffect = $dataSQL->Effect;
			$this->bSearch = $dataSQL->Search;
			$this->AFunction = $dataSQL->AFunction;
			$this->bReadOnly = $dataSQL->ReadOnly;
			$this->bRequired = $dataSQL->Required;
			$this->RefFormCode = $dataSQL->RefFormCode;
			$this->RefObjectCode = $dataSQL->RefObjectCode;
			$this->RefFieldCode = $dataSQL->RefFieldCode;
			$this->RefDisplayCode = $dataSQL->RefDisplayCode;
			$this->intFieldWidth = $dataSQL->FieldWidth;
			$this->szRefLabel = $dataSQL->RefLabel;
			$this->szDefaultVal = $dataSQL->DefaultVal;
			$this->bUnique = $dataSQL->Unique;
			//$this->szFilter = $dataSQL->Filter;
			//	$this->intRefIOID = $dataSQL->RefIOID;
			$this->isRefresh = $dataSQL->isRefresh;
			$this->szStyle = $dataSQL->Style;
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Save system field
	 *
	 * @return boolean
	 */
	public function b_fSave ($params)
	{
		$sql = sprintf('select * from qsfields where FieldCode = %1$s and ObjectCode = "%2$s" and FieldCode != "%3$s"',/* */
		$this->_o_DB->quote($params['szFieldID']),/* */
		$params['intObjID'],/* */
		$params['intFieldID']);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			return false;
		}
		$grid = bindec(sprintf('%1$d%2$d%3$d%4$d%5$d',
				(@$params['multiEdit'])?1:0,
				(@$params['eMobile'])?1:0,
				(@$params['eGrid'])?1:0,
				(@$params['Mobile'])?1:0,
				(@$params['bGrid'])?1:0));
		$data = array('FieldCode'=>$params['szFieldID'],
					'ObjectCode'=>$params['intObjID'],
					'FieldNo'=>$params['intFieldNo'],
					'FieldName'=>$params['szFieldName'],
					'FieldType'=>$params['intFieldType'],
					'DefaultVal'=>$params['szDefaultVal'],
					'Unique'=>(int)@$params['bUnique'],
					'RefFieldCode'=>$params['intRefFieldID'],
					'RefDisplayCode'=>$params['intRefDisplayID'],
					'Regx'=>trim($params['szRegx']),
					'Effect'=>(int)@$params['bEffect'],
					'ReadOnly'=>(int)@$params['bReadOnly'],
					'RefFormCode'=>$params['intRefFID'],
					'RefObjectCode'=>$params['intRefObjID'],
					'InputType'=>$params['intInputType'],
					'FieldWidth'=>$params['intFieldWidth'],
					'RefLabel'=>$params['szRefLabel'],
					'Search'=>(int)@$params['bSearch'],
					'Required'=>(int)@$params['bRequired'],
					'Grid'=>$grid,
					'TValue'=>$params['szTValue'],
					'FValue'=>$params['szFValue'],
					'AFunction'=>$params['AFunction'],
					'Pattern'=>$params['szPattern'],
					'PatternMessage'=>$params['szPatternMessage'],
					//'Filter'=>$params['szFilter'],
					'isRefresh'=>(int)@$params['bRefresh'],
					'Style'=>$params['szStyle']
		);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['FieldName_'.$item->Code] = @$params['szFieldName_'.$item->Code];
		}
		if($params['intFieldID'])
		{
			$sql=sprintf('update qsfields set %1$s where FieldCode = "%2$s" and ObjectCode = "%3$s"',
			$this->arrayToUpdate($data),
			$params['intFieldID'],
			$params['intOID']);
		}
		else
		{
			$sql=sprintf('insert into qsfields %1$s',
			$this->arrayToInsert($data));
		}
		$this->_o_DB->execute($sql);
		$dataSQL = $this->getStepRights($params['intObjID'], $params['szFieldID']);
		foreach ($dataSQL as $item)
		{
			$steprights = bindec(sprintf('%1$d%2$d%3$d',
				(@$params['rights_U_'.$item->SID])?1:0,
				(@$params['rights_R_'.$item->SID])?1:0,
				(@$params['rights_C_'.$item->SID])?1:0));
			$sql = sprintf('replace into qssteprights(SID,FieldCode,ObjectCode,Rights,GroupID)
					values(%1$d,%2$s,%3$s,%4$d,0)'
				,$item->SID
				,$this->_o_DB->quote($params['szFieldID'])
				,$this->_o_DB->quote($params['intObjID'])
				,$steprights);
			$this->_o_DB->execute($sql);
		}
		return true;
	}

	/**
	 * Delete system field
	 *
	 * @return boolean
	 */
	public function b_fDelete ()
	{}
	public function getByCode($ObjectCode,$FieldCode)//getById
	{
		$sql = sprintf('select * from qsfields where FieldCode=%1$s and ObjectCode=%2$s', 
				$this->_o_DB->quote($FieldCode),
				$this->_o_DB->quote($ObjectCode));
		return $this->_o_DB->fetchOne($sql);
	}
	public function getObjectField($objectcode,$fieldcode)
	{
		$sql = sprintf('select * from qsfields
					inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
					where qsfields.ObjectCode=%1$s and FieldCode=%2$s', 
					$this->_o_DB->quote($objectcode),
					$this->_o_DB->quote($fieldcode));
		return $this->_o_DB->fetchOne($sql);
	}

	public function getJsonRegx()
	{
		if($this->szRegx && $this->jsonData === false)
		{
			$this->jsonData = (array)Qss_Json::decode($this->szRegx);
		}
		return $this->jsonData;
	}
	public function getJsonCell()
	{
		if($this->szFValue && $this->jsonCell === false)
		{
			$this->jsonCell = (array)Qss_Json::decode($this->szFValue);
		}
		return $this->jsonCell;
	}
	public function getFilters()
	{
		if(count($this->arrFilters))
		{
			return sprintf('and (%1$s)', implode(' and ', $this->arrFilters));
		}
		else 
		{
			return '';
		}
	}
	public function delete($objectcode,$fieldcode)
	{
		$sql = sprintf('delete from qsfields
					where qsfields.ObjectCode=%1$s and FieldCode=%2$s', 
					$this->_o_DB->quote($objectcode),
					$this->_o_DB->quote($fieldcode));
		return $this->_o_DB->execute($sql);
	}
	
	public function getStepRights($objectcode,$fieldcode)
	{
		$sql = sprintf('select qssteprights.*,qsworkflowsteps.*,qsforms.FormCode, qsforms.Name as FName 
						from qsworkflows 
						inner join qsworkflowsteps on qsworkflows.WFID = qsworkflowsteps.WFID
						inner join qsforms on qsforms.FormCode = qsworkflows.FormCode
						inner join qsfobjects on qsfobjects.FormCode = qsforms.FormCode
						left join qssteprights on qsworkflowsteps.SID = qssteprights.SID
						and qssteprights.ObjectCode = qsfobjects.ObjectCode and qssteprights.FieldCode=%2$s
						where qsforms.Effected = 1 and qsworkflows.Actived = 1 and qsfobjects.ObjectCode=%1$s
						order by qsforms.FormCode,qsworkflowsteps.OrderNo', 
					$this->_o_DB->quote($objectcode),
					$this->_o_DB->quote($fieldcode));
		return $this->_o_DB->fetchAll($sql);
	}
}
?>