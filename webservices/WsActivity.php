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
        echo $classManager -> initWilsonResponse(false, ['Invalid Token'], []);
        return false;
    } else {
        
        switch ($_GET['action']) {
            case 'list':
                $dateStart = isset($_GET['dateStart']) ? $_GET['dateStart'] : null;
                $dateStart = DateUtils::getStartOfDay($dateStart);
                $dateEnd = DateUtils::getEndOfDay($dateStart);
                $payload = $classManager->getListByFilters($_GET['idResident'], $dateStart, $dateEnd);
                
                break; 
            case 'listForCategory':
                $payload = $classManager-> listForCategory($_GET['idResident']);
                
                break; 
              
            case 'getById':
                $idActivityEdition = isset($_GET['idActivityEdition']) ? $_GET['idActivityEdition'] : null;
                $payload = $classManager->getById($idActivityEdition);
                
                break;
                
            case 'getPlannedById':
                $idActivity = isset($_GET['idActivity']) ? $_GET['idActivity'] : null;
                $payload = $classManager->getPlannedById($idActivity);
                
                break;
        }
        $result = $classManager -> initWilsonResponse( $payload->success, $payload->message, $payload->data, $tokenIsValid );
        echo json_encode($result);
    }
?>