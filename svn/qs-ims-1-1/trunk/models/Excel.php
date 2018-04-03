<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';
class Qss_Model_Excel extends Qss_Model_Abstract
{
	public $wsMain;

	protected $objPHPExcel;

	protected $_arrData;
	
	protected $_arrExcelColumn;
	
	protected $_raw;


	function __construct($fn,$raw = false)
	{
		parent::__construct();
		if(!file_exists($fn))
		{
			throw new Qss_Exception('QSS error: No excel template file');
		}
		$this->_raw = $raw;
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$this->objPHPExcel = $objReader->load($fn);
		$this->_arrStyle = array();
		$this->_arrExcelColumn = array_keys(Qss_Lib_Const::$EXCEL_COLUMN);
	}
	function init($data = array(), $sheetindex = 0)
	{
		$this->wsMain = $this->objPHPExcel->setActiveSheetIndex($sheetindex);
		//load style row to array need to use for table row
		$this->_arrData = $data;
		$this->setSingleContent();
	}
	//-----------------------------------------------------------------------
	private function setSingleContent()
	{
		foreach ($this->wsMain->getRowIterator() as $row) {
			$this->setRowContent($row,$this->_arrData);
		}
	}

    public function getSheetIndexByName($name)
    {
        for($i = 0; $i < $this->objPHPExcel->getSheetCount(); $i++) {
            if($this->objPHPExcel->getSheet($i)->getTitle() == $name) {
                return $i;
            }
        }
        return -1; // Không tồn tại sheet này.
    }

	public function setActiveSheetByName($name)
    {
        for($i = 0; $i < $this->objPHPExcel->getSheetCount(); $i++) {
            if($this->objPHPExcel->getSheet($i)->getTitle() == $name) {
                $this->setActiveSheetIndex($i);
                break;
            }
        }
    }

	// Lấy tên sheet
    public function getSheetTitle($index)
    {
        return $this->objPHPExcel->getSheet($index)->getTitle();
    }

    // Merge các cell lại với nhau A1:B2
    public function mergeCells($rangeMerge)
    {
        $this->wsMain->mergeCells($rangeMerge);
    }

    // Thiết lập xem sheet nào đang hoạt động
    public function setActiveSheetIndex($sheetIndex) {
        $this->wsMain = $this->objPHPExcel->setActiveSheetIndex($sheetIndex);
    }

    // Thiết lập chiều cao của dòng
    public function setRowHeight($row, $height = -1)
    {
        $this->wsMain->getRowDimension($row)->setRowHeight($height);
    }

    // Thiết lập chiều rộng của cột
    public function setColumnWidth($column, $width = 0)
    {
        $this->wsMain->getColumnDimension($column)->setWidth($width);
    }

    // Xóa cột
    public function removeColumn($column)
    {
        $this->wsMain->removeColumn($column);
    }

    // Chuyển cột từ dạng text về dạng số  A => 1
    public function columnToNumber($colString)
    {
        return PHPExcel_Cell::columnIndexFromString($colString);
    }

    // Chuyển cột từ dạng số về dạng text  1 => A
    public function numberToColumn($colNumber)
    {
        return PHPExcel_Cell::stringFromColumnIndex($colNumber);
    }

    // Chèn ảnh vào trong ô
    public function setImage($row, $col, $width, $height, $image)
    {
        $width  = PHPExcel_Shared_Drawing::pixelsToPoints($width);
        $height = PHPExcel_Shared_Drawing::pixelsToPoints($height);
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Sample');
        $objDrawing->setDescription('Sample');
        $objDrawing->setPath($image);
        $objDrawing->setResizeProportional(false);
        $objDrawing->setWidth($width);
        $objDrawing->setHeight($height);
        $objDrawing->setCoordinates($col.$row);
        $objDrawing->setOffsetX(5);
        $objDrawing->setOffsetY(5);
        $objDrawing->setWorksheet($this->wsMain);
        $this->setRowHeight($row, $height);
    }

