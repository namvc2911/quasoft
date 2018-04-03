<?php
require_once QSS_ROOT_DIR . '/library/PHPExcel.php';
require_once QSS_ROOT_DIR . '/library/PHPExcel/IOFactory.php';
class Qss_Model_Import extends Qss_Model_Form
{
	protected $szMessage;
	protected $arrImportedRow;
	protected $szFN;
	protected $arrConfig;
	protected $intImported;
	protected $intError;
	protected $objWriter;
    function __constructor()
    {
        parent::__constructor();
    }
    function fetchHeader($ws,$object)
    {
		$this->arrConfig = array();
		$row = $this->getRow($ws,1);
		$cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
        foreach ($cellIterator as $cell)
        {
            if (!is_null($cell))
            {     
                foreach($object->arrFields as $f)
				{
					if($f->ObjectCode.'_'.$f->FieldCode == $cell->getValue())
					{
						$this->arrConfig[$f->ObjectCode][$f->FieldCode] = $cell->getColumn();
					}
				}
            }
        }
		if(!sizeof($this->arrConfig))
		{
			return false;
		}
        foreach($object->arrFields as $f)
		{
			if($f->bRequired && !isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]))
			{
				return false;
			}
		}
		return true;
    }

    function getImportPage()
    {
		global $sysfolder,$db,$params;
		$download = get_param('download');
		$fn="../$sysfolder/designs/eimport.html";
		$this->data['js'] = $this->getJS();
		$this->data["banner"] = TMenu::getMenu();	
		if($params->pIFID)
		{
			if(!$this->init1($params->pIFID,$params->pDeptID))
			{
				return;
			}
		}
		elseif($params->pFID)
		{
			if(!$this->init($params->pFID,$this->intDepartmentIDM))
			{
				return;
			}
		}
		if($download)
		{
			$this->download($download);
		}
		if(!$params->pIFID)
		{
			$sql=sprintf('select qsforms.* from qsforms 
					inner join qsuserforms on qsforms.FormCode=qsuserforms.FormCode 
					where qsuserforms.GroupID in(%1$s) and Rights !=0',
					$this->szGroupList);
			$db->query($sql);
			$array[0]="----- Chọn module import -----";		
			while($db->next_record())
			{
				$oparams = clone $params;
				$oparams->pFID = $db->f("FormCode");
				$array[ObjectToParams($oparams)]=$db->f("Name");
			}
			$db->close();
			$export = parent::ComboBox("params",$array,ObjectToParams($params),400);
			$this->getButton(array('btnUPDATE'));	
		}
		else
		{
			$export = $this->ArrObject[$params->pOID]->title;
			$linkparams = ObjectToParams($params);
			$export .= "<input type='hidden' name='params' value='{$linkparams}'>";
			$this->getButton(array('btnUPDATE','btnBACK'));	
		}
		if($params->pFID)
		{
			$this->doImport();
		}
		$this->data["module"] = $export . ' <a href="#" onclick="download(1);">Tải mẫu</a> '
										. ' <a href="#" onclick="download(2);">Tải mẫu và dữ liệu</a> '
										. ' <a href="#" onclick="download(3);">Tải dữ liệu</a> ';
		$this->data["file"] = parent::File("pFile",'','');	
		$this->data["message"] = $this->szMessage;
		$this->data["grid"] = $this->getImportedGrid();
		return parent::parseTemplateFile($fn,$this->data);
    }
	function getImportedGrid()
	{
		$ret = '';
		if(sizeof($this->arrImportedRow))
		{
			$ret = '<table class="grid">';
			foreach($this->arrImportedRow as $val)
			{
				$ret .= $val;
			}
			$ret .= '</table>';
		}
		return $ret;
	}
    function printRow($object)
    {
        $ret = '<tr class="imported">';
        foreach($object->arrFields as $f)
        {
			if(isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]))
			{
				$ret .= '<td>';
				$ret .= $f->getValue();
				$ret .= '</td>';
			}
        }
        $ret .= '</tr>';
		$this->intImported++;
		$this->arrImportedRow[] = $ret;
    }
    function printErrorRow($object)
    {
        $ret = '<tr class="error">';
        foreach($object->arrFields as $f)
        {
			if(isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]))
			{
				$ret .= '<td>';
				$ret .= $f->getValue();
				$ret .= '</td>';
			}
        }
        $ret .= '</tr>';
		$this->intError++;
		$this->arrImportedRow[] = $ret;
    }
    function doImport()
    {
		global $sysfolder,$params;
		if(!is_dir("../tmp"))
		{
			mkdir("../tmp");
		}
		$this->arrImportedRow = array();
		$this->intImported = 0;
		$this->intError = 0;
		if($params->pOID)
		{
			$object = $this->ArrObject[$params->pOID];		
		}
		else
		{
			$arr = array_keys($this->ArrObject);
			$key = $arr[0];
			$object = $this->ArrObject[$key];	
		}
		$object->LoadFields();
		if(isset($_FILES['pFile']))
		{	
			if($_FILES['pFile']['error']==UPLOAD_ERR_OK)
			{
				$limitedext=array("application/vnd.ms-excel","application/force-download");
				if(in_array(strtolower($_FILES['pFile']["type"]),$limitedext))
				{
					$ext = strtolower(pathinfo($_FILES['pFile']['name'],PATHINFO_EXTENSION));
					$this->szFN = "../tmp/" . uniqid() . ".xls";
					$ret=@move_uploaded_file($_FILES['pFile']["tmp_name"],$this->szFN);
				}
			}
		}
		if(file_exists($this->szFN))
		{	
	        $objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load($this->szFN);
			$ws = $objPHPExcel->setActiveSheetIndex(0);
			if($this->fetchHeader($ws,$object))
			{
				foreach ($ws->getRowIterator() as $row)
				{
					if($row->getRowIndex() == 1)
					{
						continue;
					}
					$this->importRow($ws,$row,$object);
				}
				$this->szMessage = "{$this->intImported} dòng được import, {$this->intError} dòng bị lỗi";
			}
			else
			{
				$this->szMessage = 'File không đúng định dạng';
			}
		}
    }
    function importRow($ws,$row,$object)
    {
		$object->init($this->intIFID,$this->intDepartmentID,-1,$this->intDepartmentIDM);
		$object->LoadFields();
        foreach($object->arrFields as $f)
        {
			if(isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]))
			{
				$cell = $ws->getCell($this->arrConfig[$f->ObjectCode][$f->FieldCode].$row->getRowIndex());
				if($f->intInputType == 3 || $f->intInputType == 4 || $f->intInputType == 11 || $f->intInputType == 12)
				{
					$user = Qss_Register::get('userinfo');
					$val = $f->LookupValue($cell->getValue(),$f->intRefObjectCode,$f->RefFieldCode,$f->intRefFormCode,$user);
					$f->setValue($val);
				}
				else
				{
					$f->setValue($cell->getValue());
				}
			}
        }
        if($object->doUpdate())
        {
            $this->printRow($object);
        }
        else
        {
            $this->printErrorRow($object);
        }
    }
	private function getRow($ws,$idx)
    {
        foreach ($ws->getRowIterator() as $row) 
		{
			if($row->getRowIndex() == $idx)
				return $row;
		}
		return null;
    }
	function __destruct()
	{
		if(file_exists($this->szFN))
		{
			unlink($this->szFN);
		}
	}
	function download($download)
	{
		global $sysfolder,$params;
		if(!is_dir("../tmp"))
		{
			mkdir("../tmp");
		}
		if($params->pOID)
		{
			$object = $this->ArrObject[$params->pOID];		
		}
		else
		{
			$arr = array_keys($this->ArrObject);
			$key = $arr[0];
			$object = $this->ArrObject[$key];	
		}
		$object->LoadFields();
		$this->szFN = "../tmp/" . uniqid() . ".xls";
		$objPHPExcel = new PHPExcel();
        $this->objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
		//$objPHPExcel = $objReader->load($this->szFN);
		$ws = $objPHPExcel->setActiveSheetIndex(0);
		$this->doExport($ws,$object,$download);
	}
	function doExport($ws,$object,$download)
	{
		global $db3;
		$ws->setTitle($object->title);
		$this->genHeader($ws,$object,$download);
		if($download == 2 || $download == 3)
		{
			if($this->intType == 1)
			{
				$sql=sprintf('select * from %1$s as v
					inner join qsiforms on qsiforms.IFID=v.IFID_%2$s
					inner join qsusers on qsiforms.UID=qsusers.UID
					where qsiforms.Deleted<>1'
					,$object->ObjectCode,
					$this->FormCode);
			}
			elseif($this->intType == 2)
			{
				$sql=sprintf('select * from %1$s as v
					inner join qsiforms on qsiforms.IFID=v.IFID_%2$s
					inner join qsusers on qsiforms.UID=qsusers.UID
					where qsiforms.Deleted<>1 
					and qsrecforms.DepartmentID in(%3$d)'
					,$object->ObjectCode,
					$this->FormCode,
					$this->user_info->user_dept_id.','.$this->szDeptList);
			}
			else
			{
				$sql=sprintf('select qsrecforms.*,IFNULL(qsusers.UserName,\'Mới đăng ký\') as UserName,qsiforms.*,qsworkflowsteps.SID, 
					IFNULL(qsworkflowsteps.Name,\'Hoàn thành\') as Name ,qsworkflowsteps.StepNo
					from %1$s as v
					inner join qsiforms on qsiforms.IFID=v.IFID_%3$s
					left join qsusers on qsiforms.UID=qsusers.UID
					left join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=%4$d
					where qsiforms.UID!=0 and (qsiforms.IFID in(Select IFID From qsftrace where UID=%2$d) 
					or qsiforms.UID=%2$d or qsiforms.GroupID in(%7$s) or (%5$d&8 and qsiforms.DepartmentID in(%6$s))) and qsiforms.deleted<>1'
					,$object->ObjectCode,
					$this->intUserID,
					$this->FormCode,
					$this->intWorkFlowID,
					$object->intRights,
					$this->user_info->user_dept_id.','.$this->szDeptList,
					$this->szGroupList);
	
			}
			if(!$object->bMain)
			{
				$sql=sprintf('select * from %2$s as v
					inner join qsiforms on qsiforms.IFID=v.IFID_%3$s
					where qsiforms.Deleted<>1 and qsiforms.IFID=%1$d and qsiforms.DepartmentID=%4$d %5$s',
					$this->i_IFID,
					$object->ObjectCode,
					$this->FormCode,
					$this->intDepartmentID,
					($object->intParentID?'and qsrecforms.ParentID='.$object->intIParentID:''));
			}
			$db3->query($sql);
			$j = 2;
			while($db3->next_record())
			{
				$object->init($db3->f('IFID'),$db3->f('DepartmentID'),$db3->f('IOID'),$db3->f('ObjectDept'));
				$object->code  					= $db3->f('code');
				$object->LoadFields();
				$i = 0;
				foreach($object->arrFields as $f)
				{
					$cell = $ws->getCellByColumnAndRow($i,$j);
					$cell->setValue($f->strValue());
					$i++;
				}
				$j++;
			}
			$db3->close();
			$this->intDepartmentID = $this->user_info->user_dept_id;
			$this->intIOID=0;
		}
		$this->objWriter->save($this->szFN);
		if(file_exists($this->szFN)) 
		{
			$file = $this->szFN;
			header("Content-type: application/force-download");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: ".filesize($file));
			header("Content-disposition: attachment; filename=\"".basename($file)."\"");
			readfile("$file");
			unlink($this->szFN);
		} 
		else 
		{
			header("Content-type: text/html; charset=UTF-8");
			echo "Không tồn tại!";
		}
		die();
	}
	function genHeader($ws,$object,$download)
	{
		$i = 0;
        foreach($object->arrFields as $f)
		{
			$cell = $ws->getCellByColumnAndRow($i,1);
			if($download == 1 || $download == 2)
			{
				$cell->setValue($f->ObjectCode.'_'.$f->FieldCode);
			}
			else
			{
				$cell->setValue($f->szFieldName);
			}
			$i++;
		}
	}
}
?>