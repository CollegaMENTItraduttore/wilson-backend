<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    require_once('../utils/DateUtils.php');
    require_once('EventExtraParam.php');
    
    class PrimaryNeeds extends WilsonBaseClass {
        function __construct($db) {   
            parent::__construct($db);        
        }
        
        function launch( $params, $data ) {
        }

        function getById($idPrimaryNeed) {

            $data = [];
            if (!isset($idPrimaryNeed)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idPrimaryNeed'));
            }

            try {

                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                    SELECT 
                        id,
                        name,
                        description,
                        value_text,
                        value_num,
                        created_by
                    FROM event_extra_param eep 
                    WHERE eep.id_primary_need = ?
                ");
                $stmt->bindValue(1, $idPrimaryNeed, PDO::PARAM_INT);
                $stmt->execute();
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;

        }
        
        function getListByFilters($idResident, $dateStart, $dateEnd) {
            $data = [];
            if (!isset($idResident)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idResident'));
            }
            if (!isset($dateStart)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'dateStart'));
            }
            if (!isset($dateEnd)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'dateEnd'));
            }
            $conn = $this->connectToDatabase();
            try {
                
                $stmt = $conn->prepare("
                    select 
                        pn.id_resident,
                        pn.id_type,
                        pn.id as id_primary_need,
                        et.id_category,
                        et.name as event_name,
                        pn.created_on,
                        ec.name as category

                    FROM primary_need pn
                    INNER JOIN event_type et
                        ON et.id = pn.id_type
                    INNER JOIN event_category ec
                        ON ec.id = et.id_category
                    WHERE pn.id_resident = ? AND pn.created_on >= ? AND pn.created_on <= ?
                    ORDER BY pn.created_on ASC
                ");

                $stmt->bindValue(1, $idResident, PDO::PARAM_INT);
                $stmt->bindValue(2, $dateStart->format('Y-m-d H:i:s'), PDO::PARAM_STR);
                $stmt->bindValue(3, $dateEnd->format('Y-m-d H:i:s'), PDO::PARAM_STR);
                $stmt->execute();
               
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
        
        function getList() {           
        }

        function checkCampiObbligatori($record, $msg) {
            return true;
        }
        
        function new( $array_object ) {

            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    

            $conn = $this->connectToDatabase();
                        
            try {
                $conn->beginTransaction();
                $stmt = $conn->prepare('insert into primary_need 
                    (
                        created_on, 
                        id_resident,
                        id_type,
                        id_primary_need_sipcar
                   ) 
                    values(?, ?, ?, ?) ');

                $managerEventExtraParam =  new EventExtraParam(parent::getDb(), $conn);
               
                foreach ($array_object as $record) {
    
                    $msg = array();
                    $status = $this->checkCampiObbligatori($record, $msg);
                    //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                    if ( !$status && count($msg) > 0 ) {
                        throw new Exception(implode("", $msg));
                    }

                    $stmt->bindValue(1, $record->createdOn, PDO::PARAM_STR);
                    $stmt->bindValue(2, $record->idResident, PDO::PARAM_INT);
                    $stmt->bindValue(3, $record->idType, PDO::PARAM_INT);    
                    $stmt->bindValue(4, $record->idRecordSipcar, PDO::PARAM_INT);    
    
                    $stmt->execute();

                    //capire se a questo punto ho l'id appena inserito
                    $newId = $conn->lastInsertId();

                    if (!empty($newId)) {

                        //compisizione tabella event extra param
                        $eventExtraParam = (object) array(
                            'idPrimaryNeed' => $newId,
                            'valueNum' => $record->valueNum,
                            'valueText' => $record->valueText,
                            'name' => $record->name,
                            'createdBy' => $record->createdBy
                        );
                        $managerEventExtraParam -> new($eventExtraParam);
                    }
                }         
                $conn->commit();
   
            } catch (Exception $e) {
               
                $conn->rollback();
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            } 
            return $data;

        }
    }
?>