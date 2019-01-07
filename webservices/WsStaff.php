<?php
    header('Content-Type: application/json');
    require_once('../managers/Staff.php');

    $db = isset($_GET['db']) ? $_GET['db'] : null;
    $classManager = new Staff($db);
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
           
                case 'list':
                    $payload = $classManager -> list();
                    break;
                case 'get':
                    $id = isset($_GET['id']) ? $_GET['id'] : null;
                    $payload = $classManager -> get($id);
                    break;      
                case 'new':
                    $payload = $classManager -> new($_POST);
                    break;   
                case 'update':
                    $payload = $classManager -> update($_POST);
                    break;
                case 'delete':
                    $id =  (isset($_GET['id']) ? $_GET['id'] : null);
                    $payload = $classManager -> delete($id);
                    break;   
            }
            array_push($message, Costanti::OPERATION_OK);

        } catch(Exception $e) {
            $success = false;
            array_push($message, $e->getMessage());

        } finally {
            $result = $classManager -> initWilsonResponse( $success, $message, $payload,$tokenIsValid );
            echo json_encode($result);
        }       
    }



?>