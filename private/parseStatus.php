<?php
//
// Description
// -----------
// This function will parse a status report aprs message.
//
// Arguments
// ---------
// q:
// station_id:
// args: The arguments for the hook
//
function qruqsp_aprs_parseStatus(&$q, $station_id, $packet, &$obj, &$data) {

    $obj['atype'] = 16;

    //
    // Check for a maidenhead grid.
    // Specification for APRS says should be uppercase for first 2 characters, we are checking for upper or lower.
    //
    if( preg_match("/>([a-zA-Z][a-zA-Z])([0-9][0-9])([a-zA-Z][a-zA-Z])(.)(.) (.*)$/", $data, $matches) ) {
        $obj['maidenhead'] = strtoupper($matches[1]) . $matches[2] . strtolower($matches[3]);
        $obj['symbol_table'] = $matches[4];
        $obj['symbol_code'] = $matches[5];
        $obj['message'] = $matches[6];
    }
    elseif( preg_match("/>([a-zA-Z][a-zA-Z])([0-9][0-9])(.)(.) (.*)$/", $data, $matches) ) {
        $obj['maidenhead'] = strtoupper($matches[1]) . $matches[2];
        $obj['symbol_table'] = $matches[3];
        $obj['symbol_code'] = $matches[4];
        $obj['message'] = $matches[5];
    }
    elseif( preg_match("/>([0-9][0-9])([0-9][0-9])([0-9][0-9])z(.*)$/", $data, $matches) ) {
        $day = $matches[1];
        $hour = $matches[2];
        $minute = $matches[3];
        $obj['message'] = $matches[4];
        $utc_of_traffic = new DateTime($packet['utc_of_traffic'], new DateTimezone('UTC'));
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
    }

    print_r($obj);

    return array('stat'=>'ok');
}
?>
