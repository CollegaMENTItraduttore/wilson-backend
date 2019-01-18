<?php 

require_once('../classes/std/WilsonBaseClass.php');
require_once('../utils/Costanti.php');

class Staff extends WilsonBaseClass  {
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
     * @return boolean
     */
    function checkCampiObbligatori($object, &$msg = array()) {
        return true;
    }

    function list() {

        $data = [];    
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('
                select s.id, 
                       s.first_name as firstName, 
                       s.last_name as lastName, 
                       s.username as username, 
                       s.picture as picture, 
                       s.mail as mail, 
                       s.id_role as idRole, 
                       s.id_rsa as idRsa,
                       s.id_teanapers as idTeAnaPers
                from staff s
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
     *
     * @param [type] $id
     * @return void
     */
    function get($id = null) {
        
        if (!isset($id)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id")); 
        }

        $data = [];    
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('
                select s.id, 
                       s.first_name as firstName, 
                       s.last_name as lastName, 
                       s.username, 
                       s.picture, 
                       s.mail, 
                       s.id_role as idRole, 
                       s.id_rsa as idRsa
                from staff s
                where s.id =?'
            );
            $stmt->bindValue(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            if (count($data) > 0)
                $data = $data[0];

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
    /**
     * Inserimento operatori di tipo "Staff"
     *
     * @param [type] $array_object
     * @return void
     */
    function new($array_object) {
        
        $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
        $msg = array();
        $data = [];    
        $conn = null;
        try {
            
            $conn = $this->connectToDatabase();
            $conn->beginTransaction();
            /**
             *  per l'inserimento di operatori di tipo staff è necessario 
             *  collegarli all'rsa, partendo, dal db 
             */
            $id_rsa = $this->getIdRsaByDb();
            
            if (empty($id_rsa)) {
                //blocco il flusso
                throw new Exception(sprintf(Costanti::INVALID_FIELD, "rsa"));
            }
            $stmt = $conn->prepare('
                insert into staff 
                    (
                        first_name, 
                        last_name, 
                        mail, 
                        id_role, 
                        id_rsa, 
                        username,
                        id_teanapers
                    ) 
                    values(?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        first_name = values(first_name),
                        last_name = values(last_name),
                        mail = values(mail),
                        id_role = values(id_role),
                        id_rsa = values(id_rsa),
                        username = values(username)
                    '
                );

            foreach ($array_object as $record) {
                
                $msg =  array();
                $status = $this->checkCampiObbligatori($record, $msg);
                //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                if ( !$status && count($msg) > 0 ) {
                    throw new Exception(implode("", $msg));
                }
                $stmt->bindValue(1, $record->firstName, PDO::PARAM_STR);
                $stmt->bindValue(2, $record->lastName, PDO::PARAM_STR);
                $stmt->bindValue(3, $record->email, PDO::PARAM_STR);
                $stmt->bindValue(4, $record->idRole, PDO::PARAM_STR);
                $stmt->bindValue(5, $id_rsa, PDO::PARAM_INT);
                $stmt->bindValue(6, $record->username, PDO::PARAM_STR);
                $stmt->bindValue(7, $record->idTeAnaPers, PDO::PARAM_INT);

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
    /**
     * update operatore di tipo staff 
     *
     * @param [type] $object
     * @return void
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
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('update staff s 
                                    set s.first_name =?, 
                                        s.last_name =?, 
                                        s.mail =?,
                                        s.username =?
                                    where s.id = ?');

            $stmt->bindValue(1, $object->firstName, PDO::PARAM_STR);
            $stmt->bindValue(2, $object->lastName, PDO::PARAM_STR);
            $stmt->bindValue(3, $object->email, PDO::PARAM_STR);
            $stmt->bindValue(4, $object->username, PDO::PARAM_STR);
            $stmt->bindValue(5, $object->id, PDO::PARAM_INT);
            $stmt->execute();

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
    /**
     * Eliminazione del record
     *
     * @param [type] $id
     * @return void
     */
    function delete($id = null) {
        //campo id obbligatorio 
        if (empty($id)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id"));
        }
        $data = [];    
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('delete from staff where id = ?');            
            $stmt->execute([$id]);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
}

?> 

