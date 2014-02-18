<?php

$logfile = '/insert/path/to/log/log.txt';
$fp = fopen($logfile,"r");

while(!feof($fp)) {			  
   list($date,$time,$ip,$ref) = fscanf($fp, "%s %s %s %s");
   $host = gethostbyaddr($ip);
   echo $date."   ".$host."   ".$ref."\n";
}
fclose($fp);

?>
