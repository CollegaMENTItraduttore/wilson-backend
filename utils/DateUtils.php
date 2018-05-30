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

    /**
     * metodo utile a fare il parse delle ricorsivita
     */
    public static function whenRepeats( $every, $on ) {
        $stringWhenRepeats = '';

        switch ($every) {
            case 'weekly':

                $arrayAllDays = array(
                    'MO'=>'Lunedì',
                    'TU'=>'Martedì',
                    'WE'=>'Mercoledì',
                    'TH'=>'Giovedì',
                    'FR'=>'Venerdì',
                    'SA'=>'Sabato',
                    'SU'=>'Domenica'
                );

                $stringWhenRepeats = 'Settimanalmente, tutti i ';
                $arrayDays = explode(';', $on);

                $countDay = 0;

                foreach($arrayDays as &$value) {
                    if (!empty($value)) {
                        $countDay++;
                        $stringWhenRepeats .= ($countDay > 1 ? ', ' : ' ') . $arrayAllDays[$value];
                    }
                }

                if ($countDay > 0) {
                    $stringWhenRepeats .= '.';
                }

                break;
            
            default:
                # code...
                break;
        }

        return $stringWhenRepeats;
    }

}


?>