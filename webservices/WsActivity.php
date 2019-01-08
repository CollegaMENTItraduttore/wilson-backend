<?php
    require_once('../managers/Activity.php');
    require_once('../utils/DateUtils.php');

    $classManager = new Activity();
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
                    $dateStart = isset($_GET['dateStart']) ? $_GET['dateStart'] : null;
                    $dateStart = DateUtils::getStartOfDay($dateStart);
                    $dateEnd = DateUtils::getEndOfDay($dateStart);
                    $payload = $classManager->getListByFilters($_GET['idResident'], $dateStart, $dateEnd);
                    break; 
                case 'getPlannedList':
                    $payload = $classManager-> getPlannedList($_GET['idResident']);
                    break; 
                  
                case 'getById': //id = idActivityEdition
                    $idActivityEdition = isset($_GET['id']) ? $_GET['id'] : null;
                    $payload = $classManager->getById($idActivityEdition);
                    break;
                    
                case 'getPlannedById'://id = id_activity
                    $idActivity = isset($_GET['id']) ? $_GET['id'] : null;
                    $payload = $classManager->getPlannedById($idActivity);
                    break;
            }
            array_push($message, Costanti::OPERATION_OK);

        } catch (Exception $e) {
            error_log($e->getMessage());
            $success = false;
            array_push($message, $e->getMessage());

        } finally {
            $result = $classManager -> initWilsonResponse( $success, $message, $payload, $tokenIsValid );
            echo json_encode($result);
        }        
    }
?>