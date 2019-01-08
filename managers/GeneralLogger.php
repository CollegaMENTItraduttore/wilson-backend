<?php
    require_once('../classes/std/WilsonBaseClass.php');
    require_once('../utils/Costanti.php');
    
    class GeneralLogger extends WilsonBaseClass {

        function __construct() {
            parent::__construct();        
        }
        
        function launch( $params, $data ) {
       
        }

        /**
         *  Metodo che si occupa di loggare le fetch
         *  provenienti dalle singole parti dell'app.
         */
        function logFetch($object) {
            $data = null;        
            try {

                $conn = $this->connectToLogDatabase();
                $stmt = $conn->prepare("
                    INSERT INTO logged_fetch (
                        log_date,
                        class_name_fn,
                        user_id_dm7
                    )
                    VALUES (
                        :log_date,
                        :class_name_fn,
                        :user_id_dm7
                    )
                ");

                $stmt->bindValue(":log_date", date("Y-m-d H:i:s"), PDO::PARAM_STR);
                $stmt->bindValue(":class_name_fn", $object->className, PDO::PARAM_STR);
                $stmt->bindValue(":user_id_dm7", $object->user_id, PDO::PARAM_STR);

                $stmt->execute();

            } catch (Exception $e) {
                throw new Exception(sprintf(Costanti::OPERATION_KO, $e->getMessage()));
            }
            return $data;
        }
    }
?>