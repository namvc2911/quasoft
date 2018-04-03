<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_System extends Qss_Model_Abstract
{
	protected static $_rights = array();
	public function __construct ()
	{
		parent::__construct();

	}
	public function getFormRights($formcode,$groupList)
	{
		$rights = 0;
		if(isset(self::$_rights[$formcode][$groupList]))
		{
			return self::$_rights[$formcode][$groupList];
		}
		$sql = sprintf('select qsforms.Type,bit_or(Rights) as Rights from qsforms
                                        inner join qsuserforms on qsforms.FormCode=qsuserforms.FormCode and qsuserforms.GroupID in(%1$s)
                                        where qsforms.FormCode=%2$s group by qsforms.Type', $groupList, $this->_o_DB->quote($formcode));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			/* merge first 4 bit with other*/
			$rights =  $dataSQL->Rights;
		}
		self::$_rights[$formcode][$groupList] = $rights;
		return $rights;
	}
	public function getFormsByObject($objid)
	{
		$sql = sprintf('select qsforms.*,qsfobjects.Public from qsforms
                              inner join qsfobjects on qsfobjects.FormCode = qsforms.FormCode
                              where Effected = 1 and qsfobjects.ObjectCode = "%1$s"', $objid);
		return $this->_o_DB->fetchOne($sql);
	} 
	/*public function getVID($fieldID, $IOID)
	{
	    $sql = sprintf('
	        SELECT qsrecobjects.RecordID
	        FROM qsrecobjects
	        INNER JOIN qsfields ON qsrecobjects.FieldCode = qsfields.FieldCode
	        WHERE qsrecobjects.FieldCode = "%1$s"
	        and qsrecobjects.IOID = %2$d
        ', $fieldID, $IOID);
	    $dataSql = $this->_o_DB->fetchOne($sql);	    
	    return $dataSql?$dataSql->RecordID:0;
	}*/
    
    public function getFieldByCode($objectCode, $fieldCode)
    {
        $sql = sprintf('
            select qsfields.* 
            from qsfields
            inner join qsobjects on qsfields.ObjectCode = qsobjects.ObjectCode
            where qsobjects.ObjectCode = %1$s
            AND qsfields.FieldCode = %2$s'
        , $this->_o_DB->quote($objectCode)
        , $this->_o_DB->quote($fieldCode));
        return $this->_o_DB->fetchOne($sql);
    }
	public function getFormObject($formCode, $objectCode)
    {
        $sql = sprintf('
            select qsfobjects.* 
            from qsfobjects
            where qsfobjects.ObjectCode = %1$s
            AND qsfobjects.FormCode = %2$s'
        , $this->_o_DB->quote($objectCode)
        , $this->_o_DB->quote($formCode));
        return $this->_o_DB->fetchOne($sql);
    }
  	/// *Function: getStepsByForm - lay tat ca step cua mot form
  	public function getStepsByForm($formCode, $lang = 'vn')
	{
		$status_name_lang = ($lang == 'vn')?'':'_'.$lang;
		$sql = sprintf('SELECT ws.* 
						, ws.StepNo AS StepNo 
						, ws.`Name%2$s` AS `Name`
						FROM qsforms as f
						inner join qsworkflows as w on f.FormCode = w.FormCode
						inner join qsworkflowsteps as ws on ws.WFID = w.WFID
						where f.FormCode = %1$s
						and ifnull(w.Actived, 0) = 1
						order by ws.OrderNo,StepNo, SID'
				, $this->_o_DB->quote($formCode)
				, $status_name_lang);
		return $this->_o_DB->fetchAll($sql);
	}

	public function getAllApproveByUser($uid)
    {
        $status_name_lang = ($this->_user->user_lang == 'vn')?'':'_'.$this->_user->user_lang;
        $sql = sprintf('
            SELECT qsstepapprover.*, qsworkflows.FormCode, qsforms.Name%2$s AS FormName
            FROM qsstepapprover 
            INNER JOIN qsstepapproverrights ON qsstepapprover.SAID = qsstepapproverrights.SAID	            
			INNER JOIN qsworkflowsteps ON qsworkflowsteps.SID = qsstepapprover.SID	
            INNER JOIN qsworkflows  ON qsworkflowsteps.WFID = qsworkflows.WFID      
            INNER JOIN qsforms ON qsworkflows.FormCode = qsforms.FormCode
            WHERE qsstepapproverrights.UID = %1$d AND IFNULL(qsforms.Effected, 0) = 1
            ORDER BY qsworkflows.FormCode, qsworkflowsteps.StepNo, qsstepapprover.OrderNo'
            , $uid, $status_name_lang);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getApprovalBySAID($said)
    {
        return $this->_o_DB->fetchOne(sprintf('
            select qsstepapprover.*, qsfobjects.FormCode, qsfobjects.ObjectCode AS MainObjectCode
            from qsstepapprover
            INNER JOIN qsstepapproverrights ON qsstepapprover.SAID = qsstepapproverrights.SAID
            INNER JOIN qsworkflowsteps ON qsworkflowsteps.SID = qsstepapprover.SID
            INNER JOIN qsworkflows  ON qsworkflowsteps.WFID = qsworkflows.WFID
            INNER JOIN qsfobjects ON qsworkflows.FormCode = qsfobjects.FormCode AND qsfobjects.Main = 1
            INNER JOIN qsforms ON qsfobjects.FormCode = qsforms.FormCode
            where qsstepapprover.SAID = %1$d AND IFNULL(qsforms.Effected, 0) = 1
            LIMIT 1
        ', $said));
    }

    public function getApproveListBySAID($said, $page = 0, $display = 0, $onlyNotApproveYet = false)
    {
        $approve = $this->getApprovalBySAID($said);

        if(!$approve)
        {
            return array();
        }

        $limit = '';
        $where = '';

        if($page && $display)
        {
            $startLimit = ceil(($page - 1) * $display);
            $limit      = ($startLimit>=0)?" limit {$startLimit}, {$display}":'';
        }

        $status_name_lang = ($this->_user->user_lang == 'vn')?'':'_'.$this->_user->user_lang;

        if($onlyNotApproveYet)
        {
            $where = 'WHERE IFNULL(qsstepapproverrights.SAID, 0) <> 0';
        }

        $sql = sprintf('
            SELECT 
                obj.*
                , wfsteps.`Name%5$s` AS `StepName`
                , wfsteps.Color
                , iForm.IFID
                , iForm.FormCode
                , "%1$s" AS ObjectCode
                , IFNULL(qsstepapproverrights.SAID, 0) AS NeedApprove
                , IFNULL(stepAppr.SAID, 0) AS SAID
                , ifnull(qsfapprover.ADate, "") AS ADate 
                , ifnull(qsfapprover.RDate, "") AS RDate
                , stepAppr.Name AS ApproveName
                , IF(wfsteps.StepType = 1, (IF(
                    (
                        select count(1) 
                        from qsfapprover AS fAppr
                        INNER JOIN qsstepapproverrights AS stApprRig ON fAppr.SAID = stApprRig.SAID AND fAppr.UID = stApprRig.UID
                        INNER JOIN qsstepapprover AS stAppr ON stApprRig.SAID = stAppr.SAID
                        where fAppr.IFID = iForm.IFID AND stAppr.OrderNo < stepAppr.OrderNo 
                            AND (IFNULL(fAppr.ADate, "") = "" OR IFNULL(fAppr.RDate, "") != "")
                    ) > 0 
                    , 1
                    , 0)
                
                ), 0) AS ChuaDuyetDuyetTruoc
            FROM %1$s AS obj
            INNER JOIN qsiforms AS iForm ON iForm.IFID = obj.IFID_%2$s
            INNER JOIN qsworkflows  ON qsworkflows.FormCode = iForm.FormCode
			INNER JOIN qsworkflowsteps as wfsteps ON wfsteps.WFID = qsworkflows.WFID 
			    AND iForm.Status = wfsteps.StepNo            
            LEFT JOIN qsstepapprover AS stepAppr ON wfsteps.SID = stepAppr.SID AND stepAppr.SAID = %4$d
            LEFT JOIN qsstepapproverrights ON stepAppr.SAID = qsstepapproverrights.SAID	
                AND qsstepapproverrights.UID = %3$d 
            LEFT JOIN qsfapprover ON iForm.IFID = qsfapprover.IFID
                AND stepAppr.SAID = qsfapprover.SAID
                AND qsstepapproverrights.UID = qsfapprover.UID
            %7$s
            GROUP BY obj.IFID_%2$s
            ORDER BY obj.IFID_%2$s DESC
            %6$s
        ', $approve->MainObjectCode, $approve->FormCode, $this->_user->user_id, $said, $status_name_lang, $limit, $where);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }


    public function countApproveListBySAID($said, $onlyNotApproveYet = false)
    {
        $approve = $this->getApprovalBySAID($said);
        $where   = '';

        if(!$approve)
        {
            return 0;
        }

        if($onlyNotApproveYet)
        {
            $where = 'WHERE IFNULL(qsstepapproverrights.SAID, 0) <> 0';
        }


        $sql = sprintf('
            SELECT count(1) AS Total
            FROM
            (
                SELECT 1
                FROM %1$s AS obj
                INNER JOIN qsiforms ON qsiforms.IFID = obj.IFID_%2$s
                INNER JOIN qsworkflows  ON qsworkflows.FormCode = qsiforms.FormCode
                INNER JOIN qsworkflowsteps ON qsworkflowsteps.WFID = qsworkflows.WFID 
                    AND qsiforms.Status = qsworkflowsteps.StepNo		
                LEFT JOIN qsstepapprover  ON qsworkflowsteps.SID = qsstepapprover.SID AND qsstepapprover.SAID = %4$d
                LEFT JOIN qsstepapproverrights ON qsstepapprover.SAID = qsstepapproverrights.SAID	
                    AND qsstepapproverrights.UID = %5$d          
                %6$s
                GROUP BY obj.IFID_%2$s
            ) AS table_a              
        ', $approve->MainObjectCode, $approve->FormCode, $this->_user->user_id, $said, $this->_user->user_id, $where);

        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->Total:0;
    }

    public function countPendingApprovalsBySAID($said)
    {
        $approve = $this->getApprovalBySAID($said);

        if(!$approve)
        {
            return 0;
        }

        $sql = sprintf('
            SELECT 
                count(1) AS Total
            FROM %1$s AS obj
            INNER JOIN qsiforms ON qsiforms.IFID = obj.IFID_%2$s
            INNER JOIN qsworkflows  ON qsworkflows.FormCode = qsiforms.FormCode
			INNER JOIN qsworkflowsteps ON qsworkflowsteps.WFID = qsworkflows.WFID 
			    AND qsiforms.Status = qsworkflowsteps.StepNo			  
            INNER JOIN qsstepapprover ON qsworkflowsteps.SID = qsstepapprover.SID
            INNER JOIN qsstepapproverrights ON qsstepapprover.SAID = qsstepapproverrights.SAID	
                AND qsstepapproverrights.UID = %3$d
            WHERE qsstepapprover.SAID = %4$d  AND IFNULL(qsworkflowsteps.StepType, 0) != 0          
        ', $approve->MainObjectCode, $approve->FormCode, $this->_user->user_id, $said);

        //echo '<pre>'; print_r($sql); die;
        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->Total:0;
    }

    public function getUserByIFID($ifid)
    {
        $sql = sprintf('
            SELECT qsusers.*
            FROM qsusers
            INNER JOIN qsiforms ON qsiforms.UID = qsusers.UID
            WHERE qsiforms.IFID = %1$d
        ', $ifid);

        return $this->_o_DB->fetchOne($sql);
    }
}
?>