<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an aprs entry.
// This method is typically used by the UI to display a list of changes that have occured
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station to get the details for.
// entry_id:          The ID of the aprs entry to get the history for.
// field:                   The field to get the history for.
//
function qruqsp_aprs_entryHistory($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'APRS Entry'),
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'aprs', 'private', 'checkAccess');
    $rc = qruqsp_aprs_checkAccess($q, $args['station_id'], 'qruqsp.aprs.entryHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbGetModuleHistory');
    return qruqsp_core_dbGetModuleHistory($q, 'qruqsp.aprs', 'qruqsp_aprs_history', $args['station_id'], 'qruqsp_aprs_entries', $args['entry_id'], $args['field']);
}
?>
