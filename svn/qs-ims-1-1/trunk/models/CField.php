<?php
class Qss_Model_CField extends Qss_Model_Field
{
	public static $PRIMARY;
	public static $CURRENCIES;
	
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
		$CURRENCIES = array();
	}
	
	function getValue ($repBr = true,$user = null)
	{
		$ret = $this->szValue;
		if(!$ret && $this->intRefIOID)
		{
			$dat = $this->getDataById($this->intRefIOID);
			if($dat)
			{
				$this->setValue($dat->Code);
				$ret = $this->szValue;
			}
		}
		if(!$ret && !$this->intIOID && ($this->szDefaultVal !== '') && $this->szDefaultVal!='KEEP')
		{
			if($this->szDefaultVal == 'AUTO')
			{
				$ret = $this->PrimaryDisplay();
			}
			else
			{
				$ret = (string) $this->szDefaultVal;
			}
		}
		return $ret;
	}

	function sz_fGetDisplay ($special = false,$width = 0)
	{
		$ret = $this->szValue;
		if(!$ret && $this->intRefIOID)
		{
			$dat = $this->getDataById($this->intRefIOID);
			if($dat)
			{
				$this->setValue($dat->Code);
				$ret = $this->szValue;
			}
		}
		if(!$ret && !$this->intIOID && ($this->szDefaultVal !== '') && $this->szDefaultVal!='KEEP')
		{
			if($this->szDefaultVal == 'AUTO')
			{
				$ret = $this->PrimaryDisplay();
			}
			else
			{
				$ret = (string) $this->szDefaultVal;
			}
		}
		return $ret;
	}
	public function getRefIOID($user = null)
	{
		if(is_null($user))
		{
			$user = Qss_Register::get('userinfo');
		}
		if(!$this->intRefIOID && $this->szValue)
		{
			$this->setRefIOID($this->LookupValue($this->szValue));
		}
		return $this->intRefIOID;
	}	
	public function getDataById ($id)
	{
		$ret = '';
		$sql = sprintf('select * from qscurrencies where Enabled = 1 and CID = %1$d', /* */
			$id);
		//echo $sql; ;
		return  $this->_o_DB->fetchOne($sql);
	}

	/**
	 *
	 * @param Qss_Model_UserInfo $user
	 * @return array
	 */
	public function a_fGetReference (Qss_Model_UserInfo $user = null,$selected = null)
	{
		$sql = sprintf('select * from qscurrencies where Enabled = 1');
		return $this->_o_DB->fetchAll($sql);
	}

	function b_fSave ($objectcode)
	{
		if($this->bEditStatus)
		{
			$logs = Qss_Register::get('Logs');
			$logs[$this->FieldCode] = $this->szFieldName;
			Qss_Register::set('Logs',$logs);
		}
		return $this->bEditStatus ;
	}

	function LookupValue ($val,$refobjcode=0, $reffieldcode=0, $refformcode=0, $user=null)
	{
		$sql = sprintf('select * from qscurrencies where Enabled = 1 and Code = %1$s ', $this->_o_DB->quote($val));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->CID:0;
	}

	
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
		$sql = sprintf('select * from qscurrencies where Enabled = 1');
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$a_Combo = array('' => '');
		foreach ($dataSQL as $data)
		{
			$a_Combo[$data->Code] = $data->Code;
		}
		return Qss_Lib_Template::ComboBox($elename, $a_Combo, $this->getValue(), $this->intFieldWidth,$onchangefunction,1,$this->bReadOnly);
	}
	function getSelectBox ()
	{
		$elename = sprintf('%1$s_%2$d', $this->ObjectCode, $this->FieldCode);
		$value = $this->intRefIOID;
		return sprintf('<div class="select-popup"><span onclick="popupSelect(%5$d,this,%6$d)" refid="%1$s">%2$s</span></div><input id="%1$s" name="%1$s" type="hidden" value="%3$s">',
		$elename,(!$this->sz_fGetDisplay())?'Bấm để chọn':$this->sz_fGetDisplay(),$value,$this->RefFormCode,$this->FieldCode,$this->isRefresh,$this->RefObjectCode);
	}
	
	public function getLookUp ($search = '')
	{
		$where = '';
		if($search)
		{
			$where = sprintf(' and Code like %1$s',$this->_o_DB->quote('%'.$search.'%'));
		}
		$sql = sprintf('select * from qscurrencies where Enabled = 1 %1$s
						order by Code limit 100',$where); 
		return $this->_o_DB->fetchAll($sql);
	}
	public function PrimaryValue()
	{
		if(self::$PRIMARY)
		{
			return self::$PRIMARY->CID;
		}
		$sql = sprintf('select * from qscurrencies where `Primary` = 1');
		
		$dataSQL = $this->_o_DB->fetchOne($sql);
		self::$PRIMARY = $dataSQL;
		return $dataSQL?$dataSQL->CID:0;
	}
	public function PrimaryDisplay()
	{
		if(self::$PRIMARY)
		{
			return self::$PRIMARY->Code;
		}
		$sql = sprintf('select * from qscurrencies where `Primary` = 1');
		$dataSQL = $this->_o_DB->fetchOne($sql);
		self::$PRIMARY = $dataSQL;
		return $dataSQL?$dataSQL->Code:'';
	}
	public function getPrimary()
	{
		if(self::$PRIMARY)
		{
			return self::$PRIMARY;
		}
		$sql = sprintf('select * from qscurrencies where `Primary` = 1');
		$dataSQL = $this->_o_DB->fetchOne($sql);
		self::$PRIMARY = $dataSQL;
		return $dataSQL;
	}
	public function getDataByCode ($code)
	{
		if(!$code)
		{
			return;
		}
		if(isset(self::$CURRENCIES[$code]))
		{
			return self::$CURRENCIES[$code];
		}
		$sql = sprintf('select * from qscurrencies where Enabled = 1 and `Code` = %1$s', /* */
		 $this->_o_DB->quote($code));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		self::$CURRENCIES[$code] = $dataSQL;
		return $dataSQL;
	}
}
?>