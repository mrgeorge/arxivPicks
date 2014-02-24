<?php

// safari.php
// Receive GET requests from arxivPicks for Safari,
// Grab paper metadata from arxiv.org API
// Update arxivPicks database with paper information.
// Note: only difference from arxiv.php is POST->GET

// Set path to arxivPicks database
$APHOME = "/path/to/arxivPicks";
$db_file = $APHOME."/backend/arxivPicks.db";

include_once("php/simplepie.inc");

// Open database object
$db = new SQLiteDatabase($db_file) or die("Could not open database");

// Get ID for bookmark table
$arxiv_id = sqlite_escape_string($_GET['id']);

// Check if the arxiv_id has been bookmarked and update or add entry
$first_row_only = TRUE;
$result = $db->singlequery("SELECT count FROM articles WHERE arxiv_id = ".$arxiv_id, $first_row_only);

if($result) {
    $query = "UPDATE articles SET count = count+1, date = DATETIME('NOW','LOCALTIME') WHERE arxiv_id = ".$arxiv_id;
}

else {
    $count = 1;

    // Get article info from arxiv and add to table
    // See example arxiv.org API query at
    // http://export.arxiv.org/api_help/docs/examples/php_arXiv_parsing_example.txt

    // Base api query url
    $base_url = 'http://export.arxiv.org/api/query?';

    // Search parameters
    $id_list = $arxiv_id;
    $max_results = 1;

    // Construct the query with the search parameters
    $api_query = "id_list=".$id_list."&max_results=".$max_results;

    $feed = new SimplePie();
    $feed->set_feed_url($base_url.$api_query);
    $feed->enable_cache(false);
    $feed->init();
    $feed->handle_content_type();

    $item = $feed->get_item(0);

    // Use these namespaces to retrieve tags
    $atom_ns = 'http://www.w3.org/2005/Atom';
    $opensearch_ns = 'http://a9.com/-/spec/opensearch/1.1/';
    $arxiv_ns = 'http://arxiv.org/schemas/atom';

    $title = sqlite_escape_string($item->get_title());

    // Get the links to the abs page and pdf for this e-print
    foreach ($item->get_item_tags($atom_ns,'link') as $link) {
        if ($link['attribs']['']['rel'] == 'alternate') {
            $uri_abs = $link['attribs']['']['href'];
        } elseif ($link['attribs']['']['title'] == 'pdf') {
            $uri_pdf = $link['attribs']['']['href'];
        }
    }
    // Gather a list of authors and affiliation
    // This is a little complicated due to the fact that the author
    // affiliations are in the arxiv namespace (if present)
    // Manually getting author information using get_item_tags
    $authors = array();
    foreach ($item->get_item_tags($atom_ns,'author') as $author) {
        $name = $author['child'][$atom_ns]['name'][0]['data'];
	$affils = array();
        
	// If affiliations are present, grab them
	if ($author['child'][$arxiv_ns]['affiliation']) {
            foreach ($author['child'][$arxiv_ns]['affiliation'] as $affil) {
                array_push($affils,$affil['data']);
            }
	    if ($affils) {
                $affil_string = join(', ',$affils);
            	$name = $name." (".$affil_string.")";
            }
	}
    	array_push($authors,$name);
    }
    $author_string = sqlite_escape_string(join(', ',$authors));
    $abstract = sqlite_escape_string($item->get_description());

    // Now put the article information into the database
    $query = "INSERT INTO articles (date, count, arxiv_id, uri_abs, uri_pdf, title, authors, abstract) VALUES (DATETIME('NOW','LOCALTIME'), ".$count.", \"".$arxiv_id."\", \"".$uri_abs."\", \"".$uri_pdf."\", \"".$title."\", \"".$author_string."\", \"".$abstract."\")";
}

// Finally run the INSERT or UPDATE query
$result = $db->query($query) or die("Error in query");

// close database file
unset($db);

?> 
