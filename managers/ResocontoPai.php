<?php 

require_once('../classes/std/WilsonBaseClass.php');
require_once('../utils/Costanti.php');
require_once('../utils/DateUtils.php');

class ResocontoPai extends WilsonBaseClass  {
    function __construct($db) {   
        parent::__construct($db);        
    }

    function launch( $params, $data ) {
        
    }
    /**
     *  Campi obbligatori durante update or insert 
     */
    function checkCampiObbligatori($object, &$msg = array()) {
        if (empty($object->note)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "note"));
            return false;
        }
        if (empty($object->idResident)) {
            array_push($msg, sprintf(Costanti::INVALID_FIELD, "id_resident"));
            return false;
        }
        return true;
    }
    /**
     *  Ritorna una lista di resoconto_pai, ordinata per "created_on" relativi all'utente selezionato
     *  
     */
    function list($id_resident = null, $date = null) {

        $data = [];    
        $date = (isset($date) && !empty($date) ? $date : new DateTime());
        //init date
        $data_dal =  DateUtils::getStartOfDay($date);
        $data_al =  DateUtils::getEndOfDay($date);

        $conn = $this->connectToDatabase();
        try {

            $condition = [];
            $sqlParam = [];
            $sql = "
                select p.id, 
                        p.created_on, 
                        p.created_by, 
                        p.note, 
                        p.id_resident
                from pai_resoconto p
            ";

            if (!empty($id_resident)) {
                array_push($condition, "id_resident = ? ");
                array_push($sqlParam, $id_resident);
            }

            if (!empty($date)) {
                array_push($condition,"p.created_on >= ? AND p.created_on <=? ");
                array_push($sqlParam, $data_dal->format('Y-m-d H:i:s'));
                array_push($sqlParam, $data_al->format('Y-m-d H:i:s'));
            }
            
            if (count($condition) > 0) {
                $sql.= " where ".implode($condition, " and ");
            }
            $sql.= " order by p.created_on";
            $stmt = $conn->prepare($sql); 
            $stmt->execute($sqlParam);
            $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
        }
        return $data;

    }
    /**
     * Metodo che recupera il singolo resoconto pai
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
                            p.created_on, 
                            p.created_by, 
                            p.note, 
                            p.id_resident
                    from pai_resoconto p
                    where p.id = ?'
            );
            $stmt->execute(array($id));
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
    function new($array_object) {

        $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
        $data = [];    
        $conn = $this->connectToDatabase();

        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare('insert into pai_resoconto 
                                    (
                                        created_by, 
                                        created_on,
                                        id_resident,
                                        note
                                    ) 
                                    values(?, ?, ?, ?) ');
            //inserimento sequential 
            foreach ($array_object as $record) {

                $msg = array();
                $status = $this->checkCampiObbligatori($record, $msg);
                //se l'inserimento non va a buon fine interrompo il ciclo di tutto ed esco
                if ( !$status && count($msg) > 0 ) {
                    throw new Exception(implode("", $msg));
                }

                $stmt->bindValue(1, null, PDO::PARAM_STR);
                $stmt->bindValue(2, $record->data, PDO::PARAM_STR);
                $stmt->bindValue(3, $record->idResident, PDO::PARAM_INT);
                $stmt->bindValue(4, $record->note, PDO::PARAM_STR);   
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
    function delete($id = null) {
    }
}

?> 