<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
/*
Plugin Name:  AVNode events 2
Plugin URI:   http://www.flyer.it
Text Domain: avnode-events2
Domain Path: /languages/
Description:  Adds FLxER.net Event in your wordpress website
Version:      2.0
Author:       Gianmluca Del Gobbo
Author URI:   http://www.flyer.it
License:      GPL 2

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License v2 as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

*/
add_action( 'plugins_loaded', 'myplugin_load_textdomain' );
function myplugin_load_textdomain() {
	load_plugin_textdomain( 'avnode-events2', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}


require_once ( dirname(__FILE__) . '/avnode-events-functions.php');
require_once ( dirname(__FILE__) . '/avnode-events-rewrite-rules.php');
require_once ( dirname(__FILE__) . '/avnode-events-writers.php');


define('GDG_REGEXP', '\[(\[?)(avnode)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)');

add_action('admin_menu', 'avnode_events_admin_menu');
function avnode_events_admin_menu() {
	add_options_page('AVnode Events Options', 'AVnode Events Options', 'manage_options', 'AVnode-Events-Options', 'avnode_events_options');
}

add_action('wp_title', 'avnode_events', 100,1);
//add_action('wp_head', 'avnode_events',1,1);


function avnode_events() {
	global $post;
	$res = array();
	if ($post->post_content) $res = avnode_events_replace($post->post_content);
    if ($res && $res["content"]) $post->post_content=$res["content"];
	if($res && $res["title"]) $post->post_title = $res["title"];
		//print_r($post->post_title."BBBBBB");
	if($res && $res["mainimage"]) $post->mainimage = $res["mainimage"];
}

function avnode_events_replace($content) {
	$matches2 = preg_match_all("/".GDG_REGEXP."/s", $content, $matches);
	if ($matches) {
		global $post;
	    $links=$matches[0]; 
	    for ($j=0;$j<$matches2;$j++) {
	    	$pat_attributes = "(\S+)=[']?((?:.(?![']?\s+(?:\S+)=|[>']))+.)[']?";
			preg_match_all( "/".$pat_attributes."/s", "<a ".$matches[3][$j], $ms);
			//print_r($var);
			/*
			$tmpA = explode(" ",$matches[3][$j]);
			$var = array();
			foreach ($tmpA as $v) {
				$tmp2 = explode("=",$v);
				if ($tmp2[0]) {
					$var[trim($tmp2[0])] = trim($tmp2[1]);
				}
			}
			*/
			$var = array();
			for($i=0;$i<count($ms[1]);$i++) {
				$var[$ms[1][$i]] = $ms[2][$i];
			}			
			//print_r($var);
			$filters = array();
			$tmp = explode(",",$var['filter']);
			foreach($tmp as $filter) {
				$filters[]=str_replace(array("'","’","′","&#8217;","&#8242;"),"",$filter);
			}
			$params = array();
			if (isset($var['params'])) {
				$tmp = explode(",",$var['params']);
				foreach($tmp as $param) {
					$params[]=str_replace(array("'","’","′","&#8217;","&#8242;"),"",$param);
				}
			}
			$days = array();
			if (isset($var['days'])) {
				$tmp = explode(",",$var['days']);
				foreach($tmp as $day) {
					$days[]=str_replace(array("'","’","′","&#8217;","&#8242;"),"",$day);
				}
			}
			$room = false;
			if (isset($var['room'])) {
				$tmp = explode(",",$var['room']);
				$room = str_replace(array("'","’","′","&#8217;","&#8242;"),"",$tmp[0]);
			}
			$class = false;
			if (isset($var['class'])) {
				$tmp = explode(",",$var['class']);
				$class = str_replace(array("'","’","′","&#8217;","&#8242;"),"",$tmp[0]);
			}
			$replaceA = avnode_events_plugin_callback(array("source"=>$var['source'],"view"=>$var['view'],"title"=>$post->post_title,"filter"=>$filters[0],"params"=>$params,"days"=>$days,"room"=>$room,"class"=>$class));
			global $wp_query;
			if ($wp_query->query_vars['avnodepermalink']) {
		    	$content=$replaceA["content"]; 
		    } else {
		        $content=str_replace($matches[0][$j],$replaceA["content"],$content); 
			}
			$title = ($replaceA["title"] ? $replaceA["title"] : false);
			$mainimage = ($replaceA["mainimage"] ? $replaceA["mainimage"] : false);
		}
	}
	return array("content"=>$content,"title"=>$title,"mainimage"=>$mainimage,"testo"=>$replaceA['testo']);
}

function avnode_events_plugin_callback($values) {
	global $wp_query;
	switch ($values["view"]) {
		case "gallery" :
			if ($wp_query->query_vars['avnodepermalink']) {
				$obj = array();
				$lang = "";
				$domain = "https://flxer.net/api/";
				if (function_exists("qtranxf_getLanguage")) $lang = qtranxf_getLanguage();
				if ($lang && $lang != "en" ) $domain = "https://".$lang.".flxer.net/api/";
				$urlDett = $domain.$wp_query->query_vars['avnodepermalink'];
				$apiRes = @file_get_contents($urlDett);
				if ($apiRes) {
					$obj['dettData'] = json_decode($apiRes,true);
				} else {
					$obj['dettData']["error"] = "404";
				}
			} else {
				$obj = json_decode(file_get_contents($values["source"]),true);
			}
		break;
		case "programme" :
		case "performances" :
		case "partners" :
			$obj = json_decode(file_get_contents($values["source"]),true);
		break;
		case "performers" :
			if ($wp_query->query_vars['avnodepermalink']) {
				$permalink = explode("/",$_SERVER['REQUEST_URI']);
				$obj = array();
				$obj = json_decode(file_get_contents($values["source"]),true);
				// ELIMINARE
				if (count($permalink)==5 || count($permalink)==6 || count($permalink)==7 || count($permalink)==8 || count($permalink)==9) $obj = json_decode(file_get_contents($values["source"]),true);
				// END ELIMINARE
				$lang = "";
				$domain = "https://flxer.net/api/";
				if (function_exists("qtranxf_getLanguage")) $lang = qtranxf_getLanguage();
				if ($lang && $lang != "en" ) $domain = "https://".$lang.".flxer.net/api/";
				$urlDett = $domain.($values["view"]=="performers" || $values["view"]=="gallery" ? $wp_query->query_vars['avnodepermalink'] : $values["view"]."/".$wp_query->query_vars['avnodepermalink']);
				//print_r($urlDett);
				$apiRes = @file_get_contents($urlDett);
				if ($apiRes) {
					$obj['dettData'] = json_decode($apiRes,true);
				} else {
					$obj['dettData']["error"] = "404";
				}
			} else {
				$obj = json_decode(file_get_contents($values["source"]),true);
			}
		break;
	}
	/*
	$obj = json_decode(file_get_contents($values["source"]),true);
	if ($wp_query->query_vars['avnodepermalink']) {
		$obj = array();
		$urlDett = "https://flxer.net/api/".($values["view"]=="performers" || $values["view"]=="gallery" ? $wp_query->query_vars['avnodepermalink'] : $values["view"]."/".$wp_query->query_vars['avnodepermalink']);
		//print_r($urlDett);
		$apiRes = @file_get_contents($urlDett);
		if ($apiRes) {
			$obj['dettData'] = json_decode($apiRes,true);
		} else {
			$obj['dettData']["error"] = "404";
		}
	}
	*/
	$output = "";
	if (!isset($obj['dettData']["error"])) {
		switch ($values["view"]) {
			case 'partners' :
				$output = writePartnersList($obj['partners'],$values["filter"],$values["class"]);
				break;
			case 'programme' :
				$output = writeProgrammeList($obj['performances'],$values);
				break;
			case 'performances' :
				$output = writePerformancesList($obj['performances'],$values);
				break;
			case 'gallery' :
				if ($obj['dettData']['sorted']) {
					$output = writeGalleryDett($obj['dettData']);
					$mainimagecnt = $obj['dettData']['dettMedia'] ? $obj['dettData']['dettMedia'] : $obj['sorted'][0];
					if ($mainimagecnt['type']=='video') {
						$mainimage = "https://flxer.net/".$mainimagecnt['preview_file'];
					} else {
						$mainimage = "https://flxer.net/".$mainimagecnt['folder']."/".$mainimagecnt['name'];
					}
					//if(!$mainimage) $mainimage = "https://flxer.net/".$obj['sorted'][0]['folder']."/".$obj['sorted'][0]['name'];
					$title = "Gallery | ".$obj['dettData']['titolo'].($obj['dettData']['dettMedia']['titolo'] ? ": ".$obj['dettData']['dettMedia']['titolo'] : "");
				} else if ($obj['sorted']) {
					//print_r($obj);
					$output = writeGalleryDett($obj);
					global $wp_query;
					if ($wp_query->query_vars['avnodepermalink']) {
						$mainimage = "https://flxer.net/".$obj['dettMedia']['folder']."/".$obj['dettMedia']['name'];
						if(!$mainimage) $mainimage = "https://flxer.net/".$obj['sorted'][0]['folder']."/".$obj['sorted'][0]['name'];
						$title = "Gallery | ".$obj['titolo'].($obj['dettMedia']['titolo'] ? ": ".$obj['dettMedia']['titolo'] : "");
					}
				} else {
					$output = writeGalleryList($obj['gallery']);
				}
				break;
			case 'performers' :
				if ($obj['dettData']['nomearte']) {
					//$title =  $values["title"].": ".$obj['dettData']['nomearte'];
					$title =  "Artists | ".$obj['dettData']['nomearte'];
					$output = writeArtistDett($obj['dettData'], $obj['performances'], $title);
					$mainimage = getOriginalFileURL("https://flxer.net".$obj['dettData']['avatar']);
				} else if ($obj['dettData']['titolo']) {
					//$title = "Performance | ".$obj['dettData']['titolo'];
					$title = $obj['dettData']['typeStr']." | ".$obj['dettData']['titolo'];
					//print_r($obj['dettData']);
					$output = writePerformanceDett($obj['dettData'], $title,$obj['performances']);
					$mainimage = getOriginalFileURL("https://flxer.net".$obj['dettData']['img_arr']);
				} else {
					$output = writeArtistsList($obj);
				}
				break;
		}
	} else {
		//print_r("stocazzo ".$values["source"]."\n");
		//print_r($obj['dettData']["error"]);
		status_header(404);
		$output = writeError();
	}
	//print_r(getMLFieldValue($obj['dettData']['testo']));
	return array("content"=>$output,"title"=>$title,"mainimage"=>$mainimage,"testo"=>getMLFieldValue($obj['dettData']['testo']));
}


function avnode_events_options() {
	
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.', 'avnode-events2') );
	}
	
	$avnode_events = get_option('avnode_events');
	
	?>
	<div class="wrap">
		<h2>AVnode Events</h2>
		<form method="post" action="options.php">
			<?php wp_nonce_field('update-options'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="option_avnode_events_folders_array">Valid AVnode folders:</label></th>
					<td>
						<input type="text" size="90" id="option_avnode_events_folders_array" name="avnode_events[folders_array]" value="<?php echo $avnode_events['folders_array']; ?>" /><br />
						<i>Comma delimited</i>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="option_avnode_events_events_array">Valid AVnode events:</label></th>
					<td>
						<input type="text" size="90" id="option_avnode_events_events_array" name="avnode_events[events_array]" value="<?php echo $avnode_events['events_array']; ?>" /><br />
						<i>Comma delimited</i>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="option_avnode_events_status">Events status:</label></th>
					<td>
						<textarea type="text" rows="20" cols="85" id="option_avnode_events_status" name="avnode_events[avnode_events_status]"><?php echo $avnode_events['avnode_events_status']; ?></textarea><br />
						<i>{"940":{"status":"call || approving"}},{"940":{"status":"call || approving"}}</i>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="avnode_events_show_all_artists">Show "Still not approved" artists:</label></th>
					<td>
						<input type="radio" id="avnode_events_show_all_artists_yes" name="avnode_events[avnode_events_show_all_artists]" value="1" <?php echo ($avnode_events['avnode_events_show_all_artists']==1 ? " checked=\"checked\"" : ""); ?> /><label for="avnode_events_show_all_artists_yes">YES</label>&nbsp;&nbsp;&nbsp;
						<input type="radio" id="avnode_events_show_all_artists_no" name="avnode_events[avnode_events_show_all_artists]" value="0" <?php echo (!isset($avnode_events['avnode_events_show_all_artists']) || $avnode_events['avnode_events_show_all_artists']==="0" ? " checked=\"checked\"" : ""); ?> /><label for="avnode_events_show_all_artists_no">NO</label><br />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="avnode_events_show_schedule">Show performances schedule day and time:</label></th>
					<td>
						<input type="radio" id="avnode_events_show_schedule_yes" name="avnode_events[avnode_events_show_schedule]" value="1" <?php echo ($avnode_events['avnode_events_show_schedule']==1 ? " checked=\"checked\"" : ""); ?> /><label for="avnode_events_show_schedule_yes">YES</label>&nbsp;&nbsp;&nbsp;
						<input type="radio" id="avnode_events_show_schedule_no" name="avnode_events[avnode_events_show_schedule]" value="0" <?php echo (!isset($avnode_events['avnode_events_show_schedule']) || $avnode_events['avnode_events_show_schedule']==="0" ? " checked=\"checked\"" : ""); ?> /><label for="avnode_events_show_schedule_no">NO</label><br />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="avnode_events_list_style">List thumbnail size:</label></th>
					<td>
						<select type="thumb_size" id="avnode_events_list_style_thumb_size" name="avnode_events[thumb_size]">
							<option <?php echo ($avnode_events['thumb_size']=="" ? " selected=\"selected\"" : ""); ?>>Default (55x55)</option>
							<option value="55x55"<?php echo ($avnode_events['thumb_size']=="55x55" ? " selected=\"selected\"" : ""); ?>>55x55</option>
							<option value="55x55"<?php echo ($avnode_events['thumb_size']=="55x55" ? " selected=\"selected\"" : ""); ?>>55x55</option>
							<option value="90x68"<?php echo ($avnode_events['thumb_size']=="90x68" ? " selected=\"selected\"" : ""); ?>>90x68</option>
							<option value="128x96"<?php echo ($avnode_events['thumb_size']=="128x96" ? " selected=\"selected\"" : ""); ?>>128x96</option>
							<option value="190x107"<?php echo ($avnode_events['thumb_size']=="190x107" ? " selected=\"selected\"" : ""); ?>>190x107</option>
							<option value="190x142"<?php echo ($avnode_events['thumb_size']=="190x142" ? " selected=\"selected\"" : ""); ?>>190x142</option>
							<option value="280x210"<?php echo ($avnode_events['thumb_size']=="280x210" ? " selected=\"selected\"" : ""); ?>>280x210</option>
							<option value="334x250"<?php echo ($avnode_events['thumb_size']=="334x250" ? " selected=\"selected\"" : ""); ?>>334x250</option>
							<option value="400x300"<?php echo ($avnode_events['thumb_size']=="400x300" ? " selected=\"selected\"" : ""); ?>>400x300</option>
							<option value="660x371"<?php echo ($avnode_events['thumb_size']=="660x371" ? " selected=\"selected\"" : ""); ?>>660x371</option>
							<option value="660x495"<?php echo ($avnode_events['thumb_size']=="660x495" ? " selected=\"selected\"" : ""); ?>>660x495</option>
						</select>
					</td>
				</tr>
			</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="avnode_events" />
			<p class="submit">
				<input type="submit" value="Update Options" />
			</p>
		</form>
	</div>
	<?php } ?>
