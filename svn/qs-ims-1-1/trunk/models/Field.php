<?php
class Qss_Model_Field extends Qss_Model_System_Field
{

	public $intDepartmentID;

	public $szValue;

	public $intIOID;
	
	public $intIFID;

	public $intRefIFID;

	public $intRefIOID;

	public $bEditStatus;

	public $bVisible = true;
	
	public $lookupFilter = 1;
	
	public $FormCode; //instant thì mới có, có thể dùng chung

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
	function a_fGetAllByObjectId ($i_OID)
	{}

	//-----------------------------------------------------------------------
	/**
	* Get all system field
	*
	* @return array
	*/
	public function b_fInit ($ObjectCode,$FieldCode)
	{
		return parent::init($ObjectCode,$FieldCode);
	}

	function getValue ($repBr = true,$user = null)
	{	
		if(is_null($user))
		{
			$user = Qss_Register::get('userinfo');
		}
		$ret = "";
		if($this->bVisible)
		{
			switch ( $this->intFieldType )
			{
				case 1:
				case 2:
				case 3:
				case 8:
				case 9:
				case 12:
					if ( !$repBr || !($this->intInputType == 2 && $this->szTValue))
					{
						$ret = Qss_Lib_Util::htmlToText($this->szValue);
					}
					else
					{
						$ret = Qss_Lib_Util::textToHtml($this->szValue);
					}
					break;
				case 4:
					if ( $this->szValue )
					{
						$ret = Qss_Lib_Date::formatTime($this->szValue);
					}
					break;
				case 5:
					$ret = $this->szValue;
					break;
				case 6:
					$ret = $this->szValue;
					break;
				case 18:
					$ret = $this->szValue;
					break;
				case 10:
					if ( $this->szValue )
					{
						if($repBr)
						{
							$ret = Qss_Lib_Date::mysqltodisplay($this->szValue);
						}
						else
						{
							$ret = Qss_Lib_Date::displaytomysql($this->szValue);
						}
					}
					break;
				case 11:
					if ( !$repBr )
					{
						$ret = $this->szValue?round($this->szValue/1000):$this->szValue; // @note: Khong chap nhan so thap phan
					}
					else
					{
						$cfield = new Qss_Model_CField();
						$curr = $this->intRefIOID;
						if($this->intInputType == 3)//trường hợp lookup
						{
							$curr = $cfield->getPrimary()->Code;
						}
						elseif(!$curr)
						{
							$curr = $cfield->getPrimary()->Code;
							$this->intRefIOID = $curr;
						}
						$currency = $cfield->getDataByCode($curr);
						if ( $currency)
						{
                            $money = round($this->szValue/1000); // @note: Khong chap nhan so thap phan
						    $ret = is_numeric($this->szValue)?number_format($money, (int)$currency->Precision, $currency->DecPoint, $currency->ThousandsSep):$this->szValue;
						}
					}
					break;
				case 13:
					$ret = $this->szValue;
					break;
				case 7:
					$ret = $this->szValue;
					break;

			}
			if($this->RefFormCode && $this->RefObjectCode && $this->RefFieldCode)
			{
				if(!$ret && $this->intRefIOID)
				{
					$dat = $this->getDataById($this->intRefIOID);
					if($dat)
					{
						if(property_exists($dat,'Ref_'.$this->RefFieldCode) 
							&& !is_numeric($dat->{'Ref_'.$this->RefFieldCode})
							&& !is_null($dat->{'Ref_'.$this->RefFieldCode}))//thằng raduio
						{
							$this->setValue($dat->{'Ref_'.$this->RefFieldCode});
						}
						elseif($this->intFieldType == 11)
						{
							$this->setValue($dat->{$this->RefFieldCode}/1000);
						}
						else
						{
							$this->setValue($dat->{$this->RefFieldCode});
						}
						$ret = $this->szValue;
					}
				}
			}
			if(!$ret 
				&& !$this->intIOID 
				&& ($this->szDefaultVal != '') 
				&& $this->szDefaultVal!='KEEP'
				&& $this->szDefaultVal!='AUTO')
			{
				if($this->RefFormCode && $this->RefObjectCode && $this->RefFieldCode)
				{
					$ret = $this->LookupValue($this->szDefaultVal, $this->RefObjectCode, $this->RefFieldCode, $this->RefFormCode, $user);
				}
				elseif($this->intFieldType == 10)
				{
					$ret = date('d-m-Y');
				}
				elseif($this->intFieldType == 4)
				{
					$ret = date('H:i');
				}
				else
				{
					$ret = (string) $this->szDefaultVal;
				}
			}
		}
		return $ret;
	}
	public function getRefIOID($user = null)
	{
		if($this->intInputType != 3 && $this->intInputType != 4)//Giới tính linh hoạt
		{
			return 0;
		}
		if(is_null($user))
		{
			$user = Qss_Register::get('userinfo');
		}
		if($this->RefFormCode 
			&& $this->RefObjectCode 
			&& $this->RefFieldCode
			&& !$this->intRefIOID 
			&& $this->szValue)
		{
			$this->setRefIOID($this->LookupValue($this->szValue, $this->RefObjectCode, $this->RefFieldCode, $this->RefFormCode, $user));
		}
		return $this->intRefIOID;
	} 
	function sz_fGetDisplay ($special = false,$width = 0)
	{
		$ret = "";
		if($this->bVisible)
		{
			if($this->intInputType == 12)
			{
				return $this->szValue;
			}
			/*elseif($this->intInputType == 13)
			{
				//$ret = $this->getCustomButton();
				$ret = sprintf('<button type="button">%1$s</button>',
						$this->szFieldName);
				return $ret;
			}*/
			switch ( $this->intFieldType )
			{
				case 1:
				case 2:
				case 3:
				case 12:
					$ret = Qss_Lib_Util::textToHtml($this->szValue);
					break;
				case 7:
					$ret = $this->szValue?$this->szTValue:$this->szFValue;
					break;
				case 4:
					$ret = ($this->szValue!=='00:00:00')?$this->szValue:'';
					break;
				case 5:
					$ret = $this->szValue;
					break;
				case 6:
					$ret = $this->szValue;
					break;
				case 10:
					if ( $this->szValue )
					{
						$ret = Qss_Lib_Date::mysqltodisplay($this->szValue);
					}
					break;
				case 11:
					$cfield = new Qss_Model_CField();
					$curr = '';
					if($this->intInputType == 3)//trường hợp lookup
					{
						$curr = $cfield->getPrimary()->Code;
					}
					elseif(!$this->intRefIOID )
					{
						$curr = $cfield->getPrimary()->Code;
						$this->intRefIOID = $curr;
					}
					$currency = $cfield->getDataByCode($curr);
					if ( $currency)
					{
					    $money = round($this->szValue / 1000); // khong chap nhan so thap phan
						$ret = is_numeric($this->szValue)?number_format($money, (int)$currency->Precision, $currency->DecPoint, $currency->ThousandsSep):$this->szValue;
						$ret .= $currency->Symbol;
					}
					break;
				case 8:
					$ret = Qss_Lib_Template::FileDown($this->getValue(), $this);
					break;
				case 9:
					if($special)
					{
						$ret = Qss_Lib_Template::ImageUrl($this->intRefIOID,  $this->szValue);
					}
					else
					{
						$h = ((int)$this->szFValue/(int)$this->szTValue)*($width?$width:$this->intFieldWidth);
						$ret = Qss_Lib_Template::Image($this->szValue,$width?$width:$this->intFieldWidth,$h);
					}
					break;
				case 13:
					$ret = Qss_Lib_Template::getBarCode($this->szValue, $this->szTValue, $this->szFValue);
					break;

			}
			if($this->RefFormCode && $this->RefObjectCode && $this->RefFieldCode)//trường hợp lookup
			{
				if(!$ret && $this->intRefIOID)
				{
					$dat = $this->getDataById($this->intRefIOID);
					if($dat)
					{
						$this->setValue($dat->{$this->RefFieldCode});
						$ret = $this->szValue;
					}
				}
			}
			if(!$ret 
				&& !$this->intIOID 
				&& ($this->szDefaultVal != '') 
				&& $this->szDefaultVal!='KEEP' 
				&& $this->szDefaultVal!='AUTO')
			{
				$ret = (string) $this->szDefaultVal;
			}
			if($this->intInputType == 5 || $this->intInputType == 3)
			{
				$jsondata = $this->getJsonRegx();
				if(isset($jsondata[$ret]))
				{
					$ret = $jsondata[$ret];
				} 
				else 
				{
					$ret = '';
				}
			}
		}
		return $ret;
	}

