<?php
    require_once('../classes/std/WilsonBaseClass.php');

    class PrimaryNeeds extends WilsonBaseClass {
        function __construct() {
            parent::__construct();        
            
        }
        
        function launch( $params, $data ) {
            
        }
        
        function getById( $id ) {
            $responseSuccess = true;
            $responseMessage = [];
            $responseData = [];
            
            $conn = $this->connectToDatabase();
            
            if ( isset($id) ) {
                $stmt = $conn->prepare("
                    SELECT a.id, a.creator, a.join_date
                    FROM company a
                    WHERE a.id = ?
                ");
                $stmt->bind_param("i", $_GET['id']);
                
                if ($stmt->execute()) {
                    array_push( $responseMessage, 'Ricerca eseguita con successo!' );
                    $responseData = $stmt -> fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $responseSuccess = false;
                    array_push( $responseMessage, "Error: " . $conn->error );
                }
                
                $stmt->close();
            }
            $conn->close();
            
            return $this->initWilsonResponse( $responseSuccess, $responseMessage, $responseData, '' );
        }
        
        function getList() {
            
        }
        
        function save( $data ) {
            
        }
    }
?>