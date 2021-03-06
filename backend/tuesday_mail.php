<?php

// tuesday_mail.php
// Email list of papers for discussion as of Tuesday morning

// Set path to arxivPicks database
$APHOME = "/path/to/arxivPicks";
$db_file = $APHOME."/backend/arxivPicks.db";

// Determine if script is being called at the right time
date_default_timezone_set('America/Los_Angeles');
$diff_lim = 5.0; // max minutes away from goal_time
$goal_time = strtotime('10:15:00');
$goal_day = "Tuesday";

$day = date("l");
$now = time();
$diff = abs(($now-$goal_time)/60.); //time difference in minutes

//Send email if called near the desired cron time
if (($day == $goal_day) && ($diff < $diff_lim)) {

   $body = "Greetings humans,\n\nArxiv Discussion will take place today at 11:15am in the lounge.";

   // Open database object
   $db = new SQLiteDatabase($db_file) or die("Could not open database");

   $query = "SELECT * FROM articles WHERE date > DATETIME('NOW','LOCALTIME','WEEKDAY 4','-7 DAYS','START OF DAY','+12 HOURS') ORDER BY count DESC, date DESC";
   $result = $db->query($query) or die("Error retrieving articles");

   if($result->numRows() > 0) {

      $body .= " The following paper(s) have been added to the queue since Thursday at noon:\n\n";

      while($row = $result->fetch()) {
	$body .= $row['uri_abs']."\n";
	$body .= "Title: ".$row['title']."\n";
	$body .= "Authors: ".$row['authors']."\n";
	$body .= $row['abstract']."\n\n";
      }
   }
   else {
      $body .= " No articles have been added to the queue since Thursday at noon, so go read some!\n";
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
  echo "Mail not sent: tuesday_mail.php called at the wrong time";
}

?>
