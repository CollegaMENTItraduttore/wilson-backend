<?php
    require_once('../managers/PrimaryNeeds.php');

    $classManager = new PrimaryNeeds();
    /**
    *    Valido in questo punto il token per evitare che malintenzionati
    *    provino a confermare dati non validi nella speranza che il token
    *    non venga refreshato...(questo è un esempio)
    */
    $tokenIsValid = $classManager->validateToken($_GET['token']);

    if (!$tokenIsValid) {
        echo $classManager -> initWilsonResponse(false, ['Invalid Token'], []);
        return false;
    } else {
        
        switch ($_GET['action']) {
            case 'get':
                $payload = $classManager->getById( $_GET['params']);
                break;
            
            case 'list':
                
                break;
            
            case 'save':
                //TODO: controllare se c'è
                $data = json_decode( file_get_contents('php://input') );
                $classManager->save( $data );
                break;
        }

        echo $classManager -> initWilsonResponse( $payload->success, $payload->message, $payload->data, $tokenIsValid );
    }
?>