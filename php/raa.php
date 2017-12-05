<?php
reset ($_REQUEST);
while (list ($key, $val) = each ($_REQUEST)) {
  if( !is_array( $val)) {
    ${$key} = trim($val);
  }
 }


if( isset($sub) and strlen($sub)>10) {
    echo file_get_contents( "https://lsched.ls-cat.org/raa?sub=".$sub);
}

if( isset($uns) and strlen($uns)>10) {
    echo file_get_contents( "https://lsched.ls-cat.org/raa?uns=".$uns);
}


echo file_get_contents( "https://lsched.ls-cat.org/raa");


