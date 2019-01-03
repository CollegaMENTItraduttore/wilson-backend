<?php
    header('Content-Type: application/json');
    require_once('../managers/Resident.php');

    $classManager = new Resident();
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
                    $payload = $classManager->getList();
                    break;   
                case 'getById':
                    $idResident = isset($_GET['idResident']) ? $_GET['idResident'] : null;
                    $payload = $classManager->getById($idResident);
                    break;   
                case 'update':
                    $object = json_decode( file_get_contents('php://input'));
                    //var_dump($object);
                    $payload = $classManager->update($object);
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