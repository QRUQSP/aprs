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
function qruqsp_aprs_parseThirdPartyTraffic(&$ciniki, $tnid, $packet, &$obj, &$data) {

    $obj['atype'] = 24;

    //
    // TNC-2 Format
    //
    if( preg_match("/^}(.*),(.*),(.*)\*:/", $data, $matches) ) {
        $data = substr($data, strlen($matches[0]));
        $obj['source_path'] = $matches[1];
        $obj['third_party_network'] = $matches[2];
        $obj['receiving_tenant'] = $matches[3];
    } 

    //
    // AEA Format
    //
    elseif( preg_match("/^}(.*)\>([^\>]{1,9})\>([^\>]{1,9})\*\>([^:]{1,9}):/", $data, $matches) ) {
        $data = substr($data, strlen($matches[0]));
        $obj['source_path'] = $matches[1];
        $obj['third_party_network'] = $matches[2];
        $obj['receiving_tenant'] = $matches[3];
        $obj['destination_tenant'] = $matches[4];
    }

    return array('stat'=>'ok');
}
?>
