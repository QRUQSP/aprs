#!/opt/local/bin/php56
<?php

//
// Setup q array
//
$rc = qInit();
if( $rc['stat'] != 'ok' ) {
    print_r($rc['err']);
    exit;
}
$q = $rc['q'];

//
// Watch the standard input
//
while($f = fgets(STDIN)) {
    // chan,utime,isotime,source,heard,level,error,dti,name,symbol,latitude,longitude,speed,course,altitude,frequency,offset,tone,system,status,comment
    // 0,1488664770,2017-03-04T21:59:30Z,W7KYG,W7KYG,32(11/12),0,`,W7KYG,/[,32.757167,-97.133833,0.0,149.0,146.0,,,,Yaesu VX-8,Off Duty,,W7KYG testing qruqsp.org direwolf skimmer message #1
    // 0,1488664792,2017-03-04T21:59:52Z,W7KYG,W7KYG,32(9/10),0,`,W7KYG,/[,32.757333,-97.133667,0.0,88.0,175.0,,,,Yaesu VX-8,Off Duty,,W7KYG testing qruqsp.org direwolf skimmer message #1
    
    $line = str_getcsv($f);

    if( $line[0] == 'chan' ) {
        continue;
    }
    $entry = array(
        'decoder'=>'direwolf',
        'channel'=>$line[0],
        'utc_of_traffic'=>$line[2],
        'from_call_sign'=>$line[3],
        'heard_call_sign'=>$line[4],
        'level'=>$line[5],
        'error'=>$line[6],
        'dti'=>$line[7],
        'name'=>$line[8],
        'symbal'=>$line[9],
        'latitude'=>$line[10],
        'longitude'=>$line[11],
        'speed'=>$line[12],
        'course'=>$line[13],
        'altitude'=>$line[14],
        'frequency'=>$line[15],
        'offset'=>$line[16],
        'tone'=>$line[17],
        'system'=>$line[18],
        'status'=>$line[19],
        'telemetry'=>$line[20],
        'comment'=>$line[21],
        );
    $rc = qAPI($q, 'qruqsp.aprs.entryAdd', array(), $entry);
    if( $rc['stat'] != 'ok' ) {
        print_r($rc);
    }
}

exit;


function qInit() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, false);

    //
    // Setup the Q variable to store api info. This is stored in curlapi so that $q can
    // be used the same was a $q variable in other qruqsp code.
    //
    $q = array(
        'curlapi'=>array(
            'ch'=>$ch,
            'url'=>'http://qruqsp.local/qruqsp-json.php',
            'apikey'=>'36126f448916a9253caa1a74fa726144',
            'token'=>'',
            'username'=>'andrew',
            'password'=>'veggie12',
            'station_id'=>1,
        ));

    //
    // Authenticate with the server
    //
    $rc = qAuth($q);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    
    return array('stat'=>'ok', 'q'=>$q);
}

function qAuth(&$q) {
    $request_url = $q['curlapi']['url'] . "?method=qruqsp.core.auth";
    $request_url .= '&api_key=' . $q['curlapi']['apikey'];
    $request_url .= '&auth_token=' . $q['curlapi']['token'];
    $request_url .= '&format=json';
    curl_setopt($q['curlapi']['ch'], CURLOPT_URL, $request_url);
    $post_content = "username=" . rawurlencode($q['curlapi']['username']) . "&password=" . rawurlencode($q['curlapi']['password']);
    curl_setopt($q['curlapi']['ch'], CURLOPT_POST, false);
    curl_setopt($q['curlapi']['ch'], CURLOPT_POSTFIELDS, $post_content);
    $rsp = curl_exec($q['curlapi']['ch']);
//  curl_close($q['curlapi']['ch']);
    if( $rsp === false ) {
        print "Error: " . curl_error($q['curlapi']['ch']) . "\n";
        exit;
    }
    $rc = json_decode($rsp, true);
    if( !isset($rc['stat']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.1', 'msg'=>'Invalid response from server', 'pmsg'=>$rsp));
    }
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['auth']['token']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.aprs.2', 'msg'=>'No token returned from server'));
    }
    $q['curlapi']['token'] = $rc['auth']['token'];

    return array('stat'=>'ok');
}

function qAPI(&$q, $method, $args, $post_content='') {
    //
    // Build the request URL
    //
    $request_url = $q['curlapi']['url'] . '?method=' . $method;
    $request_url .= '&api_key=' . $q['curlapi']['apikey'];
    $request_url .= '&auth_token=' . $q['curlapi']['token'];
    $request_url .= '&station_id=' . $q['curlapi']['station_id'];
    foreach($args as $arg=>$val) {
        $request_url .= "&$arg=" . rawurlencode($val);
    }
    $request_url .= '&format=json';
    curl_setopt($q['curlapi']['ch'], CURLOPT_URL, $request_url);
    curl_setopt($q['curlapi']['ch'], CURLOPT_POST, false);
    if( is_array($post_content) ) {
        $content = '';
        foreach($post_content as $arg=>$val) {
            $content .= '&' . rawurlencode($arg) . '=' . rawurlencode($val);
        }
        curl_setopt($q['curlapi']['ch'], CURLOPT_POSTFIELDS, $content);
    } elseif( $post_content != '' ) {
        curl_setopt($q['curlapi']['ch'], CURLOPT_POSTFIELDS, $post_content);
    }
    $rsp = curl_exec($q['curlapi']['ch']);
    if( $rsp === false ) {
        print "Error: " . curl_error($q['curlapi']['ch']) . "\n";
        exit;
    }
    $rc = json_decode($rsp, true);
    if( !isset($rc['stat']) ) {
        print "Error: Invalid response\n";
        print $rsp . "\n";
        exit;
    }

    return $rc;
}

/*
function prompt_silent($prompt = "Enter Password:") {
  if (preg_match('/^win/i', PHP_OS)) {
    $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
    file_put_contents(
      $vbscript, 'wscript.echo(InputBox("'
      . addslashes($prompt)
      . '", "", "password here"))');
    $command = "cscript //nologo " . escapeshellarg($vbscript);
    $password = rtrim(shell_exec($command));
    unlink($vbscript);
    return $password;
  } else {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
      trigger_error("Can't invoke bash");
      return;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \""
      . addslashes($prompt)
      . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
  }
}
*/

function print_usage() {
    print "Usage: FIXME\n";
}

?>
