<?php

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

//phpinfo();

$doc = new DOMDocument();
$xsl = new XSLTProcessor();

$edoc = new DOMDocument();
$exsl = new XSLTProcessor();



$doc->load( "/var/www/ls-cat.org/xsl/acmApp.xsl");
$xsl->importStyleSheet( $doc);

$edoc->load( "/var/www/ls-cat.org/xsl/acmAppEmail.xsl");
$exsl->importStyleSheet( $edoc);


$dom = new DOMDocument();
$dom->load( "/var/www/ls-cat.org/xml/acmApp.xml");


$top = $dom->getElementsByTagName( "acmApp")->item(0);
$nl = $top->childNodes;

for( $i=0; $i < $nl->length; $i++) {
  $n = $nl->item($i);
  if( $n->nodeName == "#text") {
    continue;
  }
  if( isset( ${$n->nodeName})) {
    $tn = $dom->createTextNode( ${$n->nodeName});
    $n->appendChild( $tn);
  }
 }



echo $xsl->transformToXML( $dom);

if( isset( $_REQUEST["subbieInput"])) {
  mail( "k-brister@northwestern.edu", "acm Registration", $exsl->transformToXML( $dom), "From: kebrister+acmRegistration@ls-cat.org");
 }
