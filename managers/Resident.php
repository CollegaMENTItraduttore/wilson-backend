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
                    ORDER BY nominative'
                    
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
                    SELECT r.id, r.first_name, r.last_name, r.gender,
                           r.picture, r.birthday, r.birthplace,
                           r.biography
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
    }
?>