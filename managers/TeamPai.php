<?php 

require_once('../classes/std/WilsonBaseClass.php');
require_once('Resident.php');

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
        if (empty($object->figuraProfessionale)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "figura_professionale"));
            return false;
        }
        if (empty($object->idTeAnaPers)) {
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
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('
                select  p.id, 
                        p.nominativo, 
                        p.figura_professionale, 
                        p.is_family_navigator, 
                        p.id_teanapers,
                        p.id_resident
                from care_team p
                where p.id_resident = ?
                order by p.is_family_navigator desc, p.nominativo'
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
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('
                    select  p.id, 
                            p.nominativo, 
                            p.figura_professionale, 
                            p.is_family_navigator, 
                            p.id_teanapers,
                            p.id_resident
                    from care_team p
                    where p.id = ?'
            );
            $stmt->execute(array($id));
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }

    function getHashMapResident() {
        $mpaResident = new stdClass(); 
        $managerResident = new Resident($this->getDb(), null);
        $listUtenti = $managerResident->getList();
            //hasmap per la lista dei residenti
            foreach ($listUtenti as $ospite) {
            $mpaResident->{$ospite['cod_utente']} = $ospite['id'];
        }
        return $mpaResident;
    }
    /**
     * Inserimento operatore di tipo "Staff"
     */
    function new($array_object) {

        $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
        $data = [];   

        $conn = null;
        try {    
            $conn = $this->connectToDatabase();
            $conn->beginTransaction();
            //svuoto la tabella 
            $this->deleteAll();
            
            
            $stmt = $conn->prepare('insert into care_team 
                                    (
                                        nominativo, 
                                        figura_professionale,
                                        is_family_navigator,
                                        id_teanapers,
                                        id_resident
                                    ) 
                                    values(?, ?, ?, ?, ?) ');
            
            $mpaResident = $this->getHashMapResident();
           
            //inserimento sequential 
            foreach ($array_object as $record) {

                $msg = array();
                $status = $this->checkCampiObbligatori($record, $msg);
                //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                if ( !$status && count($msg) > 0 ) {
                    throw new Exception(implode("", $msg));
                }
                $idResident = $mpaResident->{$record->idResident};
                $stmt->bindValue(1, $record->nominativo, PDO::PARAM_STR);
                $stmt->bindValue(2, $record->figuraProfessionale, PDO::PARAM_STR);
                $stmt->bindValue(3, $record->isFamilyNavigator, PDO::PARAM_INT);
                $stmt->bindValue(4, $record->idTeAnaPers, PDO::PARAM_INT);   
                $stmt->bindValue(5, $idResident, PDO::PARAM_INT);   

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
     * Update operatore di tipo "Staff"
     */
    function update($object) {
    }
    /**
     * Cancellazione dell'operatore, in base all'id passato
     */
    function deleteAll() {
        //campo id obbligatorio 
        $data = [];    
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('delete from care_team');            
            $stmt->execute();

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;
    }
    function shared($id_resident = null) {

        $data = [];    
        if (!isset($id_resident) || empty($id_resident)) {
            throw new Exception(sprintf(Costanti::INVALID_FIELD, "id_resident")); 
        }
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('
                select  p.id as id, 
                        p.nominativo as nominativo, 
                        p.figura_professionale as figuraProfessionale, 
                        p.is_family_navigator as isFamilyNavigator, 
                        p.id_teanapers as idTeAnaPers,
                        p.id_resident as idResident
                from care_team p
                inner join resident res
                on (p.id_resident = res.id)
                where res.cod_utente = ?'
            );
            $stmt->execute(array($id_resident));
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;

    }

}

?> 