<?php 

class DateUtils {

    /**
     *  Metodo che passata una data/ora 
     *  ritorna una nuova data, con ore minuti e sec a "00:00:00"
     */
    public static function getStartOfDay($data) {
        
        $newDate = isset($data) ? $data : 'now';
        
        if (!$newDate instanceof DateTime) { 
            $newDate = new DateTime($newDate);     
        } else {
            $newDate = clone $data;
        }
        $newDate->setTime(0, 0, 0);
        return $newDate;
    }
    /**
     *  Metodo che passata una data/ora 
     *  ritorna una nuova data, con ore minuti e sec "23:59:59"
     */
    public static function getEndOfDay($data) {
    
        $newDate = isset($data) ?  $data : 'now';
        
        if (!$newDate instanceof DateTime) { 
            $newDate = new DateTime($newDate);   
        } else {
            $newDate = clone $data;
        }
        $newDate->setTime(23, 59, 59);
        return $newDate;
    }
}


?>