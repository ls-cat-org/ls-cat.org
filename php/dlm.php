<?php
$s = "Here I am";
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Content-Type: application/x-ls-cat");
header("Content-Disposition: attachment; filename: download-101220.ls-cat");
header("Content-Length: "+strlen($s));
echo $s;

