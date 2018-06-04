<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    require_once('../utils/DateUtils.php');
    
    class PrimaryNeeds extends WilsonBaseClass {
        function __construct() {
            parent::__construct();        
            
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
                        et.name as event_name,
                        pn.created_on,
                        ec.name as category,
                        eep.name as nameExtraParam,
                        eep.value_text as valueString,
                        eep.value_num as valueNum

                    FROM primary_need pn
                    INNER JOIN event_type et
                        ON et.id = pn.id_type
                    INNER JOIN event_category ec
                        ON ec.id = et.id_category
                    INNER JOIN event_extra_param eep 
                        ON pn.id = eep.id_primary_need
                    WHERE pn.id = ?
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
        
        function save( $data ) {
        }
    }
?>