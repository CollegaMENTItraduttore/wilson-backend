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
     * Campi obbligatori durante update or insert 
     *
     * @param [type] $object
     * @param array $msg
     * @return void
     */
    function checkCampiObbligatori($object, &$msg = array()) {
        return true;
    }
    /**
     * Ritorna una lista di team di cura filtrata per id_resident
     *
     * @param [type] $id_resident
     * @return
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
     *
     * @param [type] $id
     * @return void
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
    /**
     * Hasmap, codice = cod_utente valore = id (tabella resident db collegamenti)
     *
     * @return
     */
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
     * Metodo che recupera tutti i compiltori presenti nella tabella care_team
     * e ne ritorna una lista di id
     * @param idResident
     */
    function getIdCompilatori($idResident) {
        $data = $this->list($idResident);
        $array = [];
        if (count($data) > 0) {
            foreach($data as $compilatore) {
                array_push($array, $compilatore['id_teanapers']);
            }
        }
        return $array;
    }
    /**
     * Inserimento, team pai
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
            //svuoto la tabella 
           
            
            
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
            $idResident = null;
            $listIdCompilatori = [];
            //inserimento sequential 
            foreach ($array_object as $record) {

                $msg = array();
                $status = $this->checkCampiObbligatori($record, $msg);
                //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                if ( !$status && count($msg) > 0 ) {
                    throw new Exception(implode("", $msg));
                }
                if (empty($idResident)) {
                    //questo pezzo di codice lo eseguo solo una volta
                    $idResident = $mpaResident->{$record->idResident};
                    /**
                     * elimino tutti i compilatori del pai, esclusi quelli presenti 
                     * nella tabella sent_message relativi a quell'idResident
                     */
                    $listIdCompilatori = $this->compilatoriNotDeleted($idResident);
                }
                //se compilatore incluso nella mia lista non lo inserisco
                if ( (count($listIdCompilatori) > 0) && (in_array($record->idTeAnaPers, $listIdCompilatori)) ) {
                    continue;
                }
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
     * Metodo che prevede la cancellazione dei record all'interno della tabella 
     * care_team per idResident, se e solo se non è stato inviato un messaggio
     *
     * @param [type] $idResident
     * @return void
     */
    function compilatoriNotDeleted($idResident) {
        //campo id obbligatorio 
        $data = [];
        $array = [];    
        
        try {
            $conn = $this->connectToDatabase();
            $stmt = $conn->prepare('
                delete team from care_team team
                left join sent_message sent
                    on (sent.id_care_team = team.id)
                where sent.id_care_team is  null and 
                      team.id_resident = ?
            ');            
            $stmt->execute([$idResident]);

            //richiamo la list mi riscarico i compilatori avanzati dalla delete
            $data = $this->list($idResident);

            if (count($data) > 0) {
                foreach($data as $compilatore) {
                    array_push($array, $compilatore['id_teanapers']);
                }
            }
        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        var_dump($array);
        return $array;
    }
    /**
     * Ritorna al ws traduttore la lista dei compilatori pai già compilati
     *
     * @param [type] $id_resident
     * @return void
     */
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