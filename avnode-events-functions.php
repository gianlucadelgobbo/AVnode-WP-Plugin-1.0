<?
function drawBox($field) {
	$box = types_render_field($field,array("raw" => "true"));
	$box = avnode_events_replace($box);
	return $box["content"];
}
function getMLFieldValue($xmlfiledValue){
	if (function_exists("qtranxf_getLanguage")) define('ICL_LANGUAGE_CODE', qtranxf_getLanguage());
	if (!defined('ICL_LANGUAGE_CODE')) {
		 define('ICL_LANGUAGE_CODE', 'en');

	}
	$trovato=false;
	$trovato = $xmlfiledValue[ICL_LANGUAGE_CODE];
	if ($trovato=="<p>insert Text</p>" || $trovato=="insert Text") $trovato="";
	if(!$trovato && ICL_LANGUAGE_CODE!="en"){
		$trovato = $xmlfiledValue["en"];
	}
	$trovato = wpautop(makeTextPlainToRich($trovato),false);
	return $trovato;	
}

function makeTextPlainToRich($str){
	$str=str_replace('"','&quot;',$str);
	$str=str_replace("###b###","<b>",$str);
	$str=str_replace("###/b###","</b>",$str);
	$str=preg_replace('((mailto:|(news|(ht|f)tp(s?))://){1}\S+)','<a href="\0" target="_blank">\0</a>',$str);
	$str=str_replace(">mailto:", ">", $str);
	$str=str_replace("\r\n","<br />",$str);
	$str=str_replace("\n","<br />",$str);
	return $str;	
}

function getOriginalFileURL($url) {
	$find = array("_jpg.jpg","_gif.jpg","_png.jpg","");
	$replace = array(".jpg",".gif",".png",);
	$original = str_replace($find,$replace,str_replace(array("280x210/","400x300/"),"",$url));
	return $original;	
}

function cmp($a, $b) {
    if ($a['sort'] == $b['sort']) {
        return 0;
    }
    return ($a['sort'] > $b['sort']) ? 1 : -1;
}
function cmpDate($a, $b) {
    if (date("G,i",strtotime($a["data_i"])) == date("G,i",strtotime($b["data_i"]))) {
        return 0;
    }
    return (date("G,i",strtotime($a["data_i"])) > date("G,i",strtotime($b["data_i"]))) ? 1 : -1;
}
function cmpUsers($a, $b) {
    if (strtoupper($a['nomearte']) == strtoupper($b['nomearte'])) {
        return 0;
    }
    return (strtoupper($a['nomearte']) > strtoupper($b['nomearte'])) ? 1 : -1;
}
function array_split($array, $pieces=2) {   
    if ($pieces < 2) 
        return array($array); 
    $newCount = ceil(count($array)/$pieces); 
    $a = array_slice($array, 0, $newCount); 
    $b = array_split(array_slice($array, $newCount), $pieces-1); 
    return array_merge(array($a),$b); 
} 

?>