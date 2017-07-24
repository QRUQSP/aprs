<?php
//
// Description
// -----------
// This function will look for aprs data in a packet.
//
// Arguments
// ---------
// q:
// station_id:
// args: The arguments for the hook
//
function qruqsp_aprs_parseWeatherReport(&$q, $station_id, $packet, &$obj, &$data) {

    $obj['atype'] = 21;
    if( !isset($obj['weather_flags']) ) {
        $obj['weather_flags'] = 0;
    }

    //
    // Look for the data to start with month day hour minute (MDHM)
    //
    if( preg_match("/_([0-9][0-9])([0-9][0-9])([0-9][0-9])([0-9][0-9])/", $data, $matches) ) {
        $month = $matches[1];
        $day = $matches[2];
        $hour = $matches[3];
        $minute = $matches[4];

        //
        // Shift the data array by 8 bytes
        //
        $data = substr($data, 8);

        //
        // Start with the date and time the packet was received
        //
        $dt = new DateTime($packet['utc_of_traffic'], new DateTimezone('UTC'));

        //
        // Check if month is later month than month of utc traffic, remove a year.
        // This occurs when a december timestamp is parsed in january and the year will be wrong if using current year.
        //
        if( $dt->format('n') < $month ) {
            $dt->setDate($dt->format('Y')-1, $month, $day);
        } else {
            $dt->setDate($dt->format('Y'), $month, $day);
        }
        $dt->setTime($hour, $minute, 0);

        //
        // Add the sent_date to the aprs object
        //
        $obj['sent_date'] = $dt->format('Y-m-d H:i:s');
        $obj['flags'] |= 0x01;
    }

    //
    // Incase there is a mixup in formatting, the following is run as a loop
    //
    $found = 1;
    while( $found ) {
        $found = 0;
        if( preg_match("/([csgtrpPL])([0-9][0-9][0-9])/", $data, $matches) ) {
            $found = 1;
            $data = substr($data, 4);
            switch($matches[1]) {
                case 'c': 
                    $obj['wind_direction'] = $matches[2];
                    $obj['weather_flags'] |= 0x01;
                    break;
                case 's': 
                    $obj['wind_speed'] = $matches[2];
                    $obj['weather_flags'] |= 0x02;
                    break;
                case 'g': 
                    $obj['wind_gust'] = $matches[2];
                    $obj['weather_flags'] |= 0x04;
                    break;
                    break;
                case 't': 
                    $obj['temperature'] = $matches[2];
                    $obj['weather_flags'] |= 0x08;
                    break;
                case 'r': 
                    $obj['rain_last_hour'] = $matches[2];
                    $obj['weather_flags'] |= 0x10;
                    break;
                case 'p': 
                    $obj['rain_last_day'] = $matches[2];
                    $obj['weather_flags'] |= 0x20;
                    break;
                case 'P': 
                    $obj['rain_since_midnight'] = $matches[2];
                    $obj['weather_flags'] |= 0x40;
                    break;
                case 'L': 
                    $obj['luminosity'] = $matches[2];
                    $obj['weather_flags'] |= 0x0200;
                    break;
            }
        }
        elseif( preg_match("/l([0-9][0-9][0-9][0-9])/", $data, $matches) ) {
            $data = substr($data, 4);
            $obj['luminosity'] = $matches[2];
            $obj['weather_flags'] |= 0x0200;
            break;
        }
        elseif( preg_match("/h([0-9][0-9])/", $data, $matches) ) {
            $found = 1;
            $data = substr($data, 3);
            $obj['humidity'] = $matches[1];
            $obj['weather_flags'] |= 0x80;
        }
        elseif( preg_match("/b([0-9][0-9][0-9])([0-9][0-9])/", $data, $matches) ) {
            $found = 1;
            $data = substr($data, 6);
            $obj['barometric_pressure'] = $matches[1] . '.' . $matches[2];
            $obj['weather_flags'] |= 0x0100;
        }
        elseif( preg_match("/([a-zA-Z])([a-zA-Z][a-z0-9A-Z\-]{1,3})/", $data, $matches) ) {
            $found = 1;
            $data = substr($data, strlen($matches[2]));
            $obj['software_type'] = $matches[1];
            $obj['weather_unit_type'] = $matches[2];
        }
    }

    if( isset($obj['weather_flags']) && $obj['weather_flags'] > 0 ) {
        $obj['flags'] |= 0x04;
    }

    return array('stat'=>'ok');
}
?>
