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
            
            $responseSuccess = true;
            $responseMessage = [];
            $responseData = [];
            
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
                array_push( $responseMessage, Costanti::OPERATION_OK);
                $responseData = $stmt -> fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                $responseSuccess = false;
                array_push( $responseMessage, sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $this->initWilsonResponse( $responseSuccess, $responseMessage, $responseData, '' );
        }
    }
?>