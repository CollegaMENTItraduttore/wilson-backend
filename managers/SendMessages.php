<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    
    class SendMessages extends WilsonBaseClass {
        function __construct($db) {   
            parent::__construct($db);        
        }
        
        function launch( $params, $data ) {
       
        }
        /**
         *  Metodo per recupere tutte le richieste di appuntamento
         * questo lato traduttore 
         */
        function list() {
            $data = null;        
            try {

                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                select 
                    m.id as id, 
                    m.id_relative as idRelative, 
                    m.sent_on as sentOn, 
                    m.message as message, 
                    concat(f.last_name, f.first_name) as nominativoRelative, 
                    concat(r.last_name, r.first_name) as nominativoResident, 
                    t.nominativo as nominativoOperatore,
                    t.figura_professionale as figProf,
                    t.id_teanapers as idTeAnaPers
                FROM sent_message m 
                    inner join relative f on m.id_relative=f.id 
                    inner join resident r on f.id_resident=r.id 
                    inner join care_team t on m.id_care_team=t.id;
                ");
                $stmt->execute();
                $data = $stmt ->fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
        /**
         *  Esegue l'inserimento per il messaggio relativo alla prenotazione
         *  @param object
         */
        function contactCareTeam($object) {
            $data = null;        
            try {

                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                    INSERT INTO sent_message (id_relative, sent_on, id_care_team, message)
                    VALUES (:id_relative, :sent_on, :id_care_team, :message)
                ");

                $stmt->bindValue(":id_relative", $object->id_relative, PDO::PARAM_INT);
                $stmt->bindValue(":sent_on", date("Y-m-d H:i:s"), PDO::PARAM_STR);
                $stmt->bindValue(":id_care_team", $object->id_care_team, PDO::PARAM_INT);
                $stmt->bindValue(":message", $object->message, PDO::PARAM_STR);

                $stmt->execute();
                //recupero l'id
                $data = $conn->lastInsertId();

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
        
    }
?>