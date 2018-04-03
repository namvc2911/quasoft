<?php
class Print_M759_documentController extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    public function indexAction()
    {
        $this->html->data = $this->_params;
        $this->html->status = $this->_form->i_Status;
    }
}
?>