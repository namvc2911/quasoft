<?php

class Qss_Model_Admin_Print extends Qss_Model_Abstract
{


	//-----------------------------------------------------------------------
	/**
	* construct a group
	*
	* @access  public
	*/
	function __construct ()
	{
		parent::__construct();
	}

	//-----------------------------------------------------------------------
	public function getById($id)
	{
		$sql = sprintf('select * from qsfprints where FPID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
	}

	/**
	 * Get all design
	 *
	 * @access  public
	 * @param   width
	 */
	function getAll ()
	{
		$sql = sprintf('select * from qsfprints',$lang);
		return $this->_o_DB->fetchAll($sql);
	}
	/**
	 *
	 * Enter description here ...
	 * @param int $fid
	 * @param int $type
	 */
	public function getByFID($fid)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select FPID,ifnull(Name%2$s,Name) as Name,Path,FormCode from qsfprints where FormCode = "%1$s"',$fid,$lang);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getPrintForms($user)
	{
		$lang = ($user->user_lang == 'vn')?'':'_'.$user->user_lang;
		$sql = sprintf('select qsforms.*,sum(case when qsfprints.FormCode is null then 0 else 1 end) as Quantity 
					from qsforms
					left join qsfprints on qsfprints.FormCode=qsforms.FormCode
					where qsforms.FormCode in (select FormCode from qsuserforms where qsuserforms.GroupID in(%1$s) and Rights !=0 and (class = \'\' or class is null))
					group by qsforms.FormCode
					order by qsforms.FormCode',
					$user->user_group_list);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getByFIDAndStep($fid,$step)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select FPID,ifnull(Name%2$s,Name) as Name,Path,FormCode from qsfprints 
					where FormCode = "%1$s" and Step like %3$s',$fid,$lang,$this->_o_DB->quote('%'.(int)$step.'%'));
		return $this->_o_DB->fetchAll($sql);
	}
	/**
	 * Update content of template
	 *
	 * @access  public
	 * @todo  	call form update form of department
	 * @param   variable have default value is 200
	 */
	function save ($params)
	{
		$data = array('FPID'=>$params['FPID'],
					'Name'=>$params['Name'],
					'FormCode'=>$params['FID'],
					'Path'=>$params['Path'],
					'Step'=>$params['Step']);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['Name_'.$item->Code] = @$params['Name_'.$item->Code];
		}
		if($data['FPID'])
		{
			$sql=sprintf('update qsfprints set %1$s
					where FPID = %2$d',/* */
			$this->arrayToUpdate($data),$params['FPID']);
		}
		else
		{
			$sql=sprintf('insert into qsfprints %1$s',/* */
			$this->arrayToInsert($data));
		}
		$this->_o_DB->execute($sql);
		return true;
	}

	//-----------------------------------------------------------------------
	/**
	* Update content of template
	*
	* @access  public
	* @todo  	call form update form of department
	* @param   variable have default value is 200
	*/
	function delete ($id)
	{
		$sql = sprintf('delete from qsfprints where FPID = %1$d',$id);
		return $this->_o_DB->execute($sql);
	}
}
?>