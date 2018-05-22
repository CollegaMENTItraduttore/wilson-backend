<?php
    /* class WilsonBaseClass {
        function __construct() {
            
        }
            
        function initWilsonResponse( $success, $message, $data, $token) {
            return new class WilsonResponse() {
                var $success = true;
                var $message = [];
                var $data = $data;
                var $token = '';
                function __construct($success, $message, $data, $token) {		
                    $this->success = $success;
                    $this->message = $message;
                    $this->token = $token;
                    $this->data = $data;
                }			
            };
        }
        
        validateToken( $token ) {
            return 'dskjf87dfkjhdai759fdaihgo65';
        }
        
        connectToDatabase() {
            // Create connection
            $conn = new mysqli(SERVERNAME, USERNAME_DB, PWD_DB, DBNAMEMAIN);
            // Check connection
            if ($conn->connect_error) {
        // 		die("Connection failed: " . $conn->connect_error);
                $response->success = false;
                $response->message = "Error: " . $conn->connect_error;
            }
        }
    } */

    //--------------------------------------------------

    /* class PrimaryNeeds extends WilsonBaseClass {
        function __construct() {
            parent::__construct();        
            
        }
        
        launch( $params, $data ) {
            
        }
        
        getById( $id ) {
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
        
        getList() {
            
        }
        
        save( $data ) {
            
        }
    } */

    /* class Activity extends WilsonBaseClass {
        function __construct() {
            parent::__construct();
            
        }
    }
 */
    //----------------------------------------------------
    //WsPrimaryNeeds.php
    //--------------------
    require_once('../PrimaryNeeds.php');

    $classManager = new PrimaryNeeds();
    /**
    *    Valido in questo punto il token per evitare che malintenzionati
    *    provino a confermare dati non validi nella speranza che il token
    *    non venga refreshato...(questo è un esempio)
    */
    $tokenIsValid = $classManager->validateToken($_GET['token']);

    if (!$tokenIsValid) {
        echo $classManager -> initWilsonResponse(false, ['aaaa'], []);
        return false;
    } else {
        
        switch ($_GET['action']) {
            case 'get':
                $payload = $classManager->getById( $_GET['params']);
                break;
            
            case 'list':
                
                break;
            
            case 'save':
                //TODO: controllare se c'è
                $data = json_decode( file_get_contents('php://input') );
                $classManager->save( $data );
                break;
        }

        echo $classManager -> initWilsonResponse( $payload->success, $payload->message, $payload->data, $tokenIsValid );
    }

?>