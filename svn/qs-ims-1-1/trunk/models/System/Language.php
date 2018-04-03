<?php
class Qss_Model_System_Language extends Qss_Model_Abstract
{

	public $code;

	public $name;

	public $active;

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
	* Get all system object
	*
	* @return void
	*/
	public function getAll ($active = 0,$vn = true)
	{
		$sql = sprintf('select * from qslanguage where 1=1 %1$s %2$s
						order by case when Code = "vn" then 1 else 2 end,Code'
					,$active?' and Active = 1':''
					,$vn?'':' and Code != \'vn\'');//Code != \'vn\' and
		return $this->_o_DB->fetchAll($sql);
	}

	//-----------------------------------------------------------------------
	/**
	* Init the system object
	*
	* @return void
	*/
	public function init ($code)
	{
		$sql = sprintf('select * from qslanguage where code=%1$s', $this->_o_DB->quote($code));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			$this->code = $dataSQL->Code;
			$this->name = $dataSQL->Name;
			$this->active = $dataSQL->Active;
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Save system object
	 *
	 * @return boolean
	 */
	public function save ($params)
	{
		$this->code = $params['Code'];
		$this->name  = $params['Name'];
		$this->active = (int)@$params['Active'];
		$sql = sprintf('replace into qslanguage(Code,Name,Active)
				values(%1$s,%2$s,%3$d)',$this->_o_DB->quote($this->code),$this->_o_DB->quote($this->name),$this->active);/* */
		$this->_o_DB->execute($sql);
		if($this->active && $params['Code'] != 'vn')
		{
			$this->generate($this->code);
		}
		return true;
	}
	public function generate($code)
	{
		$sql = sprintf('ALTER TABLE qsobjects ADD ObjectName_%1$s VARCHAR( 200 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsfields ADD FieldName_%1$s VARCHAR( 200 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsforms ADD Name_%1$s VARCHAR( 200 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsmenu ADD MenuName_%1$s VARCHAR( 255 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsworkflowsteps ADD Name_%1$s VARCHAR( 255 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsfprints ADD Name_%1$s VARCHAR( 255 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsuserreports ADD Name_%1$s VARCHAR( 255 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsuiboxs ADD Title_%1$s VARCHAR( 255 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsbash ADD BashName_%1$s VARCHAR( 255 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
		$sql = sprintf('ALTER TABLE qsuigroups ADD Name_%1$s VARCHAR( 255 ) NULL DEFAULT NULL',$this->code);
		try
		{
			$this->_o_DB->execute($sql);
		}
		catch (Exception $e)
		{
		}
	}
	public function getAllTranslation()
	{
		$sql = sprintf('select * from qstranslation order by ID desc');
		return $this->_o_DB->fetchAll($sql);
	}
	public function getTranslation($id)
	{
		$sql = sprintf('select * from qstranslation where ID = %1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function saveTranslation($params)
	{
		$sql = sprintf('replace into qstranslation(ID,Language,Text)
					values(%1$d,%2$s,%3$s)',
		$params['ID'],$this->_o_DB->quote($params['Language']),$this->_o_DB->quote($params['Text']));
		return $this->_o_DB->execute($sql);
	}
	public function deleteTranslate($id)
	{
		$sql = sprintf('delete from qstranslation where ID = %1$d',$id);
		return $this->_o_DB->execute($sql);
	}
	public function isDuplicate($id)
	{
		$sql = sprintf('select 1 from qstranslation where ID = %1$d',$id);
		return (bool) $this->_o_DB->fetchOne($sql);
	}
}
?>