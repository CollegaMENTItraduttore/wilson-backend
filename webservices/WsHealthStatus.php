<?php
    header('Content-Type: application/json');

    require_once('../managers/HealthStatus.php');

    $db = isset($_GET['env']) ? $_GET['env'] : null;
    $classManager = new HealthStatus($db);
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
                    $dateStart = DateUtils::getStartOfMonth($dateStart);
                    $dateEnd = DateUtils::getEndOfMonth($dateStart);
                    $payload = $classManager->getListByFilters($idResident, $dateStart, $dateEnd);
                    break;

                case 'getHealthStatusMedicine':
                    $idResident = isset($_GET['idResident']) ? $_GET['idResident']: null; 
                    $type = isset($_GET['type']) ? $_GET['type'] : null;
                    $dateStart = isset($_GET['dateStart']) ? $_GET['dateStart'] : null;
                    $dateStart = DateUtils::getStartOfMonth($dateStart);
                    $dateEnd = DateUtils::getEndOfMonth($dateStart);
                    $payload = $classManager->getHealthStatusMedicine($idResident, $dateStart, $dateEnd, $type);
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