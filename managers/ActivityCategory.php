<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');

    class ActivityCategory extends WilsonBaseClass {
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
        function new($array_object) {

            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    
            $conn = $this->connectToDatabase();
    
            try {
                $conn->beginTransaction();
                $stmt = $conn->prepare('insert into activity_category 
                                        (
                                            name, 
                                            description
                                        ) 
                                        values(?, ?) ');
                //inserimento sequential 
                foreach ($array_object as $record) {
    
                    $msg = array();
                    $status = $this->checkCampiObbligatori($record, $msg);
                    //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                    if ( !$status && count($msg) > 0 ) {
                        throw new Exception(implode("", $msg));
                    }
                    $stmt->bindValue(1, $record->name, PDO::PARAM_STR);
                    $stmt->bindValue(2, $record->description, PDO::PARAM_STR);

                    $stmt->execute();
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