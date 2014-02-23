<?php

// Set path to arxivPicks files
$APHOME = "/path/to/arxivPicks"
$db_file = $APHOME."/backend/arxivPicks.db";
$logfile = $APHOME."/website/log.txt";

// Log IP address
$IP = $_SERVER['REMOTE_ADDR'];
$REF = $_SERVER['HTTP_REFERER'];
date_default_timezone_set('America/Los_Angeles');
$date_time = date('Y-m-d H:i:s');
$day_of_week = date("l");
$fp = fopen($logfile, "a");
fputs($fp, "$date_time $IP $REF\n");
fclose($fp);

// Write html for display
echo "<html>\n";
echo "<head>\n";
echo "<title>arXiv Picks</title>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"arxiv.css\" />\n";
echo "</head>\n\n";

echo "<body>\n";
echo "<div id=\"header\">\n";
echo "<h1>arXiv Picks</h1>\n";
echo "</div>\n";
echo "<div id=\"content\"><div id=\"abs\"><div class=\"subheader\">\n";
echo "<h1><a href=\"http://arxiv.org/list/astro-ph/new?skip=0&show=250\">astro-ph</a> | <a href=\"old.php\">Old papers</a></h1>\n";
echo "</div></div>\n";
echo "<div id=\"dlpage\">\n";

// Open database object
$db = new SQLiteDatabase($db_file) or die("Could not open database");

// Design database query depending on date
if($day_of_week == "Wednesday" || $day_of_week == "Thursday") {
   echo "<br><div class=\"list-dateline\">Submissions received since Tuesday at noon</div>\n";
   $query = "SELECT * FROM articles WHERE date > DATETIME('NOW','LOCALTIME','WEEKDAY 2','-7 DAYS','START OF DAY','+12 HOURS') ORDER BY count DESC, date DESC";
}
else {
   echo "<br><div class=\"list-dateline\">Submissions received since Thursday at noon</div>\n";
   $query = "SELECT * FROM articles WHERE date > DATETIME('NOW','LOCALTIME','WEEKDAY 4','-7 DAYS','START OF DAY','+12 HOURS') ORDER BY count DESC, date DESC";
}

// Execute query
$result = $db->query($query) or die("Error retrieving articles");

// Display results
if($result->numRows() > 0) {
    echo "<dl>\n";
    while($row = $result->fetch()) {
	echo "<dt>[".$row['count']."]&nbsp;   <span class=\"list-identifier\"><a href=".$row['uri_abs']." title=\"Abstract\">".$row['uri_abs']."</a> [<a href=".$row['uri_pdf']." title=\"Download PDF\">pdf</a>]</span></dt>\n";
	echo "<dd>\n";
	echo "<div class=\"meta\">\n\n";

	echo "<div class=\"list-title\">\n";
	echo "<span class=\"descriptor\">Title:</span>".$row['title']."\n";
	echo "</div>\n";
	echo "<div class=\"list-authors\">\n";
	echo "<span class=\"descriptor\">Authors:</span>\n";
	echo $row['authors'];
	echo "</div>\n\n";

	echo "<p>".$row['abstract']."</p>\n";
	echo "</div>\n";
	echo "</dd>\n\n\n";
    }
    echo "</dl>\n";
}
else {
    echo "No articles";
}

// Close page and database
echo "</div></div>\n";

unset ($db);

echo "</body>\n";
echo "</html>";

?>
