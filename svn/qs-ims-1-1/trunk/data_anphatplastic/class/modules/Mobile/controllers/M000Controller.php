<?php

class Mobile_M000Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mobile.php';
        $this->html->user = $this->_user;
    }

    public function indexAction()
    {

    }

    /******************************************************************************************************************/



    /******************************************************************************************************************/

    /**
     * B: Chức năng hiển yêu cầu duyệt vật tư
     */

    /**
     * Chức năng hiển thị yêu cầu duyệt vật tư - Danh sách duyệt vật tư
     */
    public function outputApprovalIndexAction()
    {

    }

    /**
     * Chức năng hiển thị yêu cầu duyệt vật tư - Danh sách duyệt vật tư
     */
    public function outputApprovalListAction()
    {

    }

    /**
     * Chức năng hiển thị yêu cầu duyệt vật tư - Duyệt dòng vật tư
     */
    public function outputApprovalApproveAction()
    {

    }
}