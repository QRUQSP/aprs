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

    $packet_txt = $packet['id'] . ': ';
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
            $packet_txt .= 'current mic-e data';
        } 
       
        //
        // Old Mic-E Data (Rev 0 beta)
        //
        elseif( $chr == 0x1d ) {
            $packet_txt .= 'old mic-c data';
        }

        //
        // Position without timestamp (no APRS messaging), or Ultimeter 2000 WX Station
        //
        elseif( $chr == '!' ) {
            $packet_txt .= 'Position without timestamp';
        }

        //
        // Peet Bros U-II Weather Station
        //
        elseif( $chr == '#' ) {
            $packet_txt .= 'Peet Bros U-II Weather Station';
        }

        //
        // Raw GPS Data or Ultimeter 2000
        //
        elseif( $chr == '$' ) {
            $packet_txt .= 'Raw GPS Data';
        }

        //
        // Agrelo DFJr/MicroFinder
        //
        elseif( $chr == '%' ) {
            $packet_txt .= 'Agrelo DFJr/MicroFinder';
        }

        //
        // Old Mic-E Data (but Current data for TM-D700)
        //
        elseif( $chr == '\'' ) {
            $packet_txt .= 'Old Mic-E Data (but Current data for TM-D700)';
        }

        //
        // Item
        //
        elseif( $chr == ')' ) {
            $packet_txt .= 'Item';
        }

        //
        // Peet Bros U-II Weather Station
        //
        elseif( $chr == '*' ) {
            $packet_txt .= 'Peet Bros U-II Weather Station';
        }

        //
        // Invalid Data or Test Data
        //
        elseif( $chr == ',' ) {
            $packet_txt .= 'Invalid Data or Test Data';
        }

        //
        // Position with timestamp (no APRS messaging)
        //
        elseif( $chr == '/' ) {
            $packet_txt .= 'Position with timestamp (no APRS messaging)';
        }

        //
        // Message
        //
        elseif( $chr == ':' ) {
            $packet_txt .= 'Message';
        }

        //
        // Object
        //
        elseif( $chr == ';' ) {
            $packet_txt .= 'Object';
        }

        //
        // Station Capabilities
        //
        elseif( $chr == '<' ) {
            $packet_txt .= 'Station Capabilities';
        }

        //
        // Position without timestamp (with APRS messaging)
        //
        elseif( $chr == '=' ) {
            $packet_txt .= 'Position without timestamp (with APRS messaging)';
        }

        //
        // Status
        //
        elseif( $chr == '>' ) {
            $packet_txt .= 'Status';
        }

        //
        // Query
        //
        elseif( $chr == '?' ) {
            $packet_txt .= 'Query';
        }

        //
        // Position with timestamp (with APRS messaging)
        //
        elseif( $chr == '@' ) {
            $packet_txt .= 'Position with timestamp (with APRS messaging)';
        }

        //
        // Telemetry Data
        //
        elseif( $chr == 'T' ) {
            $packet_txt .= 'Telemetry Data';
        }

        //
        // Maidenhead grid locator beacon (obsolete)
        //
        elseif( $chr == '[' ) {
            $packet_txt .= 'Maidenhead grid locator beacon (obsolete)';
        }

        //
        // Weather Report
        //
        elseif( $chr == '_' ) {
            $packet_txt .= 'Weather Report';
        }

        //
        // Current Mic-E Data (not used in TM-D700)
        //
        elseif( $chr == '`' ) {
            $packet_txt .= 'Current Mic-E Data (not used in TM-D700)';
        }

        //
        // User-Defined APRS packet format
        //
        elseif( $chr == '{' ) {
            $packet_txt .= 'User-Defined APRS packet format';
        }

        //
        // Third-party traffic
        //
        elseif( $chr == '}' ) {
            $packet_txt .= 'Third-party traffic';
        }

        //
        // 
        //
        else {
            $packet_txt .= 'Unknown: ' . $chr;
        }
    }

    print $packet_txt . "\n";

    return array('stat'=>'ok');
}
?>