	public function setValue ($value)
	{
		if((!$this->RefFieldCode || $value != '') 
			&& (((string)$this->szValue !== (string)$value && $this->intInputType != 4 && $this->intInputType != 5 && $this->intInputType != 3) 
			|| ($this->szValue !== $value && ($this->intInputType == 4 || $this->intInputType == 3 || $this->intInputType == 5))))
		{
			$this->bEditStatus = true;
		}

		switch ( $this->intFieldType )
		{
			case 11:
				$cfield = new Qss_Model_CField();
				$curr = $this->intRefIOID;
				if($this->intInputType == 3)
				{
					$curr = $cfield->getPrimary()->Code;
				}
				elseif(!$this->intRefIOID)
				{
					$curr = $cfield->getPrimary()->Code;
					$this->intRefIOID = $curr;
				}
				$currency = $cfield->getDataByCode($curr);
				$value = str_replace(' ', '', $value);
				$value =  str_replace($currency->ThousandsSep, '', $value);
				$this->szValue =  str_replace($currency->DecPoint, '.',$value);
				$this->szValue = $this->szValue?($this->szValue * 1000):$this->szValue;
				break;
			case 5:
			case 6:
				$value = str_replace(' ', '', $value);
				$this->szValue =  str_replace(',', '', $value);
				break;
			default:
				$this->szValue = $value;
				if(($this->RefFormCode && $this->RefObjectCode && $this->RefFieldCode 
					&&($this->intInputType == 3 || $this->intInputType == 4)) //giới tính linh hoạt
					|| $this->intFieldType == 14 
					|| $this->intFieldType == 15
					|| $this->intFieldType == 16)
				{
					if(!$this->getRefIOID())	
					{
						$this->szValue = '';
					}
				}
				break;
		}
	}
	
