<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    require_once('../utils/DateUtils.php');
    require_once('EventExtraParam.php');
    require_once('Resident.php');
    require_once('Staff.php');
    
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
            
            try {
                $conn = $this->connectToDatabase();
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
        /**
         *  HasMap che recupera tutta la lista degli utenti, 
         *  inserisce nel campo 
         *      codice: id riferimento cartella
         *      valore: id tabella collegamenti 
         */
        function getHashMapResident() {

            $mpaResident = new stdClass();
                
            $managerResident = new Resident($this->getDb(), null);
            $listUtenti = $managerResident->getList();
             //hasmap per la lista dei residenti
             foreach ($listUtenti as $ospite) {
                $mpaResident->{$ospite['cod_utente']} = $ospite['id'];
            }
            return $mpaResident;
        }
        /**
         *  HasMap che recupera tutta la lista degli staff, 
         *  inserisce nel campo 
         *      codice: id riferimento cartella (TEANAPERS)
         *      valore: id tabella collegamenti 
         */
        function getHashMapStaff() {

            $mapStaff = new stdClass();

            $managerStaff = new Staff($this->getDb());
            $listStaff = $managerStaff->list();

            foreach ($listStaff as $staff) {
                $mapStaff->{$staff['idTeAnaPers']} = $staff['id'];
            }
            return $mapStaff;
        }
        
        function new( $array_object ) {

            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    

            $conn = null;
                        
            try {
                $conn = $this->connectToDatabase();
                $conn->beginTransaction();
                $stmt = $conn->prepare('insert into primary_need 
                    (
                        created_on, 
                        id_resident,
                        id_type,
                        id_primary_need_sipcar
                   ) 
                    values(?, ?, ?, ?) ');

                $mpaResident = $this->getHashMapResident();
                $mapStaff = $this->getHashMapStaff();
                $managerEventExtraParam =  new EventExtraParam(parent::getDb(), $conn);
               
                foreach ($array_object as $record) {
    
                    $msg = array();
                    $status = $this->checkCampiObbligatori($record, $msg);
                    //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                    if ( !$status && count($msg) > 0 ) {
                        throw new Exception(implode("", $msg));
                    }

                    $idResident = $mpaResident->{$record->idResident};
                    $idStaff = $mapstaff->{$record->createdBy};

                    $stmt->bindValue(1, $record->createdOn, PDO::PARAM_STR);
                    $stmt->bindValue(2, $idResident, PDO::PARAM_INT);
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
                            'createdBy' => $idStaff
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
        /**
         * Metodo utilizzato, dal tradutottore per sapere quali attivita 
         * sono state condivise
         */
        function shared($idResident, $listType) {

            $data = [];
            

            if (empty($idResident)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idResident'));
            }
            $param = [];
            $sql = '
                select  
                     p.id_primary_need_sipcar as idRecordSipcar, 
                     p.id_type as idType
                from primary_need p 
                where p.id_resident = ?
            ';
            try {
                $conn = $this->connectToDatabase();
                //todo da controllare
                array_push($param, $idResident);

                if(isset($listType) && !empty($listType)) {
                    $sql .=' and p.id_type in ('.$listType.')'; 
                }
                $stmt = $conn->prepare($sql); 
 
                $stmt->execute($param);
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
    }
?>