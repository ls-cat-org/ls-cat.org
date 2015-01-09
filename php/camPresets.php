<?php

//listFormPreset
header( "Content-type: application/xml");


$zz = "/usr/bin/curl http://cam01.ls-cat.org/view/view.shtml | /usr/bin/xmllint --html --xmlout -";
system($zz);

