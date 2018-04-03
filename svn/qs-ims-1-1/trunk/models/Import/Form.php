<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Import_Form extends Qss_Model_Form
{

	protected $_data;//có IFID

	protected $_nocheckall;

	protected $_ignore;

	protected $_objects;

	protected $_error = false;
	
	protected $_arrPicture;

	const ERROR_TYPE_DUPLICATE = 1;

	const ERROR_TYPE_REQUIRED = 2;

	const ERROR_TYPE_LOOKUP = 3;

	/**
	 * Working with all design of form that user acess via module management
	 * $nocheckall: Cho tất cả được thì ok, ko thôi
	 * @access  public
	 */
	public function __construct ($FormCode,$ignore=false,$nocheckall = true,$deptid = 0,$userid = 0)
	{
		parent::__construct();
		$this->_data = array();
		$this->_objects = array();
		$this->_arrPicture = array();
		$deptid = $deptid?$deptid:$this->_user->user_dept_id;
		$userid = $userid?$userid:$this->_user->user_id;
		$this->_ignore = $ignore;
		$this->_nocheckall = $nocheckall;
		$this->init($FormCode,$deptid,$userid);

	}
	public function setData($data)
	{
		$this->_data[] = $data;
	}
	public function generateSQL()
	{
		if(!count($this->_data))
		{
			return;
		}
		//$this->_o_DB->beginTransaction(); Không cần nữa
		//Tạo bảng temp nếu chưa có, giống bảng view nhưng thêm cột Error và ErrorMessage, ExistsIFID,ExistsIOID
		foreach ($this->a_Objects as $object)
		{
			$this->createTemporaryTable($object);
		}
		//Insert dữ liệu vào bảng temp
		$this->insertTemporaryData();
		//cập nhật lookup cho các bảng temp
		$this->updateLookup();
		if(!$this->_error || $this->_nocheckall)
		{
			//update những bản ghi tồn tại
			$this->updateExists();
			//insert những bản ghi mới
			$this->insertData();
			//Chạy cho Radio nhưng trong patch
			//Chạy update lookup, chỉ cho data vừa cập nhật
			$this->updateReferLookup();
			
			//chạy lại tree nếu có
			$this->updateTree();
			//copy picuture nếu có
			$this->copyPicture();
		}
		//$this->debugs();
		//$this->_o_DB->commit();không cần nữa
		return !$this->_error;
	}
	public function createTemporaryTable($object)
	{
		$sql=sprintf('select * from qsfields where ObjectCode="%1$s" and FieldType != 17 order by FieldNo'
		,$object->ObjectCode);
		$dataSQLField = $this->_o_DB->fetchAll($sql);
			
		$viewCreate = '';
		$addColumns = ',Error INT DEFAULT 0,ErrorMessage TEXT, ExistsIFID INT DEFAULT 0,ExistsIOID INT DEFAULT 0,`No` INT';
		$viewInsert = '';
		$index = '';
		foreach($dataSQLField as $data)
		{
			switch($data->FieldType)
			{
				case 3:
					$viewCreate.=sprintf(', %1$s TEXT',$data->FieldCode);
					break;
				case 5:
					$viewCreate.=sprintf(', %1$s INT',$data->FieldCode);
					break;
				case 6:
					$precision = (int) $data->TValue;
					$precision = $precision?$precision:2;
					$viewCreate.=sprintf(', %1$s DECIMAL(24,%2$d)',$data->FieldCode,$precision);
					break;
				case 7:
					$viewCreate.=sprintf(', %1$s TINYINT',$data->FieldCode);
					break;
				case 10:
					$viewCreate.=sprintf(', %1$s DATE',$data->FieldCode);
					break;
				case 4:
					$viewCreate.=sprintf(', %1$s TIME',$data->FieldCode);
					break;
				case 11:
					$viewCreate.=sprintf(', %1$s BIGINT',$data->FieldCode);
					$viewCreate.=sprintf(', Ref_%1$s varchar(10)',$data->FieldCode);
					break;
				case 18:
					$viewCreate.=sprintf(', %1$s DATETIME',$data->FieldCode);
					break;
				default:
					$viewCreate.=sprintf(', %1$s varchar(255)',$data->FieldCode);
					break;
			}
			if($data->RefFieldCode || $data->FieldType == 14 || $data->FieldType == 16)
			{
				$viewCreate.=sprintf(', Ref_%1$s int',$data->FieldCode);
			}
			elseif(($data->InputType == 5 || $data->InputType == 3) && Qss_Json::decode($data->Regx) !== null)
			{
				$viewCreate.=sprintf(', Ref_%1$s varchar(100)',$data->FieldCode);
			}
		}

		//Add more IFID for each FID
		$sql=sprintf('select * from qsfobjects
						inner join qsforms on qsforms.FormCode = qsfobjects.FormCode 
						where ObjectCode = "%1$s"',$object->ObjectCode);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$indexno = 0;
		$fidname = ''; //for sub object tree
		foreach ($dataSQL as $data)
		{
			$viewInsert.=sprintf('IFID_%1$s int,',$data->FormCode);
			if($indexno < 15)
			{
				$index .= sprintf(', KEY IFID_%1$s (IFID_%1$s)',$data->FormCode);
			}
			$indexno++;
		}

		foreach ($dataSQLField as $data)
		{
			if($data->RefFieldCode && $data->RefObjectCode)
			{
				if($indexno < 15)
				{
					$index .= sprintf(', KEY Ref_%1$s (Ref_%1$s)',$data->FieldCode);
				}
				$indexno++;
			}
			if($data->Unique && $data->FieldType != 3)
			{
				if($indexno < 15)
				{
					$index .= sprintf(', KEY %1$s (%1$s)',$data->FieldCode);
				}
				$indexno++;
			}
		}
		$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_%1$s',
		$object->ObjectCode);
		$this->_o_DB->execute($sql);
		$sql = sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS tmp_%1$s(%2$s IOID int NOT NULL AUTO_INCREMENT,DeptID int %3$s %5$s %6$s, PRIMARY KEY (IOID) %4$s)',
		$object->ObjectCode,
		$viewInsert,
		$viewCreate,
		$index,
		($object->b_Tree)?',lft int null, rgt int null':'',
		$addColumns);
		$this->_o_DB->execute($sql);
	}
	public function insertTemporaryData()
	{
		$ioidtemp = -1;
		$arrInsert = array();//lấy tạm ra để buid query sau cho tiện
		foreach ($this->_data as $id=>$item)
		{
			//$id làm IFID tạm thời
			$id++;
			$ifid = 0;
			foreach ($item as $objectCode=>$arrObjectData)//k objectcode, v mảng
			{
				if(!isset($arrInsert[$objectCode]))
				{
					$this->_objects[] = $objectCode;
					$arrInsert[$objectCode] = array();
				}
				$arrInsert[$objectCode][$ioidtemp] = array();
				$object = $this->o_fGetObjectByCode($objectCode);
				foreach ($arrObjectData as $objectData)
				{
					$arrInsert[$objectCode][$ioidtemp]['ifidtemp'] = -$id;
					$arrInsert[$objectCode][$ioidtemp]['ioid'] = (int)@$objectData['ioid'];
					$arrInsert[$objectCode][$ioidtemp]['deptid'] = (int)@$objectData['deptid'];
					if($this->o_fGetObjectByCode($objectCode)->b_Main)
					{
						$arrInsert[$objectCode][$ioidtemp]['ifid'] = (int)@$objectData['ifid'];
						$ifid = (int)@$objectData['ifid'];
					}
					else
					{
						$arrInsert[$objectCode][$ioidtemp]['ifid'] = $ifid?$ifid:((int)@$objectData['ifid']);
					}
					//Số tt của excel
					$arrInsert[$objectCode][$ioidtemp]['no'] = (int)@$objectData['no'];
					foreach ($object->loadFields() as $f)
					{
						//$f->bEditStatus = false;//@todo: Cứ dòng sau trắng thì nó ko cập nhật lun
						if ( isset($objectData[$f->FieldCode]))
						{
							$f->bEditStatus = true;
							$val = $objectData[$f->FieldCode];
							$val = ($val === null)?'':$val;
							if ( $f->intInputType == 3
							|| $f->intInputType == 4
							|| $f->intInputType == 11
							|| $f->intInputType == 12
							|| ($f->RefFieldCode && $f->intInputType != 1))
							{
								/*default*/
								if(is_int($val) && (($f->RefFieldCode && $f->intInputType != 1)
								|| $f->intFieldType == 14
								|| $f->intFieldType == 16))
								{
									$arrInsert[$objectCode][$ioidtemp]['Ref_'.$f->FieldCode] = $val;
								}
								else
								{
									$arrInsert[$objectCode][$ioidtemp][$f->FieldCode] = $val;
								}
							}
							elseif($f->intFieldType == 7 )
							{
								$arrInsert[$objectCode][$ioidtemp][$f->FieldCode] = $val?true:false;
							}
							elseif($f->intFieldType == 9 )
							{
								//copy file
								$ext = $val;
								if($val && file_exists($val))
								{
									if(!isset($this->_arrPicture[$objectCode][$f->FieldCode]))
									{
										$this->_arrPicture[$objectCode][$f->FieldCode] = $f;
									}
									$ext = basename($val);
								}
								$arrInsert[$objectCode][$ioidtemp][$f->FieldCode] = $ext;
							}
							else
							{
								$arrInsert[$objectCode][$ioidtemp][$f->FieldCode] = $val;
							}
						}
						if(isset($objectData['Ref_'.$f->FieldCode]))
						{
							$f->bEditStatus = true;
							$arrInsert[$objectCode][$ioidtemp]['Ref_'.$f->FieldCode] = $objectData['Ref_'.$f->FieldCode];
						}
					}
					$ioidtemp--;
				}
			}
		}
		foreach ($this->a_Objects as $object)
		{
			if(isset($arrInsert[$object->ObjectCode]))
			{
				$sqlInsert = sprintf('(IFID_%1$s,IOID,DeptID,ExistsIFID,ExistsIOID,No'
				,$this->FormCode);
				$sqlValue = '';
				foreach ($object->loadFields() as $f)
				{
					if($f->bEditStatus)
					{
						$sqlInsert .= sprintf(',%1$s',$f->FieldCode);
						if(($f->RefFieldCode && $f->intInputType != 1)
						|| $f->intFieldType == 14
						|| $f->intFieldType == 16
						|| (($f->intInputType == 5 || $f->intInputType == 3) && Qss_Json::decode($f->szRegx) !== null))
						{
							$sqlInsert .= sprintf(',Ref_%1$s',$f->FieldCode);
						}
					}
				}
				$sqlInsert .= sprintf(')');
				foreach ($arrInsert[$object->ObjectCode] as $k=>$v)
				{
					//foreach ($data as )
					{
						if($sqlValue)
						{
							$sqlValue .= ', ';
						}
						//$sqlInsert = sprintf('(IFID_%1$s,IOID,DeptID,ExistsIFID,ExistsIOID'
						$sqlValue .= sprintf('(%1$d,%2$d,%3$d,%4$d,%5$d,%6$d'
						,$v['ifid']?$v['ifid']:$v['ifidtemp']
						,$k
						,$v['deptid']?$v['deptid']:$this->i_DepartmentID
						,$v['ifid']
						,$v['ioid']
						,$v['no']);
						foreach ($object->loadFields() as $f)
						{
							if($f->bEditStatus)
							{
								if(isset($v[$f->FieldCode]) && $v[$f->FieldCode] !== '')
								{
									$sqlValue .= sprintf(',%1$s',$this->_o_DB->quote($v[$f->FieldCode]));
								}
								else
								{
									$sqlValue .= sprintf(',null');
								}
								if(($f->RefFieldCode && $f->intInputType != 1)
								|| $f->intFieldType == 14
								|| $f->intFieldType == 16
								|| (($f->intInputType == 5 || $f->intInputType == 3) && Qss_Json::decode($f->szRegx) !== null))
								{
									if(isset($v['Ref_'.$f->FieldCode]) && $v['Ref_'.$f->FieldCode] !== '')
									{
										$sqlValue .= sprintf(',%1$s',$this->_o_DB->quote($v['Ref_'.$f->FieldCode]));
									}
									else
									{
										$sqlValue .= sprintf(',null');
									}
								}
							}
						}
						$sqlValue .= ')';
					}
				}
				$sql = sprintf('insert into tmp_%1$s%2$s value%3$s'
				,$object->ObjectCode
				,$sqlInsert
				,$sqlValue);
				$this->_o_DB->execute($sql);
			}
		}
	}
	public function updateLookup()
	{
		//update từ id -> value
		foreach($this->_objects as $objectcode)
		{
			$object = $this->o_fGetObjectByCode($objectcode);
			//chỉ lấy từ table cũ vì nếu là mới thì chưa có id mà chỉ có value
			foreach ($object->loadFields() as $f)
			{
				if($f->bEditStatus)
				{
					if(($f->RefFieldCode && $f->intInputType != 1))
					{
						$sqlUpdate = sprintf('update tmp_%1$s as v
									inner join %3$s as t on t.IOID = v.Ref_%2$s
									set v.%2$s = t.%4$s 
									where v.%2$s is null
									and v.Ref_%2$s is not null'
									,$object->ObjectCode
									,$f->FieldCode
									,$f->RefObjectCode
									,$f->RefFieldCode);
									$this->_o_DB->execute($sqlUpdate);
					}
					elseif($f->intFieldType == 14)
					{
						$sqlUpdate = sprintf('update tmp_%1$s as v
									inner join qsdepartments as t on t.DeptCode = v.%2$s
									set v.Ref_%2$s = t.DepartmentID
									where v.Ref_%2$s is null
									and v.%2$s is not null'
									,$object->ObjectCode
									,$f->FieldCode);
									$this->_o_DB->execute($sqlUpdate);
					}
					elseif($f->intFieldType == 16)
					{
						$sqlUpdate = sprintf('update tmp_%1$s as v
									inner join qsusers as t on t.UserID = v.%2$s
									set v.Ref_%2$s = t.UID
									where v.Ref_%2$s is null
									and v.%2$s is not null'
									,$object->ObjectCode
									,$f->FieldCode);
									$this->_o_DB->execute($sqlUpdate);
					}
				}
			}
			//update eIOID nếu co eIFID
			if($object->b_Main)
			{
				$sqlUpdate = sprintf('update tmp_%1$s as v
								inner join %1$s as t on t.IFID_%2$s = v.ExistsIFID
								set v.ExistsIOID = t.IOID 
								where v.ExistsIFID is not null'
								,$objectcode
								,$this->FormCode);
								$this->_o_DB->execute($sqlUpdate);
			}
		}
		//update value->id, phức tạp
		//cập nhật lookup không them chiếu đối tượng của form thì chỉ càn update từ bảng gốc
		$arrLookupMainForm = array();
		foreach($this->_objects as $objectcode)
		{
			$InFormwhere = '';
			$object = $this->o_fGetObjectByCode($objectcode);
			//check duplicate ngay tại bảng tmp để loại các dòng trùng
			$select = '';
			$group = '';
			$on = '';
			foreach ($object->loadFields() as $f)
			{
				if($f->bUnique)
				{
					if($select != '')
					{
						$select .= ',';
					}
					$select .= $f->FieldCode;
					if($group != '')
					{
						$group .= ',';
					}
					$group .= $f->FieldCode;
					if($f->intFieldType <= 3 && $f->bUnique == 1)
					{
						$group .= Qss_Lib_Const::MYSQL_COLLATION;
					}
					if($on != '')
					{
						$on .= ' and ';
					}
					$on .= sprintf('v.%1$s = t.%1$s'
					,$f->FieldCode);
					if($f->intFieldType <= 3 && $f->bUnique == 1)
					{
						$on .= Qss_Lib_Const::MYSQL_COLLATION;
					}
				}
			}
			if($select)
			{
				if(!$object->b_Main)
				{
					if($group != '')
					{
						$group .= ',';
					}
					$group .= sprintf('IFID_%1$s',$this->FormCode);
					if($select != '')
					{
						$select .= ',';
					}
					$select .= sprintf('IFID_%1$s',$this->FormCode);
				}
				//insert vào bảng tmp2 lúc này hợp lý
				$sql=sprintf('DROP TEMPORARY TABLE IF EXISTS tmp2_%1$s',$object->ObjectCode);
				$this->_o_DB->execute($sql);
				$sql=sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS tmp2_%1$s
							select * from tmp_%1$s',$object->ObjectCode);
				$this->_o_DB->execute($sql);
				$sqlUpdate = sprintf('UPDATE tmp_%1$s as v
					JOIN (
					    SELECT %2$s, MIN(IOID) minIOID
					    FROM tmp2_%1$s
					    GROUP BY %5$s
					    HAVING COUNT(*) > 1) t ON %3$s AND v.IOID != t.minIOID
					SET v.Error = v.Error|%4$d,v.ErrorMessage="%2$s"
					where v.ExistsIOID = 0 and v.Error = 0'//v.ExistsIFID = 0 and v.ExistsIOID = 0 and
				,$objectcode
				,$select
				,$on
				,self::ERROR_TYPE_DUPLICATE
				,$group);
				$this->_o_DB->execute($sqlUpdate);
			}
			//chỉ lấy từ table cũ vì nếu là mới thì chưa có id mà chỉ có value
			$arrLookupUpdateObjects = array();//tách ra vì trường hợp cùng đến 1 đối tượng và 1 trường, như người giao và người thực hiện trong pbt
			$arrLookupObjects = array();
			$arrRefFields = array();
			foreach ($object->loadFields() as $f)
			{
				if($f->bEditStatus && ($f->RefFieldCode && $f->intInputType != 1))
				{
					//nếu lặp lại cùng lookup đến 1 trường thì xử lại
					if(isset($arrRefFields[$f->RefObjectCode]) && $arrRefFields[$f->RefObjectCode] == $f->RefFieldCode)
					{
						$arrLookupUpdateObjects[] = $arrLookupObjects;
						$arrLookupObjects = array();
					}
					if(!isset($arrLookupObjects[$f->RefObjectCode]))
					{
						$arrLookupObjects[$f->RefObjectCode] = array();
					}
					$arrLookupObjects[$f->RefObjectCode][] = $f;
					$arrRefFields[$f->RefObjectCode] = $f->RefFieldCode;		
				}
			}
			if(count($arrLookupObjects))
			{
				$arrLookupUpdateObjects[] = $arrLookupObjects;
			}
			foreach($arrLookupUpdateObjects as $arrLookupObjects)
			{
				//Nếu cùng lkup đến 1 đối tượng thì chung IOID.
				//Đặt arry formcode lookup đến, thằng nào có trong thằng trước thì thêm điều kiện
				$arrLookupForms = array();
				foreach($arrLookupObjects as $key=>$value)
				{
					$InFormwhere = '';
					$tbl = $key;
					$keyObject = $this->o_fGetObjectByCode($key);
					if($keyObject)
					{
						//Trong fom
						if(in_array($key,$this->_objects))
						{
							if(!$keyObject->b_Main)
							{
								$InFormwhere = sprintf(' and t.IFID_%1$s = v.IFID_%1$s '
								,$this->FormCode);
								//$InFormwhere = sprintf(' and (t.IFID_%1$s = v.ExistsIFID or t.IFID_%1$s = v.IFID_%1$s)'
							}
							//nếu trong đối tượng đang import thì tạo bảng tmp2
							if($key == $object->ObjectCode)
							{
								//phát sinh vấn đề là lookup sang dữ liệu cũ có vấn đề/ phải so sánh cả tbl cũ
								if($object->b_Main)
								{
									$tbl = sprintf('(select * from tmp2_%1$s
										union all select *,0,"",0,0,0 from %2$s)'
										,$key
										,$object->ObjectCode);
								}
								else
								{
									$tbl = sprintf('tmp2_%1$s',$key);
								}
							}
							else
							{
								$tbl = sprintf('tmp_%1$s',$key);
							}
						}
					}
					$set = '';
					$where = '';
					foreach ($value as $f)
					{
						if($set != '')
						{
							$set .= ',';
						}
						//Với auto thì khác
						if($f->szRegx == Qss_Lib_Const::FIELD_REG_AUTO)
						{
							if($where != '')
							{
								$set .= sprintf('v.Ref_%1$s = ifnull(v.Ref_%1$s,t.IOID) '
								,$f->FieldCode
								,$f->RefFieldCode);
							}
						}
						else
						{
							$set .= sprintf('v.Ref_%1$s = if(v.%1$s is null,null,ifnull(v.Ref_%1$s,t.IOID)) '
							,$f->FieldCode
							,$f->RefFieldCode);
						}
						if($where != '' && $f->szRegx == Qss_Lib_Const::FIELD_REG_AUTO)//nếu auto thì map luôn cả tên
						{
							if($set != '')
							{
								$set .= ',';
							}
							$set .= sprintf('v.%1$s = t.%2$s '
							,$f->FieldCode
							,$f->RefFieldCode);
						}
						if($f->szRegx != Qss_Lib_Const::FIELD_REG_AUTO)//auto thì đã có thằng trước rồi
						{
							if($where != '')
							{
								$where .= ' and ';
							}
							$where .= sprintf('v.%1$s = if(v.%1$s is null,v.%1$s,t.%2$s)'//collate latin1_bin
							,$f->FieldCode
							,$f->RefFieldCode);
							$lookupfield = Qss_Lib_System::getFieldByCode($f->RefObjectCode, $f->RefFieldCode);
							if($f->intFieldType <= 3 && $lookupfield->Unique == 1)//lookup phân biệt hoa thường
							{
								$where .= Qss_Lib_Const::MYSQL_COLLATION;
							}
							$InFormwhere .= sprintf(' and (v.%1$s is not null and v.Ref_%1$s is null)'
							,$f->FieldCode);
						}
						//neu khong co trong form nay va đã có field lookup đến cùng form đã co trước thì thêm điều kiện lọc theo form
						if($this->FormCode != $f->RefFormCode
						&& isset($arrLookupForms[$f->RefFormCode])
						&& $f->szRegx != Qss_Lib_Const::FIELD_REG_AUTO)
						{
							$fold = $arrLookupForms[$f->RefFormCode];
							if($fold->RefObjectCode != $f->RefObjectCode)//nếu cùng thì đã xử trên kia rồi,
							{
								$InFormwhere .= sprintf(' and t.IFID_%1$s in (select IFID_%1$s from %2$s where IOID = ifnull(v.Ref_%3$s,0))'
								,$fold->RefFormCode
								,$fold->RefObjectCode
								,$fold->FieldCode);
							}
						}
						elseif($this->FormCode != $f->RefFormCode && isset($arrLookupMainForm[$f->RefFormCode])
						&& $f->szRegx != Qss_Lib_Const::FIELD_REG_AUTO)
						{
							$fold = $arrLookupMainForm[$f->RefFormCode];
							if($fold->RefObjectCode != $f->RefObjectCode)//nếu cùng thì đã xử trên kia rồi,
							{
								$InFormwhere .= sprintf(' and t.IFID_%1$s in (select m.IFID_%1$s from %2$s as m
										inner join tmp_%4$s as n on m.IOID = n.Ref_%3$s
										 where n.IFID_%5$s = v.IFID_%5$s or n.ExistsIFID = v.IFID_%5$s)'
										 ,$fold->RefFormCode
										 ,$fold->RefObjectCode
										 ,$fold->FieldCode
										 ,$fold->ObjectCode
										 ,$this->FormCode);
							}
						}
						//nếu không refer đến form này thì ghi lại FormCode cho thằng sau
						if($this->FormCode != $f->RefFormCode && !isset($arrLookupForms[$f->RefFormCode]))
						{
							$arrLookupForms[$f->RefFormCode] = $f;
							if($object->b_Main)
							{
								$arrLookupMainForm[$f->RefFormCode] = $f;
							}
						}
					}
	
					if($set != '')
					{
						//insert vào bảng tmp2 lúc này hợp lý
						/*echo '<pre>';
						$sql=sprintf('select * from tmp_OBaoTriDinhKy',$object->ObjectCode);
						print_r($this->_o_DB->fetchAll($sql));*/
						$sql=sprintf('DROP TEMPORARY TABLE IF EXISTS tmp2_%1$s',$object->ObjectCode);
						$this->_o_DB->execute($sql);
						$sql=sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS tmp2_%1$s
									select * from tmp_%1$s',$object->ObjectCode);
						$this->_o_DB->execute($sql);
						$sqlUpdate = sprintf('update tmp_%1$s as v
										inner join %2$s as t on %3$s
										set %4$s
										where v.Error = 0 %5$s'//@todo bo truong hop id->value
						,$objectcode
						,$tbl
						,$where
						,$set
						,$InFormwhere);
						$this->_o_DB->execute($sqlUpdate);
					}
				}
			}

			//update lại value nếu ref vẫn = 0 hoặc null, nếu thằng nào đang có mà Ref 0 hoặc null thì set loi cho nó
			//$set = '';
			$setRequired = '';
			$setError = '';//nếu bất cứ cái nào edit mà lại null
			foreach ($object->loadFields() as $f)
			{
				if($f->bEditStatus && ($f->RefFieldCode && $f->intInputType != 1))
				{
					/*if($set != '')
					 {
						$set .= ',';
						}
						$set .= sprintf('v.%1$s = if(v.Ref_%1$s is null, null,v.%1$s)'
						,$f->FieldCode);
						//concat_ws(',',if(IOID=1,'IOID',null),if(DeptID=1,'DeptID',null))*/
					if($setRequired != '')
					{
						$setRequired .= ',';
					}
					$setRequired .= sprintf('if(v.Ref_%1$s is null and v.%1$s is not null, "%1$s",null)'
					,$f->FieldCode);
					if($setError != '')
					{
						$setError .= ' or ';
					}
					$setError .= sprintf('(v.Ref_%1$s is null and v.%1$s is not null)'
					,$f->FieldCode);
				}

			}
			if($setRequired != '')
			{
				$sqlUpdate = sprintf('update tmp_%1$s as v
									set 
									v.Error = if((%2$s),%3$d,0)
									,v.ErrorMessage = concat_ws(",",%4$s)
									where v.Error = 0'
									,$object->ObjectCode
									,$setError
									,self::ERROR_TYPE_LOOKUP
									,$setRequired);
									$this->_o_DB->execute($sqlUpdate);
			}
			//Check duplicate đối tương chính
			$where = '';
			foreach ($object->loadFields() as $f)
			{
				if($f->bUnique)
				{
					if($where != '')
					{
						$where .= ' and ';
					}
					if($f->RefFieldCode)
					{
						$where .= sprintf('ifnull(v.Ref_%1$s,0) = ifnull(t.Ref_%1$s,0)'
								,$f->FieldCode);
					}
					else
					{
						$where .= sprintf('ifnull(v.%1$s,0)  =  ifnull(t.%1$s,0)'
								,$f->FieldCode);
					}
					if(!$f->RefFieldCode && $f->intFieldType <= 3 && $f->bUnique == 1)
					{
						$where .= Qss_Lib_Const::MYSQL_COLLATION;
					}
				}
			}
			if($where != '')
			{
				//Cập nhật IFID và IOID nếu có sẵn
				if(!$this->_ignore)
				{
					$InFormwhere = '';
					if(!$object->b_Main)
					{
						$InFormwhere = sprintf(' and (t.IFID_%1$s = v.ExistsIFID or t.IFID_%1$s = v.IFID_%1$s)'
						,$this->FormCode);
					}
					$sqlUpdate = sprintf('update tmp_%1$s as v
										INNER JOIN %1$s AS t ON %2$s
										SET v.ExistsIFID = t.IFID_%3$s,v.ExistsIOID = t.IOID
										where v.ExistsIOID = 0 %4$s'
										,$objectcode
										,$where
										,$this->FormCode
										,$InFormwhere);
					$this->_o_DB->execute($sqlUpdate);
					//update eIFID cho các sub object
					if($object->b_Main)
					{
						foreach($this->_objects as $updateIFIDObject)
						{
							if($updateIFIDObject != $objectcode)
							{
								$sqlUpdateIFID = sprintf('update tmp_%1s as v
										inner join tmp_%2$s as t on  v.IFID_%3$s = t.IFID_%3$s 
										set v.ExistsIFID = t.ExistsIFID,v.IFID_%3$s = if(t.ExistsIFID=0,v.IFID_%3$s,t.ExistsIFID)'//trường hợp update thì thêm vào, còn mới thì chưa có nên =0 
								,$updateIFIDObject
								,$objectcode
								,$this->FormCode);
								$this->_o_DB->execute($sqlUpdateIFID);
							}
						}
					}
				}
				$condition1 = '';
				$condition2 = '';
				if(!$object->b_Main)
				{
					$condition1 = sprintf(' and (t.IFID_%1$s = v.ExistsIFID and t.IOID != v.ExistsIOID)'
					,$this->FormCode);
					$condition2 = sprintf(' and (t.IFID_%1$s = v.IFID_%1$s and t.IOID != v.IOID)'
					,$this->FormCode);
				}
				//check tất cả chính và phuj
				$sqlUpdate = sprintf('update tmp_%1$s as v
										SET v.Error = %4$d,v.ErrorMessage="%5$s"
								where v.ExistsIOID = 0 and Error = 0
								and exists (select 1 from %1$s AS t where %2$s %3$s)'//v.ExistsIFID = 0 and v.ExistsIOID = 0 and
				,$objectcode
				,$where
				,$condition1
				,self::ERROR_TYPE_DUPLICATE
				,$select);
				$this->_o_DB->execute($sqlUpdate);
				//check phụ
				if($condition2 != '')
				{
					$sqlUpdate = sprintf('update tmp_%1$s as v
										SET v.Error = v.Error|%4$d,v.ErrorMessage="%5$s"
									where v.ExistsIOID = 0 and Error = 0
									and exists (select 1 from %1$s AS t where %2$s %3$s)'//v.ExistsIFID = 0 and v.ExistsIOID = 0 and @todo check lại
					,$objectcode
					,$where
					,$condition2
					,self::ERROR_TYPE_DUPLICATE
					,$group);
					$this->_o_DB->execute($sqlUpdate);
				}
			}

			//Check yêu cầu, cân nhắc nếu có existIFID và eIOID
			$where = '';
			$where2 = '';
			$require = '';
			foreach ($object->loadFields() as $f)
			{
				if($f->bRequired)
				{
					if($require != '')
					{
						$require .= ',';
					}
					$require .= $f->FieldCode;
					if($where != '')
					{
						$where .= ' or ';
					}
					$where .= sprintf('v.%1$s is null'
					,$f->FieldCode);
					if($f->bEditStatus)
					{
						if($where2 != '')
						{
							$where2 .= ' or ';
						}
						$where2 .= sprintf('v.%1$s is null'
						,$f->FieldCode);
					}
				}
			}
			if($where != '')//cho trường hợp insert vào
			{
				$sqlUpdate = sprintf('UPDATE tmp_%1$s as v
					SET v.Error = v.Error|%3$d,v.ErrorMessage="%4$s"
					where (%2$s) and v.ExistsIOID = 0 and v.Error = 0'//and v.ExistsIFID = 0
				,$objectcode
				,$where
				,self::ERROR_TYPE_REQUIRED
				,$require);
				$this->_o_DB->execute($sqlUpdate);
			}
			if($where2 != '')//cho trường hợp update, có trường ko sửa vẫn giữ nguyên
			{
				$sqlUpdate = sprintf('UPDATE tmp_%1$s as v
					SET v.Error = v.Error|%3$d,v.ErrorMessage="%4$s"
					where (%2$s) and v.ExistsIFID != 0 and v.ExistsIOID != 0 and v.Error = 0'
					,$objectcode
					,$where2
					,self::ERROR_TYPE_REQUIRED
					,$require);
					$this->_o_DB->execute($sqlUpdate);
			}
			//update Lỗi các đối tuownjg phụ nếu thằng chính lỗi
			if($object->b_Main)
			{
				foreach($this->_objects as $updateErrorObject)
				{
					if($updateErrorObject != $objectcode)
					{
						$sqlUpdateError = sprintf('update tmp_%1s as v
									inner join tmp_%2$s as t on  (v.IFID_%3$s = t.IFID_%3$s or (v.ExistsIFID !=0 and v.ExistsIFID = t.ExistsIFID)) 
									SET v.Error = t.Error|v.Error,v.ErrorMessage=concat(ifnull(v.ErrorMessage,""),t.ErrorMessage)
									where v.Error = 0 and t.Error <> 0'
									,$updateErrorObject
									,$objectcode
									,$this->FormCode);
									$this->_o_DB->execute($sqlUpdateError);
					}
				}
			}
			$this->hookValidate($objectcode);
			//if(!$this->_nocheckall)
			//nếu tham số được là phải được hết thì kiểm tra từng đối tượng, nếu có lỗi là set error= true;
			{
				$sql = sprintf('select 1 from tmp_%1s
									where Error <> 0'
									,$objectcode);
									$dataSQL = $this->_o_DB->fetchOne($sql);
				if($dataSQL)
				{
					$this->_error = true;
				}
			}
		}
	}
	public function updateExists()
	{
		foreach($this->_objects as $objectcode)
		{
			$object = $this->o_fGetObjectByCode($objectcode);
			//chỉ lấy từ table cũ vì nếu là mới thì chưa có id mà chỉ có value
			$set = '';
			foreach ($object->loadFields() as $f)
			{
				if($f->bEditStatus)
				{
					if($set != '')
					{
						$set .= ',';
					}
					$set .= sprintf(' v.%1$s = t.%1$s',$f->FieldCode);
					if(($f->RefFieldCode && $f->intInputType != 1)
					|| $f->intFieldType == 14
					|| $f->intFieldType == 16
					|| (($f->intInputType == 5 || $f->intInputType == 3) && Qss_Json::decode($f->szRegx) !== null))
					{
						if($set != '')
						{
							$set .= ',';
						}
						$set .= sprintf(' v.Ref_%1$s = t.Ref_%1$s',$f->FieldCode);
					}
				}
			}
			if($set != '')
			{
				$sqlUpdate = sprintf('update %1$s as v
							inner join tmp_%1$s as t on v.IFID_%3$s = t.ExistsIFID and v.IOID = t.ExistsIOID 
							set %2$s
							where t.Error = 0'
							,$object->ObjectCode
							,$set
							,$this->FormCode);
							$this->_o_DB->execute($sqlUpdate);
			}
		}
	}
	public function updateTree()
	{
		foreach ($this->_objects as $objectcode)
		{
			$object = $this->o_fGetObjectByCode($objectcode);
			if($object->b_Tree)
			{
				$object->createTree($object->b_Main,'IFID_'.$this->FormCode);
			}
		}
	}
	public function copyPicture()
	{
		$arrPicture = array();
		foreach ($this->_objects as $objectcode)
		{
			if(isset($this->_arrPicture[$objectcode]))
			{
				$sql = sprintf('select * from tmp_%1$s where Error = 0',$objectcode);
				$dataSQL = $this->_o_DB->fetchAll($sql);
				foreach($dataSQL as $item)
				{
					foreach ($this->_arrPicture[$objectcode] as $fieldcode=>$f)
					{
						$ext = $item->{$fieldcode};
						if($ext)
						{
							$tmpfile = QSS_DATA_DIR . "/tmp/" . $ext;
							$destfile = QSS_DATA_DIR . "/documents/" . $ext;
							copy($tmpfile, $destfile);
							Qss_Lib_Template::PictureResize($destfile, $f->szTValue, $f->szFValue);
							@unlink($tmpfile);
						}
					}
				}
			}
		}
	}
	public function insertData()
	{
		//insert qsiforms
		$formCount = 0;
		$formLastID = 0;
		foreach ($this->_objects as $objectcode)
		{
			$objectCount = 0;
			$replaceIFID = array();
			$replaceIFIDTmp = array();
			$replaceIOID = array();
			$replaceIOIDTmp = array();
			$object = $this->o_fGetObjectByCode($objectcode);
			//phục vụ việc add vào tmp_IOID và tmp_IFID để lát nữa update
			if($object->b_Main)
			{
				$sql = sprintf('select * from tmp_%1$s where Error = 0 and ExistsIFID=0 order by ABS(IOID) ASC'//
				,$object->ObjectCode);
			}
			else
			{
				$sql = sprintf('select * from tmp_%1$s where Error = 0 and ExistsIOID=0 order by ABS(IOID) ASC'//
				,$object->ObjectCode);
			}
			$objectData = $this->_o_DB->fetchAll($sql);
			$objectCount = count($objectData);
			if($objectCount)
			{
				if($object->b_Main)
				{
					//lấy cái form chưa có eIFID
					$formCount = count($objectData);
					$ifidSQL = '';
					for ($i = 0;$i < $formCount; $i++)
					{
						if($ifidSQL != '')
						{
							$ifidSQL .= ',';
						}
						$status = 0;
						if($this->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
						{
							$status = 1;
							if(isset($this->_data[$i][$objectcode][0]['status']))
							{
								$status = (int) $this->_data[$i][$objectcode][0]['status'];
								$status = $status ? $status : 1;
							}
						}
						$ifidSQL .= sprintf('(%1$d,%2$d,%3$d,%4$s,%5$d,0)'
						,time()
						,$this->i_UserID
						,$status
						,$this->_o_DB->quote($this->FormCode)
						,$objectData[$i]->DeptID?$objectData[$i]->DeptID:$this->i_DepartmentID);
					}
					$sql = sprintf('insert into qsiforms(SDate,UID,Status,FormCode,DepartmentID,deleted)
						values%1$s',$ifidSQL);
					$formLastID = $this->_o_DB->execute($sql);

					for($i=$formLastID;$i < ($formLastID + $formCount);$i++)
					{
						$replaceIFID[] = $i;
					}

				}
				//tạo sql update cho các field của objects
				//$fieldUpdate = 'DeptID';
				$fieldUpdate = sprintf('IFID_%1$s,DeptID',$this->FormCode);
				foreach ($object->loadFields() as $f)
				{
					if($f->bEditStatus)
					{
						if($fieldUpdate != '')
						{
							$fieldUpdate .= ',';
						}
						$fieldUpdate .= $f->FieldCode;
						if(($f->RefFieldCode && $f->intInputType != 1)
						|| $f->intFieldType == 14
						|| $f->intFieldType == 16
						|| (($f->intInputType == 5 || $f->intInputType == 3) && Qss_Json::decode($f->szRegx) !== null))
						{
							if($fieldUpdate != '')
							{
								$fieldUpdate .= ',';
							}
							$fieldUpdate .='Ref_'.$f->FieldCode;
						}
					}
				}
				if($object->b_Main)
				{
					$sql = sprintf('insert into %1$s(%2$s) select %2$s from tmp_%1$s where Error=0 and ExistsIFID = 0 order by ABS(IFID_%3$s) ASC'
					,$object->ObjectCode
					,$fieldUpdate
					,$this->FormCode);
				}
				else
				{//loanh quanh sắp xếp
					$sql = sprintf('insert into %1$s(%2$s) select %2$s from tmp_%1$s where Error=0 and ExistsIOID = 0 order by ABS(IOID) ASC'
					,$object->ObjectCode
					,$fieldUpdate);
				}
				$objectLastID = $this->_o_DB->execute($sql);
				for($i=$objectLastID;$i < ($objectLastID + $objectCount) ;$i++)
				{
					$replaceIOID[] = $i;
				}
					
				foreach ($objectData as $data)
				{
					if($object->b_Main)
					{
						$replaceIFIDTmp[] = $data->{'IFID_'.$this->FormCode};
					}
					$replaceIOIDTmp[] = $data->IOID;
				}
				if($object->b_Main)
				{
					//tạo bảng temp để insert cặp IFID vào
					$formTmpSQL = '';
					foreach($replaceIFID as $k=>$v)
					{
						if($formTmpSQL != '')
						{
							$formTmpSQL .= ',';
						}
						$formTmpSQL .= sprintf('(%1$d,%2$d)',$v,$replaceIFIDTmp[$k]);
					}
					$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_IFID');
					$this->_o_DB->execute($sql);
					$sql = sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS tmp_IFID(oldIFID int,newIFID int)');
					$this->_o_DB->execute($sql);
					$sql = sprintf('INSERT INTO tmp_IFID(oldIFID,newIFID) VALUES%1$s',$formTmpSQL);
					$this->_o_DB->execute($sql);
				}
				$objectTmpSQL = '';
				foreach($replaceIOID as $k=>$v)
				{
					if($objectTmpSQL != '')
					{
						$objectTmpSQL .= ',';
					}
					$objectTmpSQL .= sprintf('(%1$d,%2$d,%3$s)'
					,$v
					,$replaceIOIDTmp[$k]
					,$this->_o_DB->quote($object->ObjectCode));
				}
				//$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_IOID');
				//$this->_o_DB->execute($sql);
				//$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_IOID');
				//$this->_o_DB->execute($sql);
				$sql = sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS tmp_IOID(oldIOID int,newIOID int,Code varchar(255))');
				$this->_o_DB->execute($sql);
				$sql = sprintf('INSERT INTO tmp_IOID(oldIOID,newIOID,Code) VALUES%1$s',$objectTmpSQL);
				$this->_o_DB->execute($sql);
				//@todo:
				/*$sql = sprintf('DROP TABLE IF EXISTS tmp_IOID_%1$s',$object->ObjectCode);
				$this->_o_DB->execute($sql);
				$sql = sprintf('CREATE TABLE IF NOT EXISTS tmp_IOID_%1$s select * from tmp_IOID',$object->ObjectCode);
				$this->_o_DB->execute($sql);*/

				//@todo:
				/*$sql = sprintf('DROP TABLE IF EXISTS tmp_%1$s_1',$object->ObjectCode);
				$this->_o_DB->execute($sql);
				$sql = sprintf('CREATE TABLE IF NOT EXISTS tmp_%1$s_1 select * from tmp_%1$s',$object->ObjectCode);
				$this->_o_DB->execute($sql);
				$sql = sprintf('DROP TABLE IF EXISTS tmp_%1$s_2',$object->ObjectCode);
				$this->_o_DB->execute($sql);
				$sql = sprintf('CREATE TABLE IF NOT EXISTS tmp_%1$s_2 select * from tmp2_%1$s',$object->ObjectCode);
				$this->_o_DB->execute($sql);*/

				//chỉnh lại ifid
				//cập nhật thằng cũ nếu có
				$sql = sprintf('update %1$s as v
							inner join tmp_IOID as t on v.IOID = t.oldIOID
							inner join tmp_%1$s as t1 on t1.IOID = t.newIOID
							set v.IFID_%2$s = t1.ExistsIFID
							where v.IFID_%2$s is null and ifnull(t1.ExistsIFID,0) != 0'
							,$object->ObjectCode
							,$this->FormCode);
							$this->_o_DB->execute($sql);
							if($formCount)//câp nhật thằng mới
							{
								$sql = sprintf('update %1$s as v
							inner join tmp_IFID as t on v.IFID_%2$s = t.newIFID
							set v.IFID_%2$s = t.oldIFID
							where v.IFID_%2$s < 0'
							,$object->ObjectCode
							,$this->FormCode);
							$this->_o_DB->execute($sql);
							}
							//chỉnh lại ioid cho thằng mới
							$sql = sprintf('update %1$s as v
							inner join tmp_IOID as t on v.IOID = t.newIOID
							set v.IOID = t.oldIOID
							where v.IOID < 0'
							,$object->ObjectCode);
							$this->_o_DB->execute($sql);
							//chỉnh lại các looup ioid
							foreach ($object->loadFields() as $f)
							{
								if($f->bEditStatus)
								{
									if(($f->RefFieldCode && $f->intInputType != 1) && in_array($f->RefObjectCode,$this->_objects))
									{
										$sql = sprintf('update %1$s as v
									inner join tmp_IOID as t on v.Ref_%2$s = t.newIOID
									set v.Ref_%2$s = t.oldIOID
									where v.Ref_%2$s is not null'
									,$object->ObjectCode
									,$f->FieldCode);
									$this->_o_DB->execute($sql);
									}
								}
							}
			}
			//update trường hợp lookup đến đúng object với những exists IOID
			foreach ($object->loadFields() as $f)
			{
				if($f->bEditStatus)
				{
					if(($f->RefFieldCode && $f->intInputType != 1) && $f->RefObjectCode == $f->ObjectCode)
					{
						$sql = sprintf('update %1$s as v
								inner join tmp_%1$s as t on v.Ref_%2$s = t.IOID
								set v.Ref_%2$s = t.ExistsIOID
								where v.Ref_%2$s < 0 and t.ExistsIOID is not null'
								,$f->ObjectCode
								,$f->FieldCode);
								$this->_o_DB->execute($sql);
					}
				}
			}
			//chỉnh lại các looup ioid âm, trường hợp thêm đối tượng phụ, cập nhật đối tượng phụ
			if(!$object->b_Main)
			{
				foreach ($object->loadFields() as $f)
				{
					if($f->bEditStatus)
					{
						if(($f->RefFieldCode && $f->intInputType != 1) && $f->RefFormCode == $object->FormCode)
						{
							$sql = sprintf('update %1$s as v
									inner join tmp_%3$s as t on v.Ref_%2$s = t.IOID
									set v.Ref_%2$s = t.ExistsIOID
									where v.Ref_%2$s < 0'
									,$object->ObjectCode
									,$f->FieldCode
									,$f->RefObjectCode);
									$this->_o_DB->execute($sql);
						}
					}
				}
			}
			//nếu là radio
			foreach ($object->loadFields() as $f)
			{
				if($f->bEditStatus)
				{
					if(($f->intInputType == 5 || $f->intInputType == 3) && Qss_Json::decode($f->szRegx) !== null)
					{
						$json = (array) Qss_Json::decode($f->szRegx);
						if($json)
						{
							foreach ($json as $key=>$value)
							{
								$sql = sprintf('update %1$s set Ref_%2$s = %3$s where %2$s = %4$s'
								,$object->ObjectCode
								,$f->FieldCode
								,$this->_o_DB->quote($value)
								,$this->_o_DB->quote($key));
								$this->_o_DB->execute($sql);
							}
						}
					}
				}
			}
		}
	}
	public function countFormError()
	{
		$formError = 0;
		$object = $this->o_fGetMainObject();
		if(in_array($object->ObjectCode,$this->_objects))
		{
			$sql = sprintf('select count(*) as Error from tmp_%1$s where Error != 0'
			,$object->ObjectCode);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			$formError = $dataSQL?$dataSQL->Error:0;
		}
		return $formError;
	}
	public function countFormImported()
	{
		$formError = 0;
		$object = $this->o_fGetMainObject();
		if(in_array($object->ObjectCode,$this->_objects))
		{
			$sql = sprintf('select count(*) as Error from tmp_%1$s where Error = 0'
			,$object->ObjectCode);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			$formError = $dataSQL?$dataSQL->Error:0;
		}
		return $formError;
	}
	public function countObjectError()
	{
		$objectError = 0;
		foreach ($this->_objects as $objectcode)
		{
			$object = $this->o_fGetObjectByCode($objectcode);
			$sql = sprintf('select count(*) as Error from tmp_%1$s where Error != 0'
			,$object->ObjectCode);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			$objectError += $dataSQL?$dataSQL->Error:0;
		}
		return $objectError;
	}
	public function getImportRows()
	{
		$ret = array();//return $ret;
		foreach ($this->_objects as $objectcode)
		{
			$object = $this->o_fGetObjectByCode($objectcode);
			$sql = sprintf('select * from tmp_%1$s'
			,$object->ObjectCode);
			$dataSQL = $this->_o_DB->fetchAll($sql);
			$ret[$objectcode] = $dataSQL;
		}
		return $ret;
	}
	public function getErrorRows()
	{
		$ret = array();//return $ret;
		foreach ($this->_objects as $objectcode)
		{
			$object = $this->o_fGetObjectByCode($objectcode);
			$sql = sprintf('select * from tmp_%1$s where Error <> 0 order by No'
			,$object->ObjectCode);
			$dataSQL = $this->_o_DB->fetchAll($sql);
			$ret[$objectcode] = $dataSQL;
		}
		return $ret;
	}
	public function getIFIDs()
	{
		$sql = sprintf('select * from tmp_IFID');
		return $this->_o_DB->fetchAll($sql);
	}
	public function cleanTemp()
	{
		foreach($this->_objects as $objectCode)
		{
			$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_%1$s',
			$objectCode);
			$this->_o_DB->execute($sql);
			$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp2_%1$s',
			$objectCode);
			$this->_o_DB->execute($sql);
		}
		$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_IFID');
		$this->_o_DB->execute($sql);
		$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_IOID');
		$this->_o_DB->execute($sql);
	}
	public function isError()
	{
		return $this->_error;
	}
	public function hookValidate($objectcode)
	{
		$classname = 'Qss_Bin_Import_'.$this->FormCode.'_' . $objectcode;
		if(class_exists($classname))
		{
			$validate = new $classname($this);
			$validate->__doExecute();
		}
	}
	public function debugs()
	{
		echo '<pre>';
		foreach ($this->_objects as $objectcode)
		{
			$sql = sprintf('select * from tmp_%1$s'
			,$objectcode);
			$dataSQL = $this->_o_DB->fetchAll($sql);
			print_r($dataSQL);
		}
		$sql = sprintf('select * from tmp_IOID'
		,$objectcode);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		print_r($dataSQL);
	}
	public function updateReferLookup()
	{
		foreach($this->_objects as $objectcode)
		{
			$object = $this->o_fGetObjectByCode($objectcode);
			//chỉ lấy từ table cũ vì nếu là mới thì chưa có id mà chỉ có value
			foreach ($object->loadFields() as $f)
			{
				if($f->bEditStatus)
				{
					if($f->intFieldType == 1
					|| $f->intFieldType == 2
					|| $f->intFieldType == 3)
					{
						$this->updateLookUpText($f->ObjectCode,$f->FieldCode);
					}
				}
			}
		}
	}
	public function updateLookUpText($ObjectCode,$FieldCode)
	{
		$sqlLookup = sprintf('select distinct FieldCode,qsfields.ObjectCode
								from qsfields 
								inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
								inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode
								inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
								where qsforms.Effected = 1 and RefObjectCode = %2$s and RefFieldCode = %1$s',
		$this->_o_DB->quote($FieldCode)
		,$this->_o_DB->quote($ObjectCode));
		$dataLookup = $this->_o_DB->fetchAll($sqlLookup);
		foreach ($dataLookup as $lookup)
		{
			if($this->_o_DB->tableExists($lookup->ObjectCode))
			{
				$sqlUpdateLookup = sprintf('update %1$s as t
										inner join %2$s as v on v.IOID = t.Ref_%4$s
										set t.%4$s = v.%3$s'
				,$lookup->ObjectCode
				,$ObjectCode
				,$FieldCode
				,$lookup->FieldCode);
				//echo $sqlUpdateLookup;die;
				$this->_o_DB->execute($sqlUpdateLookup);
				$this->updateLookUpText($lookup->ObjectCode,$lookup->FieldCode);
			}
		}
	}
}
?>