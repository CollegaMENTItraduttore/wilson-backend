<?php
    class WilsonResponse {
        
        var $success = true;
        var $message = [];
        //var $data = [];
        var $token = '';

        function __construct($success, $message, $data, $keyData, $token) {		
            $this->success = $success;
            $this->message = $message;
            $this->token = $token;
            $this->{isset($keyData) ? $keyData : 'data'} = $data;
        }			
    }
?>