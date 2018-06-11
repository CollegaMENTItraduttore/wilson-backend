<?php
    class WilsonResponse {
        
        var $success = true;
        var $message = [];
        var $data = [];

        function __construct($success, $message, $data, $token) {		
            $this->success = $success;
            $this->message = $message;
            $this->token = $token;
            $this->data = $data;
        }			
    }
?>