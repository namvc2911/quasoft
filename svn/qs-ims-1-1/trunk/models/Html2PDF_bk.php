<?php
class Qss_Model_Html2PDF extends Qss_Lib_PDF_FPDF
{
	private $arrAlign = array('center'=>'C',
								'left'=>'L',
								'right'=>'R',
								'top'=>'T',
								'bottom'=>'B');
	private $fontsize;
	private $fontname;
	private $widths;
	private $heights;
	private $nbs;
	private $rowData;
	private $orientation = 'portrait';
	private $size = 'A4';
	private $startrow = 1;
	private $arrRepeats = array();
	private $repeatpage = 0;
	private $pageno = 1;

	function __construct()
	{
		/*set default value*/
		$this->widths = array();
		$this->heights = array();
		$this->rowData = array();
		$this->nbs = array();
		$this->fontsize = 10;
		$this->fontname = 'arial';

		parent::__construct();
	}
	function setWidths($column,$width)
	{
		$this->widths[$column]=$width;
	}
	function setHeights($row,$height)
	{
		$this->heights[$row]=$height;
	}
	function getWidths($column)
	{
		if(isset($this->widths[$column]))
		{
			return $this->widths[$column];
		}
		return 20;
	}
	function getHeights($row)
	{
		if(isset($this->heights[$row]))
		{
			return $this->heights[$row];
		}
		return 5.61;
	}
	function render(DOMDocument $dom,$fn = '')
	{
		$config = $dom->getElementsByTagName('excel');
		$element = $config->item(0);
		if($config != null && $element)
		{
			$orientation = $element->getAttribute('orientation');
			$size = $element->getAttribute('size');
			$margin_top = $element->getAttribute('margin-top');
			$margin_bottom = $element->getAttribute('margin-bottom');
			$margin_left = $element->getAttribute('margin-left');
			$margin_right = $element->getAttribute('margin-right');
			$font_name = $element->getAttribute('font-name');
			$font_size = $element->getAttribute('font-size');
			if($orientation)
			{
				$this->orientation = $orientation;
			}
			if($size)
			{
				$this->size = $size;
			}
			if($margin_top)
			{
				$this->SetTopMargin($margin_top * 10);
			}
			if($margin_bottom)
			{
				$this->SetAutoPageBreak(true,$margin_bottom * 10);
			}
			if($margin_left)
			{
				$this->SetLeftMargin($margin_left * 10);
			}
			if($margin_right)
			{
				$this->SetRightMargin($margin_right * 10);
			}
			if($font_name)
			{
				//$this->AddFont('Arial','','arial.ttf',true);
				$this->fontname = $font_name;
			}
			if($font_size)
			{
				$this->fontsize = $font_size;
				//$this->SetFont('Arial','',$font_size);
			}
		}
		//		$this->AddFont('UArial','','arial.ttf',true);
		//		$this->SetFont('UArial','',10);
		$images = $dom->getElementsByTagName('xmg');
		foreach ($images as $image)
		{
			$data = array();
			$name = $image->getAttribute('name');
			$description = $image->getAttribute('description');
			$path = $image->getAttribute('path');
			$height = $image->getAttribute('height');
			$width = $image->getAttribute('width');
			$coordinates = $image->getAttribute('coordinates');
			$x = $image->getAttribute('x');
			$y = $image->getAttribute('y');
			$rotation = $image->getAttribute('rotation');
			$direction = $image->getAttribute('direction');

			$data['path'] = QSS_PUBLIC_DIR . $path;
			$data['x'] = $x;
			$data['y'] = $y;
			$data['width'] = $width;
			$data['height'] = $height;
			$row = preg_replace('/[^0-9]/','',$coordinates);
			$column = str_replace($row, '', $coordinates);
			$this->rowData[$row][Qss_Lib_Const::$EXCEL_COLUMN[$column]] = $data;
		}
		//
		//
		$elements = $dom->getElementsByTagName('xls');
		foreach ($elements as $element)
		{
			$row = $element->getAttribute('row');
			$data = array();
			$column = $element->getAttribute('column');
			$bgcolor = $element->getAttribute('bgcolor');
			$column_merge = $element->getAttribute('column-merge');
			$row_merge = $element->getAttribute('row-merge');
			$font_size = $element->getAttribute('font-size');
			$font_color = $element->getAttribute('color');
			$border = $element->getAttribute('border');
			$wrap_text = $element->getAttribute('wrap-text');
			$width = $element->getAttribute('width');
			$height = $element->getAttribute('height');
			$bold = $element->getAttribute('bold');
			$italic = $element->getAttribute('italic');
			$underline = $element->getAttribute('underline');
			$valign = $element->getAttribute('v-align')?$this->arrAlign[$element->getAttribute('v-align')]:$element->getAttribute('v-align');
			$halign = $element->getAttribute('h-align')?$this->arrAlign[$element->getAttribute('h-align')]:$element->getAttribute('h-align');
			$value = utf8_decode($element->nodeValue);
			$repeat = $element->getAttribute('repeat');
			if($repeat)
			{
				$this->arrRepeats[] = $row;
			}
			//$data['column'] = Qss_Lib_Const::$EXCEL_COLUMN[$column];
			$data['bgcolor'] = $bgcolor;
			$data['column_merge'] = $column_merge?Qss_Lib_Const::$EXCEL_COLUMN[$column_merge]:0;
			$data['row_merge'] = $row_merge;
			$data['font_size'] = $font_size;
			$data['font_color'] = $font_color;
			$data['border'] = $border;
			$data['wrap_text'] = $wrap_text;
			$data['width'] = $width;
			$data['height'] = $height;
			$data['bold'] = $bold;
			$data['italic'] = $italic;
			$data['underline'] = $underline;
			$data['valign'] = $valign;
			$data['halign'] = $halign;
			$data['value'] = $value;
			$this->rowData[$row][Qss_Lib_Const::$EXCEL_COLUMN[$column]] = $data;
		}
		$this->arrRepeats = array_unique($this->arrRepeats,SORT_NUMERIC);
		ksort($this->rowData);
		$this->generate($fn);
	}
	//-----------------------------------------------------------------------
	public function generate($fn = '')
	{
		$this->AddPage($this->orientation,$this->size);
		$this->AddFont('arial','','arial.ttf',true);
		$this->AddFont('ariali','','ariali.ttf',true);
		$this->AddFont('arialbd','','arialbd.ttf',true);
		$this->AddFont('arialbi','','arialbi.ttf',true);

		$this->fixWidth();
		$this->fixHeight();
		foreach ($this->rowData as $row=>$data)
		{
			$this->Row($row, $data);
		}
		$this->Output($fn);
	}
	function fixWidth()
	{
		foreach ($this->rowData as $row=>$data)
		{
			foreach($data as $column=>$value)
			{
				if (isset($value['path']))
				{
					continue;
				}
				if($value['width'] && !$value['column_merge'])
				{
					$this->setWidths($column, $value['width'] * 2.2);
				}
			}
		}
	}
	function fixHeight()
	{
		foreach ($this->rowData as $row=>$data)
		{
			$nb=0;
			$height = 0;//$this->getHeights($row);
			foreach ($data as $column=>$value)
			{
				if (isset($value['path']))
				{
					continue;
				}
				$fontsize = $this->fontsize;
				$fontstyle = '';
				if($value['height'])
				{
					$this->setHeights($row, max($height,$value['height']*0.22));
				}
				if($value['wrap_text'])
				{
					if($value['font_size'])
					{
						$fontsize = $value['font_size'];
					}
					if($value['bold'] && $value['italic'])
					{
						$fontstyle = 'BI';
					}
					elseif($value['bold'])
					{
						$fontstyle = 'BD';
					}
					elseif($value['italic'])
					{
						$fontstyle.= 'I';
					}
					if($value['underline'])
					{
						$fontstyle .= 'U';
					}

					$this->SetFont('arial',$fontstyle,$fontsize);
					$w = $this->getWidths($column);
					if($value['column_merge'])
					{
						for($i = $column;$i<=$value['column_merge'];$i++)
						{
							$w += $this->getWidths($i);
						}
					}
					$nb = max($nb, $this->NbLines($w, $value['value']));
				}
			}
			$this->nbs[$row] = $nb?$nb:1;
			if($nb)
			{
				$this->setHeights($row, $nb * $this->getHeights($row));
			}
		}
	}
	function CheckPageBreak($h,$row)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
		{
			$this->AddPage($this->CurOrientation);
			$this->pageno++;
			if($this->pageno > $this->repeatpage)
			{
				foreach ($this->arrRepeats as $key)
				{
					$this->Row($key, $this->rowData[$key]);
				}
			}
			$this->startrow = $row;
		}
	}

	function Row($row,$data)
	{
		//Calculate the height of the row
		$nb = $this->nbs[$row];

		//Issue a page break first if needed
		$this->CheckPageBreak($this->getHeights($row),$row);
		//Draw the cells of the row
		foreach($data as $column=>$value)
		{
			if (isset($value['path']))
			{
				$this->Image($value['path'], $value['x']*0.254 + $this->lMargin, $value['y']*0.254 + $this->tMargin,$value['width']*0.254,$value['height']*0.254);
				continue;
			}
			/*set style*/
			$fontname = $this->fontname;
			$fontsize = $this->fontsize;
			$fontstyle = '';
			$fontcolor = array(0,0,0);
			$bgcolor = array(255,255,255);
			//$fontname = $this->fontname;
			//			if($value['font_name'])
			//			{
			//				$fontsize = $value['font_name'];
			//			}
			if($value['font_size'])
			{
				$fontsize = $value['font_size'];
			}
			if($value['bold'] && $value['italic'])
			{
				$fontstyle = 'BI';
			}
			elseif($value['bold'])
			{
				$fontstyle = 'BD';
			}
			elseif($value['italic'])
			{
				$fontstyle.= 'I';
			}
			if($value['underline'])
			{
				$fontstyle .= 'U';
			}
			if($value['font_color'])
			{
				$fontcolor = $this->html2rgb($value['font_color']);
			}
			if($value['bgcolor'])
			{
				$bgcolor = $this->html2rgb($value['bgcolor']);
			}
			//$this->AddFont('arial','','arial.ttf',true);
			$this->SetFont('arial',$fontstyle,$fontsize);
			$this->SetFillColor($bgcolor[0], $bgcolor[1], $bgcolor[2]);
			$this->SetTextColor($fontcolor[0], $fontcolor[1], $fontcolor[2]);

			$w = $this->getWidths($column);
			if($value['column_merge'])
			{
				for($i = $column + 1;$i<=$value['column_merge'];$i++)
				{
					$w += $this->getWidths($i);
				}
			}
			$addNB = 0;
			if($value['row_merge'])
			{
				for($i = 1;$i<=$value['row_merge'];$i++)
				{
					$addNB += (int)(isset($this->nbs[$row+$i])?$this->nbs[$row+$i]:1);
				}
			}
			$border = (int)$value['border'];
			$a = $value['halign'];
			$x=$this->GetXX($column);
			$y=$this->GetYY($row);
			//Save the current position
			$this->SetXY($x, $y);
			//Print the text
			if($value['wrap_text'])
			{
				$nb1 = $this->NbLines($w, $value['value']);
				$this->MultiCell($w,($this->getHeights($row)+ 5.61 * $addNB)/$nb1, $value['value'],$border , $a,true);
			}
			else
			{
				$this->Cell($w, $this->getHeights($row)* ($nb+$addNB), $value['value'],$border,0, $a,true);
			}
			if(in_array($row,$this->arrRepeats) && !$this->repeatpage)
			{
				$this->repeatpage = $this->pageno;
			}
			//Put the position to the right of the cell
		}
		//Go to the next line
		$this->Ln($this->getHeights($row));
	}
	function GetXX($column)
	{
		$retval = $this->lMargin;
		for($i = 1; $i < $column; $i++)
		{
			$retval += $this->getWidths($i);
		}
		return $retval;
	}
	function GetYY($row)
	{
		//$addY = ($this->pageno - 1) * $this->CurPageSize[1];
		$retval = $this->tMargin;
		//	print_r($this->getHeights(11));die();
		if($this->pageno > $this->repeatpage)
		{
			foreach ($this->arrRepeats as $i)
			{
				if($row > $i)
				{
					$retval += $this->getHeights($i);
				}
			}
		}
		if(!in_array($row, $this->arrRepeats) || $this->pageno == 1)
		{
			for($i = $this->startrow; $i < $row; $i++)
			{
				$retval += $this->getHeights($i);
			}
		}
		//		if($this->pageno > 1 && in_array($row, $this->arrRepeats))
		//		{
		//			echo $retval;die();
		//		}
		return $retval;// + ($this->tMargin + $this->bMargin)*($this->pageno-1) - $addY;
	}
	function NbLines($w, $txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin);

		$s=str_replace("\r", '', $txt);
		if ($this->unifontSubset) {
			$nb=mb_strlen($s, 'utf-8');
			while($nb>0 && mb_substr($s,$nb-1,1,'utf-8')=="\n")	$nb--;
		}
		else {
			$nb = strlen($s);
			if($nb>0 && $s[$nb-1]=="\n")
			$nb--;
		}
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			// Get next character
			if ($this->unifontSubset) {
				$c = mb_substr($s,$i,1,'UTF-8');
			}
			else {
				$c=$s[$i];
			}
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
			$sep=$i;
			if ($this->unifontSubset) {
				$l += $this->GetStringWidth($c);
			}
			else {
				$l += $cw[$c]*$this->FontSize/1000;
			}
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
					$i++;
				}
				else
				$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
			$i++;
		}
		return $nl;
	}
	function html2rgb($color)
	{
		if ($color[0] == '#')
		{
			$color = substr($color, 1);
		}
		else
		{
			$color = $this->color_name_to_hex($color);
		}
		if (strlen($color) == 6)
		{
			list($r, $g, $b) = array($color[0].$color[1],
			$color[2].$color[3],
			$color[4].$color[5]);
		}
		elseif (strlen($color) == 3)
		{
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		}
		else
		{
			return false;
		}
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return array($r, $g, $b);
	}
	function color_name_to_hex($color_name)
	{
		// standard 147 HTML color names
		$colors  =  array(
        'aliceblue'=>'F0F8FF',
        'antiquewhite'=>'FAEBD7',
        'aqua'=>'00FFFF',
        'aquamarine'=>'7FFFD4',
        'azure'=>'F0FFFF',
        'beige'=>'F5F5DC',
        'bisque'=>'FFE4C4',
        'black'=>'000000',
        'blanchedalmond '=>'FFEBCD',
        'blue'=>'0000FF',
        'blueviolet'=>'8A2BE2',
        'brown'=>'A52A2A',
        'burlywood'=>'DEB887',
        'cadetblue'=>'5F9EA0',
        'chartreuse'=>'7FFF00',
        'chocolate'=>'D2691E',
        'coral'=>'FF7F50',
        'cornflowerblue'=>'6495ED',
        'cornsilk'=>'FFF8DC',
        'crimson'=>'DC143C',
        'cyan'=>'00FFFF',
        'darkblue'=>'00008B',
        'darkcyan'=>'008B8B',
        'darkgoldenrod'=>'B8860B',
        'darkgray'=>'A9A9A9',
        'darkgreen'=>'006400',
        'darkgrey'=>'A9A9A9',
        'darkkhaki'=>'BDB76B',
        'darkmagenta'=>'8B008B',
        'darkolivegreen'=>'556B2F',
        'darkorange'=>'FF8C00',
        'darkorchid'=>'9932CC',
        'darkred'=>'8B0000',
        'darksalmon'=>'E9967A',
        'darkseagreen'=>'8FBC8F',
        'darkslateblue'=>'483D8B',
        'darkslategray'=>'2F4F4F',
        'darkslategrey'=>'2F4F4F',
        'darkturquoise'=>'00CED1',
        'darkviolet'=>'9400D3',
        'deeppink'=>'FF1493',
        'deepskyblue'=>'00BFFF',
        'dimgray'=>'696969',
        'dimgrey'=>'696969',
        'dodgerblue'=>'1E90FF',
        'firebrick'=>'B22222',
        'floralwhite'=>'FFFAF0',
        'forestgreen'=>'228B22',
        'fuchsia'=>'FF00FF',
        'gainsboro'=>'DCDCDC',
        'ghostwhite'=>'F8F8FF',
        'gold'=>'FFD700',
        'goldenrod'=>'DAA520',
        'gray'=>'808080',
        'green'=>'008000',
        'greenyellow'=>'ADFF2F',
        'grey'=>'808080',
        'honeydew'=>'F0FFF0',
        'hotpink'=>'FF69B4',
        'indianred'=>'CD5C5C',
        'indigo'=>'4B0082',
        'ivory'=>'FFFFF0',
        'khaki'=>'F0E68C',
        'lavender'=>'E6E6FA',
        'lavenderblush'=>'FFF0F5',
        'lawngreen'=>'7CFC00',
        'lemonchiffon'=>'FFFACD',
        'lightblue'=>'ADD8E6',
        'lightcoral'=>'F08080',
        'lightcyan'=>'E0FFFF',
        'lightgoldenrodyellow'=>'FAFAD2',
        'lightgray'=>'D3D3D3',
        'lightgreen'=>'90EE90',
        'lightgrey'=>'D3D3D3',
        'lightpink'=>'FFB6C1',
        'lightsalmon'=>'FFA07A',
        'lightseagreen'=>'20B2AA',
        'lightskyblue'=>'87CEFA',
        'lightslategray'=>'778899',
        'lightslategrey'=>'778899',
        'lightsteelblue'=>'B0C4DE',
        'lightyellow'=>'FFFFE0',
        'lime'=>'00FF00',
        'limegreen'=>'32CD32',
        'linen'=>'FAF0E6',
        'magenta'=>'FF00FF',
        'maroon'=>'800000',
        'mediumaquamarine'=>'66CDAA',
        'mediumblue'=>'0000CD',
        'mediumorchid'=>'BA55D3',
        'mediumpurple'=>'9370D0',
        'mediumseagreen'=>'3CB371',
        'mediumslateblue'=>'7B68EE',
        'mediumspringgreen'=>'00FA9A',
        'mediumturquoise'=>'48D1CC',
        'mediumvioletred'=>'C71585',
        'midnightblue'=>'191970',
        'mintcream'=>'F5FFFA',
        'mistyrose'=>'FFE4E1',
        'moccasin'=>'FFE4B5',
        'navajowhite'=>'FFDEAD',
        'navy'=>'000080',
        'oldlace'=>'FDF5E6',
        'olive'=>'808000',
        'olivedrab'=>'6B8E23',
        'orange'=>'FFA500',
        'orangered'=>'FF4500',
        'orchid'=>'DA70D6',
        'palegoldenrod'=>'EEE8AA',
        'palegreen'=>'98FB98',
        'paleturquoise'=>'AFEEEE',
        'palevioletred'=>'DB7093',
        'papayawhip'=>'FFEFD5',
        'peachpuff'=>'FFDAB9',
        'peru'=>'CD853F',
        'pink'=>'FFC0CB',
        'plum'=>'DDA0DD',
        'powderblue'=>'B0E0E6',
        'purple'=>'800080',
        'red'=>'FF0000',
        'rosybrown'=>'BC8F8F',
        'royalblue'=>'4169E1',
        'saddlebrown'=>'8B4513',
        'salmon'=>'FA8072',
        'sandybrown'=>'F4A460',
        'seagreen'=>'2E8B57',
        'seashell'=>'FFF5EE',
        'sienna'=>'A0522D',
        'silver'=>'C0C0C0',
        'skyblue'=>'87CEEB',
        'slateblue'=>'6A5ACD',
        'slategray'=>'708090',
        'slategrey'=>'708090',
        'snow'=>'FFFAFA',
        'springgreen'=>'00FF7F',
        'steelblue'=>'4682B4',
        'tan'=>'D2B48C',
        'teal'=>'008080',
        'thistle'=>'D8BFD8',
        'tomato'=>'FF6347',
        'turquoise'=>'40E0D0',
        'violet'=>'EE82EE',
        'wheat'=>'F5DEB3',
        'white'=>'FFFFFF',
        'whitesmoke'=>'F5F5F5',
        'yellow'=>'FFFF00',
        'yellowgreen'=>'9ACD32');

		$color_name = strtolower($color_name);
		if (isset($colors[$color_name]))
		{
			return ($colors[$color_name]);
		}
		else
		{
			return ($color_name);
		}
	}

}
?>