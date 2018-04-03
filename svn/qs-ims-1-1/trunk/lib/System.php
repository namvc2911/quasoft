<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_System
{
	public static $currency;

	public static function fieldExists($objectcode,$fieldcode)
	{
		$db = Qss_Db::getAdapter('main');
		return $db->fieldExists($objectcode, $fieldcode);
	}

	public static function fieldActive($objectcode,$fieldcode)
	{
		$model = new Qss_Model_System_Field();
		$field = $model->getObjectField($objectcode, $fieldcode);
		return $field?$field->Effect:0;
	}
	public static function formActive($formcode)
	{
		$model = new Qss_Model_System_Form();
		$form = $model->getByCode($formcode);
		return $form?$form->Effected:0;
	}
	public static function objectInForm($formcode,$objectcode)
	{
		$model = new Qss_Model_System_Form();
		$form = $model->getObjectByCode($formcode,$objectcode);
		return (bool)$form;

	}
	public static function getFormRights ($formcode,$groupList)
	{
		$model = new Qss_Model_System();
		return $model->getFormRights($formcode,$groupList);
	}
	public static function getUIBoxName ($boxid,$lang)
	{
		$retval = '';
		$model = new Qss_Model_System_UI();
		$dataSQL = $model->getBoxByID($boxid);
		if($dataSQL)
		{
			$lang = Qss_Translation::getInstance()->getLanguage();
			$name = 'Title'.(($lang=='vn')?'':'_'.$lang);
			if(isset($dataSQL->$name) && $dataSQL->$name)
			{
				$retval = $dataSQL->$name;
			}
			else
			{
				$retval = $dataSQL->Title;
			}
		}
		return $retval;
	}
	public static function getUIGroupName ($groupid,$lang)
	{
		$retval = '';
		$model = new Qss_Model_System_UI();
		$dataSQL = $model->getGroupByID($groupid);
		if($dataSQL)
		{
			$lang = Qss_Translation::getInstance()->getLanguage();
			$name = 'Name'.(($lang=='vn')?'':'_'.$lang);
			if(isset($dataSQL->$name) && $dataSQL->$name)
			{
				$retval = $dataSQL->$name;
			}
			else
			{
				$retval = $dataSQL->Name;
			}
		}
		return $retval;
	}
	public static function getFormsByObject ($objid)
	{
		$model = new Qss_Model_System();
		return $model->getFormsByObject($objid);
	}
	/*public static function objectActive($code)
	{
		$model = new Qss_Model_System();
		return $model->objectActive($code);
	}*/
	
	/*public static function getVID($fieldID, $IOID)
	{
	    $model = new Qss_Model_System();
	    return $model->getVID($fieldID, $IOID);
	}*/
    
    public static function getFieldRegx($objectCode, $fieldCode)
    {
        $model = new Qss_Model_System();
        $data  = $model->getFieldByCode($objectCode, $fieldCode);
        $json  = $data?$data->Regx:'{}';
        return Qss_Json::decode($json);
    }
    
    public static function getFieldRegxByVal($objectCode, $fieldCode, $value)
    {
        $model = new Qss_Model_System();
        $data  = $model->getFieldByCode($objectCode, $fieldCode);
        $json  = $data?$data->Regx:'{}';
        $array = Qss_Json::decode($json);
        
        if(is_array($array) && count($array))
        {
            return $array[$value];
        }
        return '';
    }
	public static function getFormObject($formcode,$objectcode)
	{
		$model = new Qss_Model_System();
		return $model->getFormObject($formcode,$objectcode);
	}   

	public static function getFormByCode($code)
	{
	    $model = new Qss_Model_System_Form();
	    return $model->getByCode($code);
	}
	
	public static function getStepsByForm($formCode, $lang = '')
	{
        if($lang == '') {
            $user = Qss_Register::get('userinfo');
            $lang = $user->user_lang;
        }

		$model = new Qss_Model_System();
		return $model->getStepsByForm($formCode, $lang);
	}

    public static function getStepsArray($formCode, $lang = '')
    {
        if($lang == '') {
            $user = Qss_Register::get('userinfo');
            $lang = $user->user_lang;
        }

        $model = new Qss_Model_System();
        $steps = $model->getStepsByForm($formCode, $lang);
        $ret   = array();

        foreach ($steps as $item)
        {
            $ret[$item->StepNo] = $item;
        }

        return $ret;
    }
	
	public static function getCurrencyByCode($code = 'VND')
	{
		if(isset(self::$currency[$code]))
		{
			return self::$currency[$code];
		}
		$model = new Qss_Model_Currency();
		if(!$code)
		{
			$retval = $model->getPrimary();
			$code = $retval->Code;
		}
		else
		{
			$retval = $model->getByCode($code);
		}
		if($retval)
		{
			self::$currency[$code] = $retval;
		}
		return $retval;
	}
	public static function formSecure($formcode)
	{
		$model = new Qss_Model_System_Form();
		$form = $model->getByCode($formcode);
		return $form?$form->Secure:0;
	}

	public static function getTemplateFile($formCode, $fileName)
    {
        $retval = QSS_DATA_DIR."/template/{$formCode}/{$fileName}";

        if(!file_exists($retval))
        {
            $retval = QSS_DATA_BASE."/template/{$formCode}/{$fileName}";
        }

        return $retval;
    }

    public static function getFieldsByObject($formCode, $objectCode)
    {
        $mObject = new Qss_Model_Object();
        $mObject->v_fInit($objectCode, $formCode);
        return $mObject;
    }

    public static function getReportTitle($formCode)
    {
        $mForm = new Qss_Model_Form();
        $mForm->init($formCode);
        return $mForm->sz_Name;
    }

    public static function getUpperCaseReportTitle($formCode)
    {
        $mForm = new Qss_Model_Form();
        $mForm->init($formCode);
        return mb_strtoupper($mForm->sz_Name, 'UTF-8');
    }

    public static function getUserByIFID($ifid)
    {
        $mSys = new Qss_Model_System();
        return $mSys->getUserByIFID($ifid);
    }


    public static function loadHeaderIniFile()
    {
        $arr           = explode('?', $_SERVER['REQUEST_URI']);
        $url           = $arr[0];
        $retval        = array();
        $urlToFileName = $url?trim(str_replace(array('/','\\'), array('-', '-'), (string)$url), '-'):'';
        $pathToIniFile = $urlToFileName? QSS_DATA_DIR.'/' .'template/' .$urlToFileName.'.ini':'';

        if($pathToIniFile && file_exists($pathToIniFile)) {
            $ini_array = parse_ini_file($pathToIniFile);

            if(count($ini_array)) {
                $retval = $ini_array;
            }
        }

        return $retval;
    }
    public static function getFieldByCode($objectcode,$fieldcode)
	{
		$model = new Qss_Model_System();
		return $model->getFieldByCode($objectcode, $fieldcode);
	}   
}
?>