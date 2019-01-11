<?php
    header('Content-Type: application/json');
    require_once('../managers/SendMessages.php');

    $db = isset($_GET['env']) ? $_GET['env'] : null;
    $classManager = new SendMessages($db);
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
           
                case 'contactCareTeam':
                    $object = json_decode( file_get_contents('php://input'));
                    $payload = $classManager->contactCareTeam($object);
                    break;   
            }
            array_push($message, Costanti::OPERATION_OK);

        } catch(Exception $e) {
            error_log($e->getMessage());
            $success = false;
            array_push($message, $e->getMessage());

        } finally {
            $result = $classManager -> initWilsonResponse( $success, $message, $payload,$tokenIsValid );
            echo json_encode($result);
        }       
    }
?>