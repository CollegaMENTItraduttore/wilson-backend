<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');

    class EventExtraParam extends WilsonBaseClass {
        function __construct($db, $conn) {   
            parent::__construct($db, $conn);        
        }

        function launch( $params, $data ) {
       
        }
        /**
         * Controllo campi obbligatori
         *
         * @param [type] $object
         * @param array $msg
         * @return void
         */
        function checkCampiObbligatori($object, &$msg = array()) {
            return true;
        }
        /**
         * Inserimento informazioni extra relativi al tipo di evento 
         *
         * @param [type] $object
         * @return
         */
        function new($object) {

            $msg = array();
            $result = $this->checkCampiObbligatori($object, $msg);
           
            if ( !$result && count($msg) > 0 ) {
                throw new Exception(implode("", $msg));
            }
            $data = [];    
            

            try {
                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare(
                    'insert into event_extra_param 
                        (
                            name, 
                            value_text,
                            value_num,
                            created_by,
                            id_primary_need
                        ) 
                        values(?, ?, ? ,?, ?)');
    
                $stmt->bindValue(1, $object->name, PDO::PARAM_STR);
                $stmt->bindValue(2, $object->valueText, PDO::PARAM_STR);
                $stmt->bindValue(3, $object->valueNum, PDO::PARAM_INT);
                $stmt->bindValue(4, $object->createdBy, PDO::PARAM_INT);
                $stmt->bindValue(5, $object->idPrimaryNeed, PDO::PARAM_INT);                
                $stmt->execute();
    
            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
    }
?>