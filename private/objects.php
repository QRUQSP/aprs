<?php
//
// Description
// -----------
// This function returns the list of objects for the module.
//
// Arguments
// ---------
//
function qruqsp_aprs_objects(&$q) {
    //
    // Build the objects
    //
    $objects = array();
    $objects['entry'] = array(
        'name'=>'APRS Entry',
        'o_name'=>'entry',
        'o_container'=>'entries',
        'sync'=>'yes',
        'table'=>'qruqsp_aprs_entries',
        'fields'=>array(
            'decoder'=>array('name'=>'Decoder'),
            'channel'=>array('name'=>'Channel', 'default'=>'0'),
            'utc_of_traffic'=>array('name'=>'Time'),
            'from_call_sign'=>array('name'=>'From Call Sign', 'default'=>''),
            'from_call_suffix'=>array('name'=>'From Call Suffix', 'default'=>''),
            'heard_call_sign'=>array('name'=>'Heard Call Sign', 'default'=>''),
            'heard_call_suffix'=>array('name'=>'Heard Call Suffix', 'default'=>''),
            'level'=>array('name'=>'Level', 'default'=>''),
            'error'=>array('name'=>'Error', 'default'=>''),
            'dti'=>array('name'=>'DTI', 'default'=>''),
            'name'=>array('name'=>'Name', 'default'=>''),
            'symbol'=>array('name'=>'Symbol', 'default'=>''),
            'latitude'=>array('name'=>'Latitude', 'default'=>''),
            'longitude'=>array('name'=>'Longitude', 'default'=>''),
            'speed'=>array('name'=>'Speed', 'default'=>''),
            'course'=>array('name'=>'Course', 'default'=>''),
            'altitude'=>array('name'=>'Altitude', 'default'=>''),
            'frequency'=>array('name'=>'Frequency', 'default'=>''),
            'offset'=>array('name'=>'Offset', 'default'=>''),
            'tone'=>array('name'=>'Tone', 'default'=>''),
            'system'=>array('name'=>'System', 'default'=>''),
            'status'=>array('name'=>'Status', 'default'=>''),
            'telemetry'=>array('name'=>'Telemetry', 'default'=>''),
            'comment'=>array('name'=>'Comment', 'default'=>''),
            ),
        'history_table'=>'qruqsp_aprs_history',
        );
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
