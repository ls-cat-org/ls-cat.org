<?php
reset ($_REQUEST);
while (list ($key, $val) = each ($_REQUEST)) {
  if( !is_array( $val)) {
    ${$key} = trim($val);
  }
 }

class Q {
  protected $pg;

  function __construct( $dbname, $user) {
    $pg = pg_connect( "host=contrabass.ls-cat.org dbname=".$dbname." user=".$user);
    if( !$pg) {
      echo "Error connecting to database<br>";
      echo pg_last_error();
      exit();
    }
    $this->pg = $pg;
  }

  function query( $qs) {
    $qr = pg_query( $this->pg, $qs);
    if( !$qr) {
      echo "Query failed<br>";
      echo $qs."<br>\n";
      echo pg_last_error( $this->pg);
      exit();
    }
    return( $qr);
  }
}

header( "Content-type: Application/xml");

$q = new Q( "ls", "lsuser");
$qr = $q->query( "select rmt.screeningdates() as sd");
$r = pg_fetch_object( $qr);
echo $r->sd;
