<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');

    class ActivityEdition extends WilsonBaseClass {
        function __construct($db) {   
            parent::__construct($db);        
           
        }
        
        function launch( $params, $data ) {
       
        }
        /**
         * 
         */
        function checkCampiObbligatori($object, &$msg = array()) {
            return true;
        }
        /**
         * 
         */
        function new($object) {

            $msg = array();
            $result = $this->checkCampiObbligatori($object, $msg);
            
            if ( !$result && count($msg) > 0 ) {
                throw new Exception(implode("", $msg));
            }
            $data = [];    
            $conn = $this->connectToDatabase();
            try {
            
                $stmt = $conn->prepare('insert into activity_edition (
                                                    id_activity, 
                                                    start_date, 
                                                    end_date
                                        ) 
                                        values(?, ?, ?)');
    
                $stmt->bindValue(1, $object->id_activity, PDO::PARAM_INT);
                $stmt->bindValue(2, $object->startDate, PDO::PARAM_STR);
                $stmt->bindValue(3, $object->endDate, PDO::PARAM_STR);
                $stmt->execute();
    
            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
    }
?>