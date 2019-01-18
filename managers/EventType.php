<?php 

require_once('../classes/std/WilsonBaseClass.php');
require_once('../utils/Costanti.php');

class EventType extends WilsonBaseClass  {
    function __construct($db) {
        parent::__construct($db);        
    }

    function launch( $params, $data ) {
        
    }
    /**
     * Controllo campi obbligatori
     *
     * @param [type] $object
     * @param array $msg
     * @return 
     */
    function checkCampiObbligatori($object, &$msg = array()) {
        return true;
    }
    /**
     * Inserimento tipo evento
     *
     * @param [type] $array_object
     * @return
     */
    function new($array_object) {
        
        $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
        $data = [];    
        $conn = null;

        try {
            $conn = $this->connectToDatabase();
            $conn->beginTransaction();
            $stmt = $conn->prepare(
                'insert into event_type 
                    (
                        id,
                        name, 
                        id_category
                    ) 
                    values(?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        name = values(name),
                        id_category = values(id_category)
                    ');
            //inserimento sequential 
            foreach ($array_object as $record) {

                $msg = array();
                $status = $this->checkCampiObbligatori($record, $msg);
                //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                if ( !$status && count($msg) > 0 ) {
                    throw new Exception(implode("", $msg));
                }
                $stmt->bindValue(1, $record->id, PDO::PARAM_STR);
                $stmt->bindValue(2, $record->name, PDO::PARAM_STR);
                $stmt->bindValue(3, $record->idCategory, PDO::PARAM_STR);
                $stmt->execute();
            }         
            $conn->commit();

        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        } 
        return $data;
    }
    /**
     * Eliminazione tipo evento
     *
     * @param [type] $id
     * @return 
     */
    function delete($id = null) {
        //campo id obbligatorio 
        if (empty($id)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id"));
        }
        $data = [];    
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('delete from event_type where id = ?');            
            $stmt->execute([$id]);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
}

?> 