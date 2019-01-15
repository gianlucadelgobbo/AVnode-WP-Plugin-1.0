<?
/* REWRITE URLS */
$url = explode("/", $_SERVER['REQUEST_URI']);
$slug = $url[1];

$avnode_events_options = get_option('avnode_events');
$folders_array = explode(",",$avnode_events_options['folders_array']);
$events_array = explode(",",$avnode_events_options['events_array']);
/*
$GLOBALS['rewriteSez'] = array();
foreach($folders_array as $sez) {
	$GLOBALS['rewriteSez'][] = $sez;
}
//print_r($GLOBALS['rewriteSez']);
*/
$GLOBALS['rewriteEvents'] = array();
foreach($events_array as $sez) {
	foreach($folders_array as $folder) {
		$GLOBALS['rewriteEvents'][] = $sez."/".$folder;
	}
}
//print_r($GLOBALS['rewriteEvents']);


// Adding a new rule
function my_insert_rewrite_rules( $rules ) {
	$newrules = array();
	//$newrules['(.?.+?)(/[0-9]+)?/?$'] = 'index.php?pagename=$matches[1]&page=$matches[2]';
	/*
	if ($GLOBALS['rewriteSez']) foreach($GLOBALS['rewriteSez'] as $sez) {
		$newrules['('.$sez.')/(.*)$'] = 'index.php?pagename=$matches[1]&avnodepermalink=$matches[2]';
	}
	*/
	if ($GLOBALS['rewriteEvents']) foreach($GLOBALS['rewriteEvents'] as $sez) {
		$newrules['(exhibitions/'.$sez.')/(.*)$'] = 'index.php?exhibitions='.$sez.'&avnodepermalink=$matches[2]';
		$newrules['(edition/'.$sez.')/(.*)$'] = 'index.php?edition='.$sez.'&avnodepermalink=$matches[2]';
		$newrules['(editions/'.$sez.')/(.*)$'] = 'index.php?editions='.$sez.'&avnodepermalink=$matches[2]';
		$newrules['('.$sez.')/(.*)$'] = 'index.php?editions='.$sez.'&avnodepermalink=$matches[2]';
	}
	$newrules['(gallery)/(.*)$'] = 'index.php?pagename=gallery&avnodepermalink=$matches[2]';
	/*
	if ($rules["//?$"]) 								unset($rules["//?$"]); 
	if ($rules["//page/([0-9]{1,})/?$"]) 				unset($rules["//page/([0-9]{1,})/?$"]); 
	if ($rules["//feed/(feed|rdf|rss|rss2|atom)/?$"]) 	unset($rules["//feed/(feed|rdf|rss|rss2|atom)/?$"]); 
	if ($rules["//(feed|rdf|rss|rss2|atom)/?$"]) 		unset($rules["//(feed|rdf|rss|rss2|atom)/?$"]); 
	*/
	//if ($rules["//?$"]) unset($rules["//?$"]); 
	//my_flush_rules();
	$rules = array_merge($newrules , $rules);
	return $rules;
}

// Adding the id var so that WP recognizes it
function my_insert_query_vars( $vars ) {
    array_push($vars, 'avnodepermalink');
    return $vars;
}
function my_flush_rules(){
	$rules = get_option( 'rewrite_rules' );
//	print_r($rules);
	$setnewrules = false;
	if ($GLOBALS['rewriteSez']) foreach($GLOBALS['rewriteSez'] as $sez) if(!isset( $rules['('.$sez.')/(.*)$'])) $setnewrules = true;
	if ($setnewrules) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}
add_filter( 'rewrite_rules_array','my_insert_rewrite_rules' );
add_filter( 'query_vars','my_insert_query_vars' );
add_action( 'wp_loaded','my_flush_rules' );
/* END REWRITE URLS */
?>
