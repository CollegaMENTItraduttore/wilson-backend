<?php
    header('Content-Type: application/json');
    require_once('../managers/GeneralLogger.php');

    $classManager = new GeneralLogger();
    
        $success = true;
        $message = [];
        $payload = [];
        try {

            switch ($_GET['action']) {             
                case 'logFetch':
                    $object = json_decode( file_get_contents('php://input'));
                    $payload = $classManager->logFetch($object);
                    break;   
            }
            array_push($message, Costanti::OPERATION_OK);

        } catch(Exception $e) {

            $success = false;
            array_push($message, $e->getMessage());

        } finally {
            $result = $classManager -> initWilsonResponse( $success, $message, $payload, null );
            echo json_encode($result);
        }

?>