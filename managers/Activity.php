<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    require_once('../utils/DateUtils.php');

    require_once('ActivityInfo.php');
    require_once('ActivityEdition.php');
    require_once('Resident.php');
    require_once('Staff.php');

    class Activity extends WilsonBaseClass {
        function __construct($db) {   
            parent::__construct($db);        
        }
        
        function launch( $params, $data ) {
       
        }
        /**
         *  Metodo che ritorna la lista ordinate per id Categoria
         * 
         *  @param idResident
         */
        function getPlannedList($idResident) {
            $data = [];
            if (!isset($idResident)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idResident'));
            }
            
            try {
                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                    SELECT  
                        a.id as id_activity,
                        a.id_resident,
                        ac.id as id_category,
                        ai.name,
                        ac.name as category
                    FROM activity a
                    INNER JOIN activity_info ai
                        ON a.id_activity_info = ai.id
                    INNER JOIN activity_category ac
                        ON ai.id_activity_category = ac.id
                    where a.id_resident = ?
                    GROUP BY ai.name 
                    ORDER BY ac.name ASC
                ");
                
                $stmt->bindValue(1, $idResident, PDO::PARAM_INT);
                $stmt->execute();
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        } 
        /**
         *  Metodo che ritorna la lista ordinate per data inizio "ASC" delle attivita di un 
         *  determinato residente
         * 
         *  @param idResident
         *  @param dateStart
         *  @param dateEnd 
         */
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
                    SELECT 
                        ae.id as id_activity_edition,
                        a.id as id_activity,
                        a.id_resident,
                        ai.name,
                        ae.start_date, 
                        ae.end_date 
                    FROM activity_edition ae
                    INNER JOIN activity a
                        on ae.id_activity = a.id
                    INNER JOIN activity_info ai
                        ON a.id_activity_info = ai.id
                    WHERE a.id_resident = ? AND ae.end_date >= ? AND ae.start_date <= ?
                    ORDER BY ae.start_date ASC"
                );

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
        /**
         *  Metodo che ritorna la singola attività della tabella
         *  "Activity_Edition"
         * 
         *  @param id_activity_edition
         */
        function getById($idActivityEdition) {
            
            if (!isset($idActivityEdition)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idActivityEdition'));
            }
            
            try {
                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                    SELECT 
                        ae.id as id_activity_edition,
                        a.id as id_activity,
                        ai.id_activity_category as id_activity_category,
                        a.id_resident,
                        ai.name,
                        ae.start_date, 
                        ae.end_date ,
                        ai.description,
                        a.location,
                        a.repeats_every,
                        a.repeats_on,
                        ac.name as category,
                        a.organized_by,
                        concat(staff.first_name, ' ', staff.last_name) as organized_by_name,
                        ae.comment

                    FROM activity_edition ae
                    INNER JOIN activity a
                        on a.id = ae.id_activity
                    INNER JOIN activity_info ai
                        ON a.id_activity_info = ai.id
                    INNER JOIN activity_category ac
                        ON ai.id_activity_category = ac.id
                    INNER JOIN staff staff
                        on a.organized_by = staff.id
                    WHERE ae.id = ? 
                ");

                $stmt->bindValue(1, $idActivityEdition, PDO::PARAM_INT);
                $stmt->execute();

                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

                foreach ($data as &$value) {
                    $value['when_repeats'] = DateUtils::whenRepeats($value['repeats_every'], $value['repeats_on']);
                }

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }

        /**
         *  Metodo che ritorna la pianificazione attività
         *  dalla lista generica per utente
         * 
         *  @param id_activity
         */
        function getPlannedById($idActivity) {
            
            $data = [];

            if (!isset($idActivity)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idActivity'));
            }
           
            
            try {
                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                    SELECT
                        a.id as id_activity,
                        a.id_resident,
                        ai.name,
                        ai.description,
                        a.location,
                        a.repeats_every,
                        a.repeats_on,
                        ac.name as category,
                        a.organized_by,
                        concat(staff.first_name, ' ', staff.last_name) as organized_by_name

                    FROM activity a
                    INNER JOIN activity_info ai
                        ON a.id_activity_info = ai.id
                    INNER JOIN activity_category ac
                        ON ai.id_activity_category = ac.id
                    INNER JOIN staff staff
                        on a.organized_by = staff.id
                    WHERE a.id = ? 
                ");

                $stmt->bindValue(1, $idActivity, PDO::PARAM_INT);
                $stmt->execute();

                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

                foreach ($data as &$value) {
                    $value['when_repeats'] = DateUtils::whenRepeats($value['repeats_every'], $value['repeats_on']);
                }

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
        /**
         * HasMap che recupera tutta la lista degli utenti, 
         * inserisce nel campo 
         *      codice: id riferimento cartella
         *      valore: id tabella collegamenti
         *
         * @return
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
         * HasMap che recupera tutta la lista degli delle anagrafiche attività, 
         * inserisce nel campo 
         *    codice: id riferimento cartella
         *    valore: id tabella collegamenti
         *
         * @return
         */
        function getHashMapActivity() {

            $mapActivityInfo = new stdClass();

            $managerActivityInfo = new ActivityInfo($this->getDb(), null);
            $listActivityInfo = $managerActivityInfo->list(); 

            foreach ($listActivityInfo as $attivita) {
                $mapActivityInfo->{$attivita['id_activity_sipcar']} = $attivita['id'];
            }
            return $mapActivityInfo;
        }
        /**
         * HasMap che recupera tutta la lista degli staff, 
         * inserisce nel campo 
         *      codice: id riferimento cartella (TEANAPERS)
         *      valore: id tabella collegamenti
         *
         * @return
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
        /**
         * Controllo campi obbligatori in fase di insert or update
         *
         * @param [type] $object
         * @param array $msg
         * @return
         */
        function checkCampiObbligatori($object, &$msg = array()) {
            return true;
        }
        /**
         * Inserimento attività e relativa tabella di dettaglio (singoli eventi)
         *
         * @param [type] $array_object
         * @return
         */
        function new($array_object) {

            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    

           $conn = null;
            
            try {
                $conn = $this->connectToDatabase();
                $conn->beginTransaction();
                $stmt = $conn->prepare(
                    'insert into activity 
                        (
                            created_on, 
                            duration,
                            id_activity_info,
                            repeats_every,
                            repeats_on,
                            location,
                            can_volunteer,
                            can_partecipate,
                            can_request_appointment,
                            organized_by,
                            id_resident,
                            id_plan_sipcar
                        ) 
                        values (?, ?, ?, ?, ?, ? ,? ,?, ?, ?, ?, ?) ');

                $managerActivityEdition =  new ActivityEdition($this->getDb(), $conn);

                $mpaResident = $this->getHashMapResident();
                $mapActivity = $this->getHashMapActivity();
                $mapStaff = $this->getHashMapStaff();

                foreach ($array_object as $record) {
    
                    $msg = array();
                    $status = $this->checkCampiObbligatori($record, $msg);
                    //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                    if ( !$status && count($msg) > 0 ) {
                        throw new Exception(implode("", $msg));
                    }

                    //$idStaff = $mapCompilatori->{$record->oragnizedBy};
                    $idResident = $mpaResident->{$record->idResident};
                    $idActivityInfo = $mapActivity->{$record->idActivityInfo};

                    $idStaff = null;
                    if (property_exists($mapStaff, $record->organizedBy)) {
                        $idStaff = $mapStaff->{$record->organizedBy};
                    }                   
                    if (empty($idResident)) {
                        throw new Exception("nessun idResident trovato");
                    }

                    if (empty($idActivityInfo)) {
                        throw new Exception("nessun idActivityInfo trovato");
                    }
                    
                    $stmt->bindValue(1, $record->createdOn, PDO::PARAM_STR);
                    $stmt->bindValue(2, $record->duration, PDO::PARAM_INT);
                    $stmt->bindValue(3, $idActivityInfo, PDO::PARAM_INT);
                    $stmt->bindValue(4, $record->repeatsEvery, PDO::PARAM_STR);   
                    $stmt->bindValue(5, $record->repeatsOn, PDO::PARAM_STR);
                    $stmt->bindValue(6, $record->location, PDO::PARAM_STR);
                    $stmt->bindValue(7, $record->canVolunteer, PDO::PARAM_INT);
                    $stmt->bindValue(8, $record->canPartecipate, PDO::PARAM_INT);
                    $stmt->bindValue(9, $record->canRequestAppointment, PDO::PARAM_INT);
                    $stmt->bindValue(10, $idStaff, PDO::PARAM_INT);
                    $stmt->bindValue(11, $idResident, PDO::PARAM_INT);
                    $stmt->bindValue(12, $record->idPlanSipcar, PDO::PARAM_INT);
    
                    $stmt->execute();

                    //capire se a questo punto ho l'id appena inserito
                    $newId = $conn->lastInsertId();

                    if (!empty($newId)) {

                        $activityEdition = $record->activityEdition;
                        if (count($activityEdition) > 0) {
                            //inserisco nella tabella di activity edition
                            foreach ($activityEdition as $edition) {
                                $edition->id_activity = $newId; 
                                $managerActivityEdition->new($edition);
                            } 
                        }
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
         *
         * @param [type] $idResident
         * @return 
         */
        function shared($idResident) {

            $data = [];
            

            if (empty($idResident)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idResident'));
            }

            try {
                $conn = $this->connectToDatabase();
                //todo da controllare
                $stmt = $conn->prepare("
                    select 
                        att.id_plan_sipcar as idPlanSipcar, 
                        max(att_edition.start_date) as lastDate 
                    from activity att 
                    inner join activity_edition att_edition 
                        on (att_edition.id_activity = att.id)
                    inner join resident resident
                        on (resident.id = att.id_resident)
                    where resident.cod_utente = ?
                    group by att.id_plan_sipcar
                ");                
                $stmt->execute([$idResident]);
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
    }
?>