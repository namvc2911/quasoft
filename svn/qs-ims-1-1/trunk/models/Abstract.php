<?php
class Qss_Model_Abstract
{

	/**
	 * Database
	 * @var object
	 */
	protected $_o_DB;
	
	protected $_user;

	/**
	 * Build constructor
	 *
	 * @return void
	 */
	public function __construct ()
	{
		$this->_o_DB = Qss_Db::getAdapter('main');
		$this->_o_DB instanceof Qss_Db_Abstract; 
		$this->_user = Qss_Register::get('userinfo');
	}
	public function arrayToInsert($data)
	{
		$key = array();
		foreach (array_keys($data) as $item)
		{
			$key[] = '`'.$item.'`';
		}
		$val= array();
		foreach (array_values($data) as $item)
		{
			if($val === null || $val === '')
			{
				$val[] = 'null';
			}
			else
			{
				$val[] = $this->_o_DB->quote($item);
			}
		}
		return '('.implode(',', $key) . ') VALUES('. implode(',', $val) .')';
	}
	public function arrayToUpdate($data)
	{
		$ret = '';
		foreach ($data as $key=>$val)
		{
			if($ret != '')
			{
				$ret .= ',';
			}
			if($val === null || $val === '')
			{
				$ret .= '`' . $key . '`' . '=null';
			}
			else
			{
				$ret .= '`' . $key . '`' . '=' . $this->_o_DB->quote($val);
			}
		}
		return $ret;
	}
	public function updateTemp($objid, $treename,$ioid,$ifid,$dataOld,$dataNew)
	{
		$sqlTree = '';
		if($treename)
		{
			$sql = sprintf('select qsforms.FormCode,qsfobjects.Main from qsforms 
							inner join qsfobjects on qsfobjects.FormCode = qsforms.FormCode
							where ObjectCode="%1$s" and qsforms.Effected = 1 
							LIMIT 1',$objid);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL && !$dataSQL->Main)
			{
				$sqlTree = sprintf(' and IFID_%1$s = %2$d',$dataSQL->FormCode,$ifid);
			}
		}
		if($treename)
		{
			//Lấy cái cũ, để biết là tạo mới hay update
			/*$sql = sprintf('select child.*,parent.lft as plft,parent.rgt as prgt 
					from %2$s as child
					left join  %2$s as parent on parent.IOID = child.%3$s
					where child.IOID = %1$d',
				$ioid,$objid,$treename);
				//echo $sql;die;
			$dataOld= $this->_o_DB->fetchOne($sql);*/
			if($dataOld)
			{
				$oldlft = (int) $dataOld->lft; 
				$oldrgt = (int) $dataOld->rgt;
				$oldplft = (int) $dataOld->plft; 
				$oldprgt = (int) $dataOld->prgt;	
			}
			else
			{
				$sql = sprintf('select max(rgt) as rgt
					from %1$s where 1=1',
					$objid);
				$sql .= $sqlTree;
				$dataMax = $this->_o_DB->fetchOne($sql);
				if($dataMax)
				{
					$oldlft = $dataMax->rgt+1;
					$oldrgt = $oldlft+1;
				}
				else 
				{
					$oldlft = 1;
					$oldrgt = 2;
				}
				$oldplft = 0; 
				$oldprgt = 0;	
			}
		}
		if($treename)
		{
			$sql = sprintf('update %1$s set lft=%2$d, rgt=%3$d where IOID = %4$d',
				$objid,
				$oldlft,
				$oldrgt,
				$ioid);
			$this->_o_DB->execute($sql);
		}
		if($treename)
		{
			//get new
			/*$sql = sprintf('select child.*,parent.lft as plft,parent.rgt as prgt 
					from %2$s as child
					left join  %2$s as parent on parent.IOID = child.%3$s
					where child.IOID = %1$d',
				$ioid,$objid,$treename);
			$dataNew = $this->_o_DB->fetchOne($sql);*/
			//update
			if(!$dataNew )//deleted
			{
				if($oldplft && $oldprgt)
				{
					$sql = sprintf('update %1$s
							set lft = lft-%2$d
							where lft>=%3$d',
							$objid,
							($oldrgt-$oldlft+1),
							$oldlft);
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
					$sql = sprintf('update %1$s
							set rgt = rgt-%2$d
							where rgt>%3$d',
							$objid,
							($oldrgt-$oldlft+1),
							$oldrgt);
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
				}
			}
			elseif((!$dataOld || ($dataOld->{$treename} != $dataNew->{$treename})))
			{
				$newlft = (int) $dataNew->lft; 
				$newrgt = (int) $dataNew->rgt;
				$newplft = (int) $dataNew->plft; 
				$newprgt = (int) $dataNew->prgt;
				//update new parent
				if($newplft && $newprgt)
				{
					$sql = sprintf('update %1$s
						set rgt = rgt + %2$d  
						where rgt >= %3$d',
						$objid,($oldrgt - $oldlft)?($oldrgt - $oldlft+1):2,$newprgt);
						//echo $sql.'---';
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
					$sql = sprintf('update %1$s
						set lft = lft + %2$d  
						where lft >= %3$d',
						$objid,($oldrgt - $oldlft)?($oldrgt - $oldlft+1):2,$newprgt);
					//	echo $sql.'---';
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
					$newlft = $newprgt; 
					$newrgt = $newlft + (($oldrgt - $oldlft)?($oldrgt - $oldlft):1); 
				}
				else 
				{
					if(!isset($dataMax))
					{
						$sql = sprintf('select max(rgt) as rgt
						from %1$s where 1=1',
						$objid);
						$sql .= $sqlTree;
						$dataMax = $this->_o_DB->fetchOne($sql);
					}
					if($dataMax)
					{
						$newlft = $dataMax->rgt+1;
						$newrgt = $newlft + (($oldrgt - $oldlft)?($oldrgt - $oldlft):2);
					}
					else 
					{
						$newlft = 1;
						$newrgt = 2;
					}
				}
				//update itseft & it's childs
				$sql = sprintf('update %1$s
						set lft = lft+%2$d,rgt = rgt+%2$d
						where lft>=%3$d and rgt<=%4$d',
						$objid,
						($newlft<$oldlft)?($newlft-$oldrgt-1):($newlft-$oldlft) ,
						($newlft<$oldlft)?$oldrgt+1:$oldlft,
						($newrgt<$oldrgt)?2*$oldrgt+1-$oldlft :$oldrgt);
						//echo $sql.'---';
				$sql .= $sqlTree;
				$this->_o_DB->execute($sql);
				//update old parent
				if($oldplft && $oldprgt)
				{
					$sql = sprintf('update %1$s
							set lft = lft-%2$d
							where lft>=%3$d',
							$objid,
							($oldrgt-$oldlft+1),
							($newlft<$oldlft)?$oldrgt+1:$oldlft);
						//	echo $sql.'---';
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
					$sql = sprintf('update %1$s
							set rgt = rgt-%2$d
							where rgt>%3$d',
							$objid,
							($oldrgt-$oldlft+1),
							$oldrgt);
							//echo $sql.'---';
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
				}
			} 
		}
	}
}
?>