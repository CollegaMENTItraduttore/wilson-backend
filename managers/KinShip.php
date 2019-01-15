<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');

    class Kinship extends WilsonBaseClass {
        function __construct($db) {   
            parent::__construct($db);        
        }
        
        function launch( $params, $data ) {
       
        }
        /**
         * Metodo per recuperare tutti i gradi di parentela 
         */
        function list() {
            $data = [];    
        
            try {
                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare('
                    select 
                        k.id,
                        k.description
                    from kinship k'
                );
                $stmt->execute();
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data; 
        }
         
        /**
         * 
         */
        function checkCampiObbligatori($object, &$msg = array()) {
            return true;
        }
        /**
         * Metodo per inserimento gradi di parentela
         */
        function new($array_object) {

            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    
            $conn = null;
    
            try {
                $conn = $this->connectToDatabase();
                $conn->beginTransaction();
                $stmt = $conn->prepare('
                        insert into kinship 
                            (
                                id,
                                description
                            ) 
                            values(?, ?)
                        ');
                //inserimento sequential 
                foreach ($array_object as $record) {
    
                    $msg = array();
                    $status = $this->checkCampiObbligatori($record, $msg);
                    //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                    if ( !$status && count($msg) > 0 ) {
                        throw new Exception(implode("", $msg));
                    }
                    $stmt->bindValue(1, $record->id, PDO::PARAM_INT);
                    $stmt->bindValue(2, $record->description, PDO::PARAM_STR);

                    $stmt->execute();
                }         
                $conn->commit();
    
            } catch (Exception $e) {
                if(!empty($conn))
                    $conn->rollback();
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            } 
            return $data;
        }
    }
?>