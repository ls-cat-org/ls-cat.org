<?php
  // sync_from_aps.php
  //
  // Script to support synchornizing the APS and LS-CAT calendars
  // (C) 2011 by Keith Brister
  // All rights reserved.
  //


$boundary="bf9946a7f753105788636cc73fa8a10f";


//
// sendStatus
//
// progress:   0 to 100 percent complete
// message:    optional message that may or may not be displayed to the user
// label:      optional label to display on the progress bar (if any).  Default is progress% Complete.
// done:       Boolean: false implies a future update, true will likely make the client stop listening
// error:      Possibly send the error message as an alert and stop the linstener.
//
  
function sendStatus( $progress, $message="",  $label=null, $done=false, $error=null) {
  global $boundary;

  if( $label == null) {
    if( $message != null)
      $label = $progress . "% " . $message;
    else
      $label = $progress . "%";
  }

  $s = '<status>';
  $s .= '<progress current="'.$progress.'" maximum="100"><![CDATA['.rawurlencode($label).']]></progress>';
  if( $message != "") {
    $s .= '<message><![CDATA['.rawurlencode($message).']]></message>';
  }
  if( $error != null) {
    $s .= '<error><![CDATA['.rawurlencode($error).']]></error>';
  }
  $s .= '</status>';
  $l = strlen($s);
  echo "Content-Type: application/xml\r\n";
  echo "Content-Length: ".$l."\r\n\r\n";
  echo $s;

  if( $done) {
    echo "\r\n--".$boundary."--\r\n";
  } else {
    echo "\r\n--".$boundary."\r\n";
  }
  flush();
}


//
// Require a secure conneciton
//
if( !isset( $_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
  sendStatus( 0, "Secure connection not found.", "Error: https connection needed.", true, "Secure connection required.");
  exit();
 }



reset ($_REQUEST);
while (list ($key, $val) = each ($_REQUEST)) {
  if( !is_array( $val)) {
    if( get_magic_quotes_gpc()) {
      ${$key} = stripslashes(trim($val));
    } else {
      ${$key} = trim($val);
    }
  } else {
    ${$key} = array();
    reset( $_REQUEST[$key]);
    while( list( $key2, $val2) = each( $_REQUEST[$key])) {
      if( get_magic_quotes_gpc()) {
	${$key}[$key2] = stripslashes(trim($val2));
      } else {
	${$key}[$key2] = trim($val2);
      }
    }
  }
}

if( !isset($theUserName)) {
  $theUsername = "<<None>>";
 }


if( !isset($thePassword)) {
  $thePassword = "<<None>>";
 }

if( !isset($theRun)) {
  $theRun = "2017-1";
 }

header( 'Expires: Mon, 18 Apr 2005 17:30:00 GMT');
header( 'Cache-Control: no-store');
header( 'Pragma: public');
header('Content-Type: multipart/mixed; boundary="'.$boundary.'"');

//
// Can't put this in the real header since the browser will eat it up and
// not pass it on to our parser.
//
echo 'Content-Type: multipart/mixed; boundary="'.$boundary.'"'."\r\n\r\n";
echo "LS-CAT update stream version 1.0\r\n";
echo "(C) 2012 by Keith Brister\r\n";
echo "All rights reserved.\r\n";
echo "--".$boundary."\r\n";
flush();


$l = ldap_connect( "ldap://ldap.ls-cat.org");
$lsearch = ldap_search( $l, "dc=people,dc=ls-cat,dc=org","uid=".$theUserName, array("apsBadgeNo"));
$rslt = ldap_get_entries( $l, $lsearch);

if( $rslt["count"] != 1 || $rslt[0]["apsbadgeno"]["count"] != 1) {
  sendStatus( -1, "count: ".$rslt["count"], "Bad Username Or Password", true, "Badge Number Not Found");
  exit();
 }


$apsUserName = $rslt[0]["apsbadgeno"][0];
if( strlen( $apsUserName) <= 4) {
  sendStatus( -1, "Badge Number Not Found", "Error", true, "Bad Badge Number: ".$apsUserName);
  exit();
 }

$poio = array(
		 0 => array("pipe", "r"),
		 1 => array("pipe", "w"),
		 2 => array("file", "/tmp/sync_from_aps.stderr", "a")
		 );
$cwd = "/tmp";
$env = array(
	     "theUserName" => $apsUserName,
	     "thePassword" => $thePassword,
	     "theRun"      => $theRun
	     );

sendStatus( 1, "", "Starting recvFromAPS...");

$po = proc_open( "/var/www/ls-cat.org/php/sync/recvFromAPS.py", $poio, $popipes, $cwd, $env);

if( !is_resource($po)) {
  sendStatus( 0, "", "Error", true, "Could not run recvFromAPS.");
  exit();
 }

fclose($popipes[0]);	// close off stdin: we have nothing to say.

$pgrs = 10;
while( ($s = fgets($popipes[1], 8192)) !== false) {
  $sa = explode( " ", trim($s), 2);
  
  $tmp = (int)$sa[0];

  if( count($sa) > 1) {
    $msg = trim($sa[1]);
  } else {
    $msg = "";
  }

  if( $tmp == -1) {
    sendStatus( 100, "Error", "Error", true, "Stopping: ".$msg);
    exit();
  }

  $pgrs = (int)(10 + 0.4 * $tmp);
  sendStatus( $pgrs, $msg);
 }

fclose( $popipes[1]);


//
// Now do the same for xmit2aps
//
$poio = array(
		 0 => array("pipe", "r"),
		 1 => array("pipe", "w"),
		 2 => array("file", "/tmp/sync_from_aps.stderr", "a")
		 );
$cwd = "/tmp";
$env = array(
	     "theUserName" => $apsUserName,
	     "thePassword" => $thePassword,
	     "theRun"      => $theRun
	     );

sendStatus( 50, "", "Starting xmit2aps...");

$po = proc_open( "/var/www/ls-cat.org/php/sync/xmit2APS.py", $poio, $popipes, $cwd, $env);

if( !is_resource($po)) {
  sendStatus( 0, "", "Error", true, "Could not run recvFromAPS.");
  exit();
 }

fclose($popipes[0]);	// close off stdin: we have nothing to say.

$pgrs = 60;
while( ($s = fgets($popipes[1], 8192)) !== false) {
  $sa = explode( " ", trim($s), 2);
  
  $tmp = (int)$sa[0];

  if( count($sa) > 1) {
    $msg = trim($sa[1]);
  } else {
    $msg = "";
  }

  if( $tmp == -1) {
    sendStatus( 100, "Error", "Error", true, "Stopping: ".$msg);
    exit();
  }

  $pgrs = (int)(60 + 0.4 * $tmp);
  sendStatus( $pgrs, $msg);
 }

fclose( $popipes[1]);

sendStatus( 100, "", "Done", true);
