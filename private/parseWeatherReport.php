<?php
//
// Description
// -----------
// This function will look for aprs data in a packet.
//
// Arguments
// ---------
// q:
// tnid:      
// args: The arguments for the hook
//
function qruqsp_aprs_parseWeatherReport(&$ciniki, $tnid, $packet, &$obj, &$data) {

    //
    // Strip \r or \n from end of string
    //
    $data = rtrim($data, "\r\n");

    $chr = substr($data, 0, 1);

    if( $chr == '!' && substr($data, 19, 1) == '_' ) {
        $obj['atype'] = 3;
    } elseif( $chr == '=' && substr($data, 19, 1) == '_' ) {
        $obj['atype'] = 2;
    } elseif( $chr == '@' && substr($data, 26, 1) == '_' ) {
        $obj['atype'] = 18;
    // FIXME: Add in check for compressed
    } elseif( $chr == '_' ) {
        $obj['atype'] = 21;
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.8', 'msg'=>'Invalid data for Weather Report'));
    }
    
    //
    // Make sure the weather_flags variable has be setup
    //
    if( !isset($obj['weather_flags']) ) {
        $obj['weather_flags'] = 0;
    }

    //
    // Look for positionless weather report, starting with MONTHDAYHOURMINUTE (MMDDHHMM)
    //
    if( preg_match("/^_([0-9][0-9])([0-9][0-9])([0-9][0-9])([0-9][0-9])/", $data, $matches) ) {
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
    // Check for position of positionless report
    // !9999.00N/99999.00E_
    //
    elseif( preg_match("/^!([0-9][0-9])([0-9][0-9])\.([0-9][0-9])(N|S)\/([0-9][0-9][0-9])([0-9][0-9])\.([0-9][0-9])(E|W)_$/", $data, $matches) ) {
        //
        // FIXME: This case occures when a weather tenant sends out a positionless weather report, and 
        // either bofore or after sends a position in a second packet. This positions need to be matched
        // to a previous or stored for future positionless report.
        //
        $lat_degrees = $matches[1];
        $lat_minutes = $matches[2];
        $lat_hmin = $matches[3];
        $lat_direction = $matches[4];
        $long_degrees = $matches[5];
        $long_minutes = $matches[6];
        $long_hmin = $matches[7];
        $long_direction = $matches[8];
        $lat = intval($lat_degrees) + (floatval($lat_minutes . '.' . $lat_hmin)/60);
        $long = intval($long_degrees) + (floatval($long_minutes . '.' . $long_hmin)/60);

        $obj['latitude'] = ($lat_direction == 'S' ? -$lat : $lat);
        $obj['longitude'] = ($long_direction == 'W' ? -$long : $long);
        
        return array('stat'=>'ok');
    }

    //
    // Check for position, no timestamp and weather
    //
    elseif( preg_match("/^(!|=)([0-9][0-9])([0-9][0-9])\.([0-9][0-9])(N|S)\/([0-9][0-9][0-9])([0-9][0-9])\.([0-9][0-9])(E|W)_([0-9][0-9][0-9])\/([0-9][0-9][0-9])(.+)$/", $data, $matches) ) {
        $lat_degrees = $matches[2];
        $lat_minutes = $matches[3];
        $lat_hmin = $matches[4];
        $lat_direction = $matches[5];
        $long_degrees = $matches[6];
        $long_minutes = $matches[7];
        $long_hmin = $matches[8];
        $long_direction = $matches[9];
        $lat = intval($lat_degrees) + (floatval($lat_minutes . '.' . $lat_hmin)/60);
        $long = intval($long_degrees) + (floatval($long_minutes . '.' . $long_hmin)/60);

        $obj['latitude'] = ($lat_direction == 'S' ? -$lat : $lat);
        $obj['longitude'] = ($long_direction == 'W' ? -$long : $long);

        $obj['weather_flags'] |= 0x01;
        if( isset($matches[11]) && $matches[11] != '' ) {
            $obj['wind_deg'] = $matches[10];
            $obj['wind_kph'] = round(($matches[11] * 1.609344), 2);
        }
        $obj['weather_flags'] |= 0x02;
    }
    
    //
    // Position with Timestamp with Weather Data
    // @000000z0000.00N/00000.00W_000/000g000t...
    //
    elseif( preg_match("/^(\@)([0-9][0-9])([0-9][0-9])([0-9][0-9])(z|\/|h)([0-9][0-9])([0-9][0-9])\.([0-9][0-9])(N|S)\/([0-9][0-9][0-9])([0-9][0-9])\.([0-9][0-9])(E|W)_([0-9][0-9][0-9])\/([0-9 ][0-9 ][0-9 ])(.+)$/", $data, $matches) ) {
        $utc_of_traffic = new DateTime($packet['utc_of_traffic'], new DateTimezone('UTC'));
        if( $matches[5] == 'z' ) {
            $day = $matches[2];
            $hour = $matches[3];
            $minute = $matches[4];
            $date_sent = clone $utc_of_traffic;
            //
            // Check if the month boundry was hit, the day in the packet will be greater than the utc of traffic, means it wrapped the month.
            //
            if( $day > $utc_of_traffic->format('d') ) {
                $date_sent->sub(new DateInterval('P1M'));
                $date_sent->setDate($date_sent->format('Y'), $date_sent->format('m'), $day);
            }
            $date_sent->setTime($hour, $minute, 00);
            $obj['date_sent'] = $date_sent->format('Y-m-d H:i:s');
        } elseif( $matches[5] == '/' ) {
            $day = $matches[2];
            $hour = $matches[3];
            $minute = $matches[4];
            //
            // FIXME: No localtime examples found in test data, needs to be implemented in the future
            //
        } elseif( $matches[5] == 'h' ) {
            $hour = $matches[2];
            $minute = $matches[3];
            $seconds = $matches[4];
            $date_sent = clone $utc_of_traffic;
            $date_sent->setTime($hour, $minute, $seconds);
            $obj['date_sent'] = $date_sent->format('Y-m-d H:i:s');
        }
        $lat_degrees = $matches[6];
        $lat_minutes = $matches[7];
        $lat_hmin = $matches[8];
        $lat_direction = $matches[9];
        $long_degrees = $matches[10];
        $long_minutes = $matches[11];
        $long_hmin = $matches[12];
        $long_direction = $matches[13];
        $lat = intval($lat_degrees) + (floatval($lat_minutes . '.' . $lat_hmin)/60);
        $long = intval($long_degrees) + (floatval($long_minutes . '.' . $long_hmin)/60);

        $obj['latitude'] = ($lat_direction == 'S' ? -$lat : $lat);
        $obj['longitude'] = ($long_direction == 'W' ? -$long : $long);

        $obj['weather_flags'] |= 0x01;
        if( isset($matches[15]) && $matches[15] != '' ) {
            $obj['wind_deg'] = $matches[14];
            $obj['wind_kph'] = round(($matches[15] * 1.609344), 2);
        }
        $obj['weather_flags'] |= 0x02;
    }

    //
    // FIXME: Check for compressed lat/long format (no examples to work from currently)
    //
    
    
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
                case 't': 
                    $obj['celsius'] = round(($matches[2] - 32)/(9/5), 2);
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
            $obj['millibars'] = ($matches[1] . '.' . $matches[2]) * 10;
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

    if( isset($packet['addrs'][1]['callsign']) && $packet['addrs'][1]['callsign'] != '' 
        && isset($obj['date_sent']) 
        && isset($obj['latitude']) 
        && isset($obj['longitude']) 
        ) {
        $weather_data = array(
            'sample_date' => $obj['date_sent'],
            'object' => 'qruqsp.aprs.station',
            'sensor' => 'APRS',
            'flags' => 0x10, // Remote station, read only
            'latitude' => $obj['latitude'],
            'longitude' => $obj['longitude'],
            'celsius' => $obj['celsius'],
            'humidity' => $obj['humidity'],
        );
        if( isset($obj['millibars']) ) {
            $weather_data['millibars'] = $obj['millibars'];
        }
        if( isset($obj['wind_kph']) ) {
            $weather_data['wind_kph'] = $obj['wind_kph'];
        }
        if( isset($obj['wind_deg']) ) {
            $weather_data['wind_deg'] = $obj['wind_deg'];
        }
        $weather_data['station'] = $packet['addrs'][1]['callsign'];
        if( isset($packet['addrs'][1]['ssid']) && $packet['addrs'][1]['ssid'] != '' ) {
            $weather_data['station'] .= '-' . $packet['addrs'][1]['ssid'];
        }
        $weather_data['object_id'] = $weather_data['station'];

        //
        // Check if any modules what weather data
        //
        foreach($ciniki['tenant']['modules'] as $module => $m) {
            list($pkg, $mod) = explode('.', $module);
            $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'weatherDataReceived');
            if( $rc['stat'] == 'ok' ) {
                $fn = $rc['function_call'];
                $rc = $fn($ciniki, $tnid, $weather_data);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }
    }

    return array('stat'=>'ok');
}
?>
