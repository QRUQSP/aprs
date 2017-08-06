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
function qruqsp_aprs_parseMicEData(&$q, $station_id, $packet, &$obj, &$data) {

//    $obj['atype'] = 1;

    $chr = substr($data, 0, 1);

    if( $chr == 0x1c ) {
        $obj['atype'] = 1;
    } elseif( $chr == 0x1d ) {
        $obj['atype'] = 2;
    } elseif( $chr == "'" ) {
        $obj['atype'] = 7;
    } elseif( $chr == '`' ) {
        $obj['atype'] = 22;
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.8', 'msg'=>'Invalid data for Mic E'));
    }

    //
    // Get the destination address
    //
    $dest_callsign = '';
    if( isset($packet['addrs']) ) {
        foreach($packet['addrs'] as $addr) {
            if( $addr['atype'] == 10 ) {
                $dest_callsign = $addr['callsign'];
            }
        }
    }

    $lat_degrees = '';
    $lat_minutes = '';
    $lat_hmin = '';
    $message_bits = '';
    $message_type = '';
    $lat_direction = '';
    $long_direction = '';
    $long_offset = 0;

    if( $dest_callsign != '' ) {
        $decoder = array(
            '0' => array('0', '0', '', 'S', 0, 'E'),
            '1' => array('1', '0', '', 'S', 0, 'E'),
            '2' => array('2', '0', '', 'S', 0, 'E'),
            '3' => array('3', '0', '', 'S', 0, 'E'),
            '4' => array('4', '0', '', 'S', 0, 'E'),
            '5' => array('5', '0', '', 'S', 0, 'E'),
            '6' => array('6', '0', '', 'S', 0, 'E'),
            '7' => array('7', '0', '', 'S', 0, 'E'),
            '8' => array('8', '0', '', 'S', 0, 'E'),
            '9' => array('9', '0', '', 'S', 0, 'E'),
            'A' => array('0', '1', 'C', '', 0, ''),
            'B' => array('1', '1', 'C', '', 0, ''),
            'C' => array('2', '1', 'C', '', 0, ''),
            'D' => array('3', '1', 'C', '', 0, ''),
            'E' => array('4', '1', 'C', '', 0, ''),
            'F' => array('5', '1', 'C', '', 0, ''),
            'G' => array('6', '1', 'C', '', 0, ''),
            'H' => array('7', '1', 'C', '', 0, ''),
            'I' => array('8', '1', 'C', '', 0, ''),
            'J' => array('9', '1', 'C', '', 0, ''),
            'K' => array(' ', '1', 'C', '', 0, ''),
            'L' => array(' ', '0', '', 'S', 0, 'E'),
            'P' => array('0', '1', 'S', 'N', 100, 'W'),
            'Q' => array('1', '1', 'S', 'N', 100, 'W'),
            'R' => array('2', '1', 'S', 'N', 100, 'W'),
            'S' => array('3', '1', 'S', 'N', 100, 'W'),
            'T' => array('4', '1', 'S', 'N', 100, 'W'),
            'U' => array('5', '1', 'S', 'N', 100, 'W'),
            'V' => array('6', '1', 'S', 'N', 100, 'W'),
            'W' => array('7', '1', 'S', 'N', 100, 'W'),
            'X' => array('8', '1', 'S', 'N', 100, 'W'),
            'Y' => array('9', '1', 'S', 'N', 100, 'W'),
            'Z' => array(' ', '1', 'S', 'N', 100, 'W'),
            );

        for($i = 0; $i < 6; $i++) {
            $c = substr($dest_callsign, $i, 1);
            if( isset($decoder[$c]) ) {
                //
                // Check for the message type, if not already set
                //
                if( $i < 3 && $message_type == '' && $decoder[$c][2] != '' ) {
                    $message_type = $decoder[$c][2];
                }
                if( $i < 3 ) {
                    $message_bits .= $decoder[$c][1];
                }
                if( $i == 0 ) {
                    $lat_degrees .= $decoder[$c][0];
                } elseif( $i == 1 ) {
                    $lat_degrees .= $decoder[$c][0];
                } elseif( $i == 2 ) {
                    $lat_minutes .= $decoder[$c][0];
                } elseif( $i == 3 ) {
                    $lat_minutes .= $decoder[$c][0];
                    $lat_direction = $decoder[$c][3];
                } elseif( $i == 4 ) {
                    $lat_hmin .= $decoder[$c][0];
                    $offset = $decoder[$c][4];
                } elseif( $i == 5 ) {
                    $lat_hmin .= $decoder[$c][0];
                    $long_direction .= $decoder[$c][5];
                }
            }
        }
    }

    //
    // Byte 1 - Longitude
    //
    $long_degrees = ord(substr($data, 1, 1)) - 28;
    $offset = 0;
    if( $long_degrees >= 0 && $long_degrees <= 9 ) {
        $offset = 100;
    } elseif( $long_degrees >= 10 && $long_degrees <= 99 ) {
        $offset = 0;
    } elseif( $long_degrees >= 100 && $long_degrees <= 109 ) {
        $offset = 100;
    } elseif( $long_degrees >= 110 && $long_degrees <= 179 ) {
        $offset = 100;
    }
    $long_degrees += $offset;
    if( $long_degrees > 100 ) {
        $long_degrees += 100;
    }
    if( 180 <= $long_degrees && $long_degrees <= 189 ) {
        $long_degrees -= 80;
    }
    if( 190 <= $long_degrees && $long_degrees <= 199 ) {
        $long_degrees -= 90;
    }

    //
    // Byte 2 - Minute
    //
    $long_minutes = ord(substr($data, 2, 1)) - 28;
    if( $long_minutes >= 60 ) {
        $long_minutes -= 60;
    }

    //
    // Byte 3 - Hundreths of minutes
    //
    $long_hmin = ord(substr($data, 3, 1)) - 28;
    if( $long_hmin >= 60 ) {
        $long_hmin -= 60;
    }

    //
    // Byte 4 - Hundreds/Tens of Knots
    //
    $sp = (ord(substr($data, 4, 1)) - 28) * 10;
    
    //
    // Byte 5 - Speed
    //
    $dc = ord(substr($data, 5, 1)) - 28;
    $sp_units = floor($dc/10);
    $speed = $sp + $sp_units;
    $course = ($dc%10) * 100;

    //
    // Byte 6 - Course
    //
    $course += ord(substr($data, 6, 1)) - 28;

    //
    // Final adjustments
    //
    if( $speed >= 800 ) {
        $speed -= 800;
    }
    if( $course >= 400 ) {
        $course -= 400;
    }

    //
    // Check ambiguity
    //
    if( $lat_hmin == '  ' ) {
        $long_hmin = 0;
    } elseif( isset($lat_min[1]) && $lat_hmin[1] == ' ' ) {
        $long_hmin = floor($long_hmin/10) * 10;
    }

    //
    // Convert strings to number values
    //
    $lat_degrees = intval($lat_degrees);
    $lat_minutes = intval($lat_minutes);
    $lat_hmin = intval($lat_hmin);
    
    //
    // Convert degrees, minutes, hundreds of minutes to decimal
    //
    $lat = intval($lat_degrees) + (floatval($lat_minutes . '.' . $lat_hmin)/60);
    $long = intval($long_degrees) + (floatval($long_minutes . '.' . $long_hmin)/60);

    //
    // Store the symbol as the 2 bytes (Symbol Code & Symbal Table ID)
    //
    $symbol_code = substr($data, 7, 1);
    $symbol_table = substr($data, 8, 1);

    //
    // Parse the telemetry data
    //
    $pos = 9;
    $telemetry_flag = substr($data, $pos, 1);
    if( $telemetry_flag == "'" ) {
         $pos+=6;
    } elseif( $telemetry_flag == "`" ) {
         $pos+=6;
    } elseif( $telemetry_flag == 0x1d ) {
         $pos+=6;
    } 

    //
    // 

//    %s(%s) %s%s %d\n", $lat_degrees, $lat_minutes, $lat_hmin, $message_bits, $mt, $ns, $ew, $offset);


 //   print sprintf("%5d %s %3d %3d %3d - %3d %3d - %s: %s\n", $obj['packet_id'], $dest_callsign, $degrees, $minutes, $hmin, $speed, $course, $packet['utc_of_traffic'], $packet['data']);

    //

//    printf("%s [%0.8f%s %0.8f%s] %3d %3d %3d (%s) %s\n", 
//        $dest_callsign, $lat, $lat_direction, $long, $long_direction,
//        $speed, $sp_units, $course, $packet['utc_of_traffic'], $packet['data']);
    printf("%s [%0.8f%s %0.8f%s] %s%s %s (%s) %s\n", 
        $dest_callsign, $lat, $lat_direction, $long, $long_direction,
        $symbol_code, $symbol_table, $telemetry_flag, 
        $packet['utc_of_traffic'], $packet['data']);



  /*  
    //
    // TNC-2 Format
    //
    if( preg_match("/^(.*),(.*),(.*)\*:/", $data, $matches) ) {
        $data = substr($data, strlen($matches[0]));
        $obj['source_path'] = $matches[1];
        $obj['third_party_network'] = $matches[2];
        $obj['receiving_station'] = $matches[3];
    } 

    //
    // AEA Format
    //
    elseif( preg_match("/^(.*)\>([^\>]{1,9})\>([^\>]{1,9})\*\>([^:]{1,9}):/", $data, $matches) ) {
        $data = substr($data, strlen($matches[0]));
        $obj['source_path'] = $matches[1];
        $obj['third_party_network'] = $matches[2];
        $obj['receiving_station'] = $matches[3];
        $obj['destination_station'] = $matches[4];
    }
*/
    return array('stat'=>'ok');
}
?>
