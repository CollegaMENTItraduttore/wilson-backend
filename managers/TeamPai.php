<?php 

require_once('../classes/std/WilsonBaseClass.php');

class TeamPai extends WilsonBaseClass  {
    function __construct($db) {   
        parent::__construct($db);        
    }

    function launch( $params, $data ) {
        
    }
    /**
     *  Campi obbligatori durante update or insert 
     */
    function checkCampiObbligatori($object, &$msg = array()) {

       if (empty($object->nominativo)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "nominativo"));
            return false;
        }
        if (empty($object->figura_professionale)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "figura_professionale"));
            return false;
        }
        if (empty($object->is_family_navigator)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "is_family_navigator"));
            return false;
        }
        if (empty($object->id_teanapers)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "id_teanapers"));
            return false;
        }
        return true;
    }
    /**
     *  Ritorna una lista di team di cura filtrata per id_resident
     *  
     */
    function list($id_resident = null) {

        $data = [];    
        if (!isset($id_resident) || empty($id_resident)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id_resident")); 
        }
        $conn = $this->connectToDatabase();
        try {
            $stmt = $conn->prepare('
                select  p.id, 
                        p.nominativo, 
                        p.figura_professionale, 
                        p.is_family_navigator, 
                        p.id_teanapers,
                        p.id_resident
                from team_cura p
                where p.id_resident = ?'
            );
            $stmt->execute(array($id_resident));
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;

    }
    /**
     * Metodo che recupera il singolo componente del team del pai
     */
    function get($id = null) {
        
        if (!isset($id) || empty($id)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id")); 
        }

        $data = [];    
        $conn = $this->connectToDatabase();
        try {
            $stmt = $conn->prepare('
                    select  p.id, 
                            p.nominativo, 
                            p.figura_professionale, 
                            p.is_family_navigator, 
                            p.id_teanapers,
                            p.id_resident
                    from team_cura p
                    where p.id = ?'
            );
            $stmt->execute(array($id));
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
    /**
     * Inserimento operatore di tipo "Staff"
     */
    function new($array_object) {

        $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
        $data = [];    
        $conn = $this->connectToDatabase();

        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare('insert into team_cura 
                                    (
                                        nominativo, 
                                        figura_professionale,
                                        is_family_navigator,
                                        id_teanapers,
                                        id_resident
                                    ) 
                                    values(?, ?, ?, ?, ?) ');
            //inserimento sequential 
            foreach ($array_object as $record) {

                $msg = array();
                $status = $this->checkCampiObbligatori($record, $msg);
                //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                if ( !$status && count($msg) > 0 ) {
                    throw new Exception(implode("", $msg));
                }
                $stmt->bindValue(1, $record->nominativo, PDO::PARAM_STR);
                $stmt->bindValue(2, $record->figura_professionale, PDO::PARAM_STR);
                $stmt->bindValue(3, $record->is_family_navigator, PDO::PARAM_INT);
                $stmt->bindValue(4, $record->id_teanapers, PDO::PARAM_INT);   
                $stmt->bindValue(5, $record->id_resident, PDO::PARAM_INT);   

                $stmt->execute();
            }         
            $conn->commit();

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            $conn->rollback();
        } 
        return $data;
    }
    /**
     * Update operatore di tipo "Staff"
     */
    function update($object) {
    }
    /**
     * Cancellazione dell'operatore, in base all'id passato
     */
    function delete($id = null) {
    }
}

?> 