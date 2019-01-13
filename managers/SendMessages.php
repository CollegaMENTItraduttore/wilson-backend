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
                        s.id, 
                        s.id_relative, 
                        s.sent_on,
                        s.id_care_team, 
                        s.message,
                        c.id_teanapers
                    from sent_message s
                    inner join care_team c
                    on (c.id = s.id_care_team)

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