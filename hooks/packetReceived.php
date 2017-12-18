<?php
//
// Description
// -----------
// This function accepts a packet that was recieved via TNC and checks if it is
// an aprs packet.
//
// Arguments
// ---------
// q:
// tnid:      
// args: The arguments for the hook
//
function qruqsp_aprs_hooks_packetReceived(&$ciniki, $tnid, $args) {
    
    //
    // If no packet in args, then perhaps a packet we don't understand
    //
    if( !isset($args['packet']['data']) ) {
        return array('stat'=>'ok');
    }

    //
    // Check the control and protocol are correct
    //
    if( !isset($args['packet']['control']) || $args['packet']['control'] != 0x03 
        || !isset($args['packet']['protocol']) || $args['packet']['protocol'] != 0xf0 
        ) {
        return array('stat'=>'ok');
    }

    //
    // Try to parse the data
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'aprs', 'private', 'packetParse');
    $rc = qruqsp_aprs_packetParse($ciniki, $tnid, $args['packet']);
    if( $rc['stat'] != 'ok' && $rc['stat'] != 'noaprs' ) {
        return $rc;
    }

    //
    // If the packet was parsed, or no aprs data was found, success is returned
    //
    return array('stat'=>'ok');
}
?>
