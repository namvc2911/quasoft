<?php
/**
 *
 * <REMOVE: Các chức năng này đã không còn sử dụng, file sẽ được xóa vào 16/11/2015>
 *
 */
class Extra_NewsController extends Qss_Lib_Controller {

    public $_user;

    public function init()
    {
        parent::init();
        $this->_user = Qss_Register::get('userinfo');
    }

    public function plantasksMoveAction()
    {
        $date   = $this->params->requests->getParam('date', date('d-m-Y',strtotime("-1 days")));
        $news   = new Qss_Model_Extra_News();
        $reject = $news->getRejectPlanTasks(Qss_Lib_Date::displaytomysql($date));

        $this->html->reject = $reject;
        $this->html->date   = $date;
    }

    public function plantasksMovesaveAction()
    {
        $params = $this->params->requests->getParams();
        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Extra->News->Plantasks->Move($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}