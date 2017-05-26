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
function qruqsp_aprs_packetParse(&$q, $station_id, $packet) {
    
    //
    // Check the first characters of the data packet
    //
    if( isset($packet['data'][0]) ) {
        $chr = $packet['data'][0];
//        error_log("chr: $chr");

        //
        // Current Mic-E Data (Rev 0 beta)
        //
        if( $chr == 0x1c ) {
            error_log('current mic-e data');
        } 
       
        //
        // Old Mic-E Data (Rev 0 beta)
        //
        elseif( $chr == 0x1d ) {
            error_log('old mic-c data');
        }

        //
        // Position without timestamp (no APRS messaging), or Ultimeter 2000 WX Station
        //
        elseif( $chr == '!' ) {
            error_log('Position without timestamp');
        }

        //
        // Peet Bros U-II Weather Station
        //
        elseif( $chr == '#' ) {
            error_log('Peet Bros U-II Weather Station');
        }

        //
        // Raw GPS Data or Ultimeter 2000
        //
        elseif( $chr == '$' ) {
            error_log('Raw GPS Data');
        }

        //
        // Agrelo DFJr/MicroFinder
        //
        elseif( $chr == '%' ) {
            error_log('Agrelo DFJr/MicroFinder');
        }

        //
        // Old Mic-E Data (but Current data for TM-D700)
        //
        elseif( $chr == '\'' ) {
            error_log('Old Mic-E Data (but Current data for TM-D700)');
        }

        //
        // Item
        //
        elseif( $chr == ')' ) {
            error_log('Item');
        }

        //
        // Peet Bros U-II Weather Station
        //
        elseif( $chr == '*' ) {
            error_log('Peet Bros U-II Weather Station');
        }

        //
        // Invalid Data or Test Data
        //
        elseif( $chr == ',' ) {
            error_log('Invalid Data or Test Data');
        }

        //
        // Position with timestamp (no APRS messaging)
        //
        elseif( $chr == '/' ) {
            error_log('Position with timestamp (no APRS messaging)');
        }

        //
        // Message
        //
        elseif( $chr == ':' ) {
            error_log('Message');
        }

        //
        // Object
        //
        elseif( $chr == ';' ) {
            error_log('Object');
        }

        //
        // Station Capabilities
        //
        elseif( $chr == '<' ) {
            error_log('Station Capabilities');
        }

        //
        // Position without timestamp (with APRS messaging)
        //
        elseif( $chr == '=' ) {
            error_log('Position without timestamp (with APRS messaging)');
        }

        //
        // Status
        //
        elseif( $chr == '>' ) {
            error_log('Status');
        }

        //
        // Query
        //
        elseif( $chr == '?' ) {
            error_log('Query');
        }

        //
        // Position with timestamp (with APRS messaging)
        //
        elseif( $chr == '@' ) {
            error_log('Position with timestamp (with APRS messaging)');
        }

        //
        // Telemetry Data
        //
        elseif( $chr == 'T' ) {
            error_log('Telemetry Data');
        }

        //
        // Maidenhead grid locator beacon (obsolete)
        //
        elseif( $chr == '[' ) {
            error_log('Maidenhead grid locator beacon (obsolete)');
        }

        //
        // Weather Report
        //
        elseif( $chr == '_' ) {
            error_log('Weather Report');
        }

        //
        // Current Mic-E Data (not used in TM-D700)
        //
        elseif( $chr == '`' ) {
            error_log('Current Mic-E Data (not used in TM-D700)');
        }

        //
        // User-Defined APRS packet format
        //
        elseif( $chr == '{' ) {
            error_log('User-Defined APRS packet format');
        }

        //
        // Third-party traffic
        //
        elseif( $chr == '}' ) {
            error_log('Third-party traffic');
        }

        //
        // 
        //
        else {
            error_log('Unknown: ' . $chr);
        }
    }


    return array('stat'=>'ok');
}
?>
