<?php

/**
 *
 * @author ThinhTNM
 *
 */
class Extra_HelpController extends Qss_Lib_Controller
{

	public $_form;
	public $_common;
	public $_params;
	const  VIEW_LIKE = 1; // 1 - USER - other - DEVELOPER

	public function init()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();
		$this->_params = $this->params->requests->getParams();
		$this->_common = new Qss_Model_Extra_Extra();

	}

	public function indexAction()
	{
		$this->html->menu = $this->views->Help->Menu($this->_user);
		$this->html->module = @$this->_params['module'];
		$this->html->lang = $this->_user->user_lang;

		//Qss_Lib_Extra::createHelpTemplate(array('M777'), 'vn', false, true);
		$this->setLayoutRender(false);

	}

    public function showAction()
    {
        $module       = $this->params->requests->getParam('file', '');
        $module       = strtoupper($module);
        $formActive   = Qss_Lib_System::formActive($module);
        $findModule   = '';

        $file_404     = QSS_PUBLIC_DIR . '/help/404_'. $this->_params['lang'] .'.html';
        $formContent  = ''; // Du lieu help ve form
        $formFolder   = QSS_PUBLIC_DIR . '/help/forms/';
        $formFile     = QSS_PUBLIC_DIR . '/help/forms/' . $module . '_' . $this->_user->user_lang . '.html';
        $objectFolder = QSS_PUBLIC_DIR . '/help/objects/';
        $objects      = array(); // Key ObjectCode, Du lieu help ve object
        $fields       = array(); // Key1 ObjectCode Key1.1 FieldCode, Du lieu help ve fields
        $common       = new Qss_Model_Extra_Extra();
        $objectList   = $common->getObjectsByForm($module);
        $oForm        = $common->getTableFetchOne('qsforms', array('FormCode'=>$module));


        // echo '<pre>'; var_dump($oForm); die;

        if($oForm && $formActive)
        {
            $formName     = ($this->_user->user_lang == 'vn')?$oForm->Name:$oForm->{'Name_'.$this->_user->user_lang};

            // Tieu de
            $findModule .= "
                <div id=\"info\">
                    <div id=\"info-title\" class=\"main-title\"> {$oForm->FormCode} - {$formName} </div>
                    <div id=\"info-content\">
            ";

            $findModule .= (file_exists($formFile))?Qss_Lib_Util::textToHtml(@file_get_contents($formFile)):'';

            $findModule .= "
                    </div>
                </div><!-- end #info -->
            ";


            if(trim($oForm->class) == '')
            {
                // Object list
                foreach($objectList as $object)
                {
                    $fieldFolder    = $objectFolder . $object->ObjectCode;
                    $objectTempFile = $objectFolder . $object->ObjectCode . '_' . $this->_user->user_lang . '.html';
                    $fieldList      = $common->getFieldsByObject($object->ObjectCode);
                    $objectName     = ($this->_user->user_lang == 'vn')?$object->ObjectName:$object->{'ObjectName_'.$this->_user->user_lang};

                    $findModule .= "
                <div class=\"object\">
                    <div class=\"object-title sub-title\"> {$objectName}
                    <div class=\"remove-effect\"></div>
                    </div><!-- end .object-title sub-title -->

                    <div class=\"object-des\">
            ";

                    $findModule .= (file_exists($objectTempFile))?Qss_Lib_Util::textToHtml(@file_get_contents($objectTempFile)):'';

                    $findModule .= "
                    </div>


                    <div class='field-list'>
            ";

                    foreach ($fieldList as $field)
                    {
                        $fieldTempFile = $objectFolder . $object->ObjectCode . '/' . $field->FieldCode
                            . '_' . $this->_user->user_lang . '.html';
                        $fieldName     = ($this->_user->user_lang == 'vn')?$field->FieldName:$field->{'FieldName_'.$this->_user->user_lang};

                        $require       = $field->Required?' <span class="red">(*)</span>':'';


                        $findModule .= "
                    <div >
                        <span class=\"bold\"> {$fieldName}{$require}: </span>

                        ";

                        $findModule .= (file_exists($fieldTempFile))?Qss_Lib_Util::textToHtml(@file_get_contents($fieldTempFile)):'&nbsp;';

                        $findModule .= "

                    </div>
                ";
                    }


                    $findModule .= "
                    </div><!-- end fields table -->
                </div><!-- end .object -->
            ";
                }

//        $find    = array('/M[0-9]+/');
//        $replace = array('<a href="#\0" onclick="loadHelper(\'\0\')" class="anchor">\0</a>');
//
//        $findModule = preg_replace($find, $replace, $findModule);
            }
        }
        else
        {
            $findModule = (file_exists($file_404))?@file_get_contents($file_404):'Module not found!';
        }

        $this->html->content = '<div>' . $this->replaceModule($findModule) . '</div>';
        $this->setLayoutRender(false);
    }

    private function replaceModule($content)
    {
        $find       = array('/M[0-9]+/');
        //$replace    = array('<a href="#\0" onclick="openModule(\'\0\')" class="anchor">\0</a>');
        //$replace    = array('<a href="/user/form?fid=\0" target="_blank" class="anchor">\0</a>');
        $replace    = array('<a href="#\0" onclick="loadHelper(\'\0\')" class="anchor">\0</a>');
        return preg_replace($find, $replace, $content);
    }

	public function show1Action()
	{
		if(self::VIEW_LIKE == 1)
		{
			$help_folder = 'help';
		}
		else
		{
			$help_folder = 'help/developer';
		}
		//Qss_Lib_Extra::createHelpTemplate(array('M125'), 'vn', false, true, false);

		$file_in_data = QSS_PUBLIC_DIR . '/' . _QSS_DATA_FOLDER_ . '/'.$help_folder.'/' . $this->_params['file'] . '_' . $this->_params['lang'] . '.' . 'html';
		$file_in_public = QSS_PUBLIC_DIR . '/'.$help_folder.'/' . $this->_params['file'] . '_' . $this->_params['lang'] . '.' . 'html';
		//$file_404_data = QSS_PUBLIC_DIR . '/' . _QSS_DATA_FOLDER_ . '/'.$help_folder.'/404_' . $this->_params['lang'] . '.html';
		//$file_404_public = QSS_PUBLIC_DIR . '/'.$help_folder.'/404_' . $this->_params['lang'] . '.html';
		$file_404_public = QSS_PUBLIC_DIR . '/'.$help_folder.'/404_'. $this->_params['lang'] .'.html';
		$file_404_data   = QSS_PUBLIC_DIR . '/' . _QSS_DATA_FOLDER_ . '/'.$help_folder.'/404_'. $this->_params['lang'] .'.html';


		//            $okFile = array('M001', 'M101', 'M102', 'M107', 'M108', 'M110', 'M113', 'M120', 'M125'
		//, 'M127', 'M133', 'M205', 'M219', 'M316', 'M601', 'M607', 'M701', 'M704', 'M705'
		//, 'M706'
		//);

		if (file_exists($file_in_data))
		{
			$content = @file_get_contents($file_in_data);
			//                if(!in_array($this->_params['file'], $okFile))
			//                {
			//                    $content = 'Updating...';
			//                }
		} elseif (file_exists($file_in_public))
		{
			$content = @file_get_contents($file_in_public);
			//                if(!in_array($this->_params['file'], $okFile))
			//                {
			//                    $content = 'Updating...';
			//                }
		} elseif (file_exists($file_404_data))
		{
			$content = @file_get_contents($file_404_data);
		} else
		{
			$content = @file_get_contents($file_404_public);
		}

		$find = array('/M[0-9]+/');
		$replace = array('<a href="#\0" onclick="loadHelper(\'\0\')" class="anchor">\0</a>');

		$findModule = preg_replace($find, $replace, $content);

		if (trim($findModule) == '')
		{
			$findModule = 'Updating...';
		}

		$this->html->content = '<div>' . $findModule . '</div>';
		$this->setLayoutRender(false);
	}

}
