<?php

class Report_DrilldownController extends Qss_Lib_Controller
{
    public function init ()
    {
        parent::init();
        $this->headLink($this->params->requests->getBasePath() . '/css/general.css');
        $this->headScript($this->params->requests->getBasePath() . '/js/jquery.min.1.8.2.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/jquery.cookie.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/ui/jquery-ui-1.8.1.custom.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/core-ajax.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/index.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/jquery.tablescroll.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/jquery.mask.number.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/tag.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
    }

    public function indexAction()
    {
        $fid     = $this->params->requests->getParam('fid', ''); // require
        $ifid    = $this->params->requests->getParam('ifid', 0);
        $objid   = $this->params->requests->getParam('objid', '');
        $status  = $this->params->requests->getParam('status', array());
        $page    = $this->params->requests->getParam('page', 1);
        $display = $this->params->requests->getParam('display', 10);
        $params  = $this->params->requests->getParams();
        $data    = array();
        $fields  = array();
        $count   = 1;



        if($fid)
        {
            $mForm        = new Qss_Model_Form();
            $mForm->init($fid);

            if(!$objid) // Lấy main object
            {
                $object = $mForm->o_fGetMainObject();
                $objid  = $object->ObjectCode;
            }

            $mDrilldown = new Qss_Model_Report_Drilldown();
            $sql        = $mDrilldown->sz_fGetSQLByUser(
                $fid,
                $ifid,
                $objid,
                $this->_user,
                0,
                'ASC',
                $params,
                array(),
                $status,
                true
            );


            if(isset($sql[0]) && $sql[0])
            {
                $count  = $mDrilldown->i_fGetPageCount($sql[0], $page, $display);
                $data   = $mDrilldown->a_fGetIOIDBySQL($sql[0], $page, $display);

                if($objid)
                {
                    $fields = Qss_Lib_System::getFieldsByObject($fid, $objid)->loadFields();
                }
            }
        }

        $this->html->page      = $page;
        $this->html->display   = $page;
        $this->html->countPage = $count;
        $this->html->fields    = $fields;
        $this->html->data      = $data;
        $this->html->next      = (($page+1) < $count)?($page+1):$count;
        $this->html->prev      = (($page-1) > 1)?($page-1):1;

        $this->html->fid       = $fid;
        $this->html->ifid      = $ifid;
        $this->html->objid     = $objid;
        $this->html->status    = $status;
        $this->html->params    = $params;


        $this->setLayoutRender(false);
        // $this->setHtmlRender(false);


    }
}