<?php
//
// Description
// -----------
// This method searchs for a APRS Entrys for a station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station to get APRS Entry for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
function qruqsp_aprs_entrySearch($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'aprs', 'private', 'checkAccess');
    $rc = qruqsp_aprs_checkAccess($q, $args['station_id'], 'qruqsp.aprs.entrySearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of entries
    //
    $strsql = "SELECT qruqsp_aprs_entries.id, "
        . "qruqsp_aprs_entries.decoder, "
        . "qruqsp_aprs_entries.channel, "
        . "qruqsp_aprs_entries.utc_of_traffic, "
        . "qruqsp_aprs_entries.from_call_sign, "
        . "qruqsp_aprs_entries.from_call_suffix, "
        . "qruqsp_aprs_entries.heard_call_sign, "
        . "qruqsp_aprs_entries.heard_call_suffix, "
        . "qruqsp_aprs_entries.level, "
        . "qruqsp_aprs_entries.error, "
        . "qruqsp_aprs_entries.dti, "
        . "qruqsp_aprs_entries.name, "
        . "qruqsp_aprs_entries.symbol, "
        . "qruqsp_aprs_entries.latitude, "
        . "qruqsp_aprs_entries.longitude, "
        . "qruqsp_aprs_entries.speed, "
        . "qruqsp_aprs_entries.course, "
        . "qruqsp_aprs_entries.altitude, "
        . "qruqsp_aprs_entries.frequency, "
        . "qruqsp_aprs_entries.offset, "
        . "qruqsp_aprs_entries.tone, "
        . "qruqsp_aprs_entries.system, "
        . "qruqsp_aprs_entries.status, "
        . "qruqsp_aprs_entries.telemetry, "
        . "qruqsp_aprs_entries.comment "
        . "FROM qruqsp_aprs_entries "
        . "WHERE qruqsp_aprs_entries.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND ("
            . "name LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR comment LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR comment LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . qruqsp_core_dbQuote($q, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.aprs', array(
        array('container'=>'entries', 'fname'=>'id', 
            'fields'=>array('id', 'decoder', 'channel', 'utc_of_traffic', 'from_call_sign', 'from_call_suffix', 'heard_call_sign', 'heard_call_suffix', 'level', 'error', 'dti', 'name', 'symbol', 'latitude', 'longitude', 'speed', 'course', 'altitude', 'frequency', 'offset', 'tone', 'system', 'status', 'telemetry', 'comment')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['entries']) ) {
        $entries = $rc['entries'];
        $entry_ids = array();
        foreach($entries as $iid => $entry) {
            $entry_ids[] = $entry['id'];
        }
    } else {
        $entries = array();
        $entry_ids = array();
    }

    return array('stat'=>'ok', 'entries'=>$entries, 'nplist'=>$entry_ids);
}
?>
