<?php
//
// Description
// ===========
// This method will return all the information about an aprs entry.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station the aprs entry is attached to.
// entry_id:          The ID of the aprs entry to get the details for.
//
function qruqsp_aprs_entryGet($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'APRS Entry'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this station
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'aprs', 'private', 'checkAccess');
    $rc = qruqsp_aprs_checkAccess($q, $args['station_id'], 'qruqsp.aprs.entryGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load station settings
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'intlSettings');
    $rc = qruqsp_core_intlSettings($q, $args['station_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dateFormat');
    $date_format = qruqsp_core_dateFormat($q, 'php');

    //
    // Return default for new APRS Entry
    //
    if( $args['entry_id'] == 0 ) {
        $entry = array('id'=>0,
            'decoder'=>'',
            'channel'=>'0',
            'utc_of_traffic'=>'',
            'from_call_sign'=>'',
            'from_call_suffix'=>'',
            'heard_call_sign'=>'',
            'heard_call_suffix'=>'',
            'level'=>'',
            'error'=>'',
            'dti'=>'',
            'name'=>'',
            'symbol'=>'',
            'latitude'=>'',
            'longitude'=>'',
            'speed'=>'',
            'course'=>'',
            'altitude'=>'',
            'frequency'=>'',
            'offset'=>'',
            'tone'=>'',
            'system'=>'',
            'status'=>'',
            'telemetry'=>'',
            'comment'=>'',
        );
    }

    //
    // Get the details for an existing APRS Entry
    //
    else {
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
            . "AND qruqsp_aprs_entries.id = '" . qruqsp_core_dbQuote($q, $args['entry_id']) . "' "
            . "";
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.aprs', array(
            array('container'=>'entries', 'fname'=>'id', 
                'fields'=>array('decoder', 'channel', 'utc_of_traffic', 'from_call_sign', 'from_call_suffix', 'heard_call_sign', 'heard_call_suffix', 'level', 'error', 'dti', 'name', 'symbol', 'latitude', 'longitude', 'speed', 'course', 'altitude', 'frequency', 'offset', 'tone', 'system', 'status', 'telemetry', 'comment'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.6', 'msg'=>'APRS Entry not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['entries'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.7', 'msg'=>'Unable to find APRS Entry'));
        }
        $entry = $rc['entries'][0];
    }

    return array('stat'=>'ok', 'entry'=>$entry);
}
?>
