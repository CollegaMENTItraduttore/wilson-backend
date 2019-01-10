<?php
    header('Content-Type: application/json');
    require_once('../managers/Resident.php');

    $db = isset($_GET['env']) ? $_GET['env'] : null;
    $classManager = new Resident($db);
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
                    $listResidents = isset($_GET['listResidents']) ? $_GET['listResidents'] : null;
                    $payload = $classManager->getList($listResidents);
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
            error_log($e->getMessage());
            $success = false;
            array_push($message, $e->getMessage());

        } finally {
            $result = $classManager -> initWilsonResponse( $success, $message, $payload,$tokenIsValid );
            echo json_encode($result);
        }       
    }
?>