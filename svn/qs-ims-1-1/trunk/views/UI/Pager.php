<?php

/**
 * Class Qss_View_Report_ComboBox
 */
class Qss_View_UI_Pager extends Qss_View_Abstract
{
    /**
     * Mau goi ham $this->views->UI->Pager('pageID', 'perpageID', 1, 20, 10, 'search()');
     * @param string $pageID
     * @param string $perpageID
     * @param int $currentPage
     * @param int $perpage
     * @param int $totalPage
     * @param string $jsFunction
     */
    public function __doExecute($pageID, $perpageID, $currentPage, $perpage, $totalPage, $jsFunction)
    {
        $currentPage = ($currentPage < 0 || $currentPage > $totalPage)?1:$currentPage;
        $prevPage    = (($currentPage - 1) >= 1)?($currentPage - 1):1;
        $nextPage    = (($currentPage + 1) <= $totalPage)?($currentPage + 1):$currentPage;

        $this->html->prevPage    = $prevPage;
        $this->html->nextPage    = $nextPage;
        $this->html->currentPage = $currentPage;
    }
}