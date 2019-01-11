<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    
    class Resident extends WilsonBaseClass {
        function __construct($db) {   
            parent::__construct($db);        
        }
        
        function launch( $params, $data ) {
       
        }
        
        function getList($listResidents = '') {
            
            $data = [];
            
            try {
                $conn = $this->connectToDatabase();

                $query = "  select r.id, r.first_name, r.last_name, r.gender,
                                    CONCAT( r.first_name,\" \",r.last_name) as nominative,
                                    r.picture, r.cod_utente
                            from resident r ";
                
                if (isset($listResidents) && $listResidents !== '') {
                    $query .= ' where r.id in ( ' . $listResidents . ' )';
                }

                $query .=   ' ORDER BY nominative';

                $stmt = $conn->prepare($query);
                $stmt->execute();
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(Costanti::OPERATION_KO, $e->getMessage());
            }
            return $data;
        }
        /**
         *  Metodo che ritorna la lista dei residenti 
         *  per demo 22/GIUGNO listone senza filtri 
         *  
         */
        function getById($idResident) {

            if (!isset($idResident)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, 'idResident'));
            }
            
            $data = [];
            
            
            
            try {
                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                    SELECT r.id, 
                           r.first_name, 
                           r.last_name, 
                           r.gender,
                           r.picture, 
                           r.birthday, 
                           r.birthplace,
                           r.biography,
                           r.habits,
                           r.extra_info
                    FROM resident r
                    WHERE r.id = ?"                  
                );
                $stmt -> bindValue(1, $idResident, PDO::PARAM_INT);

                $stmt->execute();
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
        /**
         *  Esegue l'update dei campi relativi all'ospite
         *  @param object
         */
        function update($object) {
            $data = null;        
            try {

                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                    UPDATE resident r 
                        SET r.biography=:BIOGRAPHY, r.habits=:HABITS, r.extra_info=:EXTRA_INFO 
                    WHERE r.id = :IDRESIDENT"
                );

                $stmt->bindValue(":BIOGRAPHY", $object->biography, PDO::PARAM_STR);
                $stmt->bindValue(":HABITS", $object->habits, PDO::PARAM_STR);
                $stmt->bindValue(":EXTRA_INFO", $object->extra_info, PDO::PARAM_STR);
                $stmt->bindValue(":IDRESIDENT", $object->id_resident, PDO::PARAM_INT);

                $stmt->execute();
                //recupero l'id
                $data = $conn->lastInsertId();

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
        /**
         *  Metodo per recuperare l'ospite in base al codUtente
         *  relativo alle teabelle di css 1.0
         */
        function getByCodUtente($codUtente) {
            
            if (empty($codUtente)) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, "codUtente")); 
            }
            $data = null;        
            try {

                $conn = $this->connectToDatabase();
                $stmt = $conn->prepare("
                        SELECT r.id, 
                            r.first_name, 
                            r.last_name, 
                            r.gender,
                            r.picture, 
                            r.birthday, 
                            r.birthplace,
                            r.biography,
                            r.habits,
                            r.extra_info
                        FROM resident r
                        WHERE r.id = ?"    
                );
                $stmt->execute([$codUtente]);

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;

        }
    }
?>