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
        function getHealthStatusMedicine($idResident, $dateStart, $dateEnd, $type) {
            //medicine
            $txt1 = "Elvira nel mese di aprile ha subito gli strascichi di un'influenza invernale, sentendosi un pò stanca e avendo alcuni episodi di febbre, Dopo la cura ricostituente ora Elvira è stabile e ha ripreso energia e appetito dal punto di vista comportamentale Elvira ha alternato momenti di confusione che sono normali per l'Alzheimer, a momenti di lucidità in cui riusciva a esprimersi e a fare richieste
            ";
            $txt2 = "Elvira sta continuando a prendere regolarmente la sua terapia farmacologica, che è costituita dalle pillole per il diabete, dalle medicine per la pressione alta e dalla terapia per l'Alzheimer che la sta aiutando a recuperare le funzioni cognitive inoltre durante l'ultimo mese Elvira ha fatto una cura ricostituente prendendo vitamine e minerali che la aiutano a riprendersi da uno stato influenzale
            ";
            $numMonth = $dateStart -> format('n');
            $data = null;
           
            if ($type == 'M') {
                if ($numMonth % 2 == 0)
                    $data = $txt1;
                else 
                    $data = $txt2;
            } else if ($type == 'S') {
                if ($numMonth % 2 == 0)
                    $data = $txt2;
                else 
                    $data = $txt1;
            } 
            return [Array('text' => $data)];
        }
    }
?>