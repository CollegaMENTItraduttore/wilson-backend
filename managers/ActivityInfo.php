<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');

    class ActivityInfo extends WilsonBaseClass {
        private $session;
        function __construct($db) {   
            parent::__construct($db);        
           
        }
        
        function launch( $params, $data ) {
       
        }

        function list() {
            $data = [];    
            
            try {
                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare('
                    select s.id, 
                           s.name, 
                           s.description, 
                           s.benefits, 
                           s.id_activity_category,
                           s.id_activity_sipcar
                    from activity_info s'
                );
                $stmt->execute();
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);
    
            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }

        function checkCampiObbligatori($object, &$msg = array()) {
            return true;
        }

        function new($array_object) {

            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    
            $conn = null;
            
            try {
                $conn = $this->connectToDatabase();
                $conn->beginTransaction();
                $stmt = $conn->prepare('
                    insert into activity_info 
                            (
                                name,
                                id_activity_category,
                                id_activity_sipcar
                            ) 
                            values(:name, :id_activity_category, :id_activity_sipcar) ON DUPLICATE KEY UPDATE
                                name = values(name), 
                                id_activity_category = values(id_activity_category)
                    ');
                //inserimento sequential 
                foreach ($array_object as $record) {
    
                    $msg = array();
                    $status = $this->checkCampiObbligatori($record, $msg);
                    //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                    if ( !$status && count($msg) > 0 ) {
                        throw new Exception(implode("", $msg));
                    }
                    $stmt->bindValue(":name", $record->name, PDO::PARAM_STR);
                    $stmt->bindValue(":id_activity_category", $record->idActivityCategory, PDO::PARAM_INT);   
                    $stmt->bindValue(":id_activity_sipcar", $record->idActivitySipcar, PDO::PARAM_INT);   
    
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