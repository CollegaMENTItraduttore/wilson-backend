<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    require_once('../utils/DateUtils.php');

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
            $conn = $this->connectToDatabase();
            try {
                
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
            $conn = $this->connectToDatabase();
            try {
                
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
            $conn = $this->connectToDatabase();
            try {
                
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
                        concat(staff.first_name, ' ', staff.last_name) as organized_by_name

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
           
            $conn = $this->connectToDatabase();
            try {
                
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
    }
?>