    /**
     * Them dinh dang vao mot o chi dinh
     * @param string $cell o chi dinh trong excel <VD: A1>
     * @param string $color ma hex mau chu cua o <VD: ffffff, khong bao gom #>
     * @param string $background ma hex mau cua o <VD: ffffff, khong bao gom #>
     * @param bool|false $bold O in dam <true/false>
     * @param bool|false $italic O in nghieng <true/false>
     * @param float $size Font size <VD: 18 >
     * @param string $name Font name <VD: Verdana>
     * @param bool|false $allBorder bo viền
     * @param string $horizontalAlign  HORIZONTAL_CENTER | HORIZONTAL_LEFT | HORIZONTAL_RIGHT
     */
    public function setStyles(
        $cell = ''
        , $color = ''
        , $background = ''
        , $bold = ''
        , $italic = ''
        , $size = 0
        , $name = ''
        , $allBorder = false
        , $horizontalAlign = false
        , $wrapText = false)
    {
        $styleArray = array();

        if($color)
        {
            $styleArray['font']['color'] = array('rgb' => $color);
        }

        if($background)
        {
            $styleArray['fill'] = array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $background)
            );
        }

        if($bold !== '')
        {
            $styleArray['font']['bold'] = $bold;
        }

        if($italic !== '')
        {
            $styleArray['font']['italic'] = $italic;
        }

        if($size)
        {
            $styleArray['font']['size'] = $size;
        }

        if($name)
        {
            $styleArray['font']['name'] = $name;
        }

        if($allBorder)
        {
            $styleArray['borders'] = array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN)
            );
        }

        if($horizontalAlign !== false)
        {
            switch($horizontalAlign)
            {
                case 'HORIZONTAL_CENTER':
                    $styleArray['alignment'] = array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    );
                    break;

                case 'HORIZONTAL_LEFT':
                    $styleArray['alignment'] = array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    );
                    break;

                case 'HORIZONTAL_RIGHT':
                    $styleArray['alignment'] = array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    );
                    break;
            }
        }

        if($wrapText)
        {
            $this->wsMain->getStyle($cell)->getAlignment()->setWrapText(true);
        }

        $this->wsMain->getStyle($cell)->applyFromArray($styleArray);
    }


    // Thiết lập giá trị cho một ô
    public function setCellValue($cell, $value)
    {
        $this->wsMain->setCellValue($cell, $value);
    }

    // Lấy giá trị của một ô
    public function getCellValue($cell)
    {
        return $this->wsMain->getCell($cell)->getValue();
    }



	////-----------------------------------------------------------------------
	private function getRow($idx)
	{
		foreach ($this->wsMain->getRowIterator() as $row)
		{
			if($row->getRowIndex() == $idx)
			return $row;
		}
		return null;
	}
	//-----------------------------------------------------------------------
	private function setRowContent($row,$data)
	{
		if(!$row)
		{
			return;
		}
		$arrExcel = array_keys(Qss_Lib_Const::$EXCEL_COLUMN);
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell)
		{
			if (!is_null($cell) && $cell->getValue())
			{
				$value = $cell->getValue();
				preg_match_all('/\{([a-zA-Z0-9_():]{2,})\}/i', $value, $matches);
				$tags = $matches[1];
				if(count($tags))
				{
					$replace = array();
					foreach($tags as $patern)
					{
						$arrPatern = explode(':', $patern);
						if(isset($data[$arrPatern[0]]) && property_exists($data[$arrPatern[0]],$arrPatern[1]))
						{
							$replace['{'.$patern.'}'] = $data[$arrPatern[0]]->{$arrPatern[1]};
						}
					}

					if(count($replace))
					{
						$value = strtr($value, $replace);
						$this->wsMain->getCell($cell->getCoordinate())->setValue($value);
					}
				}
			}
		}
	}
	//-----------------------------------------------------------------------
	public function newGridRow($data, $index, $templateindex)
	{
		$newLine = $index - 1;
		if(!$this->_raw)
		{
			$this->wsMain->insertNewRowBefore($index, 1);
		}
		$this->copyRowFull($templateindex , $index);
		$this->setRowContent($this->getRow($index),$data);
	}
	//-----------------------------------------------------------------------
	private function copyRow($fromrow,$torow)
	{
		$arrFrom = array();
		$arrTo = array();
		$cellIterator = $fromrow->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell)
		{
			if (!is_null($cell))
			{
				$arrFrom[] = $cell;
			}
		}
		/*$cellIterator = $torow->getCellIterator();
		 $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		 foreach ($cellIterator as $cell)
		 {
		 if (!is_null($cell))
		 {
		 $arrTo[] = $cell;
		 }
		 }
		 print_r(count($arrFrom));*/
		for($i = 0 ; $i < sizeof($arrFrom) ; $i++)
		{
			$arrExcel = array_keys(Qss_Lib_Const::$EXCEL_COLUMN);
			$fromcell = $arrFrom[$i]->getValue();
			$tocell = $this->wsMain->getCell($arrExcel[$i].$torow->getRowIndex());
			$tocell->setValue($fromcell);
			$this->wsMain->duplicateStyle($this->wsMain->getStyle($arrExcel[$i].$fromrow->getRowIndex()),$arrExcel[$i].$torow->getRowIndex());
		}
	}
	public function removeRow($index,$count=1)
	{
		$this->wsMain->removeRow($index,$count);
	}
	public function save($fn = 'php://output')
	{
//        // Cho độ rộng cột cuối bằng 0 để thay thế cột trắng tự động sinh ra của phpexcel
//        $lastColumn = $this->wsMain->getHighestColumn();
//        $this->wsMain->getColumnDimension($lastColumn)->setWidth(0);

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		//$objWriter->setSheetIndex(0);
		$objWriter->save($fn);
	}
	public function copyRowFull($row_from, $row_to) 
	{
		$this->wsMain->getRowDimension($row_to)->setRowHeight($this->wsMain->getRowDimension($row_from)->getRowHeight());
		$lastColumn = $this->wsMain->getHighestColumn();
		++$lastColumn;
		$start = 'A';
		$mergeCell = '';
		$oldMergeCell = '';
		for ($c = 'A'; $c != $lastColumn; ++$c) 
		{
			$cell_from = $this->wsMain->getCell($c.$row_from);
			if(!$this->_raw)
			{
				$mergeCell = '';
				foreach ($this->wsMain->getMergeCells() as $cells) 
				{
					if ($cell_from->isInRange($cells)) 
				    {
				    	$mergeCell = $cells;
				        break;
				    }
				}
				if($oldMergeCell != $mergeCell && $oldMergeCell != '')//$start != 'A' && 
				{
					$index=Qss_Lib_Const::$EXCEL_COLUMN[$c]-2;
					$oldMergeCell = '';
					//echo $start.$row_to.':'.$this->_arrExcelColumn[$index].$row_to.'<br>';
					$this->mergeCells($start.$row_to.':'.$this->_arrExcelColumn[$index].$row_to);
				}
				if($oldMergeCell != $mergeCell && $oldMergeCell == '')
				{
					$start = $c;
					//$start++;
					$oldMergeCell = $mergeCell;
				}
			}
			$cell_to = $this->wsMain->getCell($c.$row_to);
			$cell_to->setXfIndex($cell_from->getXfIndex()); // black magic here
			$cell_to->setValue($cell_from->getValue());
		}
	}
}
?>