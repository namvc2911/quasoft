<?php
class Qss_Model_System_Database extends Qss_Model_Abstract
{
	protected $_copydb;
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
		$config = Qss_Session::get('copydatabase');
		if($config)
		{
			$classname = 'Qss_Db_' . $config['adapter'];
			$this->_copydb = new $classname((array) $config);
		}	
	}

	public function replaceMenus($copyDBName)
    {
        if(isset($this->_copydb) && $this->_copydb->fetchOne('SELECT * FROM qsmenu') && $copyDBName)
        {
            // Add
            try
            {
                $this->_o_DB->execute("CREATE TABLE IF NOT EXISTS qsmenus_old SELECT * FROM qsmenus");
                $this->_o_DB->execute(sprintf('CREATE TABLE IF NOT EXISTS qsmenus_%1$d SELECT * FROM qsmenus', time()));
                $this->_o_DB->execute("CREATE TABLE IF NOT EXISTS qsmenu_old SELECT * FROM qsmenu");
                $this->_o_DB->execute(sprintf('CREATE TABLE IF NOT EXISTS qsmenu_%1$d SELECT * FROM qsmenu', time()));
                $this->_o_DB->execute("CREATE TABLE IF NOT EXISTS qsmenulink_old SELECT * FROM qsmenulink");
                $this->_o_DB->execute(sprintf('CREATE TABLE IF NOT EXISTS qsmenulink_%1$d SELECT * FROM qsmenulink', time()));
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                return false;
            }

            // Drop
            try
            {
                $this->_o_DB->execute('Delete from qsmenus');
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                return false;
            }

            try
            {
                $this->_o_DB->execute('Delete from qsmenu');
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                return false;
            }

            try
            {
                $this->_o_DB->execute('Delete from qsmenulink');
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                return false;
            }

            // Insert
            try
            {
                $this->_o_DB->execute(sprintf('Insert into qsmenus SELECT * FROM %1$s.qsmenus', $copyDBName));
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                return false;
            }

            try
            {
                $this->_o_DB->execute(sprintf('Insert into qsmenu SELECT * FROM %1$s.qsmenu', $copyDBName));
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                return false;
            }

            try
            {
                $this->_o_DB->execute(sprintf('Insert into qsmenulink SELECT * FROM %1$s.qsmenulink', $copyDBName));
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                return false;
            }

            // Set default menu
            $ok = $this->_o_DB->fetchOne('select ID from qsmenus WHERE IFNULL(`Default`, 0) = 1 LIMIT 1 ');

            if($ok)
            {
                $this->_o_DB->execute(sprintf('UPDATE qsusers SET MenuID = %1$d  WHERE UID <> -1', $ok->ID));
            }

            return true;
        }
        else
        {
            return false;
        }
    }

	public function getForms()
	{
		$sql = sprintf('select qsforms.*,qsfobjects.ObjectCode,qsobjects.ObjectName 
							from qsforms 
							left join qsfobjects on qsforms.FormCode = qsfobjects.FormCode
							inner join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode
							order by FormCode, ObjNo,ObjectCode');
		return $this->_o_DB->fetchAll($sql);
	}
	public function getCopyForms()
	{
		$sql = sprintf('select qsforms.*,qsfobjects.ObjectCode,qsobjects.ObjectName 
							from qsforms 
							left join qsfobjects on qsforms.FormCode = qsfobjects.FormCode
							inner join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode
							order by FormCode, ObjNo,ObjectCode');
		return $this->_copydb->fetchAll($sql);
	}
	
	public function getFormByCode($fid)
	{
		$sql = sprintf('select qsforms.*,qsfobjects.ObjectCode,qsobjects.ObjectName ,qsfobjects.Public
							from qsforms 
							left join qsfobjects on qsforms.FormCode = qsfobjects.FormCode
							inner join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode
							where qsforms.FormCode = %1$s
							order by FormCode,ObjNo,ObjectCode',$this->_o_DB->quote($fid));
		return $this->_o_DB->fetchAll($sql);
	}
	public function getCopyFormByCode($fid)
	{
		$sql = sprintf('select qsforms.*,qsfobjects.ObjectCode,qsfobjects.ParentObjectCode,qsfobjects.Main, qsfobjects.Public, 
							qsfobjects.Editing, qsfobjects.Track, qsfobjects.Insert, qsfobjects.Deleting, qsfobjects.ObjNo
							,qsobjects.ObjectName,qsobjects.ObjectName_en, qsobjects.OrderField, qsobjects.OrderType 
							from qsforms 
							left join qsfobjects on qsforms.FormCode = qsfobjects.FormCode
							inner join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode
							where qsforms.FormCode = %1$s
							order by FormCode,ObjNo,ObjectCode',$this->_copydb->quote($fid));
		return $this->_copydb->fetchAll($sql);
	}
	public function getObjects()
	{
		$sql = sprintf('select * from qsobjects order by ObjectCode');
		return $this->_o_DB->fetchAll($sql);
	}
	public function copyForm($data,$update = true)
	{
		if($update)
		{
			$sql = sprintf('update qsforms set %1$s where FormCode=%2$s'
						,$this->arrayToUpdate($data)
						,$this->_o_DB->quote($data['FormCode']));
		}
		else
		{
			$sql = sprintf('replace into qsforms%1$s',$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function copyFormObject($data,$update = true)
	{
		if($update)
		{
			$sql = sprintf('update qsfobjects set %1$s where FormCode=%2$s and ObjectCode=%3$s'
						,$this->arrayToUpdate($data)
						,$this->_o_DB->quote($data['FormCode'])
						,$this->_o_DB->quote($data['ObjectCode']));
		}
		else
		{
			$sql = sprintf('replace into qsfobjects%1$s',$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function copyObject($data,$update = true)
	{
		if($update)
		{
			$sql = sprintf('update qsobjects set %1$s where ObjectCode=%2$s'
						,$this->arrayToUpdate($data)
						,$this->_o_DB->quote($data['ObjectCode']));
		}
		else
		{
			$sql = sprintf('replace into qsobjects%1$s',$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function copyField($data,$update = true)
	{
		if($update)
		{
			$sql = sprintf('update qsfields set %1$s where ObjectCode=%2$s and FieldCode=%3$s'
						,$this->arrayToUpdate($data)
						,$this->_o_DB->quote($data['ObjectCode'])
						,$this->_o_DB->quote($data['FieldCode']));
		}
		else
		{
			$sql = sprintf('replace into qsfields%1$s',$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	
	public function getFieldObjects()
	{
		$sql = sprintf('select qsobjects.*,qsfields.*
							from qsobjects
							left join qsfields on qsfields.ObjectCode = qsobjects.ObjectCode
							order by qsobjects.ObjectCode,FieldCode');
		return $this->_o_DB->fetchAll($sql);
	}
	public function getCopyFieldObjects()
	{
		$sql = sprintf('select qsobjects.*,qsfields.*
							from qsobjects
							left join qsfields on qsfields.ObjectCode = qsobjects.ObjectCode
							order by qsobjects.ObjectCode,FieldCode');
		return $this->_copydb->fetchAll($sql);
	}
	//
	public function getObjectByCode($objid)
	{
		$sql = sprintf('select qsobjects.*,qsfields.*
							from qsobjects
							left join qsfields on qsobjects.ObjectCode = qsfields.ObjectCode
							where qsobjects.ObjectCode = %1$s
							order by FieldNo',$this->_o_DB->quote($objid));
		return $this->_o_DB->fetchAll($sql);
	}
	public function getCopyObjectByCode($objid)
	{
		$sql = sprintf('select qsobjects.*,qsfields.*
							from qsobjects
							left join qsfields on qsobjects.ObjectCode = qsfields.ObjectCode
							where qsobjects.ObjectCode = %1$s
							order by FieldNo',$this->_o_DB->quote($objid));
		return $this->_copydb->fetchAll($sql);
	}
	public function getFieldsByObjectCode($ObjectCode)
	{
		$sql = sprintf('select * from qsfields where ObjectCode =%1$s'
					,$this->_o_DB->quote($ObjectCode));
		return $this->_o_DB->fetchAll($sql);
	}
	public function getCopyFieldsByObjectCode($ObjectCode)
	{
		$sql = sprintf('select * from qsfields where ObjectCode =%1$s'
					,$this->_copydb->quote($ObjectCode));
		return $this->_copydb->fetchAll($sql);
	}
	
}
?>