	public function setRefIOID ($value)
	{
		if($this->intRefIOID != $value)
		{
			$this->bEditStatus = true;
		}
		$this->intRefIOID = $value;
	}

	public function getDataById ($id)
	{
		$ret = '';
		$sql = sprintf('select * from %1$s where IOID = %2$d', /* */
			$this->RefObjectCode, 
			$id,$this->RefFieldCode);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL;
	}
	function LookupValue ($val, $refobjcode, $reffieldcode, $refformcode, $user)
	{
		$where = '';
		$where .= $this->getFilters();
		$sql = sprintf('select * 
				from %1$s as v
				inner join qsiforms on qsiforms.IFID=v.IFID_%2$s 
				inner join qsforms on qsforms.FormCode = qsiforms.FormCode
                where qsiforms.deleted=0 and (qsforms.Type=1 or qsiforms.DepartmentID in (%4$s))
                and %3$s = %5$s %6$s', 
			$refobjcode,
			$refformcode,
			$reffieldcode,
			(int)$this->intDepartmentID . ',' . ($user?$user->user_dept_list:1),
			$this->_o_DB->quote($val),
			$where);
			//echo $sql.'<br>';
		$dataSQL = $this->_o_DB->fetchOne($sql);
		$ret = '';
		if($dataSQL)
		{
			$ret = $dataSQL->IOID;
		}
		return $ret;
	}

