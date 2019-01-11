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