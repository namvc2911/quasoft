<?php
/**
 * Create filter form
 *
 * @author HuyBD
 *
 */
class Qss_View_Instance_Form_Mobile extends Qss_View_Abstract
{

    public function __doExecute (Qss_Model_Form $form, Qss_Model_UserInfo $user,$dialog = false)
    {
        $objects = $form->o_fGetMainObjects();
        foreach ($objects as $item)
        {
            $classname = 'Qss_Bin_Onload_'.$item->ObjectCode;
            if(!class_exists($classname))
            {
                $classname = 'Qss_Lib_Onload';
            }
            $onload = new $classname(null,$item);
            $onload->__doExecute();
            $fields = $item->loadFields();
            foreach ($fields as $key => $f)
            {
                $onload->{$f->FieldCode}();
            }
            //@TODO chưa check xem có trùng load này ko
        }
        //$object instanceof Qss_Model_Object;
        $this->html->objects = $objects;
        $keys = array_keys($objects);
        $this->html->object = $objects[$keys[0]];

        if ( !is_dir(QSS_DATA_DIR . '/views/mobile/') )
        {
            mkdir(QSS_DATA_DIR . '/views/mobile/');
        }

        $sz_FileName1 = QSS_DATA_DIR . '/views/mobile/' . $form->FormCode. '.phtml';
        $sz_FileName1 = Qss_Lib_Template::b_fGenerateFormMobile($form, $sz_FileName1);

        if ( file_exists($sz_FileName1) )
        {
            $this->html->setHtml($sz_FileName1);
        }
    }
}

?>