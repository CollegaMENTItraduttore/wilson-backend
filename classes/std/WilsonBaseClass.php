<?php
    require_once('WilsonResponse.php');
    class WilsonBaseClass {
        function __construct() {
            
        }
            
        function initWilsonResponse( $success, $message, $data, $keyData, $token) {
            return new WilsonResponse( $success, $message, $data, $keyData, $token );
        }
        
        function validateToken( $token ) {
            return 'dskjf87dfkjhdai759fdaihgo65';
        }
        
        function connectToDatabase() {

            //connessione PDO
            $conn = null;
            try {
                $dbh = new PDO('mysql:host=localhost;dbname=wilson_db', "root", "root");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                $conn = $dbh;
            } catch (PDOException $e) {
                $conn = null;
                print "Error!: " . $e->getMessage() . "<br/>";
                die();
            }

            /*Create connection
            $conn = new PDO("localhost", "root", "root", "wilson_db");
            // Check connection
            if ($conn->connect_error) {
        // 		die("Connection failed: " . $conn->connect_error);
                $response->success = false;
                $response->message = "Error: " . $conn->connect_error;
            } */
            return $conn; 
        }
    }
?>