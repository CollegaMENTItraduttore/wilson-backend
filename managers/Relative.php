<?php 

require_once('../classes/std/WilsonBaseClass.php');
require_once('../utils/Costanti.php');

class Relative extends WilsonBaseClass  {
    function __construct($db) {   
        parent::__construct($db);        
    }

    function launch( $params, $data ) {
        
    }
    /**
     *  Campi obbligatori durante update or insert 
     */
    function checkCampiObbligatori($object, &$msg = array()) {

        $first_name = isset($object->firstName) ? $object->firstName : null;
        $last_name = isset($object->lastName) ? $object->lastName : null;
        $email = isset($object->email) ? $object->email : null;

        $cod_utente = isset($object->codUtente) ? $object->codUtente : null;
        $username = isset($object->username) ? $object->username : null;

        //check campi obbligatori
        if (empty($first_name)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "first_name"));
            return false;
        }
        if (empty($last_name)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "last_name"));
            return false;
        }       
        if (empty($email)) {
            array_push($msg,sprintf(Costanti::INVALID_FIELD, "email"));
            return false;
        }
        if (empty($username)) {
            array_push($msg,sprintf(Costanti::INVALID_FIELD, "username"));
            return false;
        }
        if (empty($cod_utente)) {
            array_push($msg,sprintf(Costanti::INVALID_FIELD, "cod_utente"));
            return false;
        }
        return true;
    }

    function list() {

        $data = [];    
        $conn = $this->connectToDatabase();
        try {
            $stmt = $conn->prepare('
                select s.id, 
                       s.first_name, 
                       s.last_name, 
                       s.username, 
                       s.picture, 
                       s.mail, 
                       s.id_role, 
                       s.id_rsa
                from relative s
                order by s.last_name'
            );
            $stmt->execute();
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;

    }
    /**
     * Metodo che recupera l'operatore passato a parametro
     */
    function get($id = null) {
        
        if (!isset($id)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id")); 
        }

        $data = [];    
        $conn = $this->connectToDatabase();
        try {
            $stmt = $conn->prepare('
                select s.id, s.first_name, 
                       s.last_name, 
                       s.username,
                       s.picture, 
                       s.mail, 
                       s.id_role, 
                       s.id_rsa
                from relative s
                where s.id =?'
            );
            $stmt->bindValue(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
    /**
     * Inserimento operatore di tipo "Staff"
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

            $stmt = $conn->prepare('insert into relative (first_name, last_name, mail, username, id_resident, id_kinship) values(?, ?, ?, ?, ?, ?)');

            $stmt->bindValue(1, $object->firstName, PDO::PARAM_STR);
            $stmt->bindValue(2, $object->lastName, PDO::PARAM_STR);
            $stmt->bindValue(3, $object->email, PDO::PARAM_STR);
            $stmt->bindValue(4, $object->username, PDO::PARAM_STR);
            $stmt->bindValue(5, $object->codUtente, PDO::PARAM_INT);
            $stmt->bindValue(6, $object->gradoParentela, PDO::PARAM_INT);

            $stmt->execute();

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
    /**
     * Update operatore di tipo "Staff"
     */
    function update($object) {
        $msg = array();
        $result = $this -> checkCampiObbligatori($object, $msg);

        if ( !$result && count($msg) > 0 ) {
            throw new Exception(implode("", $msg));
        }

        //campo id obbligatorio 
        if (!isset($object->id) || empty($object->id)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id"));
        }

        $data = [];    
        $conn = $this->connectToDatabase();
        try {
            $stmt = $conn->prepare('update relative s 
                                    SET s.first_name =?, 
                                        s.last_name =?,  
                                        s.mail =?, 
                                        s.username = ?, 
                                        s.id_resident = ?, 
                                        s.id_kinship = ?
                                    where s.id = ?');

             $stmt->bindValue(1, $object->firstName, PDO::PARAM_STR);
             $stmt->bindValue(2, $object->lastName, PDO::PARAM_STR);
             $stmt->bindValue(3, $object->email, PDO::PARAM_STR);
             $stmt->bindValue(4, $object->username, PDO::PARAM_STR);
             $stmt->bindValue(5, $object->codUtente, PDO::PARAM_INT);
             $stmt->bindValue(6, $object->gradoParentela, PDO::PARAM_INT);
             $stmt->bindValue(7, $object->id, PDO::PARAM_STR);

             $stmt->execute();

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
    /**
     * Cancellazione dell'operatore, in base all'id passato
     */
    function delete($id = null) {
        //campo id obbligatorio 
        if (empty($id)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id"));
        }
        $data = [];    
        $conn = $this->connectToDatabase();
        try {
            $stmt = $conn->prepare('delete from relative where id = ?');            
            $stmt->execute([$id]);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
}

?> 