<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';
class Qss_Model_Html2PDF extends Qss_Model_Abstract
{
	protected $wsMain;
	protected $objPHPExcel;
	protected $arrCol = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O');
	function __construct()
	{
		parent::__construct();
	}
	function init($fn = '')
	{
		if(!file_exists($fn))
		{
			$this->objPHPExcel = new PHPExcel();
		}
		else
		{
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$this->objPHPExcel = $objReader->load($fn);
		}
		$this->wsMain = $this->objPHPExcel->setActiveSheetIndex(0);
	}
	//-----------------------------------------------------------------------
	function render($dom,$fn = 'php://output')
	{
		/*$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$this->objPHPExcel = $objReader->load(QSS_DATA_DIR.'\a.xls');
		$this->wsMain = $this->objPHPExcel->setActiveSheetIndex(0);
		$cell = $this->wsMain->getCell('B10');
		$cell->setValue('OK');
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
		$this->wsMain = $this->objPHPExcel->setActiveSheetIndex(0);
		$objWriter->save($fn);
		return;*/
		if(!is_dir(QSS_DATA_DIR . "/reports"))
		{
			mkdir(QSS_DATA_DIR . "/reports");
		}
		$elements = $dom['xls'];
		$styleArray = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		)
		)
		);
		$styleArrayNon = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_NONE
		)
		)
		);
		$images =  $dom['xmg'];
		foreach ($images as $image)
		{
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$name = @$image['name'];
			$description = @$image['description'];
			$path = $image['path'];
			$height = @$image['height'];
			$width = @$image['width'];
			$coordinates = $image['coordinates'];
			$x = $image['x'];
			$y = $image['y'];
			$rotation = @$image['rotation'];
			$direction = @$image['direction'];

			$objDrawing->setName($name);
			$objDrawing->setDescription($description);
			$objDrawing->setPath(QSS_PUBLIC_DIR.$path);
			$objDrawing->setResizeProportional(false);
			$objDrawing->setWidth($width);
			$objDrawing->setHeight($height);
			$objDrawing->setCoordinates($coordinates);
			$objDrawing->setOffsetX($x);
			$objDrawing->setOffsetY($y);
			$objDrawing->setRotation($rotation);
			$objDrawing->getShadow()->setVisible(true);
			$objDrawing->getShadow()->setDirection($direction);
			$objDrawing->setWorksheet($this->wsMain);
		}

		$config =  $dom['excel'];
		$element = $config[0];
		$this->wsMain->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		if($config != null && $element)
		{
			$orientation = $element['orientation'];
			$margin_top = $element['margin-top'];
			$margin_bottom = $element['margin-bottom'];
			$margin_left = $element['margin-left'];
			$margin_right = $element['margin-right'];
			$font_name = $element['font-name'];
			$font_size = $element['font-size'];
			if($orientation)
			{
				$this->wsMain->getPageSetup()->setOrientation($orientation);
			}
			if($margin_top)
			{
				$this->wsMain->getPageMargins()->setTop($margin_top);
			}
			if($margin_bottom)
			{
				$this->wsMain->getPageMargins()->setBottom($margin_bottom);
			}
			if($margin_left)
			{
				$this->wsMain->getPageMargins()->setLeft($margin_left);
			}
			if($margin_right)
			{
				$this->wsMain->getPageMargins()->setRight($margin_right);
			}
			if($font_name)
			{
				$this->objPHPExcel->getDefaultStyle()->getFont()->setName($font_name);
			}
			if($font_size)
			{
				$this->objPHPExcel->getDefaultStyle()->getFont()->setSize($font_size);
			}
		}
		$max = 0;
		$min = 0;
		foreach ($elements as $element)
		{
			$row = $element['row'];
			$column = $element['column'];
			$bgcolor = str_replace('#', '', @$element['bgcolor']);
			$column_merge = @$element['column-merge'];
			$row_merge = @$element['row-merge'];
			$font_size = @$element['font-size'];
			$font_color = str_replace('#', '', @$element['color']);
			$border = @$element['border'];
			$wrap_text = @$element['wrap-text'];
			$width = @$element['width'];
			$height = @$element['height'];
			$bold = @$element['bold'];
			$italic = @$element['italic'];
			$underline = @$element['underline'];
			$valign = @$element['v-align'];
			$halign = @$element['h-align'];
			$repeat = @$element['repeat'];
			if($repeat)
			{
				$max= max($max,$row);
				$min = $min?$min:$max;
			}
			$cell = $this->wsMain->getCell($column.$row);
			$nostyle = @$element['nostyle'];
			if(!$nostyle)
			{
				$rowstyle = (int)@$element['rowstyle'];
				$style = $this->wsMain->getStyle($column.$row.":".$column.($row+$rowstyle));
				if($wrap_text)
				{
					$style->getAlignment()->setWrapText(true);
				}
				if($valign)
				{
					$style->getAlignment()->setVertical($valign);
				}
				if($halign)
				{
					$style->getAlignment()->setHorizontal($halign);
				}
				if($bold)
				{
					$style->getFont()->setBold(true);
				}
				if(!$column_merge && !$row_merge)
				{
					if($border)
					{
						$style->applyFromArray($styleArray);
					}
					else 
					{
						$style->applyFromArray($styleArrayNon);
					}
				}
				if($column_merge)
				{
					$this->wsMain->mergeCells($column.$row.":".$column_merge.$row);
					$style = $this->wsMain->getStyle($column.$row.":".$column_merge.$row);
					if($border)
					{
						$style->applyFromArray($styleArray);
					}
					else 
					{
						$style->applyFromArray($styleArrayNon);
					}
					$style = $this->wsMain->getStyle($column.$row);//.":".$column_merge.$row
				}
				if($row_merge)
				{
					$this->wsMain->mergeCells($column.$row.":".$column.($row + $row_merge));
					$style = $this->wsMain->getStyle($column.$row);//.":".($column_merge?$column_merge:$column).($row + $row_merge)
				}
			}
		
			if(!$nostyle)
			{
				/*if($wrap_text && !$column_merge && !$row_merge)
				{
					$style->getAlignment()->setWrapText(true);
				}*/
				if($bgcolor)
				{
					$style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$style->getFill()->getStartColor()->setRGB($bgcolor);
				}
				if($font_size)
				{
					$style->getFont()->setSize($font_size);
				}
				if($font_color)
				{
					$style->getFont()->setColor(new PHPExcel_Style_Color( $font_color));
				}
				/*if($border)
				{
					$style->applyFromArray($styleArray);
				}
				else 
				{
					$style->applyFromArray($styleArrayNon);
				}*/
				if($width)
				{
					$this->wsMain->getColumnDimension($column)->setWidth($width);
				}
				if($height)
				{
					$this->wsMain->getRowDimension($row)->setRowHeight($height);
				}
				if($italic)
				{
					$style->getFont()->setItalic(true);
				}
				if($underline)
				{
					$style->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
				}
			}
			if(@$element['value'] != '')
			{
				$text = trim($element['value']);
				if(is_numeric(str_replace('.', '', $text)))
				{
					$text = str_replace('.', ',', $text);
				}
				$cell->setValue($text);
			}
			if(!$nostyle && $row_merge)
			{
				$style = $this->wsMain->getStyle($column.$row.":".$column.($row + $row_merge));
				if($border)
				{
					$style->applyFromArray($styleArray);
				}
				else 
				{
					$style->applyFromArray($styleArrayNon);
				}
			}
		}
		if($min && $max)
		{
			$this->wsMain->getPageSetup()->setRowsToRepeatAtTop(array($min,$max));
		}
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'PDF');
		$objWriter->save($fn);
	}
	//-----------------------------------------------------------------------
}
?>