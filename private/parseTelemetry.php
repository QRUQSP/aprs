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
function qruqsp_aprs_parseTelemetry(&$q, $station_id, $packet, &$obj, &$data) {

    $obj['atype'] = 19;

    //
    // Get the sequence
    //
    if( preg_match("/T\#([0-9]{3}),([0-9]{3}),([0-9]{3}),([0-9]{3}),([0-9]{3}),([0-9]{3}),([0-1]{8})/", $data, $matches)
        || preg_match("/T\#(MIC)([0-9]{3}),([0-9]{3}),([0-9]{3}),([0-9]{3}),([0-9]{3}),([0-1]{8})/", $data, $matches) 
        ) {
        //
        // Remove the matched string from the data string
        //
        $data = substr($data, strlen($matches[0]));

        //
        // Set the flags to be telemetry data
        //
        $obj['flags'] |= 0x08;

        //
        // Store the telemetry data
        //
        $obj['telemetry_sequence'] = $matches[1];
        $obj['telemetry_analog1'] = $matches[2];
        $obj['telemetry_analog2'] = $matches[3];
        $obj['telemetry_analog3'] = $matches[4];
        $obj['telemetry_analog4'] = $matches[5];
        $obj['telemetry_analog5'] = $matches[6];
        $obj['telemetry_digital'] = bindec($matches[7]);
    }

    return array('stat'=>'ok');
}
?>
