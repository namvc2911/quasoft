<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Template
{

	const SEARCH_PATTERN = "/\{([a-zA-Z0-9_():]{2,})\}/i";

	protected $data;

	/**
	 *
	 * @param Qss_Model_Object $o_Object
	 * @return string
	 */
	public static function b_fGenerateSearch (Qss_Model_Form $form, $sz_FileName)
	{
		if ( file_exists($sz_FileName) )
		{
			return $sz_FileName;
		}
		$result = "<form id='form_{$form->FormCode}_filter' name='filter_form' method='post' onsubmit=\"rowSearch('{$form->FormCode}'); return false;\">" . "\n";
		$result .= "<input type='hidden' id='qss_form_tree' name='qss_form_tree' value='0'>" . "\n";
		$mainobjects = $form->o_fGetMainObjects();
		$subobjects = $form->a_fGetSubObjects();
		//$result .= "<div class=\"line-hr\"><span>Thông tin chung</span></div>" . "\n";
		
		$result .= "<table class=\"\" cellpadding=\"0\" cellspacing=\"0\">" . "\n";
		foreach($mainobjects as $o_Object)
		{
			$fields = $o_Object->loadFields();
			foreach ($fields as $field)
			{
				if ( $field->bSearch )
				{
					$hasSearch = true;
					$result .= '<tr style="border-bottom:1px #ccc dashed">' ;
					$result .= sprintf('<td><span>
					<?php echo $this->form->getFieldByCode("%1$s","%2$s")->szFieldName;?></span></td>', 
					trim($field->ObjectCode), trim($field->FieldCode)) ;
					
					$result .= sprintf('<td style="padding-left:5px;"><span><?php echo Qss_Lib_Template::sz_fGetFieldElement($this->form->getFieldByCode("%1$s","%2$s"),$this->filter);?></span></td>',
					trim($field->ObjectCode), trim($field->FieldCode)) ;
					$result .= '</tr>' ;
				}
			}
		}
		$result .= "</table>" . "\n";
		$result .= "<div class=\"clearfix\"></div><br/>" . "\n";
		$result .= "<table class=\"\" cellpadding=\"0\" cellspacing=\"0\">" . "\n";
		foreach($subobjects as $o_Object)
		{
			$fields = $o_Object->loadFields();
			foreach ($fields as $field)
			{
				if ( $field->bSearch )
				{
					$hasSearch = true;
					$result .= '<tr style="border-bottom:1px #ccc dashed">' ;
					$result .= sprintf('<td><span>
					<?php echo $this->form->getFieldByCode("%1$s","%2$s")->szFieldName;?></span></td>', 
					trim($field->ObjectCode), trim($field->FieldCode));
					$result .= sprintf('<td><span><?php echo Qss_Lib_Template::sz_fGetFieldElement($this->form->getFieldByCode("%1$s","%2$s"),$this->filter);?></span></td>',
					trim($field->ObjectCode), trim($field->FieldCode));
                    $result .= '</tr>' ;
				}
			}
		}
		$result .= "</table>" . "\n";
		$result .= "</form>" . "\n";
		if(!$hasSearch)
		/*{
			$result .= "<div>" . "\n";
			$result .= sprintf('<div class="btn-custom center" style="width:70px" onclick="rowSearch(\'<?php echo $this->form->FormCode;?>\');"><?php echo $this->_translate(24)?></div>') . "\n";
			//$result .= sprintf('<div class="clearfix"><br></div>');
			$result .= sprintf('<div class="btn-custom center" style="width:70px" onclick="rowCleanSearch(\'<?php echo $this->form->FormCode;?>\');"><?php echo $this->_translate(94)?></div>') . "\n";
			//$result .= sprintf('<div class="clearfix"><br></div>');
			$result .= sprintf('<div class="btn-custom center" style="width:70px" onclick="showSearch();"><?php echo $this->_translate(1)?></div>') . "\n";
			$result .= "</div>" . "\n";                        
		}
		else*/
		{
			$result .= "<div style=\"width:200px\">" . "\n";
			$result .= sprintf('<h3><?php echo $this->_translate(2)?></h3>') . "\n";
			$result .= "</div>" . "\n";          
		}
			
		$handle = fopen($sz_FileName, 'w');
		if ( fwrite($handle, $result) )
		{
			return $sz_FileName;
		}
		fclose($handle);
		return null;
	}
	/**
	 *
	 * @param Qss_Model_Object $o_Object
	 * @return string
	 */
	public static function b_fGenerateSelectSearch (Qss_Model_Object $o_Object, $sz_FileName)
	{
		if ( file_exists($sz_FileName) )
		{
			return $sz_FileName;
		}
		$result = "<form id='form_{$o_Object->FormCode}_filter' name='filter_form' method='post' onsubmit=\"selectSearch('{$o_Object->FormCode}','{$o_Object->ObjectCode}'); return false;\">" . "\n";

		$fields = $o_Object->loadFields();
		$hasSearch = false;
		$result .= "<div class=\"clearfix\">" . "\n";
		$result .= "<div class=\"label\">" . "\n";
		foreach ($fields as $field)
		{
			if ( $field->bSearch )
			{
				$hasSearch = true;
				$result .= sprintf('<span>
				<?php echo $this->object->getFieldByCode("%1$s")->szFieldName;?></span>', 
				trim($field->FieldCode));
			}
		}
		$result .= "</div>" . "\n";
		$result .= "<div  class=\"element\">" . "\n";
		foreach ($fields as $field)
		{
			if ( $field->bSearch )
			{
				$hasSearch = true;
				$result .= sprintf('<span><?php echo Qss_Lib_Template::sz_fGetFieldElement($this->object->getFieldByCode("%1$s"),$this->filter);?></span>',
				trim($field->FieldCode));
			}
		}
		$result .= "</div>" . "\n";
		$result .= "</div>" . "\n";
		$result .= "</form>" . "\n";
		if($hasSearch)
		{
			$result .= "<div class=\"fl\">" . "\n";
			$result .= sprintf('<div class="btn-custom" onclick="selectSearch(<?php echo $this->form->FormCode;?>,<?php echo $this->object->ObjectCode;?>);"><?php echo $this->_translate(24)?></div>') . "\n";
			$result .= sprintf('<div class="btn-custom" onclick="selectCleanSearch(<?php echo $this->form->FormCode;?>,<?php echo $this->object->ObjectCode;?>);"><?php echo $this->_translate(94)?></div>') . "\n";
			$result .= "</div>" . "\n";
		}
			
		$handle = fopen($sz_FileName, 'w');
		if ( fwrite($handle, $result) )
		{
			return $sz_FileName;
		}
		fclose($handle);
		return null;
	}

	/**
	 *
	 * @param Qss_Model_Object $o_Object
	 * @return string
	 */
	public static function b_fGenerateObjectSearch (Qss_Model_Object $o_Object, $sz_FileName)
	{
		if ( file_exists($sz_FileName) )
		{
			return $sz_FileName;
		}
		$result = "<form id='object_{$o_Object->ObjectCode}_filter' name='filter_form' method='post' onsubmit=\"rowSearch('<?php echo \$this->object->i_IFID?>','<?php echo \$this->object->intDepartmentID?>','{$o_Object->ObjectCode}'); return false;\">" . "\n";
		$result .= "<input type='hidden' id='qss_object_tree' name='qss_object_tree' value='0'>" . "\n";
		$result .= "<div class=\"clearfix\"></div>" . "\n";

		$fields = $o_Object->loadFields();
		$hasSearch = false;
                
		$result .= "<table class=\"\" cellpadding=\"0\" cellspacing=\"0\">" . "\n";
		foreach ($fields as $field)
		{
			if ( $field->bSearch )
			{
				$hasSearch = true;
                $result .= '<tr style="border-bottom:1px #ccc dashed">' ;
				$result .= sprintf('<td><span><?php echo $this->object->getFieldByCode("%1$s")->szFieldName;?>
				<span></td>', trim($field->FieldCode));
				$result .= sprintf('<td style="padding-left:5px;"><span><?php echo Qss_Lib_Template::sz_fGetFieldElement($this->object->getFieldByCode("%1$s"),$this->filter);?></span></td>',
				trim($field->FieldCode));
                $result  .= '</tr>';
			}
		}
                                    $result .= "</table>" . "\n";
		$result .= "</form>" . "\n";
		if($hasSearch)
		{
			$result .= "<div class=\"fl\" >" . "\n";
			$result .= "<div style=\"position:fixed;  margin-left:50px\" >" . "\n";
                        
			$result .= sprintf('<div class="btn-custom center" onclick="rowSearch(<?php echo $this->form->i_IFID;?>,<?php	echo $this->form->i_DepartmentID;?>,<?php echo $this->object->ObjectCode;?>);"><?php echo $this->_translate(24)?></div>') . "\n";
			$result .= sprintf('<div class="btn-custom center" onclick="rowCleanSearch(<?php echo $this->form->i_IFID;?>,<?php echo $this->form->i_DepartmentID;?>,<?php echo $this->object->ObjectCode;?>);"><?php echo $this->_translate(94)?></div>') . "\n";
			$result .= "</div>" . "\n";
            $result .= "</div>" . "\n";
		}
		$handle = fopen($sz_FileName, 'w');
		if ( fwrite($handle, $result) )
		{
			return $sz_FileName;
		}
		fclose($handle);
		return null;
	}

	/**
	 *
	 * @param Qss_Model_Object $o_Object
	 * @return string
	 */
	public static function b_fGenerateImportSearch (Qss_Model_Object $o_Object, $sz_FileName)
	{
		if ( file_exists($sz_FileName) )
		{
			return $sz_FileName;
		}
		$result = "<form id='object_{$o_Object->ObjectCode}_filter' name='filter_form' method='post' onsubmit=\"importSearch('<?php echo \$this->object->FormCode?>','<?php echo \$this->object->i_IFID?>','<?php echo \$this->object->intDepartmentID?>','{$o_Object->ObjectCode}'); return false;\">" . "\n";
		$result .= "<div class=\"clearfix\"></div>" . "\n";

                
		$fields = $o_Object->loadFields();
		$hasSearch = false;
		$result .= "<table class=\"\" cellpadding=\"0\" cellspacing=\"0\">" . "\n";
		foreach ($fields as $field)
		{
			if ( $field->bSearch )
			{
				$hasSearch = true;
        		$result .= '<tr style="border-bottom:1px #ccc dashed">' ;
				$result .= sprintf('<td><span><?php echo $this->object->getFieldByCode("%1$s")->szFieldName;?></span></td>',
				trim($field->FieldCode));
                $result .= sprintf('<td><span><?php echo Qss_Lib_Template::sz_fGetFieldElement($this->object->getFieldByCode("%1$s"),$this->filter);?></span></td>',
				trim($field->FieldCode));
                $result  .= '</tr>';
			}
		}
		$result .= "</table>" . "\n";
		$result .= "</form>" . "\n";
		if($hasSearch)
		{
			$result .= "<div class=\"fl\" >" . "\n";
			$result .= "<div style=\"position:fixed;  margin-left:50px\" >" . "\n";
			$result .= sprintf('<div class="btn-custom" onclick="importSearch(\'<?php echo $this->form->FormCode;?>\',<?php echo $this->form->i_IFID;?>,<?php echo $this->form->i_DepartmentID;?>,<?php echo $this->object->ObjectCode;?>);"><?php echo $this->_translate(24)?></div>') . "\n";
			$result .= sprintf('<div class="btn-custom" onclick="rowCleanSearch(\'<?php echo $this->form->FormCode;?>\',<?php echo $this->form->i_IFID;?>,<?php echo $this->form->i_DepartmentID;?>,<?php echo $this->object->ObjectCode;?>);"><?php echo $this->_translate(94)?></div>') . "\n";
			$result .= "</div>" . "\n";
			$result .= "</div>" . "\n";                        
		}
		$handle = fopen($sz_FileName, 'w');
		if ( fwrite($handle, $result) )
		{
			return $sz_FileName;
		}
		fclose($handle);
		return null;
	}

	public static function sz_fGetFieldElement ($o_Field, $a_Filter = array())
	{
		$elename = sprintf('%1$s_%2$s', $o_Field->ObjectCode, $o_Field->FieldCode);
		$sz_Val = @$a_Filter[$elename];
		if ( $o_Field->bSearch )
		{
			$elename = sprintf('filter_%1$s_%2$s', $o_Field->ObjectCode, $o_Field->FieldCode);
			switch ( $o_Field->intFieldType )
			{
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 12:
				case 6:
				case 11:
				case 13:
				case 14:
				case 15:
				case 16:
					if ( $o_Field->intInputType == 2 )
					$add = self::TextBox($elename, $sz_Val, $o_Field->intFieldWidth);
					else
					$add = self::TextBox($elename, $sz_Val, $o_Field->intFieldWidth);
					break;
				case 7:
					$add = self::CheckBox($elename, $sz_Val);
					break;
				case 10: //Date
					$sz_Val1 = @$a_Filter[sprintf('%1$s_%2$d_S', $o_Field->FieldCode, $o_Field->ObjectCode)];
					$sz_Val2 = @$a_Filter[sprintf('%1$s_%2$d_E', $o_Field->FieldCode, $o_Field->ObjectCode)];
					$add = self::DateBox($elename . '_S', $sz_Val1);
					$add .= ' to ';
					$add .= self::DateBox($elename . '_E', $sz_Val2);
					break;
				/*case 11:
					$add = self::TextBox($elename . '_S', $sz_Val, $o_Field->intFieldWidth);
					$add .= self::TextBox($elename . '_E', $sz_Val, $o_Field->intFieldWidth);
					break;*/
			}
		}
		return @$add;
	}


	//-----------------------------------------------------------------------
	public static function Radio ($name, $val, $json,$readonly = false,$extra = "")
	{
		$ret = '<div class="radio">';
		//revert the displaying type of radio to the formal one
		//$ret = sprintf('<input type=radio id="%1$s_"" name="%1$s" value="-1" checked>',
			//		$name);
		//$ret .= sprintf('<label for="%1$s_">Không chọn</label><br>',$name);
		foreach ($json as $key=>$value)
		{
			$ret .= sprintf('<input %4$s type=radio id="%1$s_%2$s"" name="%1$s" value="%2$s" %3$s %5$s>',
					$name,
					$key,
					($val == $key) ? "checked" : "",
					$readonly?'disabled':'',
					$extra);
			$ret .= sprintf('<label for="%1$s_%2$s">%3$s</label><br>',$name,$key,$value);
		}
		$ret .= '</div>';
		return $ret;
	}

	//-----------------------------------------------------------------------
	/**
	*
	* Generate a textbox
	*
	* @param   Name of textbox. For list or object form, it is a FieldID(table sFields,qsfields)
	* @param   Value of textbox
	* @param   With of textbox, default is 00px
	* @return string
	*/
	public static function TextBox ($name, $val, $width = 100,$readonly = false,$keyup = '',$limit = 0)
	{
		$val = htmlentities($val,ENT_QUOTES, "UTF-8");
		if($readonly)
		{
			$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px;' class=\"readonly\" value=\"{$val}\" readonly>";
		}
		else 
		{
			if($limit)
			{
				$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px;' value=\"{$val}\" {$keyup} maxlength=\"{$limit}\">";
			}
			else
			{
				$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px;' value=\"{$val}\" {$keyup}>";
			}
		}
		return $ret;
	}
	public static function Timmer($name, $val, $width = 100,$readonly = false,$keyup = '')
	{
        if($val) {
            $val = date('H:i', strtotime($val));
        }

		if($readonly)
		{
			$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px;' class=\"readonly timmer\" value=\"{$val}\" readonly>";
		}
		else 
		{
			$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px;' class=\"timmer\" value=\"{$val}\" {$keyup}>";
		}
		return $ret;
	}	
	public static function MoneyBox ($name, $val, $width = 100,$readonly = false,$keyup = '',$code='VND')
	{
		$currency = Qss_Lib_System::getCurrencyByCode($code);
		$val = htmlentities($val,ENT_QUOTES, "UTF-8");
		if($readonly)
		{
			$ret = "<input precision='{$currency->Precision}' thousandssep='{$currency->ThousandsSep}' decpoint='{$currency->DecPoint}' 
					type='text' id='{$name}' name='{$name}' style='width: {$width}px; text-align:right;' class=\"money readonly\" value=\"{$val}\" readonly>";
		}
		else 
		{
			$ret = "<input precision='{$currency->Precision}' thousandssep='{$currency->ThousandsSep}' decpoint='{$currency->DecPoint}'
					type='text' id='{$name}' name='{$name}' style='width: {$width}px; text-align:right;' class=\"money\" value=\"{$val}\" {$keyup}>";
		}
		return $ret;
	}
	public static function IntegerBox ($name, $val, $width = 100,$readonly = false,$keyup = '',$raw = 0)
	{
		$val = htmlentities($val,ENT_QUOTES, "UTF-8");
		if($readonly)
		{
			$raw = $raw?'raw':'';
			$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px; text-align:right;' class=\"integer readonly {$raw}\" value=\"{$val}\" readonly>";
		}
		else 
		{
			$ret = sprintf('<input type="text" id="%1$s" name="%1$s" style="width: %2$spx; text-align:right;" class="integer %5$s" value="%3$d" %4$s>'
				,$name
				,$width
				,$val
				,$keyup
				,$raw?'raw':'');
		}
		return $ret;
	}
	public static function DecimalBox ($name, $val, $width = 100,$readonly = false,$keyup = '',$precision = 2)
	{
		$val = htmlentities($val,ENT_QUOTES, "UTF-8");
		if($readonly)
		{
			$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px; text-align:right;' precision=\"{$precision}\" class=\"decimal readonly\" value=\"{$val}\" readonly>";
		}
		else 
		{
			$ret = "<input type='text' id='{$name}' name='{$name}' style='width: {$width}px; text-align:right;' precision=\"{$precision}\" class=\"decimal\" value=\"{$val}\" {$keyup}>";
		}
		return $ret;
	}
	public static function Picture ($objectcode,$fieldcode, $filename,$readonly = false,$width=0,$namespace='')
	{
		$name = $objectcode. '_' . $fieldcode;
		$ret = "<input type='file' size='30' id='{$name}_picture' name='{$name}_picture' onchange=\"return {$namespace}uploadPicture('{$name}')\"><br>";
		$ret .= sprintf('<input type="hidden" id="%1$s" name="%1$s" value="%2$s">',$name,$filename);
		$resize = self::resizeFileName($filename);
		$fname = QSS_DATA_DIR . "/documents/" . $resize;
		if ( $filename)
		{
			if ( !file_exists($fname) )
			{
				if ( !self::PictureResize($filename) )
				{
					$resize = $filename;
				}
			}
			
		}
		$ret .= sprintf('<div class="icon-QA">
						<img width="%5$d" id= "%1$d" src="/user/field/picture?file=%2$s"></img>
						<a class="hideAC" href="#1" onclick="deletePicture(this,\'%3$s_%4$s\')" title="Xóa ảnh"></a>
						</div>', 
					$filename, 
					$filename,
					$objectcode,
					$fieldcode,
					$width);
		return $ret;
	}

	//-----------------------------------------------------------------------
	public static function Image ($filename,$width = 0,$height = 0)
	{
		$ret = '';
		$fname = QSS_DATA_DIR . '/documents/' . $filename;
		if ( file_exists($fname) && $filename)
		{
            $size      = getimagesize($fname);
            $tmpWidth  = (isset($size[0]))?$size[0]:0;
            $tmpHeight = (isset($size[1]))?$size[1]:0;
            $newHeight = ($tmpWidth != 0)?($width * $tmpHeight / $tmpWidth):0;
            $height    = $height?$height:$newHeight;

			$ret = sprintf('<img id="image_%1$s" src="/user/field/picture?file=%1$s" %2$s %3$s />',
				 $filename,($width?'width='.$width:''),($height?'height='.$height:'')); 
		}
		return $ret;
	}
	public static function ImageUrl($id,$ext)
	{
		$folder = Qss_Register::get('folder');
		$fname= "../{$folder}/documents/".$id."_r.".$ext;
		if($id)
		{
			if(!file_exists($fname))
			$fname= "../{$folder}/documents/".$id.".".$ext;
			return $fname;
		}
	}
	//-----------------------------------------------------------------------
	public static function File ($name,$id, $ext)
	{
		$ret = '<input type="file" SIZE="40" id="' . $name . '_file" name="' . $name . '_file" onchange="return uploadFile(\'' . $name . '\')">';
		$ret .= sprintf('<input type="hidden" id="%1$s" name="%1$s" value="%2$s">'
				,$name
				,$ext);
		return $ret;
	}

	//-----------------------------------------------------------------------
	public static function FileDown ($filename, $field)
	{
		$view = new Qss_View();
		return $view->Instance->Field->FileDown($filename, $field);
	}

	//-----------------------------------------------------------------------
	public static function DurationBox ($name, $val1, $val2)
	{
		$ret = $this->DateBox($name . "_StartDate", $val1);
		$ret .= $this->DateBox($name . "_EndDate", $val2);
		return $ret;
	}

	//-----------------------------------------------------------------------
	public static function DateBox ($name, $val,$readonly = false,$extra = '')
	{
		if($readonly)
		{
			$ret = sprintf('<input type="text" SIZE="15" id="%1$s" name="%1$s" value="%2$s" class="readonly dater" readonly><span class="ui-datepicker-trigger icon-datepicker">&nbsp;</span>', $name, $val);
		}
		else 
		{
			$ret = sprintf('<input class="datepicker" id="%1$s" placeholder="dd-mm-yyyy" type="text" SIZE="15" name="%1$s" value="%2$s" %3$s>', $name, $val, $extra);
		}
		return $ret;
	}

    public static function b_fGenerateFormMobile (Qss_Model_Form $form, $sz_FileName,$popup = false)
    {
        if ( file_exists($sz_FileName) )
        {
            return $sz_FileName;
        }
        $result = '';

        $mainobject = $form->o_fGetMainObject();
        $ui = new Qss_Model_System_UI();
        $data = $ui->getFormUIConfig($mainobject->ObjectCode);
        $groups = array();
        $boxs = array();
        $nogroup = true;
        foreach ($data as $item)
        {
            if(is_numeric($item->UIGID))
            {
                $nogroup = false;
            }
            $groups[(int) $item->UIGID][(int) $item->BoxType][(int) $item->UIBID][] = $item;
            //$boxs[(int) $item->UIBID][] = $item;
        }
        $first = true;
        foreach ($groups as $box)
        {
            $result .= "<div class=\"box-wrap\">";
            foreach ($box as $k=>$val)
            {
                foreach($val as $key=>$v)
                {

                    if($v[0]->DisplayTitle)
                    {
                        if($form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && $first)
                            $result .= '<div class=\"box-head\"><?php echo Qss_Lib_System::getUIBoxName('.$v[0]->UIBID.',$this->user->user_lang);?> <span class="<?php echo $this->form->getStatusData($this->user->user_lang)->Color?>">(<?php echo $this->form->getStatusData($this->user->user_lang)->Name?>)</span></div>';
                        else
                            $result .= '<div class=\"box-head\"><?php echo Qss_Lib_System::getUIBoxName('.$v[0]->UIBID.',$this->user->user_lang);?></div>';
                        $first = false;
                    }
                    foreach ($v as $item)
                    {
                        $result .= sprintf('<div class="box-edit">

											<label><?php echo $this->objects["%1$s"]->getFieldByCode("%2$s")->szFieldName;?></label>


											<?php echo $this->objects["%1$s"]->sz_fGetFormFieldElement("%2$s",$this->user,$this->dialog);?>

											</div>',
                            $mainobject->ObjectCode,
                            $item->FieldCode);
                    }

                }
            }
            $result .= "</div>";
        }

        $handle = fopen($sz_FileName, 'w');
        if ( fwrite($handle, $result) )
        {
            return $sz_FileName;
        }
        fclose($handle);
        return null;
    }


	public static function b_fGenerateFormEdit (Qss_Model_Form $form, $sz_FileName,$popup = false)
	{
		if ( file_exists($sz_FileName) )
		{
			return $sz_FileName;
		}
		$result = '';
		if(!$popup)
		{
			$mainobject = $form->o_fGetMainObject();
			$ui = new Qss_Model_System_UI();
			$data = $ui->getFormUIConfig($mainobject->ObjectCode);
			$groups = array();
			$boxs = array();
			$nogroup = true;
			foreach ($data as $item)
			{
				if(is_numeric($item->UIGID))
				{
					$nogroup = false;
				}
				$groups[(int) $item->UIGID][(int) $item->BoxType][(int) $item->UIBID][] = $item;
				$boxs[(int) $item->UIGID] = $item;
			}
			$first = true;
			$result .= '<div class="navmenutab tabs-menu">';
			$result .= '<ul>';
			foreach ($groups as $id=>$box)
			{
				if($first || $id)//không in các trường hợp chưa cho vào box nào
				{
					$name = $boxs[$id]->Name?$boxs[$id]->Name:'<?php echo $this->form->sz_Name?>';
					
					$result .= '<li class="splash"></li>';
					if($first)
					{
						$result .= '<li class="active normal"><a href="#form_tab_'.$id.'"><span><?php echo Qss_Lib_System::getUIGroupName('.$id.',$this->user->user_lang);?></span></a></li>';
					}
					else
					{
			        	$result .= '<li class="normal"><a href="#form_tab_'.$id.'"><span><?php echo Qss_Lib_System::getUIGroupName('.$id.',$this->user->user_lang);?></span></a></li>';
					}
					$first = false;
				}
			}
			$result .= '</ul>';
			$result .= '</div>';
			$result .= '<div style="border-top: 1px solid #999; clear: both;"></div>';
			
			$first = true;
			foreach ($groups as  $id=>$box)
			{
				if($first)
				{
					$result .= "<div class=\"ui_box\" id=\"form_tab_{$id}\">";
				}
				else
				{
					$result .= "<div class=\"ui_box\" id=\"form_tab_{$id}\">";
				}
				foreach ($box as $k=>$val)
				{
					switch ($k) {
						case 0:
							if($nogroup)
								$result .= "<div class=\"ui_box_s50_left\">";
							else 
								$result .= "<div class=\"ui_box_s100\">";
						break;
						case 1:
							$result .= "<div class=\"ui_box_s50_left\">";
						break;
						case 2:
							$result .= "<div class=\"ui_box_s50_right\">";
						break;
					}
					foreach($val as $key=>$v)
					{
						if($v[0]->DisplayBorder || $k == 0)
						{
							$result .= "<fieldset>";
						}
						if($v[0]->DisplayTitle)
						{
							if($form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && $first)
							$result .= '<legend><?php echo Qss_Lib_System::getUIBoxName('.$v[0]->UIBID.',$this->user->user_lang);?> <span class="<?php echo $this->form->getStatusData($this->user->user_lang)->Color?>">(<?php echo $this->form->getStatusData($this->user->user_lang)->Name?>)</span></legend>';
							else 
							$result .= '<legend><?php echo Qss_Lib_System::getUIBoxName('.$v[0]->UIBID.',$this->user->user_lang);?></legend>';
							$first = false;
						}
						elseif($form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && $first)
						{
							$result .= '<legend><span class="<?php echo $this->form->getStatusData($this->user->user_lang)->Color?>">(<?php echo $this->form->getStatusData($this->user->user_lang)->Name?>)</span></legend>';
							$first = false;
						}
						$hidden = @$v[0]->Hidden;
						$hidden = $hidden?'style="display:none"':'';
						$child = '';
						foreach ($v as $item)
						{
							if($item->FieldCode == $child)
							{
								$result .= sprintf('<div class="element %1$s_%2$s" %3$s>
												<?php echo $this->objects["%1$s"]->sz_fGetFormFieldElement("%2$s",$this->user,$this->dialog);?>
												</div>',
										$mainobject->ObjectCode,
										$item->FieldCode,
										$hidden);
							}
							else 
							{
								$result .= sprintf('<div class="ui_box_line %1$s_%2$s" %3$s>
												<div class="label">
												<label><?php echo $this->objects["%1$s"]->getFieldByCode("%2$s")->szFieldName;?></label>
												</div>
												<div class="element" %3$s>
												<?php echo $this->objects["%1$s"]->sz_fGetFormFieldElement("%2$s",$this->user,$this->dialog);?>
												</div>
												</div>',
										$mainobject->ObjectCode,
										$item->FieldCode,
										$hidden);
								if(!@$item->NoTree && $item->ObjectCode == $item->RefObjectCode)
								{
									$result .= sprintf('<div class="ui_box_line %1$s_%2$s" %3$s>
												<div class="label">
												<label><?php echo $this->_translate(1);?></label>
												</div>
												<div class="element" %3$s>
												<?php echo $this->views->Instance->Field->Order($this->objects["%1$s"],"%2$s");?>
												</div>
												</div>',
											$mainobject->ObjectCode,
											$item->FieldCode,
											$hidden);
								}
							}
							$child = $item->FValue;
						}
						if($v[0]->DisplayBorder || $k == 0)
						{
							$result .= "</fieldset>";
						}
					}
					$result .= "</div>";
				}
				$result .= "</div>";
			}
		}
		else
		{
			$objects = $form->o_fGetMainObjects(); 
			$result .= "<div id='tabs_sub'>\n";
			$result .= "<div id='tabs_sub'>\n";
			$result .= "<ul class='tabslist_sub'>\n";
			foreach ($objects as $objid=>$o_Object)
			{
				$o_Object instanceof Qss_Model_Object;
				foreach ($o_Object->a_fGetRefObjectsOfForm($o_Object->FormCode) as $object)
				{
					$formcode = '';
					$forms = Qss_Lib_System::getFormsByObject($object->ObjectCode);
					if(!count((array)$forms))
					{
						continue;
					}
					foreach ($forms as $item)
					{
						if(!$item->Public)
						{
							if($formcode != '')
							{
								$formcode .= ',';
							}
							$formcode .= $item->FormCode;
						}
					}
					if($formcode)
					{
						$result .= sprintf('<li id="%1$d" objid="%1$d" fieldid="%2$d" vid="%3$d" ioid="0"><a><span><?php echo $this->objects[%4$d]->sz_fGetNameByID(%1$d)?> (%5$s)</span></a></li>', 
								$object->ObjectCode, 
								$object->FieldCode, 
								$object->RefFieldCode,
								$objid,
								$formcode);
					}
				}
				/*foreach ($o_Object->a_fGetInheritObjectsOfForm($o_Object->FormCode,$o_Object->ObjectCode) as $object)
				{
					$formcode = '';
					$forms = Qss_Lib_System::getFormsByObject($object->ObjectCode);
					if(!count((array)$forms))
					{
						continue;
					}
					foreach ($forms as $item)
					{
						if($formcode != '')
						{
							$formcode .= ',';
						}
						$formcode .= $item->FormCode;
					}
					$result .= sprintf('<li id="%1$d" objid="%1$d" fieldid="%2$d" vid="%3$d" ioid="<?php echo $this->objects[%4$d]->i_IOID?>"><a><span><?php echo $this->objects[%4$d]->sz_fGetNameByCode(%1$d)?> (%5$s)</span></a></li>', 
								$object->ObjectCode, 
								$object->FieldCode, 
								$object->RefFieldCode,
								$objid,
								$formcode);
				}*/
				break;
			}
			$result .= "</ul>\n";
			$result .= "</div>\n";
			$result .= "<div class='tl fl'></div><div class='tm fl'></div><div class='tr fl'></div>\n";
			$result .= "<div class='clear'></div>\n";
			$result .= "</div>\n";
			$result .= "<div id='sub_content'> </div>\n";
			$result .= "<div class='bl fl'></div><div class='bm fl'></div><div class='br fl'></div>\n";
			$result .= "<div class='clear'></div>\n";
		}
		$handle = fopen($sz_FileName, 'w');
		if ( fwrite($handle, $result) )
		{
			return $sz_FileName;
		}
		fclose($handle);
		return null;
	}

	public static function b_fGenerateObjectEdit (Qss_Model_Object $o_Object, $sz_FileName)
	{
		if ( file_exists($sz_FileName) )
		{
			return $sz_FileName;
		}
		$result = '';
		$ui = new Qss_Model_System_UI();
		$data = $ui->getFormUIConfig($o_Object->ObjectCode);
		$groups = array();
		$boxs = array();
		$nogroup = true;
		foreach ($data as $item)
		{
			if(is_numeric($item->UIGID))
			{
				$nogroup = false;
			}
			$groups[(int) $item->UIGID][(int) $item->BoxType][(int) $item->UIBID][] = $item;
			//$boxs[(int) $item->UIBID][] = $item;
		}
		
		foreach ($groups as $box)
		{
			$result .= "<div class=\"ui_box\">";
			foreach ($box as $k=>$val)
			{
				switch ($k) {
					case 0:
						if($nogroup)
							$result .= "<div class=\"ui_box_s50_left\">";
						else
							$result .= "<div class=\"ui_box_s100\">";
					break;
					case 1:
						$result .= "<div class=\"ui_box_s50_left\">";
					break;
					case 2:
						$result .= "<div class=\"ui_box_s50_right\">";
					break;
				}
				foreach($val as $key=>$v)
				{
					if($v[0]->DisplayBorder || $k == 0)
					{
						$result .= "<fieldset>";
					}
					if($v[0]->DisplayTitle)
					{
						$result .= '<legend><?php echo Qss_Lib_System::getUIBoxName('.$v[0]->UIBID.',$this->user->user_lang);?></legend>';
					}
					foreach ($v as $item)
					{
						$result .= sprintf('<div class="ui_box_line %2$s_%1$s">
											<div class="label">
											<label><?php echo $this->object->getFieldByCode("%1$s")->szFieldName;?></label>
											</div>
											<div class="element">
											<?php echo $this->object->sz_fGetFormFieldElement("%1$s",$this->user,$this->dialog);?>
											</div>
											</div>',
									$item->FieldCode,
									$item->ObjectCode);
						if(!@$item->NoTree && $item->ObjectCode == $item->RefObjectCode)
						{
							$result .= sprintf('<div class="ui_box_line %2$s_%1$s">
											<div class="label">
											<label><?php echo $this->_translate(1);?></label>
											</div>
											<div class="element">
											<?php echo $this->views->Instance->Field->Order($this->object,"%1$s");?>
											</div>
											</div>',
									$item->FieldCode,
									$item->ObjectCode);
						}
					}
					if($v[0]->DisplayBorder || $k == 0)
					{
						$result .= "</fieldset>";
					}
				}
				$result .= "</div>";
			}
			$result .= "</div>";
		}
		$handle = fopen($sz_FileName, 'w');
		if ( fwrite($handle, $result) )
		{
			return $sz_FileName;
		}
		fclose($handle);
		return null;
	}

    public static function b_fGenerateObjectMobile (Qss_Model_Object $o_Object, $sz_FileName)
    {
        if ( file_exists($sz_FileName) )
        {
            return $sz_FileName;
        }
        $result = '';
        $ui = new Qss_Model_System_UI();
        $data = $ui->getFormUIConfig($o_Object->ObjectCode);
        $groups = array();
        $boxs = array();
        $nogroup = true;
        foreach ($data as $item)
        {
            if(is_numeric($item->UIGID))
            {
                $nogroup = false;
            }
            $groups[(int) $item->UIGID][(int) $item->BoxType][(int) $item->UIBID][] = $item;
            //$boxs[(int) $item->UIBID][] = $item;
        }

        foreach ($groups as $box)
        {
            $result .= "<div class=\"mobile-box-wrap\">";
            foreach ($box as $k=>$val)
            {
                foreach($val as $key=>$v)
                {
                    if($v[0]->DisplayTitle)
                    {
                        $result .= '<div class="mobile-box-head-line"><?php echo Qss_Lib_System::getUIBoxName('.$v[0]->UIBID.',$this->user->user_lang);?></div>';
                    }

                    $i = 0;

                    foreach ($v as $item)
                    {
                        $class = (++$i%2==0)?'bglightblue':'';
                        $result .= sprintf('<div class="mobile-box-normal-line %2$s">

											<div class="fl"><label><?php echo $this->object->getFieldByCode("%1$s")->szFieldName;?></label></div>

											<div class="fl"><?php echo $this->object->sz_fGetFormFieldElement("%1$s",$this->user,$this->dialog);?></div>

											</div>',
                            $item->FieldCode, $class);
                    }
                }
            }
            $result .= "</div>";
        }
        $handle = fopen($sz_FileName, 'w');
        if ( fwrite($handle, $result) )
        {
            return $sz_FileName;
        }
        fclose($handle);
        return null;
    }

	//-----------------------------------------------------------------------
	public static function CheckBox ($name, $val, $ext = "",$readonly = false)
	{
		if($readonly)
		{
			$ret = sprintf('<input disabled type="checkbox" name="%1$s" value="1" %2$s %3$s>', $name, ($val ? "checked" : ""), $ext);
			$ret .= sprintf('<input type="hidden" name="%2$s" value="%1$s">', $val,$name);
		}
		else 
		{
			$ret = sprintf('<input type="checkbox" name="%1$s" value="1" %2$s %3$s>', $name, ($val ? "checked" : ""), $ext);	
		}
		return $ret;
	}
	public static function CheckBoxList ($name, $arrData, $arrSelected, $ext = 0,$readonly = false)
	{
		$ret = '';
		foreach ($arrData as $key=>$value)
		{
			if($readonly)
			{
				$ret .= sprintf('<input disabled type="checkbox" name="%1$s[]" value="%2$d" %3$s %4$s>  %5$s'
								, $name
								, $key
								, ($arrSelected[$key]?"checked" : "")
								, $ext
								, $value);
				if($arrSelected[$key])
				{
					$ret .= sprintf('<input type="hidden" name="%1$s[]" value="%2$d">', $name,$key);
				}
			}
			else 
			{
				$ret .= sprintf('<input type="checkbox" name="%1$s[]" value="%2$d" %3$s %4$s> %5$s'
								, $name
								, $key
								, ($arrSelected[$key]?"checked" : "")
								, $ext
								, $value);
			}
		}
		return $ret;
	}
	//-----------------------------------------------------------------------
	public static function Editor ($name, $val, $width = 100, $ext = '',$readonly = false,$limit = 0)
	{
		if($readonly)
		{
			$ret = sprintf('<textarea id="%1$s" name="%1$s" class="readonly" readonly style="width:%3$dpx">%2$s</textarea>'
				, $name
				, $val ? Qss_Lib_Util::htmlToText($val) : ""
				, $width);
		}
		else 
		{
			$ret = sprintf('<textarea id="%1$s" name="%1$s" %4$s style="width:%3$dpx">%2$s</textarea>'
				, $name
				, $val ? $val : ""
				, $width
				, $ext
				, $limit?'maxlength = "'.$limit.'"':'');
		}
		return $ret;
	}

	/**
	 *
	 * @param $name
	 * @param $array
	 * @param $selected
	 * @param $width
	 * @param $js
	 * @param $size
	 * @return unknown_type
	 */
	public static function ComboBox ($name, $array, $selected, $width = 100, $js = 0, $size = 1,$readonly = false,$disabled = array())
	{
		$js = $js ? "onchange=\"$js\"" : '';
		if($readonly)
		{
			$display = '';
			foreach ($array as $key => $value)
			{
				if($selected && $selected == $key)
				{
					$display = $value;
				}
			}
			$ret = "<input type='text' id='{$name}_display' name='{$name}_display' style='width: {$width}px;' class=\"readonly\" value=\"{$display}\" readonly>";
			$ret .= sprintf('<input type="hidden" id="%2$s" name="%2$s" value="%1$s">', $selected,$name);
		}
		else 
		{
			$ret = sprintf('<select id="%1$s" name="%1$s" size=%4$s style="width: %2$spx;" %3$s>', $name, $width, $js, $size);
			foreach ($array as $key => $value)
			{
				if(is_object($value))
				{
					$ret .= sprintf('<option class="green italic" value="null" org_fieldid="%6$s" org_objid="%7$s" fid="%2$s" objid="%3$s" fielid="%4$s" ifid="%5$s" >%1$s</option>',//json="%8$s"
						'Tạo mới',
						$value->RefFormCode,
						$value->RefObjectCode,
						$value->RefFieldCode,
						$value->intRefIFID,
						$value->FieldCode,
						$value->ObjectCode);//,						htmlspecialchars($value->json)
				}
				else 
				{
					$ret .= sprintf('<option value="%1$s" %2$s %4$s>%3$s</option>',
						$key,
						($selected == $key) ? 'selected' : '',
						$value,
						in_array($key, $disabled)?'disabled':'');
				}
			}
			$ret .= "</select>";
		}
		return $ret;
	}

	/**
	 *
	 * @param Qss_Model_Object $o_Object
	 * @param $sz_FileName
	 * @return unknown_type
	 */
	public static function sz_fGenerateFormDetail ($form, $sz_FileName)
	{
		if ( file_exists($sz_FileName) )
		{
			return $sz_FileName;
		}
		$result = '';
		$ui = new Qss_Model_System_UI();
		$objects = $form->o_fGetMainObjects();
		$firstobject = array_values($objects);
		$data = $ui->getFormUIConfig($firstobject[0]->ObjectCode);
		$groups = array();
		$boxs = array();
		$result = '';
		$nogroup = true;
		foreach ($data as $item)
		{
			if(is_numeric($item->UIGID))
			{
				$nogroup = false;
			}
			$groups[(int) $item->UIGID][(int) $item->BoxType][(int) $item->UIBID][] = $item;
			$boxs[(int) $item->UIGID] = $item;
		}
		$first = true;
		$result .= '<div class="navmenutab tabs-menu">';
		$result .= '<ul>';
		foreach ($groups as $id=>$box)
		{
			if($first || $id)//không in các trường hợp chưa cho vào box nào
			{
				$name = $boxs[$id]->Name?$boxs[$id]->Name:'<?php echo $this->form->sz_Name?>';
				
				$result .= '<li class="splash"></li>';
				if($first)
				{
					$result .= '<li class="active normal"><a href="#form_tab_'.$id.'"><span>'.$name.'</span></a></li>';
				}
				else
				{
		        	$result .= '<li class="normal"><a href="#form_tab_'.$id.'"><span>'.$name.'</span></a></li>';
				}
				$first = false;
			}
		}
		$result .= '</ul>';
		$result .= '</div>';
		$result .= '<div style="border-top: 1px solid #999; clear: both;"></div>';
		
		$first = true;
		foreach ($groups as $id=>$box)
		{
			if($first)
			{
				$result .= "<div class=\"ui_box\" id=\"form_tab_{$id}\">";
			}
			else
			{
				$result .= "<div class=\"ui_box\" id=\"form_tab_{$id}\">";
			}
			foreach ($box as $k=>$val)
			{
				switch ($k) {
					case 0:
						if($nogroup)
							$result .= "<div class=\"ui_box_s50_left\">";
						else 
							$result .= "<div class=\"ui_box_s100\">";
					break;
					case 1:
						$result .= "<div class=\"ui_box_s50_left\">";
					break;
					case 2:
						$result .= "<div class=\"ui_box_s50_right\">";
					break;
				}
				foreach($val as $key=>$v)
				{
					if($v[0]->DisplayBorder || $k == 0)
					{
						$result .= "<fieldset>";
					}
					if($v[0]->DisplayTitle)
					{
						if($form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && $first)
						$result .= '<legend>' . $v[0]->Title.' <span class="<?php echo $this->form->getStatusData($this->user->user_lang)->Color?>">(<?php echo $this->form->getStatusData($this->user->user_lang)->Name?>)</span></legend>';
						else 
						$result .= "<legend>{$v[0]->Title}</legend>";
						$first = false;
						
					}
					foreach ($v as $item)
					{
						$result .= sprintf('<div class="ui_box_line">
											<div class="label">
											<label><?php echo $this->objects["%1$s"]->getFieldByCode("%2$s")->szFieldName;?></label>
											</div>
											<div class="element">
											<?php echo $this->objects["%1$s"]->getFieldByCode("%2$s")->sz_fGetDisplay();?>
											</div>
											</div>',
									$firstobject[0]->ObjectCode,
									$item->FieldCode);
					}
					if($v[0]->DisplayBorder || $k == 0)
					{
						$result .= "</fieldset>";
					}
				}
				$result .= "</div>";
			}
			$result .= "</div>";
		}
					
		$result .= "<div id='tabs_sub'>\n";
		$result .= "<ul class='tabslist_sub'>\n";
		foreach ($objects as $objid=>$o_Object)
		{
			$o_Object instanceof Qss_Model_Object;
			foreach ($o_Object->a_fGetSubObjectsOfForm($o_Object->FormCode) as $object)
			{
				$result .= sprintf('<li id="%1$d" ifid="<?php echo $this->objects["%2$s"]->i_IFID?>" deptid="<?php echo $this->objects["%2$s"]->intDepartmentID?>" objid="%1$s" fieldid="0" vid="0" ioid="0"><a><span><?php echo $this->objects["%2$s"]->sz_fGetNameByCode("%1$s")?></span></a></li>', $object->ObjectCode,$objid);
			}
			break;
		}
		$result .= "</ul>\n";
		$result .= "</div>\n";
		$result .= "<div class='tl fl'></div><div class='tm fl'></div><div class='tr fl'></div>\n";
		$result .= "<div class='clear'></div>\n";
		$result .= "</div>\n";
		$result .= "<div id='sub_content'> </div>\n";
		$result .= "<div>\n";
		$result .= "<div class='bl fl'></div><div class='bm fl'></div><div class='br fl'></div>\n";
		$result .= "<div class='clear'></div>\n";

		$handle = fopen($sz_FileName, 'w');
		if ( fwrite($handle, $result) )
		{
			return $sz_FileName;
		}
		fclose($handle);
		return null;
	}

	function getMatch ($content)
	{
		if ( preg_match_all(self::SEARCH_PATTERN, $content, $arr) )
		{
			return $arr[1];
		}
		return array();
	}

	function parseContent ($content, $data)
	{
		$this->data = array();
		$this->data = $data;
		$replacementFunction = array($this, 'parseMatchedText');
		$parsedTemplate = preg_replace_callback(self::SEARCH_PATTERN, $replacementFunction, $content);
		return $parsedTemplate;
	}

	function parseMatchedText ($matches)
	{
		try
		{
			return $this->data[$matches[1]];
		}
		catch ( Exception $e )
		{

		}
	}

	function visualtotemplate ($content)
	{
		preg_match_all('/\<span[^>]*>(.*)\<\/span>/siU', $content, $matches);
		$tags = $matches[0];
		$cdata = array();
		$count = 1;
		foreach ($tags as $tag)
		{
			$name = $this->getAttribute('name', $tag);
			if ( !$name )
			continue;
			switch ( $name )
			{
				case 'cms':
					$replace = sprintf('{%1$s_%2$d_%3$d_%4$d_%5$d_%6$d}', $name, $this->getAttribute('content_id', $tag), $this->getAttribute('record_id', $tag), $this->getAttribute('design_id', $tag), $this->getAttribute('limit', $tag), $count);
					$count ++;
					break;
				case 'content':
				case 'pager':
				case 'form':
				case 'forum':
				case 'survey':
				case 'search':
				case 'ogrid':
				case 'ocomment':
				case 'otitle':
					$replace = sprintf('{%1$s_%2$d_%3$d_%4$d_%5$d}', $name, $this->getAttribute('content_id', $tag), $this->getAttribute('record_id', $tag), $this->getAttribute('design_id', $tag), $this->getAttribute('limit', $tag));
					break;
				case 'dat':
				case 'lb':
					$replace = sprintf('{%1$s_%2$d_%3$s_%4$d}', $name, $this->getAttribute('content_id', $tag), $this->getAttribute('record_id', $tag), $this->getAttribute('design_id', $tag));
					break;
				default:
					$replace = $tag;
					break;
			}
			$cdata[$tag] = $replace;
		}
		$this->data = array();
		$this->data = $cdata;
		$replacementFunction = array($this, 'pMatchedText');
		$parsedTemplate = @preg_replace_callback('/\<span[^>]*>(.*)\<\/span>/siU', $replacementFunction, $content);
		return $parsedTemplate;
	}

	function pMatchedText ($matches)
	{
		try
		{
			if ( array_key_exists($matches[0], $this->data) )
			return $this->data[$matches[0]];
			else
			return $matches[0];
		}
		catch ( Exception $e )
		{

		}
	}

	/**
	 * Resize picture
	 *
	 * Resize picture in $sysfolder/Picture folder
	 *
	 * @access  public
	 * @param   Picture ID for resize (table datpicture)
	 * @param   width of new image
	 * @param   Height of new image
	 */
	static function PictureResize ($filename, $maxwidth = '', $maxheight = '')
	{
		if ( !is_numeric($maxwidth) || !is_numeric($maxheight) || !$maxwidth || !$maxheight)
		{
			$maxwidth = 180;
			$maxheight = 150;
		}
		$file_name = QSS_DATA_DIR . "/documents/" . $filename;
		if ( !file_exists($file_name) )
		{
			return false;
		}
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$resize =self::resizeFileName($filename);
		list ($width, $height) = getimagesize($file_name);
		if ( ($height / $maxheight) > ($width / $maxwidth) )
		{
			$x = 0;
			$y = $height - ($width / $maxwidth) * $maxheight;
		}
		else
		{
			$x = $width - ($height / $maxheight) * $maxwidth;
			$y = 0;
		}
		$newimage = imagecreatetruecolor($maxwidth, $maxheight);
		imagecolortransparent($newimage, 0);
		switch ( $ext )
		{
			case "jpg":
			case "jpeg":
			case "pjpeg":
				try
				{
					$image = imagecreatefromjpeg($file_name);
				}
				catch ( Exception $e )
				{
					echo "img error";
					return false;
				}
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $maxwidth, $maxheight, $width - $x, $height - $y);
				imagejpeg($newimage, QSS_DATA_DIR . "/documents/" . $resize, 100);
				break;
			case "png":
				$image = imagecreatefrompng($file_name);
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $maxwidth, $maxheight, $width - $x, $height - $y);
				imagepng($newimage, QSS_DATA_DIR . "/documents/" . $resize);
				break;
			case "gif":
				$image = imagecreatefromgif($file_name);
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $maxwidth, $maxheight, $width - $x, $height - $y);
				imagegif($newimage, QSS_DATA_DIR . "/documents/" . $resize);
				break;
			default:
				return false;
				break;
		}
		return true;
	}

	public static function getBarCode ($barcode = '0123456789', $height = 120, $width = 200, $type = 'I25', $output = 'png', $xres = 2, $font = 5, $border = 'off', $drawtext = 'on', $stretchtext = 'off', $negative = 'off')
	{
		if ( isset($barcode) && strlen($barcode) > 0 )
		{
			$barcodeobject = new Qss_Lib_Barcode_Barcode();
			$style = BCS_ALIGN_CENTER;
			$style |= ($output == "png") ? BCS_IMAGE_PNG : 0;
			$style |= ($output == "jpeg") ? BCS_IMAGE_JPEG : 0;
			$style |= ($border == "on") ? BCS_BORDER : 0;
			$style |= ($drawtext == "on") ? BCS_DRAW_TEXT : 0;
			$style |= ($stretchtext == "on") ? BCS_STRETCH_TEXT : 0;
			$style |= ($negative == "on") ? BCS_REVERSE_COLOR : 0;
			$ret = sprintf('<img id= "%1$d" src=""></img>', $barcode);
			$ret .= sprintf('<script>showBarcode("%1$d","%2$s")</script>', $barcode, "code=" . $barcode . "&style=" . $style . "&type=" . $type . "&width=" . $width . "&height=" . $height . "&xres=" . $xres . "&font=" . $font);
			return $ret;
		}
	}

	function getElement ($fn)
	{
		$fileData = file_get_contents($fn);
		preg_match_all(self::SEARCH_PATTERN, $fileData, $retval);
		return $retval;
	}

	public function parseTemplateFile ($fn, $data)
	{
		$this->data = array();
		$this->data = $data;
		$replacementFunction = array($this, 'parseMatchedText');
		$fileData = file_get_contents($fn);
		$parsedTemplate = preg_replace_callback(self::SEARCH_PATTERN, $replacementFunction, $fileData);
		return $parsedTemplate;
	}
	/**
	 *
	 * Write visual template file
	 *
	 * @param string $fromfile
	 * @param string $tofile
	 */
	function templatetovisual($fromfile,$tofile)
	{
		$subdata = array();
		$match = $this->getElement($fromfile);
		$arr = $match[1];
		foreach($arr as $val)
		{
			if($val == 'js')
			continue;
			$arrVal = @split('_',$val);
			$fid = (int) $arrVal[1];
			$ifid = $arrVal[2];
			$design_id = (int) $arrVal[3];
			$class = 'cms_object';
			$limit = (int) $arrVal[4];
			$subdata[$val] = "<span name='{$arrVal[0]}' content_id='{$fid}' record_id='{$ifid}' design_id='{$design_id}' limit='{$limit}' class='{$class}' style='width: 100px; height: 23px;'>{$val}</span>";
		}
		$content = $this->parseTemplateFile($fromfile,$subdata);
		$handle = fopen($tofile,'w');
		fwrite($handle,$content);
		fclose($handle);

	}
	function getAttribute($attrib, $tag)
	{
		//get attribute from html tag
		$re = '/'.$attrib.'=["\']?([^"\' ]*)["\' ]/is';
		preg_match($re, $tag, $match);
		if($match)
		{
			return urldecode($match[1]);
		}
		else
		{
			return false;
		}
	}
	function contenttovisual($content)
	{
		$subdata = array();
		$arr = $this->getMatch($content);
		foreach($arr as $val)
		{
			if($val == 'js')
			continue;
			$arrVal = split('_',$val);
			$fid = (int) $arrVal[1];
			$ifid = $arrVal[2];
			$design_id = (int) $arrVal[3];
			$class = 'cms_object';
			$limit = (int) $arrVal[4];
			$subdata[$val] = "<span name='{$arrVal[0]}' content_id='{$fid}' record_id='{$ifid}' design_id='{$design_id}' limit='{$limit}' class='{$class}' style='width: 100px; height: 23px;'>{$val}</span>";
		}
		$content = $this->parseContent($content,$subdata);
		return $content;
	}
	public static function ListBox(Qss_Model_Field $field,$bMain,$dialog = false)
	{
		$view = new Qss_View();
		return $view->Instance->Field->ListBox($field,$bMain,$dialog);
	}
	public static function CustomButton(Qss_Model_Object $object,Qss_Model_Field $field)
	{
		$view = new Qss_View();
		return $view->Instance->Field->CustomButton($object,$field);
	}
	public static function resizeFileName($filename)
	{
		$arr = explode('.',$filename);
		if(count($arr) == 2 && !strpos('_r',$arr[0]))
		{
			$filename = $arr[0].'_r.'.$arr[1];
		}
		return $filename;
	}
}
?>