<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Menu extends Qss_Model_Abstract
{


	/**
	 * Working with all design of form that user acess via module management
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();

	}
	public function getAll($parent = 0,$mid = 0)
	{
		if($parent)
		{
			$where = sprintf('where ParentID = %1$d',$parent);
		}
		else 
		{
			$where = sprintf('where MID = %1$d',$mid);
		}
		$sql = sprintf('select * from qsmenu %1$s order by MenuOrder',$where);
		return $this->_o_DB->fetchAll($sql);
	}

    public function getMenuTree($mid, $parentID = 0, &$tree = array(), $level = 0)
    {
        $sql     = sprintf('
            select *
            from qsmenu
            where ParentID = %1$d AND MID = %2$d
            ORDER BY MenuOrder
        ', $parentID, $mid);
        $dataSql = $this->_o_DB->fetchAll($sql);

        if($dataSql)
        {
            ++$level;
            foreach ($dataSql as $item)
            {
                $temp          = new stdClass();
                $temp->display = $item->MenuName;
                $temp->group   = 1;
                $temp->code    = $item->MenuID;
                $temp->parent  = $parentID;
                $temp->level   = $level;
                $tree[]        = $temp;

                $sql2 = sprintf('
                    select qsmenulink.*, qsforms.*
                    from qsmenulink
                    inner join qsforms on qsmenulink.FormCode = qsforms.FormCode
                    where qsmenulink.MenuID = %1$d
                    ORDER BY qsmenulink.MenuLinkOrder'
                , $item->MenuID);

                $dataSql2 = $this->_o_DB->fetchAll($sql2);

                if($dataSql2)
                {
                    foreach ($dataSql2 as $item2)
                    {
                        $temp          = new stdClass();
                        $temp->display = "{$item2->FormCode} - {$item2->Name}";
                        $temp->group   = 0;
                        $temp->code    = $item2->FormCode;
                        $temp->parent  = $parentID;
                        $temp->level   = $level + 1;
                        $tree[]        = $temp;
                    }
                }

                $this->getMenuTree($mid, $item->MenuID, $tree, $level);
            }
        }

        return $tree;
    }

	public function getById($id)
	{
		$sql = sprintf('select * from qsmenu where MenuID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getMainMenu()
	{
		$sql = sprintf('select * from qsmenus');
		return $this->_o_DB->fetchAll($sql);
	}
	public function getMenuConfig($id)
	{
		$sql = sprintf('select qsforms.*,qsmenulink.MenuID,qsmenulink.MenuLinkOrder,
				case when qsfobjects.FormCode is null then 0 else 1 end as actived
				from qsforms 
				left join qsmenulink on qsforms.FormCode = qsmenulink.FormCode and qsmenulink.MenuID=%1$d
				left join qsfobjects on qsfobjects.FormCode=qsforms.FormCode
				group by qsforms.FormCode, qsforms.Name,qsfobjects.FormCode
				order by qsforms.FormCode, case when qsfobjects.FormCode is null then 0 else 1 end, Type,Name',
		$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function save($params)
	{
		$id = (int) $params['id'];
		$parentid = (int) $params['parentid'];

		$name= $params['szName'];
		$data = array('MenuID'=>(int) $params['id'],
					'MID'=>(int) $params['mid'],
					'ParentID'=>(int) $params['parentid'],
					'MenuName'=>$params['szName'],
					'Icon'=>$params['szIcon'],
					'MenuOrder'=>$params['intMenuOrder']);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['MenuName_'.$item->Code] = @$params['szName_'.$item->Code];
		}
		if(!$data['MenuID'])
		{
			$sql=sprintf('Insert into qsmenu %1$s',$this->arrayToInsert($data));
			$id = $this->_o_DB->execute($sql);
		}
		else
		{
			$sql=sprintf('Update qsmenu set %1$s where MenuID=%2$d',$this->arrayToUpdate($data),$id);
			$this->_o_DB->execute($sql);
		}
		$fid=isset($params['FID'])?$params['FID']:null;
		$this->_o_DB->execute("delete from qsmenulink where MenuID=".$id);
		for($i=0;$i<count($fid);$i++)
		{
			$sql = sprintf('insert into qsmenulink(MenuID,FormCode,MenuLinkOrder) values(%1$d,%2$s,%3$d)',
						$id,
						$this->_o_DB->quote($fid[$i]),
						@$params["MenuLinkOrder_".$fid[$i]]);
			$this->_o_DB->execute($sql);
		}
		return true;
	}
	public function delete($id)
	{
		$this->_o_DB->execute(sprintf('delete qsmenu from qsmenu where MenuID=%1$d and MenuID not in (select ParentID from (select * from qsmenu) as T)',$id));
		return true;
	}
}
?>
