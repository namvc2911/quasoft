<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Duplicate extends Qss_Model_Abstract
{
    // Duplicate all
    protected $formCode    = '';
    protected $ifid        = 0;
    protected $excludeObjs = array();
    protected $objects     = array();
    protected $custom      = array();

    // Duplicate line (require IFID)
    protected $ioid        = 0;
    protected $objectCode  = '';

    // Return
    protected $error       = false;
    protected $newIOID     = 0;
    protected $newIFID     = 0;



    public function init($ifid, $formCode, $excludeObjs = array(), $custom = array())
    {
        $this->ifid        = $ifid;
        $this->formCode    = $formCode;
        $this->excludeObjs = $excludeObjs;
        $this->objects     = $this->getObjects();
        $this->custom      = $custom;
    }

    public function initDuplicateLine($ifid, $formCode, $ioid, $objectCode, $custom = array())
    {
        $this->ifid       = $ifid;
        $this->formCode   = $formCode;
        $this->ioid       = $ioid;
        $this->objectCode = $objectCode;
        $this->custom     = $custom;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getNewIFID()
    {
        return $this->newIFID;
    }

    public function getNewIOID()
    {
        return $this->newIOID;
    }

    protected function getObjects()
    {
        $extWhere = '';

        if($this->excludeObjs && count($this->excludeObjs))
        {
            $extWhere = sprintf(' AND ObjectCode not in (%1$s) ', implode(', ', $this->excludeObjs));
        }

        $sql = sprintf('
            SELECT * 
            FROM qsfobjects
            WHERE FormCode = %1$s %2$s
            ORDER BY `Main` DESC, ObjNo', $this->_o_DB->quote($this->formCode), $extWhere);
        return $this->_o_DB->fetchAll($sql);
    }

    protected function getViewData($objectCode)
    {
        $extWhere = '';

        if($this->ioid)
        {
            $extWhere .= sprintf(' AND IOID = %1$d', $this->ioid);
        }

        $sqlCheckNestedSet = sprintf('
            SELECT * 
            FROM information_schema.COLUMNS 
            WHERE TABLE_NAME = "%1$s" AND COLUMN_NAME = "lft"
        ', $objectCode);
        $datCheckNestedSet = $this->_o_DB->fetchOne($sqlCheckNestedSet);



        if($datCheckNestedSet)
        {
            $sql = sprintf('
            SELECT * 
            FROM %1$s
            WHERE IFID_%2$s = %3$d %4$s
            ORDER BY lft', $objectCode, $this->formCode, $this->ifid, $extWhere);
        }
        else
        {
            $sql = sprintf('
            SELECT * 
            FROM %1$s
            WHERE IFID_%2$s = %3$d %4$s
            ORDER BY IOID', $objectCode, $this->formCode, $this->ifid, $extWhere);
        }

        return $this->_o_DB->fetchAll($sql);
    }

    protected function getFields($objectCode)
    {
        $ret = array();
        $sql = sprintf('
            SELECT * FROM qsfields WHERE ObjectCode = "%1$s" AND IFNULL(Effect, 0) = 1 ORDER BY FieldNo
        ', $objectCode);

        $dat = $this->_o_DB->fetchAll($sql);

        foreach ($dat as $item)
        {
            $ret[$item->FieldCode] = $item;
        }

        return $ret;
    }

    /**
     * Duplicate all
     */
    public function duplicate()
    {
        $insert  = array();
        $import  = new Qss_Model_Import_Form($this->formCode,false, false);
        $newIFID = 0;

        foreach ($this->objects as $obj)
        {
            $fields = $this->getFields($obj->ObjectCode);
            $dat    = $this->getViewData($obj->ObjectCode);

            $i = 0;

            foreach ($dat as $items)
            {
                foreach ($items as $fieldCode=>$val)
                {
                    if(!isset($this->custom[$obj->ObjectCode][$fieldCode]))
                    {
                        if(isset($fields[$fieldCode]) && !is_null($val))
                        {
                            $insert[$obj->ObjectCode][$i][$fieldCode] = $val;
                        }
                    }
                    else
                    {
                        if(isset($fields[$fieldCode]))
                        {
                            $insert[$obj->ObjectCode][$i][$fieldCode] = $this->custom[$obj->ObjectCode][$fieldCode];
                        }
                    }
                }

                $i++;
            }
        }

        if(count($insert))
        {
            $import->setData($insert);
            $import->generateSQL();

            $this->error   = $import->countFormError() + $import->countObjectError();

            if(!$this->error)
            {
                $importedRow = $import->getIFIDs();

                foreach ($importedRow as $item) {
                    $newIFID = $item->oldIFID;
                }

                $this->newIFID = $newIFID;
            }
        }
    }

    /**
     * Duplicate one line
     */
    public function duplicateLine()
    {
        $insert = array();
        $fields = $this->getFields($this->objectCode);
        $dat    = $this->getViewData($this->objectCode);
        $import = new Qss_Model_Import_Form($this->formCode,false, false);

        foreach ($dat as $items)
        {
            foreach ($items as $fieldCode=>$val)
            {
                if(!isset($this->custom[$this->objectCode][$fieldCode]))
                {
                    if(isset($fields[$fieldCode]) && $val)
                    {
                        $insert[$this->objectCode][0][$fieldCode] = $val;
                    }
                }
                else
                {
                    if(isset($fields[$fieldCode]))
                    {
                        $insert[$this->objectCode][0][$fieldCode] = $this->custom[$this->objectCode][$fieldCode];
                    }
                }
            }
        }

        if(count($insert))
        {
            $insert[$this->objectCode][0]['ifid'] = $this->ifid;

            $import->setData($insert);
            $import->generateSQL();

            $this->error   = $import->countFormError() + $import->countObjectError();
            $this->newIFID = $this->ifid;
        }
    }
}