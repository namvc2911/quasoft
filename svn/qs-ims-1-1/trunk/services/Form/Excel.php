<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';

class Qss_Service_Form_Excel extends Qss_Service_Abstract
{

	protected $szFN;

	protected $objWriter;

	protected $arrConfig;
	
	protected $arrBorder = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		)
		)
		);
	protected $arrColumns;
	
	public function __doExecute ($sql, Qss_Model_Form $form, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0)
	{
		$this->arrColumns = array_keys(Qss_Lib_Const::$EXCEL_COLUMN);
		$this->szFN = QSS_DATA_DIR . "/tmp/" . uniqid() . ".xls";
		$objPHPExcel = new PHPExcel();
		$this->objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$ws = $objPHPExcel->setActiveSheetIndex(0);
		$object = $form->o_fGetMainObject();
		$mainobjects = $form->o_fGetMainObjects();
		$ws->setTitle($object->sz_Name);
		$this->genHeader($ws, $mainobjects);
		$objects = $object->a_fGetIOIDBySQL($sql[0], $currentpage, $limit);
		$j = 2;
		foreach ($objects as $data)
		{
			$i = 0;
			foreach($mainobjects as $object)
			{
				$object->initData($data->IFID, $data->DepartmentID, $data->IOID);
				$object->v_fResetFields();
				foreach ($fields as $f)
				{
					$cell = $ws->getCellByColumnAndRow($i, $j);
					if($f->intFieldType == 11)
					{
						$cell->setValue(str_replace('.', '',$f->getValue()));
					}
					else
					{
						$cell->setValue($f->getValue());
					}
					$style = $ws->getStyle($this->arrColumns[$i].$j.':'.$this->arrColumns[$i].$j);
					$style->applyFromArray($this->arrBorder);
					$i ++;
				}
			}
			$j ++;
		}
		$this->fixWidth($ws, $mainobjects);
		$this->objWriter->save($this->szFN);
		return $this->szFN;
	}

	private function getRow ($ws, $idx)
	{
		foreach ($ws->getRowIterator() as $row)
		{
			if ( $row->getRowIndex() == $idx )
			return $row;
		}
		return null;
	}

	function fetchHeader ($ws, $mainobjects)
	{
		$this->arrConfig = array();
		$row = $this->getRow($ws, 1);
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell)
		{
			if ( !is_null($cell) )
			{
				$fields = $object->loadFields();
				foreach ($fields as $f)
				{
					if ( $f->ObjectCode . '_' . $f->FieldCode == $cell->getValue() )
					{
						$this->arrConfig[$f->ObjectCode][$f->FieldCode] = $cell->getColumn();
					}
				}
			}
		}
		if ( !sizeof($this->arrConfig) )
		{
			return false;
		}
		$fields = $object->loadFields();
		foreach ($fields as $f)
		{
			if ( $f->bRequired && !isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]) )
			{
				return false;
			}
		}
		return true;
	}

	function genHeader ($ws, $mainobjects)
	{ 
		$i = 0;
		foreach($mainobjects as $object)
		{
			$fields = $object->loadFields();
			foreach ($fields as $f)
			{
				$cell = $ws->getCellByColumnAndRow($i, 1);
				$cell->setValue($f->szFieldName);
				$style = $ws->getStyle($this->arrColumns[$i].'1:'.$this->arrColumns[$i].'1');
				$style->applyFromArray($this->arrBorder);
				$style->getFont()->setBold(true);
				$i ++;
			}
		}
	}
	function fixWidth ($ws, $mainobjects)
	{ 
		$i = 0;
		foreach($mainobjects as $object)
		{
			$fields = $object->loadFields();
			foreach ($fields as $f)
			{
				$ws->getColumnDimension($this->arrColumns[$i])->setAutoSize(true);
				$i ++;
			}
		}
	}
}
?>