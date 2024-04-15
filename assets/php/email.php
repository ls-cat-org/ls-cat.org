<?php

$addrs = array(
       "a-mondragon@northwestern.edu",
       "",
       "",
       "spencer-anderson@northwestern.edu",
       "m-bolbat@northwestern.edu",
       "j-brunzelle@northwestern.edu",
       "monica.green@northwestern.edu",
       "focia@northwestern.edu",
       "z-wawrzak@northwestern.edu",
       "j-vonosinski@northwestern.edu",
       "",
       "jory.folker@northwestern.edu",
       "rong.huang@northwestern.edu"
       );


$string = $addrs[trim($_REQUEST["addr"])];

header("content-type: image/png");

$im     = imagecreate( 340, 20);
$black  = imagecolorallocate($im, 0, 0, 0);
$white  = imagecolorallocate($im, 238, 238, 238);
imagefill($im, 0, 0, $white);
$px     = 5;
imagestring($im, 5, $px, 2, $string, $black);
imagepng($im);
imagedestroy($im);
?>
