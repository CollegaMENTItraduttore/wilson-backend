<?php
    require_once('../managers/PrimaryNeeds.php');

    $classManager = new PrimaryNeeds();
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
                    $idResident = isset($_GET['idResident']) ? $_GET['idResident']: null; 
                    $dateStart = isset($_GET['dateStart']) ? $_GET['dateStart'] : null;
                    $dateStart = DateUtils::getStartOfDay($dateStart);
                    $dateEnd = DateUtils::getEndOfDay($dateStart);
                    $payload = $classManager->getListByFilters($idResident, $dateStart, $dateEnd);
                    break;

                case 'getById':
                    $idPrimaryNeed = isset($_GET['idPrimaryNeed']) ? $_GET['idPrimaryNeed']: null; 
                    $payload = $classManager->getById($idPrimaryNeed);
                break;
                case 'save':
                    //TODO: controllare se c'è
                    $data = json_decode( file_get_contents('php://input') );
                    $classManager->save( $data );
                    break;
            }

        } catch(Exception $e) {
            $success = false;
            array_push($message, $e->getMessage());

        } finally {
            $result = $classManager -> initWilsonResponse( $success, $message, $payload, $tokenIsValid );
            echo json_encode($result);
        }
    }
?>