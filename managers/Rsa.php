<?php 

require_once('../classes/std/WilsonBaseClass.php');
require_once('../utils/Costanti.php');

class Rsa extends WilsonBaseClass  {
    function __construct($db) {   
        parent::__construct($db);        
    }

    function launch( $params, $data ) {
        
    }
    /**
     *  Campi obbligatori durante update or insert 
     */
    function checkCampiObbligatori($object, &$msg = array()) {
        return true;
    }
    /**
     * Inserimento rsa
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
            $stmt = $conn->prepare('
                insert into rsa (
                    name, 
                    description, 
                    id_dm7
                ) values(?, ?, ?)');

            $stmt->bindValue(1, $object->name, PDO::PARAM_STR);
            $stmt->bindValue(2, $object->description, PDO::PARAM_STR);
            $stmt->bindValue(3, $object->idDm7, PDO::PARAM_INT);

            $stmt->execute();

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
}

?> 