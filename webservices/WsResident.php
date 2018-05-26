<?php
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
        echo $classManager -> initWilsonResponse(false, ['Invalid Token'], []);
        return false;
    } else {
        
        switch ($_GET['action']) {
           
            case 'list':
                $payload = $classManager->getList();
                break;   
        }
        $result = $classManager -> initWilsonResponse( $payload->success, $payload->message, $payload->data, $tokenIsValid );
        echo json_encode($result);
    }
?>