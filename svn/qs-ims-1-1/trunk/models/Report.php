<?php
class Qss_Model_Report extends Qss_Model_Form
{
	var $arrQuery;
	 /**
	 * Working with all design of form that user acess via module management
     *'
     * @access  public
     */	
	public function __construct()
	{
	 	parent::__construct(); 
		$this->arrQuery = array();
	}	
	public function loadData()
	{
		$arr = array();
	 	foreach($this->a_Objects as $object)
		{
			$query = new Qss_Model_Query();
			$query->init($object->sz_Query);
			$query->setName($object->ObjectCode);
			$query->bMain = $object->b_Main;
			$arr1 = array_keys($this->a_Objects);
			$key1 = $arr1[0];
			/*Add param from main object*/
			foreach($this->a_Objects[$key1]->loadFields() as $field)
			{
				/* Add param from post or get */
				$param_name = "{$this->a_Objects[$key1]->ObjectCode}:{$field->FieldCode}";
				$query->addParam(array($param_name=>$field->getValue()));
			}			
			if(!$object->i_ParentID)
			{
				$arr[$object->ObjectCode] = $object;
				$this->arrQuery[$query->getName()] = $query;
			}
		}
		foreach($arr as $key=>$object)
		{
			$this->addChildToQuery($this->arrQuery[$key] , $object);
		}
	}
//-----------------------------------------------------------------------	
	function addChildToQuery(&$query , $object)
	{
		if(sizeof($object->a_Childs))
		{
			foreach($object->a_Childs as $child)
			{
				$newquery = new Qss_Model_Query();
				$newquery->init($child->szQuery);
				$newquery->setName($child->szChildID);
				$newquery->bMain = 0;
				$query->setChild($newquery);
				$this->addChildToQuery($newquery , $this->a_Objects[$child->intChildID]);
			}
		}
	}
//-----------------------------------------------------------------------	
    function generateReport()
    {
		$excel = new Qss_Model_Excel();
		$excel->init(QSS_DATA_DIR . "/report/{$this->FormCode}.xls");
		$excel->getReportDetail($this);
    }
//-----------------------------------------------------------------------	
	function save($saveparams)
    {
    	$urid = $saveparams['urid'];
    	$data = array('FormCode'=>$saveparams['fid'],
    				'Name'=>$saveparams['reportname'],
    				'Active'=>(int)@$saveparams['active'],
    				'Params'=>$saveparams['params'],
    				'Mobile'=>(int)@$saveparams['mobile']);
   	 	$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['Name_'.$item->Code] = $saveparams['reportname_'.$item->Code];
		}
    	if($urid)
    	{
    		$sql = sprintf('update qsuserreports set %1$s where URID=%2$d',
				$this->arrayToUpdate($data),$urid);
    	}
    	else
    	{
    		$sql = sprintf('insert into qsuserreports%1$s',
				$this->arrayToInsert($data));
    	}
    	$this->_o_DB->execute($sql);
    }
	function getById($id)
    {
		$sql = sprintf('select * from qsuserreports
				where qsuserreports.URID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
    }
	function getParams($userid,$urid)
    {
		$sql = sprintf('select * from qsuserreports
					inner join qsuserreport on qsuserreports.URID = qsuserreports.URID
					where UID = %1$d and qsuserreports.URID = %2$d',
				$userid,$urid);
		return $this->_o_DB->fetchOne($sql);
    }
	function getReportsByUser($userid,$mobile = 0)
    {
		$sql = sprintf('select qsuserreports.* from qsuserreport
					inner join qsuserreports on qsuserreport.URID = qsuserreports.URID 
					where qsuserreport.UID = %1$d and ifnull(Mobile,0) = %2$d',
				$userid,
				$mobile);
		return $this->_o_DB->fetchAll($sql);
    }
	function delete($urid)
    {
		$sql = sprintf('delete from qsuserreports where URID = %1$d',
				$urid);
		$this->_o_DB->execute($sql);
    }
	function getReportForm(Qss_Model_UserInfo $user)
	{
		$lang = ($user->user_lang == 'vn')?'':'_'.$user->user_lang;
		$sql = sprintf('select qsforms.*,sum(case when qsuserreports.FormCode is null then 0 else 1 end) as Quantity 
					from qsforms
					left join qsuserreports on qsuserreports.FormCode=qsforms.FormCode
					where qsforms.FormCode in (select FormCode from qsuserforms where qsuserforms.GroupID in(%1$s) and Rights !=0)
					and Effected = 1
					group by qsforms.FormCode
					order by qsforms.FormCode', 
					$user->user_group_list);
		return $this->_o_DB->fetchAll($sql);
	}
}	
?>