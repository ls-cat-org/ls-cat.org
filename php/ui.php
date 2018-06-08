<?php

header( "Content-type: application/xml");


if( !isset( $_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
  if( !($_SERVER["REMOTE_ADDR"] == "164.54.252.10" ) && $_SERVER["HTTP_X_FORWARDED_SERVER"] == "rmt.ls-cat.org") {
    $rtn = '<ui success="false" message="Secure connection required." />';
    echo $rtn;
    exit();
  }
 }



reset( $_REQUEST);
$subbiePressed = 0;
while( list( $key, $val) = each( $_REQUEST)) {
  ${$key} = trim($val);

  if( strncmp( $key, "subbie", 6) == 0) {
    $subbiePressed = 1;
  }

  if( is_array( ${$key})) {
    reset( ${$key});
    while( list( $key2, $val2) = each( ${$key})) {
      ${$key}[$key2] = trim($val2);
    }
  }
 }


if( isset($subbieReset) && $subbieReset=='true') {
  $url = "http://contrabass.ls-cat.org/rstpwd.php";
  $zz = '?badge=".urlencode($badge)."&email=".urlencode($email)';
  $cr = curl_init( $url) or die('<ui success="false" message="Connection to password reset server failed" url="'.htmlspecialchars($url).'"/>');
  curl_setopt( $cr, CURLOPT_POST, true);
  curl_setopt( $cr, CURLOPT_POSTFIELDS, array( "badge" => urlencode($badge), "email" => urlencode($email), "subbieReset" => "true"));

  curl_exec($cr) or die('<ui success="false" message="Connection to password reset server failed." url="'.htmlspecialchars($url).'" error="'.curl_error( $cr).'"/>');

  curl_close($cr);
  exit();
 }


if( isset($subbieNewUid) && $subbieNewUid=='true') {
  $url = "http://contrabass.ls-cat.org/rstpwd.php";
  $cr = curl_init( $url) or die('<ui success="false" message="Connection to password reset server failed" url="'.htmlspecialchars($url).'"/>');
  curl_setopt( $cr, CURLOPT_POST, true);
  $pf =  array( "badge" => rawurlencode($badge), "email" => rawurlencode($email), "pwdold" => rawurlencode($pwdold), "ldapUid" => rawurlencode($ldapUid), "subbieNewUid" => "true");
  curl_setopt( $cr, CURLOPT_POSTFIELDS, $pf);
  curl_exec($cr) or die('<ui success="false" message="Connection to password reset server failed." url="'.htmlspecialchars($url).'" error="'.curl_error( $cr).'"/>');
  curl_close($cr);

  exit();
 }


// set up the ldap connection
//
$ldaprdn = "cn=".$badge.",dc=people,dc=ls-cat,dc=org";
$ldapconn = @ldap_connect( "ldap.ls-cat.org") or die('<ui success="false" message="ldap server down"/>');
ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

//
// log in as the user
//
$ldappass=$pwdold;
$bindok = @ldap_bind($ldapconn, $ldaprdn, $ldappass);

if( !$bindok) {
  $rtn = '<ui success="false" message="Password and/or Badge Number Incorrect" badge="'.$badge.'" pwdold="'.htmlspecialchars($pwdold).'"/>';
  echo $rtn;
  exit();
 } 

//
// Make sure the email address corresponds to this badge number
//
$qr = @ldap_search( $ldapconn, $ldaprdn, "(&(apsBadgeNo=$badge)(mail=$email))", array("uid"));
if( $qr == FALSE) {
  ldap_unbind( $ldapconn);
  $rtn = '<ui success="false" message="Email address incorrect." error="'.htmlentities(ldap_error($ldapconn)).'"/>';
  echo $rtn;
  exit();
 }

//
// Perhaps change the password
//
if( isset( $subbieChangePassword) && $subbieChangePassword == 'true') {
  if( $pwd1 != $pwd2) {
    $rtn = '<ui success="false" message="The two passwords do not agree"/>';
    echo $rtn;
    exit();
  }

  $dss = array(
	       0 => array( "pipe", "r"),
	       1 => array( "file", "/tmp/ui.stdout", "w"),
	       2 => array( "file", "/tmp/ui.stderr", "w")
	       );
  
  $cmd = "/usr/local/bin/lspw.py -b $badge -m $email";
  
  $process = proc_open( $cmd, $dss, $pipes, "/tmp", NULL);
  if( is_resource( $process)) {
    fwrite( $pipes[0], rawurldecode($pwdold)."\n");
    fwrite( $pipes[0], rawurldecode($pwd1)."\n");
    fwrite( $pipes[0], rawurldecode($pwd2)."\n");

    fclose( $pipes[0]);

    $rv = proc_close( $process);

    if( is_numeric( $rv) && $rv == 0) {
      $rtn = '<ui success="true" message="Password changed, please log in again with your new password."/>';
      echo $rtn;
      exit();
    } else {
      $rtn = '<ui success="false" message="Password change failed." dn="'.htmlentities($ldaprdn).'" error="'.htmlentities(ldap_error($ldapconn)).'"/>';
      echo $rtn;
      exit();
    }
  }
 }


	
//
// get the uid by searching for an entry with the email and badge number of the user
//
$qr = ldap_search( $ldapconn, $ldaprdn, "(&(apsBadgeNo=$badge)(mail=$email))", array("uid"));
if( $qr == FALSE) {
  ldap_unbind( $ldapconn);
  $rtn = '<ui success="false" message="Password and/or Badge Number Incorrect."/>';
  echo $rtn;
  exit();
 }
$uids = ldap_get_entries( $ldapconn, $qr);
if( $uids["count"] != 1) {
  ldap_unbind( $ldapconn);
  $rtn = '<ui success="false" message="email address incorrect."/>';
  echo $rtn;
  exit();
 }

//
// Save the return value in case there are no subbies
//
ldap_unbind( $ldapconn);
$uid = $uids[0]["uid"][0];
$rtn = '<ui success="true" userid="'.$uid.'"/>';
echo $rtn;
