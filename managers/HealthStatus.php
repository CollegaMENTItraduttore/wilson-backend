<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    require_once('../utils/DateUtils.php');
    
    class HealthStatus extends WilsonBaseClass {
        function __construct() {
            parent::__construct();        
            
        }
        
        function launch( $params, $data ) {
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
                        hs.id_resident,
                        hs.id_type,
                        hs.id as id_health_status,
                        et.name as event_name,
                        hs.created_on,
                        eep.value_text

                    FROM health_status hs
                    INNER JOIN event_type et
                        ON et.id = hs.id_type
                    INNER JOIN event_extra_param eep
                        ON eep.id_health_status = hs.id
                    WHERE hs.id_resident = ? AND hs.created_on >= ? AND hs.created_on <= ?
                    ORDER BY hs.created_on ASC
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
    }
?>