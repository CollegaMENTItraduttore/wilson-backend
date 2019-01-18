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
         * Metodo per recupere tutte le richieste di appuntamento
         * questo lato traduttore 
         *
         * @return
         */
        function list() {
            $data = null;        
            try {

                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                select 
                    message.id as id, 
                    resident.cod_utente as codUtente, 
                    message.sent_on as sentOn, 
                    message.message as message, 
                    concat(familiare.last_name, ' ', familiare.first_name) as nominativoRelative, 
                    concat(resident.last_name, ' ', resident.first_name) as nominativoResident, 
                    team.nominativo as nominativoOperatore,
                    team.figura_professionale as figProf,
                    team.id_teanapers as idTeAnaPers
                FROM sent_message message 
                    inner join relative familiare on message.id_relative=familiare.id 
                    inner join resident resident on familiare.id_resident=resident.id 
                    inner join care_team team on message.id_care_team=team.id;
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
        /**
         * esegue l'update sulle richieste appuntamento
         *
         * @param [type] $array_object
         * @return void
         */
        function update($array_object) {
            $array_object = (!is_array($array_object) ? array($array_object) : $array_object); 
            $data = [];    
            $conn = null;
    
            try {

                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare(
                    'update sent_message  sm
                        set sm.shared_on = ?
                        where sm.id = ? 
                    ');
                //inserimento sequential 
                foreach ($array_object as $record) {
    
                    $stmt->bindValue(1, $record->sharedOn, PDO::PARAM_STR);
                    $stmt->bindValue(2, $record->id, PDO::PARAM_INT);
                    $stmt->execute();
                }         
    
            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            } 
            return $data;
        }
        
    }
?>