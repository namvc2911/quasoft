<?php

class User_HelpController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {

    }

    public function editAction()
    {
        $module       = $this->params->requests->getParam('module', '');
        $module       = strtoupper($module);
        $formActive   = Qss_Lib_System::formActive($module);

        if($module == '' || !$formActive)
        {
            return;
        }

        $formContent  = ''; // Du lieu help ve form
        $formFolder   = QSS_PUBLIC_DIR . '/help/forms/';
        $formFile     = QSS_PUBLIC_DIR . '/help/forms/' . $module . '_' . $this->_user->user_lang . '.html';
        $objectFolder = QSS_PUBLIC_DIR . '/help/objects/';
        $objects      = array(); // Key ObjectCode, Du lieu help ve object
        $fields       = array(); // Key1 ObjectCode Key1.1 FieldCode, Du lieu help ve fields
        $common       = new Qss_Model_Extra_Extra();
        $objectList   = $common->getObjectsByForm($module);
        $oForm        = $common->getTableFetchOne('qsforms', array('FormCode'=>$module));

        // Tao Form folder
        if (!file_exists($formFolder)) {
            mkdir($formFolder , 0777, true);
            chmod($formFolder, 0777);
        }

        // Tao Form Object
        if (!file_exists($objectFolder)) {
            mkdir($objectFolder , 0777, true);
            chmod($objectFolder, 0777);
        }

        // Tao file form neu chua ton tai
        if(!file_exists($formFile))
        {
            $fpForm = fopen($formFile , "wb");
            fwrite($fpForm, '');
            fclose($fpForm);
            chmod($formFile, 0777);
        }

        $formContent = @file_get_contents($formFile);

        if(trim($oForm->class) == '')
        {
            foreach($objectList as $object)
            {
                $fieldFolder    = $objectFolder . $object->ObjectCode;
                $objectTempFile = $objectFolder . $object->ObjectCode . '_' . $this->_user->user_lang . '.html';
                $fieldList      = $common->getFieldsByObject($object->ObjectCode);

                // Tao file object neu chua ton tai
                if(!file_exists($objectTempFile))
                {
                    $fpObject = fopen($objectTempFile , "wb");
                    fwrite($fpObject, '');
                    fclose($fpObject);
                    chmod($objectTempFile, 0777);
                }

                $objects[$object->ObjectCode]['html']   = @file_get_contents($objectTempFile);
                $objects[$object->ObjectCode]['detail'] = $object;

                // Tao folder object chua field
                if (!file_exists($fieldFolder)) {
                    mkdir($fieldFolder , 0777, true);
                    chmod($fieldFolder, 0777);
                }

                foreach ($fieldList as $field)
                {
                    $fieldTempFile = $objectFolder . $object->ObjectCode . '/' . $field->FieldCode
                        . '_' . $this->_user->user_lang . '.html';

                    // Tao file field neu chua ton tai
                    if(!file_exists($fieldTempFile))
                    {
                        $fpField = fopen($fieldTempFile , "wb");
                        fwrite($fpField, '');
                        fclose($fpField);
                        chmod($fieldTempFile, 0777);
                    }

                    $fields[$object->ObjectCode][$field->FieldCode]['html']   =  @file_get_contents($fieldTempFile);
                    $fields[$object->ObjectCode][$field->FieldCode]['detail'] = $field;
                }
            }
        }

        // echo '<pre>'; print_r($objects); die;

        $this->html->formContent = $formContent;
        $this->html->objects     = $objects;
        $this->html->fields      = $fields;
        $this->html->module      = $module;
        $this->html->lang        = $this->_user->user_lang;
        $this->html->form        = $oForm;
    }

    public function saveAction()
    {
        $params   = $this->params->requests->getParams();

        // echo '<pre>'; print_r($params['object']); die;

        if(isset($params['FormCode']) && $params['FormCode'])
        {
            $formFile = QSS_PUBLIC_DIR . '/help/forms/' . $params['FormCode'] . '_' . $this->_user->user_lang . '.html';

            if(isset($params['form']) && $params['form'])
            {
                $params['form'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $params['form']);
                $params['form'] = preg_replace('#<\?php(.*?)(\?>)#is', '', $params['form']);

                $fpForm = fopen($formFile , "wtr+");
                ftruncate($fpForm, 0);
                fwrite($fpForm, $params['form']);
                fclose($fpForm);
            }

            if(isset($params['object']) && $params['object'])
            {
                foreach($params['object'] as $objectKey=>$objectVal)
                {
                    $objectFile = QSS_PUBLIC_DIR . '/help/objects/' . $objectKey . '_' . $this->_user->user_lang . '.html';

                    $objectVal = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $objectVal);
                    $objectVal = preg_replace('#<\?php(.*?)(\?>)#is', '', $objectVal);

                    $fpObject = fopen($objectFile , "wtr+");
                    ftruncate($fpObject, 0);
                    fwrite($fpObject, $objectVal);
                    fclose($fpObject);
                }
            }

            if(isset($params['field']) && $params['field'])
            {
                foreach($params['field'] as $objectKey=>$fields)
                {
                    foreach($fields as $fieldKey=>$fieldVal)
                    {
                        $fieldFile = QSS_PUBLIC_DIR . '/help/objects/' . $objectKey . '/' . $fieldKey. '_' . $this->_user->user_lang . '.html';

                        $fieldVal = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $fieldVal);
                        $fieldVal = preg_replace('#<\?php(.*?)(\?>)#is', '', $fieldVal);

                        $fpField = fopen($fieldFile , "wtr+");
                        ftruncate($fpField, 0);
                        fwrite($fpField, $fieldVal);
                        fclose($fpField);
                    }

                }
            }

        }

        echo Qss_Json::encode(array('error' => 0, 'message' => '', 'redirect' => null));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}