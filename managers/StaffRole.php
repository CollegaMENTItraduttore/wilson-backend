<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');

    class StaffRole extends WilsonBaseClass {
        function __construct($db) {   
            parent::__construct($db);        
        }
        
        function launch( $params, $data ) {
       
        }
        /**
         * Undocumented function
         *
         * @param [type] $object
         * @param array $msg
         * @return void
         */
        function checkCampiObbligatori($object, &$msg = array()) {
            return true;
        }
        /**
         * Undocumented function
         *
         * @param [type] $array_object
         * @return void
         */
        function new($array_object) {

            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    
            $conn = null;
    
            try {
                $conn = $this->connectToDatabase();
                $conn->beginTransaction();
                $stmt = $conn->prepare('
                        insert into staff_role 
                            (
                                id,
                                name
                            ) 
                            values(?, ?)
                            ON DUPLICATE KEY UPDATE
                            name = values(name)
                             
                        ');
                //inserimento sequential 
                foreach ($array_object as $record) {
    
                    $msg = array();
                    $status = $this->checkCampiObbligatori($record, $msg);
                    //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                    if ( !$status && count($msg) > 0 ) {
                        throw new Exception(implode("", $msg));
                    }
                    $stmt->bindValue(1, $record->idFigProfSipcar, PDO::PARAM_INT);
                    $stmt->bindValue(2, $record->name, PDO::PARAM_STR);

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