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

        /* if (isset($on) && !empty($on)) { */

            switch ($every) {
                case 'daily':
    
                    $stringWhenRepeats = 'Giornalmente, ';
    
                    if ($on === 'MO;TU;WE;TH;FR;SA;SU') {
                        $stringWhenRepeats .= 'tutti i giorni.';
                    } else if ($on === 'MO;TU;WE;TH;FR;SA') {
                        $stringWhenRepeats .= 'tutti i giorni tranne la Domenica.';
                    } else if ($on === 'MO;TU;WE;TH;FR') {
                        $stringWhenRepeats .= 'solo nei giorni feriali.';
                    }

                    /**
                     * TODO: da gestire il caso relativo ai festivi.
                     * EXHOLIDAYS=YES in Cartella Cba
                     */

                    break;
                
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
                
                case 'monthly':
                case 'yearly':

                    $repeatsOn = '';
                    
                    $every = true;
                    if (strpos($on, 'BYDAY=1') !== false) {
                        $stringWhenRepeats .= 'Ogni primo ';
                    } else if (strpos($on, 'BYDAY=2') !== false) {
                        $stringWhenRepeats .= 'Ogni secondo ';
                    } else if (strpos($on, 'BYDAY=3') !== false) {
                        $stringWhenRepeats .= 'Ogni terzo ';
                    } else if (strpos($on, 'BYDAY=4') !== false) {
                        $stringWhenRepeats .= 'Ogni quarto ';
                    } else {
                        $every = false;
                    }

                    $everyType = true;
                    if (    (strpos($on, 'BYDAY=1MO,1TU,1WE,1TH,1FR') !== false) ||
                            (strpos($on, 'BYDAY=2MO,2TU,2WE,2TH,2FR') !== false) ||
                            (strpos($on, 'BYDAY=3MO,3TU,3WE,3TH,3FR') !== false) ||
                            (strpos($on, 'BYDAY=4MO,4TU,4WE,4TH,4FR') !== false)
                    ) {
                        /* Ogni x settimana */
                        $stringWhenRepeats .= 'settimana ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 25) . ';';
                    } else if (     (strpos($on, 'BYDAY=1SA,1SU') !== false) ||
                                    (strpos($on, 'BYDAY=2SA,2SU') !== false) ||
                                    (strpos($on, 'BYDAY=3SA,3SU') !== false) ||
                                    (strpos($on, 'BYDAY=4SA,4SU') !== false)
                    ) {
                        /* Ogni x fine settimana */
                        $stringWhenRepeats .= 'fine settimana ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 13) . ';';
                    } else if (     (strpos($on, 'BYDAY=1MO') !== false) ||
                                    (strpos($on, 'BYDAY=2MO') !== false) ||
                                    (strpos($on, 'BYDAY=3MO') !== false) ||
                                    (strpos($on, 'BYDAY=4MO') !== false)
                    ) {
                        /* Ogni x lunedì */
                        $stringWhenRepeats .= 'lunedì ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 9) . ';';
                    } else if (     (strpos($on, 'BYDAY=1TU') !== false) ||
                                    (strpos($on, 'BYDAY=2TU') !== false) ||
                                    (strpos($on, 'BYDAY=3TU') !== false) ||
                                    (strpos($on, 'BYDAY=4TU') !== false)
                    ) {
                        /* Ogni x martedì */
                        $stringWhenRepeats .= 'martedì ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 9) . ';';
                    } else if (     (strpos($on, 'BYDAY=1WE') !== false) ||
                                    (strpos($on, 'BYDAY=2WE') !== false) ||
                                    (strpos($on, 'BYDAY=3WE') !== false) ||
                                    (strpos($on, 'BYDAY=4WE') !== false)
                    ) {
                        /* Ogni x mercoledì */
                        $stringWhenRepeats .= 'mercoledì ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 9) . ';';
                    } else if (     (strpos($on, 'BYDAY=1TH') !== false) ||
                                    (strpos($on, 'BYDAY=2TH') !== false) ||
                                    (strpos($on, 'BYDAY=3TH') !== false) ||
                                    (strpos($on, 'BYDAY=4TH') !== false)
                    ) {
                        /* Ogni x giovedì */
                        $stringWhenRepeats .= 'giovedì ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 9) . ';';
                    } else if (     (strpos($on, 'BYDAY=1FR') !== false) ||
                                    (strpos($on, 'BYDAY=2FR') !== false) ||
                                    (strpos($on, 'BYDAY=3FR') !== false) ||
                                    (strpos($on, 'BYDAY=4FR') !== false)
                    ) {
                        /* Ogni x venerdì */
                        $stringWhenRepeats .= 'venerdì ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 9) . ';';
                    } else if (     (strpos($on, 'BYDAY=1SA') !== false) ||
                                    (strpos($on, 'BYDAY=2SA') !== false) ||
                                    (strpos($on, 'BYDAY=3SA') !== false) ||
                                    (strpos($on, 'BYDAY=4SA') !== false)
                    ) {
                        /* Ogni x sabato */
                        $stringWhenRepeats .= 'sabato ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 9) . ';';
                    } else if (     (strpos($on, 'BYDAY=1SU') !== false) ||
                                    (strpos($on, 'BYDAY=2SU') !== false) ||
                                    (strpos($on, 'BYDAY=3SU') !== false) ||
                                    (strpos($on, 'BYDAY=4SU') !== false)
                    ) {
                        /* Ogni x domenica */
                        $stringWhenRepeats .= 'domenica ';
                        $start = strpos($on, 'BYDAY=');
                        $repeatsOn = substr($on, $start, $start + 9) . ';';
                    } else {
                        $everyType = false;
                    }


                    /*
                    * Se non ho avvalorato entrambi i campi "Ogni" e "OgniTipo", avvaloro il campo
                    * "Ogni stesso giorno del mese/anno"
                    */
                    if ( !$every || !$everyType ) {
                        /* Ogni stesso giorno del mese/anno */

                    }


                    break;

                default:
                    /**
                     * TODO: passare data in chiaro del singolo evento
                     */
                    $stringWhenRepeats = 'Singolo evento';
                    break;
            }
        /* } */

        return $stringWhenRepeats;
    }
    /**
     *  Metodo che passata una data
     *  ritorna il primo giorno del mese con ore sec e min 00:00:00
     *  @param $data
     */
    public static function getStartOfMonth($data) {
    
        $newDate = isset($data) ?  $data : 'now';
        
        if (!$newDate instanceof DateTime) { 
            $newDate = new DateTime($newDate);   
        } else {
            $newDate = clone $data;
        }
        $newDate->modify('first day of this month');
        return $newDate;
    }
    /**
     *  Metodo che passata una data
     *  ritorna l'ultimo giorno del mese con ore sec min  a 23:59:59
     *  @param $data
     */
    public static function getEndOfMonth($data) {
    
        $newDate = isset($data) ?  $data : 'now';
        
        if (!$newDate instanceof DateTime) { 
            $newDate = new DateTime($newDate);   
        } else {
            $newDate = clone $data;
        }
        //recupero il mese
        $newDate->modify('last day of this month');
        $newDate->setTime(23, 59, 59);
        return $newDate;
    }


}


?>