	function getDataByRef ($reffield)
	{
		$ret = "";
		if ( !$this->RefFieldCode)
		return;
		$sql = sprintf('select * from %2$s as v
                                inner join qsiforms on qsiforms.IFID = v.IFID_%3$s 
                                where deleted=0 and v.IOID=%4$d',/* */
			$reffield->RefFieldCode,
			$this->RefObjectCode,
			$this->RefFormCode,
			$reffield->intIOID);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL;
	}
	function ComboBox (Qss_Model_UserInfo $user = null,$bMain = true,$dialog = false,$json)
	{
		$namespce = '';
		if($dialog)
		{
			$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
		}
		$onchangefunction = sprintf('%1$s%3$s(this,%2$s)',
					$namespce,
					$this->isRefresh,
					$bMain?'rowEditRefresh':'rowObjectEditRefresh');
		$elename = sprintf('%1$s_%2$s', $this->ObjectCode, $this->FieldCode);
		$selected = $this->getValue();
		$a_Combo = array('' => '');
		foreach ($json as $key=>$val)
		{
			$a_Combo[$key] = $val;
		}
		return Qss_Lib_Template::ComboBox($elename, $json, $selected, $this->intFieldWidth,$onchangefunction,1,$this->bReadOnly,array());
	}
	/**
	 *
	 * @param $field
	 * @param $user
	 * @return unknown_type
	 */
	function DBComboBox (Qss_Model_UserInfo $user = null,$bMain = true,$dialog = false)
	{
		$namespce = '';
		if($dialog)
		{
			$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
		}
		$onchangefunction = sprintf('%1$s%3$s(this,%2$s)',
					$namespce,
					$this->isRefresh,
					$bMain?'rowEditRefresh':'rowObjectEditRefresh');
		$elename = sprintf('%1$s_%2$s', $this->ObjectCode, $this->FieldCode);
		$selected = $this->getRefIOID();
		$dataSQL = $this->a_fGetReference($user);
		$a_Combo = array(0 => '');
		$refRights = Qss_Lib_System::getFormRights($this->RefFormCode, $this->_user->user_group_list);
		$formObject = Qss_Lib_System::getFormObject($this->RefFormCode,$this->RefObjectCode);
		if($refRights & 1 && $formObject->Main)
		{
			$a_Combo['null'] = $this;
		}
		$disabled = array();
		$selectedlft = 0;
		$selectedrgt = 0;
		foreach ($dataSQL as $data)
		{
			$level = (int)@$data->level;
			$level = ($level > 0)?($level -1):0;
			$a_Combo[$data->IOID] = str_repeat('&nbsp;&nbsp;',$level) . $data->Name . (isset($data->DisplayName)?" ({$data->DisplayName})":'');
			if($this->ObjectCode == $this->RefObjectCode && $data->IOID == $this->intIOID)
			{
				$selectedlft = @$data->lft;
				$selectedrgt = @$data->rgt;
			}
			if($selectedlft && $selectedrgt && $data->lft >= $selectedlft && $data->rgt <= $selectedrgt)
			{
				$disabled[] = $data->IOID;
			}
		}
		/*if(!$this->intIOID && $this->szDefaultVal == 'AUTO' && !$selected && count($dataSQL) == 1)
		{
			$selected = $dataSQL[0]->IOID;
		}*/
		//echo $dataSQL->IOID.'--';
		return Qss_Lib_Template::ComboBox($elename, $a_Combo, $selected, $this->intFieldWidth,$onchangefunction,1,$this->bReadOnly,$disabled);
	}
	function getSelectBox ()
	{
		$elename = sprintf('%1$s_%2$d', $this->ObjectCode, $this->FieldCode);
		$value = $this->intRefIOID;
		return sprintf('<div class="select-popup"><span onclick="popupSelect(\'%5$d\',this,%6$d)" refid="%1$s">%2$s</span></div><input id="%1$s" name="%1$s" type="hidden" value="%3$s">',
		$elename,
		(!$this->sz_fGetDisplay())?'Bấm để chọn':$this->sz_fGetDisplay(),
		$value,
		$this->RefFormCode,
		$this->FieldCode,
		$this->isRefresh,
		$this->RefObjectCode);
	}
	function getAttrBox ()
	{
		$elename = sprintf('%1$s_%2$d', $this->ObjectCode, $this->FieldCode);
		$value = $this->intRefIOID;
		if($this->bReadOnly)
		{
			return sprintf('<div class="select-popup readonly"><span >%2$s</span></div><input id="%1$s" name="%1$s" type="hidden" value="%3$s">',
			$elename,
			(!$this->sz_fGetDisplay())?'Bấm để chọn':$this->sz_fGetDisplay(),
			$value,
			$this->RefFormCode,
			$this->FieldCode,
			$this->isRefresh,
			$this->RefObjectCode);
		}
		else
		{
			return sprintf('<div class="select-popup"><span onclick="popupAttr(\'%5$d\',this,%6$d)" refid="%1$s">%2$s</span></div><input id="%1$s" name="%1$s" type="hidden" value="%3$s">',
			$elename,
			(!$this->sz_fGetDisplay())?'Bấm để chọn':$this->sz_fGetDisplay(),
			$value,
			$this->RefFormCode,
			$this->FieldCode,
			$this->isRefresh,
			$this->RefObjectCode);
		}
	}
	public function getMaxValue($formcode,$ifid)
	{
		$sql = sprintf('select max(%2$s) as max from %1$s', 
				$this->ObjectCode,
				$this->FieldCode);
		if($ifid)
		{
			$sql .= sprintf(' where IFID_%1$s= %2$d', 
				$formcode,
				$ifid);
		}
		$data = $this->_o_DB->fetchOne($sql);
		return $data?$data->max:0;
	}
	public function getMaxValueByCode($code)
	{
		$sql = sprintf('select %2$s as Data from %1$s
			where %2$s like %3$s
			order by Cast(SUBSTRING_INDEX(Data,\'-\', -1) as signed) desc limit 1', 
				$this->ObjectCode,
				$this->FieldCode,
				$this->_o_DB->quote($code. '%'));
		$data = $this->_o_DB->fetchOne($sql);
		return $data?$data->Data:0;
	}
	/**
	 *
	 * @param Qss_Model_UserInfo $user
	 * @return array
	 */
	public function a_fGetReference (Qss_Model_UserInfo $user = null,$selected = null)
	{
		$treeselect = '';
		$order = array();
		if($user === null)
		{
			$user = Qss_Register::get('userinfo');
		}
		$where = '';
		$form = new Qss_Model_System_Form();
		$form->b_fInit($this->RefFormCode);
		if($form->i_Type == 2)
		{
			$where = sprintf(' and qsiforms.DepartmentID in (%1$s) ',$user->user_dept_list);	
		}
		$where .= $this->getFilters();
		$join = '';
	
		$sql = sprintf('select * from qsfields
						inner join qsobjects on qsobjects.ObjectCode =  qsfields.ObjectCode
						 where qsfields.ObjectCode = "%1$s" and RefObjectCode = "%1$s" and Effect = 1',// and RefFieldCode = "%2$s"
						$this->RefObjectCode);//,$this->RefFieldCode
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL && !@$dataSQL->NoTree)
		{
			$treeselect = sprintf(' ,lft, rgt,(SELECT count(*) FROM %1$s as u 
									WHERE u.lft<=v.lft and u.rgt >= v.rgt 
									%2$s) as level ',
						$this->RefObjectCode,
						$where);
			$order[] = 'lft';
		}
		else
		{
			$order[] = 'Name';
		}
		$reffield = new Qss_Model_System_Field();
		$reffield->init($this->RefObjectCode,$this->RefFieldCode);
		if($this->RefDisplayCode)
		{
			$refname = $this->RefDisplayCode;
			if(strpos($refname,','))
			{
				$refname = str_ireplace(',',',\'-\',', $refname);
				$refname = sprintf('concat(%1$s)',$refname);
			} 
			$sql = sprintf('select v.IOID, v.%4$s as Name,%6$s as DisplayName
					%8$s
					from %9$s as v 
					inner join qsiforms on v.IFID_%1$s = qsiforms.IFID
					where 1=1
					%7$s and qsiforms.deleted = 0',
			$this->RefFormCode,
			$this->RefObjectCode,
			0,//Qss_Lib_Const::$DATABASE_TABLES[$reffield->intFieldType-1],
			($reffield->getJsonRegx())?'Ref_'.$this->RefFieldCode:$this->RefFieldCode,
			$this->RefFieldCode,
			$refname,
			$where,
			$treeselect,
			$this->RefObjectCode);
		}
		else
		{
			$sql = sprintf('select distinct v.IOID, v.%5$s as Name
					%8$s
					from  %9$s as v 
					inner join qsiforms on v.IFID_%1$s = qsiforms.IFID
					where 1=1
					%7$s and qsiforms.deleted = 0',
			$this->RefFormCode,
			$this->RefObjectCode,
			0,
			$this->RefFieldCode,
			($reffield->getJsonRegx())?'Ref_'.$this->RefFieldCode:$this->RefFieldCode,
			0,
			$where,
			$treeselect,
			$this->RefObjectCode);
		}
		if($this->bReadOnly)
		{
			$sql .= sprintf(' order by field(v.IOID,%1$d) desc ', $this->intRefIOID);
			$sql .= ' limit 1';
		}
		else
		{
			$sql .= sprintf(' order by %1$s',implode(',', $order)); //order by Name limit 100';
			$sql .= ' limit ' . Qss_Lib_Const_Display::COMBO_SELECT_LIMIT;
		}
		//echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function getLookUp ($search = '')
	{
		$user = Qss_Register::get('userinfo');
		$reffield = new Qss_Model_System_Field();
		$reffield->init($this->RefObjectCode,$this->RefFieldCode);
		$form = new Qss_Model_System_Form();
		$form->b_fInit($this->RefFormCode);
		$where = '';
		if($form->i_Type == 2)
		{
			$where = sprintf(' and qsiforms.DepartmentID in (%1$s) ',$user->user_dept_list);
		}
		$where .= $this->getFilters();
		//$object = new Qss_Model_System_Object();
		//$object->v_fInit($this->ObjectCode);
		//check if tree
		$treeselect = '';
		$order = array();
		$sql = sprintf('select * from qsfields
						inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
						where qsfields.ObjectCode = "%1$s" and RefObjectCode = "%1$s" and RefFieldCode = "%2$s"',
		$this->RefObjectCode,$this->RefFieldCode);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL && !@$dataSQL->NoTree)
		{
			$treeselect = sprintf(',lft,rgt ,(SELECT count(*) FROM %1$s as u 
									WHERE u.lft<=v.lft and u.rgt >= v.rgt 
									%2$s) as level ',
						$this->RefObjectCode,
						$where);
			$order[] = 'lft';
		}
		else
		{
			$order[] = 'Name';
		}
		if($this->RefDisplayCode)
		{
			$refname = $this->RefDisplayCode;
			if(strpos($refname,','))
			{
				$refname = str_ireplace(',',',\' - \',', $refname);
				$refname = sprintf('concat(%1$s)',$refname);
			} 
			if($search)
			{
				$where .= sprintf(' and (v.%2$s like %1$s or %3$s like %1$s) ',
					$this->_o_DB->quote('%'.$search.'%'),
					$reffield->FieldCode,
					$refname);
			}
			$sql = sprintf('select v.IOID, v.%6$s as Name,%7$s as DisplayName
					%4$s
					from %2$s as v
					inner join qsiforms on qsiforms.IFID = v.IFID_%1$s
					where deleted = 0
					%3$s 
					order by %5$s
					limit %8$d',
			$this->RefFormCode,
			$this->RefObjectCode,
			$where,
			$treeselect,
			implode(',', $order),
			($reffield->getJsonRegx())?'Ref_'.$this->RefFieldCode:$this->RefFieldCode,
			$refname,
			Qss_Lib_Const_Display::LISTBOX_SELECT_LIMIT);
		}
		else
		{
			if($search)
			{
				$where .= sprintf(' and v.%2$s like %1$s ',
					$this->_o_DB->quote('%'.$search.'%'),
					$reffield->FieldCode);
			}
			$sql = sprintf('select v.IOID, v.%4$s as Name
					%6$s
					from %2$s as v
					inner join qsiforms on qsiforms.IFID = v.IFID_%1$s
					where deleted=0 
					%5$s 
					order by %7$s
					limit %9$d',
			$this->RefFormCode,
			$this->RefObjectCode,
			$reffield->FieldCode,
			($reffield->getJsonRegx())?'Ref_'.$reffield->FieldCode:$reffield->FieldCode,
			$where,
			$treeselect,
			implode(',', $order),
			$this->RefObjectCode,
			Qss_Lib_Const_Display::LISTBOX_SELECT_LIMIT);
		}
		return $this->_o_DB->fetchAll($sql);
	}
	public function searchMail($user,$search = '')
	{
		$where = '';
		if($search)
		{
			$where = sprintf(' Data like %1$s',$this->_o_DB->quote('%'.$search.'%'));
		}
		$sql = sprintf('select qsiforms.IFID,qsrecobjects.IOID,qsforms.Name,Data from datmail
				inner join qsrecobjects on ID = RecordID and DataFieldType = 12
				inner join qsrecforms on qsrecforms.IOID = qsrecobjects.IOID
				inner join qsiforms on qsiforms.IFID = qsrecforms.IFID
				inner join qsforms on qsiforms.FormCode = qsforms.FormCode
				inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
				where Rights&63<>0 and qsuserforms.GroupID in(%3$s) and qsiforms.deleted = 0 and %1$s limit 50'
				,$where,$user->user_dept_id . ',' . $user->user_dept_list,$user->user_group_list);
				return $this->_o_DB->fetchAll($sql);
	}
	public function setCurrency($currency)
	{
		if(!$this->szValue)
		{
			$this->szValue = $currency;
		}
		if($currency != $this->szValue)
		{
			$this->szValue = $currency;
			$this->bEditStatus = true;
		}
	}
	function setDefaultLookupValue ($user)
	{
		$defaultlookup = Qss_Register::get('defaultlookup');
		if(is_array($defaultlookup) && isset($defaultlookup[$this->FieldCode]))
		{
			$this->setValue($defaultlookup[$this->FieldCode]);
			return;
		}
		if(!is_array($defaultlookup))
		{
			$defaultlookup = array();
		}
		$where = '';
		$where .= $this->getFilters();
		$sql = sprintf('select * from %1$s as v
				inner join qsiforms on qsiforms.IFID = IFID_%2$s
                inner join qsforms on qsiforms.FormCode = qsforms.FormCode
				where qsiforms.deleted=0 and (qsforms.Type=1 or qsiforms.DepartmentID in (%3$s))
                
				%4$s
				LIMIT 1', 
		$this->RefObjectCode,
		$this->RefFormCode,
		(int)$this->intDepartmentID . ',' . ($user?$user->user_dept_list:1),
		$where);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		$ret = '';
		if($dataSQL)
		{
			$ret = $dataSQL->IOID;
		}
		$defaultlookup[$this->FieldCode] = $ret;
		Qss_Register::set('defaultlookup', $defaultlookup);
		$this->setValue($ret);
	}
	/*
	 * Get value of lookup
	 */
	public function getRefOption($ifid,$ioid)
	{
		$retval = array();
		if($ioid)
		{
			$sql = sprintf('select * from %2$s as v
				inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
				where IOID = %1$d and deleted = 0',
				$ioid,
				$this->RefObjectCode,
				$this->RefFormCode);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$retval['ioid'] = $dataSQL->IOID;
				$retval['name'] = $dataSQL->{$this->RefFieldCode};
			}
		}
		else 
		{
			$sql = sprintf('select * from %2$s as v
				inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
				where IFID_%3$s = %1$d and deleted = 0',
				$ifid,
				$this->RefObjectCode,
				$this->RefFormCode);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$retval['ioid'] = $dataSQL->IOID;
				$retval['name'] = $dataSQL->{$this->RefFieldCode};
			}
		}
		return $retval;
	}
	public function moveFile()
	{
		$ret = $this->getValue();
		$filename = QSS_DATA_DIR . "/tmp/" . $this->getValue();
		if(file_exists($filename) && $this->getValue())
		{
			$ret = basename($filename);
			$destfile = QSS_DATA_DIR . "/documents/" . $ret;
			copy($filename, $destfile);
			Qss_Lib_Template::PictureResize($destfile, $this->szTValue, $this->szFValue);
			unlink($filename);
		}
		return $ret;
	}
	function CheckBox (Qss_Model_UserInfo $user = null,$bMain = true,$dialog = false,$json)
	{
		$retval = '';
		$namespce = '';
		if($dialog)
		{
			$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
		}
		$onchangefunction = sprintf('%1$s%3$s(this,%2$s)',
					$namespce,
					$this->isRefresh,
					$bMain?'rowEditRefresh':'rowObjectEditRefresh');
		$elename = sprintf('%1$s_%2$s', $this->ObjectCode, $this->FieldCode);
		$arrSelected = array();
		foreach ($json as $key=>$val)
		{
			$arrSelected[$key] = ($this->getValue() & $key);
		}
		return Qss_Lib_Template::CheckBoxList($elename, $json, $arrSelected,$onchangefunction,$this->bReadOnly);
	}
	public function checkLookup($user = null)
	{
		if(is_null($user))
		{
			$user = Qss_Register::get('userinfo');
		}
		$where = '';
		$where .= $this->getFilters();
		$sql = sprintf('select 1
				from %1$s as v
				inner join qsiforms on qsiforms.IFID=v.IFID_%2$s 
				inner join qsforms on qsforms.FormCode = qsiforms.FormCode
                where qsiforms.deleted=0 and (qsforms.Type=1 or qsiforms.DepartmentID in (%4$s))
                and IOID = %5$d %6$s', 
			$this->RefObjectCode,
			$this->RefFormCode,
			$this->RefFieldCode,
			(int)$this->intDepartmentID . ',' . ($user?$user->user_dept_list:1),
			$this->intRefIOID,
			$where);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?true:false;
	}
	public function getUpper()
	{
		$sql = sprintf('select * from %1$s 
						where rgt = (select lft-1 from %1$s where IOID = %2$d)'
					,$this->ObjectCode
					,$this->intIOID);
		return $this->_o_DB->fetchOne($sql);
	}
	//đương nhiên là tree
	public function getOrderLookUp ($search = '')
	{
		$user = Qss_Register::get('userinfo');
		$reffield = new Qss_Model_System_Field();
		$reffield->init($this->RefObjectCode,$this->RefFieldCode);
		$form = new Qss_Model_System_Form();
		$form->b_fInit($this->RefFormCode);
		$where = '';
		if($form->i_Type == 2)
		{
			$where = sprintf(' and qsiforms.DepartmentID in (%1$s) ',$user->user_dept_list);
		}
		$where .= $this->getFilters();
		$treeselect = sprintf(' and ifnull(Ref_%1$s,0) = %2$d and IOID <> %3$d'
					,$this->FieldCode
					,$this->intRefIOID
					,$this->intIOID);
		$order = array('lft');
		if($this->RefDisplayCode)
		{
			$refname = $this->RefDisplayCode;
			if(strpos($refname,','))
			{
				$refname = str_ireplace(',',',\' - \',', $refname);
				$refname = sprintf('concat(%1$s)',$refname);
			} 
			if($search)
			{
				$where .= sprintf(' and (v.%2$s like %1$s or %3$s like %1$s) ',
					$this->_o_DB->quote('%'.$search.'%'),
					$reffield->FieldCode,
					$refname);
			}
			$sql = sprintf('select v.IOID, v.%6$s as Name,%7$s as DisplayName
					from %2$s as v
					inner join qsiforms on qsiforms.IFID = v.IFID_%1$s
					where deleted = 0
					%3$s 
					%4$s
					order by %5$s
					limit %8$d',
			$this->RefFormCode,
			$this->RefObjectCode,
			$where,
			$treeselect,
			implode(',', $order),
			($reffield->getJsonRegx())?'Ref_'.$this->RefFieldCode:$this->RefFieldCode,
			$refname,
			Qss_Lib_Const_Display::LISTBOX_SELECT_LIMIT);
		}
		else
		{
			if($search)
			{
				$where .= sprintf(' and v.%2$s like %1$s ',
					$this->_o_DB->quote('%'.$search.'%'),
					$reffield->FieldCode);
			}
			$sql = sprintf('select v.IOID, v.%4$s as Name
					from %2$s as v
					inner join qsiforms on qsiforms.IFID = v.IFID_%1$s
					where deleted=0 
					%5$s 
					%6$s
					order by %7$s
					limit %9$d',
			$this->RefFormCode,
			$this->RefObjectCode,
			$reffield->FieldCode,
			($reffield->getJsonRegx())?'Ref_'.$reffield->FieldCode:$reffield->FieldCode,
			$where,
			$treeselect,
			implode(',', $order),
			$this->RefObjectCode,
			Qss_Lib_Const_Display::LISTBOX_SELECT_LIMIT);
		}
		return $this->_o_DB->fetchAll($sql);
	}
}
?>