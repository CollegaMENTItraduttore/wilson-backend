<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    
    class Resident extends WilsonBaseClass {

        function __construct() {
            //parent::__construct();        
        }
        
        function launch( $params, $data ) {
       
        }
        /**
         *  Metodo che ritorna la lista dei residenti 
         *  per demo 22/GIUGNO listone senza filtri 
         *  
         */
        function getList() {
            
            $data = [];
            
            $conn = $this->connectToDatabase();
            
            try {
                $stmt = $conn->prepare('
                    SELECT r.id, r.first_name, r.last_name, r.gender, 
                           CONCAT( r.first_name," ",r.last_name) as nominative,
                           r.picture
                    from resident r
                    ORDER BY nominative
                    LIMIT 4'
                );
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
            
            $conn = $this->connectToDatabase();
            
            try {
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
               //$data = $stmp->lastInsertId()

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
    }
?>