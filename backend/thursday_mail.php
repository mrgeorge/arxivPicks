<?php

// Determine if this is being called at the right time
date_default_timezone_set('America/Los_Angeles');
$diff_lim = 5.0; // max minutes away from goal_time
$goal_time = strtotime('10:15:00');
$goal_day = "Thursday";

$day = date("l");
$now = time();
$diff = abs(($now-$goal_time)/60.); //time difference in minutes

//Send email if it's called near the desired cron time
// (this avoids sending mail if it's run from the CL at another time)
if (($day == $goal_day) && ($diff < $diff_lim)) {

   $body = "Greetings humans,\n\nArxiv Discussion will take place today at 11:15am in the lounge.";

   // set path of database file
   $db_file = "/insert/path/to/database/arxiv.db";
   // open database object
   $db = new SQLiteDatabase($db_file) or die("Could not open database");

   $query = "SELECT * FROM articles WHERE date > DATETIME('NOW','LOCALTIME','WEEKDAY 2','-7 DAYS','START OF DAY','+12 HOURS') ORDER BY count DESC, date DESC";
   $result = $db->query($query) or die("Error retrieving articles");

   if($result->numRows() > 0) {

      $body .= " The following paper(s) have been added to the queue since Tuesday at noon:\n\n";

      while($row = $result->fetch()) {
	$body .= $row['uri_abs']."\n";
	$body .= "Title: ".$row['title']."\n";
	$body .= "Authors: ".$row['authors']."\n";
	$body .= $row['abstract']."\n\n";
      }
   }
   else {
      $body .= " No articles have been added to the queue since Tuesday at noon, so go read some!\n";
   }

   unset ($db);

   $body .= "Cheers,\nCron\n\nEnd transmission.";

   $to = "everyone@example.com";
   $subject = "arxiv discussion";
   $headers = "From: arxiv_bot\n";

   if (mail($to, $subject, $body, $headers)) {
      echo ("sent");
   }
   else {
      echo("failed");
   }
}

else {
  echo "Mail not sent: thursday_mail.php called at the wrong time";
}

?>
