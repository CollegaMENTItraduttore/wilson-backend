<?php
    header('Content-Type: application/json');
    require_once('../managers/EventType.php');

    $db = isset($_GET['env']) ? $_GET['env'] : null;
    $classManager = new EventType($db);
    /**
    *    Valido in questo punto il token per evitare che malintenzionati
    *    provino a confermare dati non validi nella speranza che il token
    *    non venga refreshato...(questo è un esempio)
    */
    $token = isset($_GET['token']) ? $_GET['token'] : null;
    $tokenIsValid = $classManager->validateToken($token);

    if (!$tokenIsValid) {
        $result = $classManager -> initWilsonResponse(false, ['Invalid Token'], []);
        echo json_encode($result);
    } else {
        $success = true;
        $message = [];
        $payload = [];
        try {

            switch ($_GET['action']) {
                case 'new':
                    $obj =  json_decode(file_get_contents('php://input'));
                    $payload = $classManager->new($obj);
                    break;
                case 'delete':
                    $id = isset($_GET['id']) ? $_GET['id'] : null;
                    $payload = $classManager->delete($id);
                    break;    
            }
            array_push($message, Costanti::OPERATION_OK);

        } catch(Exception $e) {
            $success = false;
            array_push($message, $e->getMessage());
            error_log($e->getMessage());

        } finally {
            $result = $classManager -> initWilsonResponse( $success, $message, $payload,$tokenIsValid );
            echo json_encode($result);
        }       
    }
?>