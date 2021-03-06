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
function qruqsp_aprs_packetParse(&$ciniki, $tnid, $packet) {

    $packet_txt = $packet['id'] . ': ';
    $obj = array(
        'packet_id' => $packet['id'],
        'type' => 0,
        'flags' => 0,
        'num_errors' => 0,
        'original_data' => $packet['data'],
        );

    //
    // Check the first characters of the data packet
    //
    if( isset($packet['data'][0]) ) {
        $chr = substr($packet['data'], 0, 1);
//        $packet['data'] = substr($packet['data'], 1);
//        error_log("chr: $chr");

        //
        // Third-party traffic, deal with first so remaining data is parsed second
        //
        if( $chr == '}' ) {
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseThirdPartyTraffic');
            $rc = qruqsp_aprs_parseThirdPartyTraffic($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            //
            // Get the new first character
            //
            $chr = substr($packet['data'], 0, 1);
//            $packet['data'] = substr($packet['data'], 1);
//            print $chr . '--' . $packet['data'] . "\n";
        }

        //
        // Old Mic-E Data (but Current data for TM-D700)
        // Current Mic-E Data (not used in TM-D700)
        // Current Mic-E Data (Rev 0 beta)
        // Old Mic-E Data (Rev 0 beta)
        //
        if( $chr == "'" || $chr == '`' || $chr == 0x1c || $chr == 0x1d ) {
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseMicEData');
            $rc = qruqsp_aprs_parseMicEData($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        } 
      
        //
        // Peet Bros U-II Weather Tenant
        // Peet Bros U-II Weather Tenant
        // Weather Report
        //
        elseif( 
//            // Check for format: !4903.50N/07201.75W_220/004 ...
//            preg_match("/^!([0-9][0-9][0-9][0-9]\.[0-9][0-9])(N|S)\/([0-9][0-9][0-9][0-9][0-9]\.[0-9][0-9])(E|W)_[0-9][0-9][0-9]\/[0-9][0-9][0-9]/", $packet['data'])
            ($chr == '!' && substr($packet['data'], 19, 1) == '_')
            || ($chr == '=' && substr($packet['data'], 19, 1) == '_')
            || ($chr == '@' && substr($packet['data'], 26, 1) == '_')
            || $chr == '#' 
            || $chr == '*' 
            || $chr == '_'  // Positionless weather report
            ) {
//            $obj['type'] = 4;
//            $packet_txt .= 'Peet Bros U-II Weather Tenant';
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseWeatherReport');
            $rc = qruqsp_aprs_parseWeatherReport($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }

        //
        // Position without timestamp (no APRS messaging), or Ultimeter 2000 WX Tenant
        //
        elseif( $chr == '!' ) {
            $obj['type'] = 3;
            $packet_txt .= 'Position without timestamp';
        }

        //
        // Raw GPS Data or Ultimeter 2000
        //
        elseif( $chr == '$' ) {
            $obj['type'] = 5;
            $packet_txt .= 'Raw GPS Data';
        }

        //
        // Agrelo DFJr/MicroFinder
        //
        elseif( $chr == '%' ) {
            $obj['type'] = 6;
            $packet_txt .= 'Agrelo DFJr/MicroFinder';
        }

        //
        // Item
        //
        elseif( $chr == ')' ) {
            $obj['type'] = 8;
            $packet_txt .= 'Item';
        }

/*        //
        //
        elseif( $chr == '*' ) {
            $obj['type'] = 9;
            $packet_txt .= 'Peet Bros U-II Weather Tenant';
        } */

        //
        // Invalid Data or Test Data
        //
        elseif( $chr == ',' ) {
            $obj['type'] = 10;
            $packet_txt .= 'Invalid Data or Test Data';
        }

        //
        // Position with timestamp (no APRS messaging)
        //
        elseif( $chr == '/' ) {
            $obj['type'] = 11;
            $packet_txt .= 'Position with timestamp (no APRS messaging)';
        }

        //
        // Message
        // Note: This is dealt with in the qruqsp.sams module.
        //
        elseif( $chr == ':' ) {
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseMessage');
            $rc = qruqsp_aprs_parseMessage($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }

        //
        // Object
        //
        elseif( $chr == ';' ) {
            $obj['type'] = 13;
            $packet_txt .= 'Object';
        }

        //
        // Tenant Capabilities
        //
        elseif( $chr == '<' ) {
            $obj['type'] = 14;
            $packet_txt .= 'Tenant Capabilities';
        }

        //
        // Position without timestamp (with APRS messaging)
        //
        elseif( $chr == '=' ) {
            $obj['type'] = 15;
            $packet_txt .= 'Position without timestamp (with APRS messaging)';
        }

        //
        // Status
        //
        elseif( $chr == '>' ) {
//            $obj['type'] = 16;
//            $packet_txt .= 'Status';
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseStatus');
            $rc = qruqsp_aprs_parseStatus($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }

        //
        // Query
        //
        elseif( $chr == '?' ) {
            $obj['type'] = 17;
            $packet_txt .= 'Query';
        }

        //
        // Position with timestamp (with APRS messaging)
        //
        elseif( $chr == '@' ) {
            $obj['type'] = 18;
            $packet_txt .= 'Position with timestamp (with APRS messaging)';
        }

        //
        // Telemetry Data
        //
        elseif( $chr == 'T' ) {
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseTelemetry');
            $rc = qruqsp_aprs_parseTelemetry($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }

        //
        // Maidenhead grid locator beacon (obsolete)
        //
        elseif( $chr == '[' ) {
            $obj['type'] = 20;
            $packet_txt .= 'Maidenhead grid locator beacon (obsolete)';
        }

/*        //
        // Weather Report
        //
        elseif( $chr == '_' ) {
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseWeatherReport');
            $rc = qruqsp_aprs_parseWeatherReport($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        } */

        //
        // User-Defined APRS packet format
        //
        elseif( $chr == '{' ) {
            $obj['type'] = 23;
            $packet_txt .= 'User-Defined APRS packet format';
        }

        //
        // Third-party traffic
        //
        elseif( $chr == '}' ) {
            ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'parseThirdPartyTraffic');
            $rc = qruqsp_aprs_parseThirdPartyTraffic($ciniki, $tnid, $packet, $obj, $packet['data']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }

        //
        // 
        //
        else {
            $packet_txt .= 'Unknown: ' . $chr;
        }
    }

    return array('stat'=>'ok');
}
?>
