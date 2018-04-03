<?php
/**
 * @author: Huy.Bui
 * @component: Cac mau in su co thiet bi
 */
class Print_M707Controller extends Qss_Lib_PrintController
{
	public function init()
	{
            parent::init();
            $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
	}

    /**
     * In bien ban su co
     */
    public function documentAction()
    {
        $this->html->data = $this->_params;
    }
}

?>