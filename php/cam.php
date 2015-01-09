<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past


$allowedCams = array(
		      1 => array( "name" => "01", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-B Ceiling",             "interface" => "staff"),
		      2 => array( "name" => "02", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 210A",    "location" => "21-ID-C Roof",                "interface" => "staff"),
		      3 => array( "name" => "03", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 221",     "location" => "21-ID-C Kohzu Beam Monitor",  "interface" => "staff"),
		      4 => array( "name" => "04", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 221",     "location" => "21-ID Roaming #4",            "interface" => "staff"),
		      5 => array( "name" => "05", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 221",     "location" => "21-ID Roaming #5",            "interface" => "staff"),
		      6 => array( "name" => "06", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 221",     "location" => "21-ID-C Kohzu Temperatures",  "interface" => "staff"),
		      7 => array( "name" => "07", "sub" => "1",    "size" => "704x576", "half" => "352x288", "quarter" => "176x144", "eighth" => "88x72", "type"=>"AXIS 241Q",    "location" => "21-ID-G Sample",              "interface" => "g"),
		      8 => array( "name" => "07", "sub" => "2",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-G Overview",            "interface" => "g"),
		      9 => array( "name" => "07", "sub" => "3",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-G Users",               "interface" => "g"),
		     10 => array( "name" => "07", "sub" => "4",    "size" => "704x576", "half" => "352x288", "quarter" => "176x144", "eighth" => "88x72", "type"=>"AXIS 241Q",    "location" => "21-ID-G Screen",              "interface" => "g"),
		     11 => array( "name" => "07", "sub" => "quad", "size" => "704x576", "half" => "352x288", "quarter" => "176x144", "eighth" => "88x72", "type"=>"AXIS 241Q",    "location" => "21-ID-G Quad",                "interface" => "g"),
		     12 => array( "name" => "08", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-D Ceiling",             "interface" => "d"),
		     13 => array( "name" => "09", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 210A",    "location" => "21-ID-B F Mono",              "interface" => "staff"),
		     14 => array( "name" => "10", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 210A",    "location" => "21-ID-B F Upstream of Lens",   "interface" => "staff"),
		     15 => array( "name" => "11", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 210A",    "location" => "21-ID-B G Mono",              "interface" => "staff"),
		     16 => array( "name" => "12", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 210A",    "location" => "21-ID-B G Upstream of Lens",   "interface" => "staff"),
		     17 => array( "name" => "13", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 221",     "location" => "21-ID Roaming #13",           "interface" => "staff"),
		     18 => array( "name" => "14", "sub" => "1",    "size" => "640x480", "half" => "320x240", "quarter" => "160x120", "eighth" => "80x60", "type"=>"AXIS 221",     "location" => "21-ID Roaming #14",           "interface" => "staff"),
		     19 => array( "name" => "15", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-F Ceiling",             "interface" => "f"),
		     20 => array( "name" => "16", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-G Ceiling",             "interface" => "g"),
		     21 => array( "name" => "17", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-E Ceiling",             "interface" => "e"),
		     22 => array( "name" => "18", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-E Robot",               "interface" => "e"),
		     23 => array( "name" => "19", "sub" => "1",    "size" => "704x576", "half" => "352x288", "quarter" => "176x144", "eighth" => "88x72", "type"=>"AXIS 241Q",    "location" => "21-ID-E Sample",              "interface" => "e"),
		     24 => array( "name" => "19", "sub" => "2",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-E Overview",            "interface" => "e"),
		     25 => array( "name" => "19", "sub" => "3",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-E Users",               "interface" => "e"),
		     26 => array( "name" => "19", "sub" => "4",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-E Screen",              "interface" => "e"),
		     27 => array( "name" => "19", "sub" => "quad", "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-E Quad",                "interface" => "e"),
		     28 => array( "name" => "20", "sub" => "1",    "size" => "704x576", "half" => "352x288", "quarter" => "176x144", "eighth" => "88x72", "type"=>"AXIS 241Q",    "location" => "21-ID-F Sample",              "interface" => "f"),
		     29 => array( "name" => "20", "sub" => "2",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-F Overview",            "interface" => "f"),
		     30 => array( "name" => "20", "sub" => "3",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-F Users",               "interface" => "f"),
		     31 => array( "name" => "20", "sub" => "4",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-F Screen",              "interface" => "f"),
		     32 => array( "name" => "20", "sub" => "quad", "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-F Quad",                "interface" => "f"),
		     33 => array( "name" => "21", "sub" => "1",    "size" => "704x576", "half" => "352x288", "quarter" => "176x144", "eighth" => "88x72", "type"=>"AXIS 241Q",    "location" => "21-ID-D Sample",              "interface" => "d"),
		     34 => array( "name" => "21", "sub" => "2",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-D Overview",            "interface" => "d"),
		     35 => array( "name" => "21", "sub" => "3",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-D Users",               "interface" => "d"),
		     36 => array( "name" => "21", "sub" => "4",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-D Screen",              "interface" => "d"),
		     37 => array( "name" => "21", "sub" => "quad", "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 241Q",    "location" => "21-ID-D Quad",                "interface" => "d"),
		     38 => array( "name" => "22", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "Conference Room",             "interface" => "conference"),
		     38 => array( "name" => "23", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-G Robot",               "interface" => "g"),
		     39 => array( "name" => "24", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-F Robot",               "interface" => "f"),
		     40 => array( "name" => "25", "sub" => "1",    "size" => "704x480", "half" => "352x240", "quarter" => "176x120", "eighth" => "88x60", "type"=>"AXIS 214 PTZ", "location" => "21-ID-D Robot",               "interface" => "d")
		     );

function toXml( $a) {
  $rtn = '<?xml version="1.0"?>'."\n";
  $rtn .= "<cams>\n";
  foreach( $a as $v) {
    $rtn .= '<cam name="'.$v["name"].'" sub="'.$v["sub"].'" size="'.$v["size"].'" half="'.$v["half"].'" quarter="'.$v["quarter"].'" eighth="'.$v["eighth"].'" type="'.$v["type"].'" location="'.$v["location"].'" interface="'.$v["interface"].'" />'."\n";
  }
  $rtn .= "</cams>\n";
  return $rtn;
}


if( isset( $_REQUEST["xml"])) {
  header( "Content-Type: application/xml");
  echo( toXml($allowedCams));
  exit();
 }

if( !isset( $_REQUEST["sub"])) {
  $sub = "1";
 } else {
  $sub = $_REQUEST["sub"];
 }



if( !isset( $_REQUEST["size"])) {
  $size = "704x480";
 } else {
  $size = $_REQUEST["size"];
 }


if( !isset( $_REQUEST["name"])) {
  $name = "01";
 } else {
  $name = $_REQUEST["name"];
 }

/*
if( $name == "21" && ($sub == "1" || $sub == "2" || $sub == "3")) {
  header( "Content-Type: video/x-flv");
  $url = "http://ls-cat.org/cam21-".$sub.".flv";
  $h = fopen( $url, "r");

  while( !feof($h)) {
    $s = fgets( $h, 8192);
    echo $s;
  }
  fclose( $h);
  exit();
 }
*/

header( "Content-Type: video/x-flv");

$zz = "/usr/local/bin/ffmpeg -i rtsp://cam".$name.".ls-cat.org/mpeg4/".$sub."/media.amp  -r 4 -s ".$size." -an -f flv -bufsize 0 -sameq - 2>/dev/null";
passthru( $zz);

?>
