<?php
    header('Content-Type: application/json');

    require_once('../managers/HealthStatus.php');

    $classManager = new HealthStatus();
    /**
    *    Valido in questo punto il token per evitare che malintenzionati
    *    provino a confermare dati non validi nella speranza che il token
    *    non venga refreshato...(questo è un esempio)
    */
    $token = isset($_GET['token']) ? $_GET['token'] : null;
    $tokenIsValid = $classManager->validateToken($token);

    if (!$tokenIsValid) {
        echo $classManager -> initWilsonResponse(false, ['Invalid Token'], []);
        return false;
    } else {

        $success = true;
        $message = [];
        $payload = [];

        try {

            switch ($_GET['action']) {
                case 'list':
                    $idResident = isset($_GET['idResident']) ? $_GET['idResident']: null; 
                    $dateStart = isset($_GET['dateStart']) ? $_GET['dateStart'] : null;
                    $dateStart = DateUtils::getStartOfMonth($dateStart);
                    $dateEnd = DateUtils::getEndOfMonth($dateStart);
                    $payload = $classManager->getListByFilters($idResident, $dateStart, $dateEnd);
                    break;

                case 'getById':
                    $idPrimaryNeed = isset($_GET['idPrimaryNeed']) ? $_GET['idPrimaryNeed']: null; 
                    $payload = $classManager->getById($idPrimaryNeed);
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