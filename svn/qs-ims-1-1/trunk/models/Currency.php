<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Currency extends Qss_Model_Abstract
{
	public $dataField = 'CID';
	public $arrField = array(1=>'Code',2=>'Name',3=>'Country',4=>'Symbol',5=>'Precision',6=>'ThousandsSep',7=>'DecPoint',8=>'EffectDate',9=>'Enabled',10=>'Primary');
	public $arrFieldName = array(1=>'Mã',2=>'Tên',3=>'Nước',4=>'Ký hiệu',5=>'Số thập phân',6=>'Phân biệt hàng ngìn',7=>'Dấu thập phân',8=>'Ngày hiệu lực',9=>'Hoạt động',10=>'Tiền tệ chính');
	public $arrFieldType = array(1=>1,2=>1,3=>1,4=>1,5=>1,6=>1,7=>1,8=>8,9=>9,10=>9);
	public $arrGroup = array();
	public $arrGroupName = array();
	/**
	 * Working with all design of form that user acess via module management
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();

	}
	public function getAll($currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$groupby,$a_Filter)
	{
		$sqlwhere = ' 1=1 ';
		$sqllimit = sprintf(' LIMIT %1$d,%2$d', (($currentpage?$currentpage:1) - 1) * $limit, $limit);
		$orderfields = array();
		if(isset($this->arrField[$fieldorder]))
		{
			$orderfields[] = '`'.$this->arrField[$fieldorder] . '` ' . $ordertype;
		}
		else
		{
			$orderfields[] = 'Code ASC';
		}
		$sqlorder = sprintf('order by %1$s',implode(',', $orderfields));
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset($this->arrField[$key]))
				{
					if($this->$arrFieldType[$key] == 8)
					{
						$sqlwhere .= sprintf(' and cast(%1$s as date) = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',$this->$arrField[$key],$this->_o_DB->quote($val));
					}
					else
					{
						$sqlwhere .= sprintf(' and qscurrencies.%1$s like %2$s',$this->arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
				}
			}
		}
		$sql = sprintf('select * from qscurrencies
				where %1$s %2$s %3$s',$sqlwhere,$sqlorder,$sqllimit);
		return $this->_o_DB->fetchAll($sql);
	}
	public function countAll($a_Filter)
	{
		$sqlwhere = ' 1=1 ';
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset($this->arrField[$key]))
				{
					if($this->$arrFieldType[$key] == 8)
					{
						$sqlwhere .= sprintf(' and %1$s = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',$this->arrField[$key],$this->_o_DB->quote($val));
					}
					else
					{
						$sqlwhere .= sprintf(' and qscurrencies.%1$s like %2$s',$this->arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
				}
			}
		}
		$sql = sprintf('select count(*) as count from qscurrencies
				where %1$s',$sqlwhere);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->count:0;
	}
	public function save($params)
	{
		$id = $params['id'];
		$data = array();
		foreach($this->arrField as $key=>$value)
		{
			if($this->arrFieldType[$key] == 8)
			{
				$data[$value] = Qss_Lib_Date::displaytomysql($params[$value]);
			}
			else
			{
				$data[$value] = $params[$value];
			}
		}
		if($id)
		{
			$sql = sprintf('update qscurrencies set %1$s where CID = %2$d',
			$this->arrayToUpdate($data),$id);
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qscurrencies%1$s',$this->arrayToInsert($data));
			$id = $this->_o_DB->execute($sql);
		}

		return $id;
	}	
	public function delete($id)
	{
		$retval = 0;
		if($id)
		{
			$sql = sprintf('delete from qscurrencies where CID = %1$d',
			$id);
			$retval = $this->_o_DB->execute($sql);
		}
		return $retval;
	}
	public function getByCode($code)
	{
		$sql = sprintf('select * from qscurrencies where Code = %1$s',
				$this->_o_DB->quote($code));
		return $this->_o_DB->fetchOne($sql);
	
	}
	
	public function getPrimary()
	{
	    $sql = sprintf('select * from qscurrencies where ifnull(qscurrencies.Primary, 0) = 1');
	    return $this->_o_DB->fetchOne($sql);
	
	}	
}
?>