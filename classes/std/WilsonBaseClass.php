<?php
    require_once('WilsonResponse.php');

    class WilsonBaseClass {
        function __construct() {
            
        }
            
        function initWilsonResponse( $success, $message, $data, $token) {
            return new WilsonResponse( $success, $message, $data, $token );
        }
        
        function validateToken( $token ) {
            return 'dskjf87dfkjhdai759fdaihgo65';
        }
        
        function connectToDatabase() {
            // Create connection
            $conn = new mysqli(SERVERNAME, USERNAME_DB, PWD_DB, DBNAMEMAIN);
            // Check connection
            if ($conn->connect_error) {
        // 		die("Connection failed: " . $conn->connect_error);
                $response->success = false;
                $response->message = "Error: " . $conn->connect_error;
            }
        }
    }
?>