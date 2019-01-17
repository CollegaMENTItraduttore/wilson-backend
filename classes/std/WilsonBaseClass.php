<?php
    require_once('WilsonResponse.php');
    class WilsonBaseClass {

        
        private $db;
        private $connection;
        

        function WilsonBaseClass($env = null, $conn = null) {
            $this->db = $env;
            $this->connection = $conn;
        }

        function getDb() {
            return $this->db;
        }
        function getIdRsaByDb() {

            if (empty($this->getDb())) {
                throw new Exception(sprintf(Costanti::INVALID_FIELD, "db")); 
            }
            $data = [];    
            
    
            try {
                $conn = $this->connectToDatabase(); 
                $stmt = $conn->prepare('
                            select r.id 
                            from rsa r
                            where r.id_dm7 = ?
                            limit 1
                ');
                $stmt->bindValue(1, $this->getDb(), PDO::PARAM_STR);
                $stmt->execute();
                $data = $stmt -> fetchAll(PDO::FETCH_ASSOC);
    
            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return (!empty($data) && count($data) > 0 ? $data[0]['id'] : null);
        } 
            
        function initWilsonResponse( $success, $message, $data, $token = 'EXPIRED') {
            return new WilsonResponse( $success, $message, $data, $token );
        }
        
        /**
         * metodo che richiama l'api PUT api/v1/auth
         * @param token
         * questa va a rinnovare l'expireDate
         * Se la chiamata risponde true ci si collega al db struttura CBA,
         * altrimenti nella response ci sarà "sessione scaduta"
         */
        function validateToken( $token ) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dm7-008.conserva.cloud/api/v1/auth/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                    "Postman-Token: ee8c6282-0232-40e9-aa9d-b12c76e0e57f",
                    "cache-control: no-cache",
                    "dm7auth: " . $token
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
                return null;
            } else {
                $responseDecoded = json_decode($response);
                
                if ($responseDecoded === NULL) {
                    return null;
                } else {
                    $success = $responseDecoded -> {'success'};
                    $access_token = $responseDecoded -> {'data'} -> {'access_token'};
    
                    return $access_token;
                }
            }

        }
        
        function connectToDatabase() {
            if (isset($this->connection) && !empty($this->connection)) {
                return $this->connection;
            }
            //connessione PDO
            $conn = null;
            try {
                $dbh = new PDO('mysql:host=localhost;dbname=env_cm_'.$this->db,"root", "root");
                /* $dbh = new PDO('mysql:host=localhost;dbname=env_cm_'.$this->db,"root", "root"); */

                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                $conn = $dbh;

            } catch (PDOException $e) {
                $conn = null;
                //print "Error!: " . $e->getMessage() . "<br/>";
                throw new Exception($e->getMessage());
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

        function connectToLogDatabase() {

            //connessione PDO
            $conn = null;
            try {
                $dbh = new PDO('mysql:host=localhost;dbname=cmlog_db', "root", "hAjScBahAjScBa0");
                /* $dbh = new PDO('mysql:host=localhost;dbname=cmlog_db', "root", "root"); */
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                $conn = $dbh;
            } catch (PDOException $e) {
                $conn = null;
                print "Error!: " . $e->getMessage() . "<br/>";
                die();
            }

            return $conn; 
        }
    }
?>