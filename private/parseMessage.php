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
function qruqsp_aprs_parseMessage(&$q, $station_id, $packet, &$obj, &$data) {

    $obj['atype'] = 12;

    //
    // Get the sequence
    //
    if( preg_match("/^([a-zA-Z0-9\- ]{9}):(.*)$/", $data, $matches) ) {
        //
        // Remove the matched string from the data string
        //
        $obj['message_address'] = $matches[1];
        $obj['message_content'] = $matches[2];
        $obj['flags'] |= 0x40;
    }

    return array('stat'=>'ok');
}
?>
