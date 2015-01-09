<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

system("/usr/local/bin/ffmpeg -i rtsp://cam".$_REQUEST["no"].".ls-cat.org/mpeg4/media.amp -er 4 -r 1 -s 176x120 -an -f flv -");

?>
