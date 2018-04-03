<?php
class Static_M189Controller extends Qss_Lib_Controller
{
    const A4_SHORT_EDGE  = 794;
    const A4_LONG_EDGE   = 1040;//1380;//1122;

    protected $cellWidth  = 0;
    protected $cellHeight = 0;

    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    	parent::init();
    }

    public function indexAction()
    {
        $this->html->equipments = $this->getEquipments();
    }

    public function showAction()
    {
        $equipments  = $this->params->requests->getParam('equipments', array()); // Array thiet bi can in
        $numOfRow    = (int)$this->params->requests->getParam('numOfRow', 5); // So hang in ra
        $numOfColumn = (int)$this->params->requests->getParam('numOfColumn', 2);
        $exclueCell  = (int)$this->params->requests->getParam('exclueCell', 0);
        $excluePage  = (int)$this->params->requests->getParam('excluePage', 0);
        $direction   = (int)$this->params->requests->getParam('direction', 0);
        $border      = (int)$this->params->requests->getParam('border', 0);
        $padding     = (int)$this->params->requests->getParam('padding', 0);
        $margin      = (int)$this->params->requests->getParam('margin', 0);
        $numOfRow    = $numOfRow?$numOfRow:1;
        $numOfColumn = $numOfColumn?$numOfColumn:1;

        $this->getSizeOfCell($direction, $numOfRow, $numOfColumn, $margin, $padding, $border);


        $this->html->equipments  = $this->getEquipmentDetails($equipments);
        $this->html->direction   = $direction;
        $this->html->numOfRow    = $numOfRow;
        $this->html->numOfColumn = $numOfColumn;
        $this->html->border      = $border;
        $this->html->padding     = $padding;
        $this->html->margin      = $margin;
        $this->html->a4width     = $direction?self::A4_LONG_EDGE:self::A4_SHORT_EDGE;
        $this->html->a4height    = $direction?self::A4_SHORT_EDGE:self::A4_LONG_EDGE;
        $this->html->cellWidth   = $this->cellWidth;
        $this->html->cellHeight  = $this->cellHeight;
    }

    /**
     * Lấy chieu rong chieu cao cua mot o thiet bi, da tru het padding margin border
     * @param $direction
     * @param $totalCell
     * @param $numOfRow
     * @param $numOfColumn
     */
    private function getSizeOfCell($direction, $numOfRow, $numOfColumn, $margin, $padding, $border)
    {
        $A4ShortEdge = self::A4_SHORT_EDGE;
        $A4LongEdge  = self::A4_LONG_EDGE;

        if($direction == 1)// chieu ngang
        {
            $cellWidth  = floor($A4LongEdge/$numOfColumn);
            $cellHeight = floor($A4ShortEdge/$numOfRow);
        }
        else // chieu doc
        {
            $cellWidth  = floor($A4ShortEdge/$numOfColumn);
            $cellHeight = floor($A4LongEdge/$numOfRow);
        }

        $this->cellWidth  = $cellWidth  - 2*$margin - 2*$padding - 2*$border;
        $this->cellHeight = $cellHeight - 2*$margin - 2*$padding - 2*$border;
    }

    /**
     * Lấy dữ liệu cho Diallog Thiết bị
     * @return array
     */
    private function getEquipments()
    {
        $common     = new Qss_Model_Extra_Extra();
        $retval     = array();
        $equipments = $common->getTable(array('*'), 'ODanhSachThietBi', array(), array('MaThietBi'), 'NO_LIMIT');
        $i          = 0;

        foreach($equipments as $dat)
        {
            $retval[0]['Dat'][$i]['ID']      = $dat->IOID;
            $retval[0]['Dat'][$i]['Display'] = "{$dat->MaThietBi} - {$dat->TenThietBi}";
            $i++;
        }

        return $retval;
    }

    private function getEquipmentDetails($equipments)
    {
        $equipments[] = 0;
        $common       = new Qss_Model_Extra_Extra();
        $where        = sprintf(' IOID IN (%1$s) ', implode(' , ', $equipments));
        $orderBy      = '';


        foreach ($equipments as $index=>$equipIOID)
        {
            $orderBy .= sprintf(' WHEN IOID = %1$d THEN %2$d ', $equipIOID, $index);
        }
        $orderBy      = $orderBy?" CASE {$orderBy} END ":'';

        return $common->getTable(array('*'), 'ODanhSachThietBi', $where, $orderBy, 'NO_LIMIT');
    }


}

?>