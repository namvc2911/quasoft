<?php
class Button_M852Controller extends Qss_Lib_Controller
{

    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();
    }

    public function checklistsIndexAction()
    {
        $this->html->ifid = $this->params->requests->getParam('ifid', 0);
    }

    public function checklistsShowAction()
    {
        $list  = $this->params->requests->getParam('list', 0);
        $mList = Qss_Model_Db::Table('OChiTietBangChonCongViec');
        $mList->select('OChiTietBangChonCongViec.*');
        $mList->join('INNER JOIN OBangChonCongViec ON OChiTietBangChonCongViec.IFID_M906  = OBangChonCongViec.IFID_M906');
        $mList->where(sprintf('OBangChonCongViec.IOID = %1$d', $list));

        $this->html->data = $mList->fetchAll();
    }

    public function checklistsSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Static->M852->Checklist->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}