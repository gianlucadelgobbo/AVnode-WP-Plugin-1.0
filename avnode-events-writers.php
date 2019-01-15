<?

function writeError(){
	$str = "<h1 class=\"entry-title\">".__('ERROR', 'avnode-events2')."</h1>
		<div class=\"rientro\">
	        <h3 class=\"errortitle\">".__('Page not found', 'avnode-events2')."</h3>
	        <h3 class=\"errortitle\">".__('Sorry, no posts matched your criteria.', 'avnode-events2')."</h3>
		</div>";
	return $str;	
}

function writePartnersList($res,$f,$class=""){
	$str = "";
	if ($res && is_array($res[$f])) {
		$position = 1;
		foreach($res[$f] as $row){
			/*
			$str.= "<li class=\"media\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/Organization\"><a itemprop=\"url\" class=\"pull-left\" href=\"".$row['websites'][0]['url']."\" title=\"".$row["nomearte"]."\"><img itemprop=\"image\" class=\"media-object\" src=\"https://flxer.net".$row['avatar']."\" alt=\"".$row["nomearte"]."\" /></a><div class=\"media-body\"><h2 itemprop=\"name\" class=\"media-heading\"><a href=\"".$row['websites'][0]['url']."\" title=\"".$row["nomearte"]."\" target=\"_blank\">".$row["nomearte"]."</a></h2><p class=\"stit\">".$row['websites'][0]['txt']."</p></div></li>\n";
			*/
			$linktext = $row['websites'][0]['txt'];
			$linktext = strrpos($linktext, "/") == strlen($linktext)-1 ? substr($linktext, 0, strlen($linktext)-1) : $linktext;
			$linktext = strrpos($linktext, "www.") === 0 ? substr($linktext, 4) : $linktext;
			$str.= "<div".($class ? " class=\"".$class." isotopeitem\"" : "")." itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><meta itemprop=\"position\" content=\"".$position."\" /><div itemprop=\"item\" itemscope itemtype=\"http://schema.org/Organization\" class=\"row partners\"><div class=\"col-xs-5 col-md-5 col-sm-12\"><a itemprop='url' href=\"".$row['websites'][0]['url']."\" target=\"_blank\" title=\"".$row["nomearte"]."\"><img itemprop='image' class=\"img-responsive\" src=\"https://flxer.net".$row['avatar']."\" alt=\"".$row["nomearte"]."\" /></a></div><div class=\"col-xs-7 col-md-7 col-sm-12\"><h2 class=\"media-heading\"  itemprop='name'><a href=\"".$row['websites'][0]['url']."\" title=\"".$row["nomearte"]."\" target=\"_blank\">".$row["nomearte"]."</a></h2><p class=\"stit\">".$linktext."</p></div></div></div>";
			$position++;
		}					
		$str = "<div class=\"".($class ? "row isotope " : "")."lists\" itemprop='sponsor' itemscope itemtype='http://schema.org/ItemList'>".$str."</div>";
	}
	return $str;	
}

function writeProgrammeList($obj,$values){
	$avnode_events_options = get_option('avnode_events');
	$basepath = $values["view"];
	$params = $values["params"];
	$room = $values["room"] ? $values["room"] : false;;
	$str = "";
	$position = 1;
	foreach($obj as $k=>$res) {
		if ($k!="tobeconfirmed" && $k!="-0001-11-30") {
			$str.= "\n<h3 itemprop=\"name\">\n".$k."</h3>\n";
			$premid = "";
			$postmid = "";
			usort($res,"cmpDate");
			foreach($res as $row){
				$perfH = date("G",strtotime($row["data_i"]));
				if ($perfH>10) {
					$premid.= writeBlockLarge($row,$basepath,$position);
				} else {
					$postmid.= writeBlockLarge($row,$basepath,$position);
				}
				$position++;
			}					
			$str.= "\n<div class=\"lists\" itemscope itemtype=\"http://schema.org/ItemList\">\n".$premid.$postmid."</div>\n";
		}
	}
	return $str;	
}

function writePerformancesList($obj,$values){
	$avnode_events_options = get_option('avnode_events');
	$basepath = $values["view"];
	$params = $values["params"];
	$room = $values["room"] ? $values["room"] : false;
	$str = "";
	$position = 1;
	$premid = "";
	$postmid = "";
	$all = array();
	//print_r("STOCAZZO 1\n");
	foreach($obj as $k=>$res) {
		//print_r("	STOCAZZO 2\n");
		if ($k!="tobeconfirmed" && $k!="-0001-11-30" && (in_array($k,$values["days"]) || in_array("ALL",$values["days"]))) {
			//print_r("		STOCAZZO 3\n");
			usort($res,"cmpDate");
			foreach($res as $row){
				//print_r("			STOCAZZO 4\n");
				$insert = false;
				if ($room) {
					if ($room == $row["room"] || $room == "ALL") {
						if (in_array($row["typeStr"],$params) || in_array($row["room"],$params) || $room == "ALL") {
							$insert = true;
						} 
					}
				} else {
					if (in_array($row["typeStr"],$params) || in_array($row["room"],$params)) {
						$insert = true;
					} 
				}
				//print_r($insert);
				if ($insert) {
					if ($room == "ALL") {
						$all[$row['id']] = writeBlock2($row,$basepath,$position); 
						$position++;
					} else {
						//print_r("				STOCAZZO 5\n");
						$perfH = date("G",strtotime($row["data_i"]));
						if ($perfH>10) {
							$premid.= writeBlock($row,$basepath,$position);
						} else {
							$postmid.= writeBlock($row,$basepath,$position);
						}
						$position++;
					}
				} 
			}					
		}
	}
	if ($room == "ALL") {
		$str = "<table class=\"table\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
		$str.= "	<thead>";
		$str.= "		<tr>";
		$str.= "			<th width=\"148\">aaa</th>";
		$str.= "			<th>aaa</th>";
		$str.= "			<th width=\"420\">aaa</th>";
		$str.= "		</tr>";
		$str.= "	</tbody>";
		$str.= "	<tbody>";
		$str.= implode("", $all);
		$str.= "	</tbody>";
		$str.= "</table>";
	} else {
		$str = "\n<ul class=\"lists\">\n".$premid.$postmid."</ul>\n";
	}
	return $str;	
}

function writeArtistsList($obj){
	//print_r($obj);
	$authors = array();
	$loc = array();				
	$authN = array();
	//$auths = array();
	$perfCount= array();
	$avnode_events_options = get_option('avnode_events');
	$avnode_events_options['avnode_events_status'] = json_decode($avnode_events_options['avnode_events_status'], true)[0];

	$showall = $avnode_events_options['avnode_events_status'][$obj["id"]] && ($avnode_events_options['avnode_events_status'][$obj["id"]]["status"]=="approving" || $avnode_events_options['avnode_events_status'][$obj["id"]]["status"]=="call") ? true : false;

	foreach($obj['performances'] as $k=>$perfBlock) {
		foreach($perfBlock as $perf){
			if ($showall || strpos($perf["rel_chiavi"],"367|369")!==false) {
				$perfCount[$perf['id']]=$perf['id'];
				foreach($perf['performers'] as $auth){
					if (!$authors[$auth['uid']]) $authors[$auth['uid']] = array();
					if (!$authors[$auth['uid']]['author']) {
						$authors[$auth['uid']]['author'] = $auth;
						$authors[$auth['uid']]['sort'] = strtoupper(substr($auth['nomearte'],0,1));
					}
					if (!$authors[$auth['uid']]['performances']) $authors[$auth['uid']]['performances'] = array();
					if (!$authors[$auth['uid']]['performances'][$perf['id']]) $authors[$auth['uid']]['performances'][$perf['id']] = $perf;
					if(is_array($auth['members'])){
						foreach($auth['members'] as $mem) {
							$authN[$mem['uid']] = 1;
							//$auths[] = ($mem['nomearte']==$mem['nome']." ".$mem['cognome'] ? $mem['nomearte'] : $mem['nome']." \"".$mem['nomearte']."\" ".$mem['cognome'])." - ".$mem['locations'][0]['country'].", ".$mem['locations'][0]['city'];
						}
					} else {
						$authN[$auth['uid']] = 1;
						//$auths[] = ($auth['nomearte']==$auth['nome']." ".$auth['cognome'] ? $auth['nomearte'] : $auth['nome']." \"".$auth['nomearte']."\" ".$auth['cognome'])." - ".$auth['locations'][0]['country'].", ".$auth['locations'][0]['city'];
					}
					if ($auth['locations']) foreach($auth['locations'] as $l) $loc[]=$l['country'];
				}
			} 
		}
	}
	//$auths = array_unique($auths);
	//sort($auths);
	//print_r($auths);
	//print_r($obj["willbepresent"]);
	if($obj["willbepresent"]){
		foreach($obj['willbepresent'] as $auth){
			if(is_array($auth['members'])){
				foreach($auth['members'] as $mem) {
					$authN[$mem['uid']] = 1;
				}
			} else {
				$authN[$auth['uid']] = 1;
			}
			if ($auth['locations']) foreach($auth['locations'] as $l) $loc[]=$l['country'];
		}
	}
					//print_r(reset($authors));
	usort($authors, "cmp");
	$conta = 0;
	foreach($authN as $k=>$v) $conta = $conta+$v;
	$loc = array_unique($loc);
	global $post;
	//$str = "<h1 itemprop=\"name\" class=\"entry-title\">".$post->post_title.( ? " <small></small>" : "")."</h1><span class=\"hide\">: </span>";
	$str = "";
	//if (function_exists('get_the_breadcrumb')) $str.= get_the_breadcrumb();
	if ($avnode_events_options['avnode_events_show_all_artists']) {
		//$str.= "<div class=\"alert alert-danger alert-artist\" role=\"alert\">".__("Submitted, confirmed and not confirmed")."</div>";
	}
	$str.= "<div itemprop=\"description\" class=\"rientro\"><strong>".$conta."</strong> ".__("artists from", 'avnode-events2')." <strong>".implode(", ",$loc)."</strong> ".__("playing", 'avnode-events2')." <strong>".count($perfCount)."</strong> ".__("performances, workshops and showcases", 'avnode-events2').".</div>";
	$conta = 0;
	$conta2 = 0;
	$col_itemsA = array();
	$col_items = intval(count($authors)/4);
	$conta3 = count($authors)-($col_items*4);
	for($a=0;$a<4;$a++) {
		if($conta3-$a) {
			$col_itemsA[$a] = $col_items+1;
		} else {
			$col_itemsA[$a] = $col_items;
		}
	}
	$myCols = array();
	foreach($authors as $id=>$author) {
		//$str.= "<li><a href=\"".$author['author']['login']."\">".$author['author']['nomearte']."</a></li>";
		$myCols[$conta2][]= $author;
		$conta++;
		if ($conta==$col_itemsA[$conta2] && $conta2!=3) {
			//$str.= "</div><div class=\"colx4".($conta2 < 3 ? " r10" : "")."\">";
			$conta = 0;
			$conta2++;
		} else {
		}
	}
	$str.= "
		<div class=\"row\">";
	$contaconta = 1;
	foreach($myCols as $col=>$block) {
		$first = reset($block);
		$last = end($block);
		$str.= "
			<div class=\"col-sm-3\">
				<h3 class=\"grid-title\">".strtoupper(mb_substr($first['author']['nomearte'],0,1))." / ".(mb_substr($last['author']['nomearte'],0,1))."</h3>
				<div>
					<ul class=\"lists\">";
		foreach($block as $id=>$author) {
			$str.= writeUserBox($author['author'],"".$GLOBALS['rewriteSez'][0]."",$author['performances'],$avnode_events_options['avnode_events_show_schedule'], $contaconta);
			$contaconta = $contaconta+1;
		}
		$str.= "</ul></div></div>";
	}
	$str.= "
		</div>";
	if($obj["willbepresent"]){
		$str.= "
			<h3 class=\"grid-title\">".__('Will be present', 'avnode-events2')."</h3>";
	$str.= "
		<div class=\"row\">";
		$conta = 0;
		$conta2 = 0;
		$col_items = intval(count($obj["willbepresent"])/4);
		$myCols = array();
		usort($obj["willbepresent"], "cmpUsers");
		foreach($obj["willbepresent"] as $author) {
			//$str.= "<li><a href=\"".$author['author']['login']."\">".$author['author']['nomearte']."</a></li>";
			$myCols[$conta2][]= $author;
			if ($conta==$col_items) {
				//$str.= "</div><div class=\"colx4".($conta2 < 3 ? " r10" : "")."\">";
				$conta = 0;
				$conta2++;
			} else {
				$conta++;
			}
		}
		foreach($myCols as $col=>$block) {
			$first = reset($block);
			$last = end($block);
			$str.= "
			<div class=\"col-sm-3\">
				<h3 class=\"grid-title\">".strtoupper(substr($first['nomearte'],0,1))." / ".strtoupper(substr($last['nomearte'],0,1))."</h3>
				<ul class=\"lists\">";
			foreach($block as $author) {
				$str.= writeUserBox($author,"".$GLOBALS['rewriteSez'][0]."",false,true,$contaconta);
				$contaconta++;
			}
			$str.= "
				</ul>
			</div>";
		}
		$str.= "
			</div>";
	}
	return $str;	
}

function writeGalleryList($obj){
	$str = "";
	if (strpos($_SERVER['REQUEST_URI'], "artists/")) {
		$basepath = explode("artists/",$_SERVER['REQUEST_URI']);
		//$mybasepath = ($lang && $lang != "en" ) ? $basepath[1]."/".$basepath[2] : $basepath[1];
		//$mybasepath = $_SERVER['REQUEST_URI'];
		$mybasepath = $basepath[0] != $_SERVER['REQUEST_URI'] ? "/editions/".explode("/", $basepath[1])[1]."/gallery/" : $basepath[0];
		$mybasepathA = explode("/",$mybasepath);
		//print_r($mybasepathA);
		$lang = "";
		if($mybasepathA[1] != "wp-json") {
			$lang = array_shift($mybasepathA);
			$lang = "/".$mybasepathA[0];
		}		
	} else {
		$basepath = explode("editions/",$_SERVER['REQUEST_URI']);
		$mybasepathA = explode("/",$_SERVER['REQUEST_URI']);
		$lang = "";
		if($mybasepathA[1] != "wp-json") {
			$lang = array_shift($mybasepathA);
			$lang = "/".$mybasepathA[0];
		}		
		$mybasepath = $lang."/".$mybasepathA[4]."/".$mybasepathA[6]."/".$mybasepathA[7]."/";
	}
	$position = 1;
	if ($obj) {
		foreach($obj as $gall){
			$stats = array();
			foreach($gall['stats'] as $k=>$v){
				$stats[]=$k.": ".$v;
			}
			$url = ($mybasepath != "gallery" ? $mybasepath : "").$gall['performers'][0]['login']."/gallery/".$gall['permalink']."/";
			/*$str.= "
				<li class=\"col-sm-4\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/CreativeWork\"><div class=\"rientro10 media\"><a itemprop=\"url\" class=\"pull-left\" href=\"".$url."\"><img class=\"media-object\" src=\"https://flxer.net".$gall['img_arr']."\" alt=\"Gallery image of ".$gall['titolo']."\" itemprop=\"image\" /></a><div class=\"media-body\"><h2 itemprop=\"name\" class=\"media-heading\"><a href=\"".$url."\">".$gall['titolo']."</a></h2><p class=\"stit small\">".$gall['performers'][0]['nomearte']."</p><p class=\"stit small pt5\">".implode("<br />",$stats)."</p></div></div></li>";
			$str.= "
				<li class=\"col-sm-4\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/CreativeWork\"><div class=\"row gallery\"><div class=\"col-xs-5 col-md-5 col-sm-12\"><a itemprop=\"url\" href=\"".$url."\"><img class=\"img-responsive\" src=\"https://flxer.net".$gall['img_arr']."\" alt=\"Gallery image of ".$gall['titolo']."\" itemprop=\"image\" /></a></div><div class=\"col-xs-7 col-md-7 col-sm-12\"><h2 itemprop=\"name\" class=\"media-heading\"><a href=\"".$url."\">".$gall['titolo']."</a></h2><p class=\"stit small\">".$gall['performers'][0]['nomearte']."</p><p class=\"stit small pt5\">".implode("<br />",$stats)."</p></div></div></li>";
				*/
			$str.= "
				<li class=\"col-xl-2 col-sm-3\" style=\"margin-bottom: 30px;\" itemprop=\"itemListElement\" itemscope=\"\" itemtype=\"http://schema.org/ListItem\"><meta itemprop=\"position\" content=\"".$position."\" /><div itemprop=\"item\" itemscope=\"\" itemtype=\"http://schema.org/CreativeWork\"><a itemprop=\"url\" href=\"".$url."\" title=\"Gallery image of ".$gall['titolo']."\"><img itemprop=\"image\" class=\"img-responsive\" src=\"https://flxer.net".$gall['img_arr']."\" alt=\"\"></a>
	<div class=\"caption\" style=\"padding-right:15px;padding-left:15px;\">
	<div class=\"caption-body\">
	<h2 itemprop=\"name\" class=\"caption-title\">".$gall['titolo']."</h2>
	<div class=\"caption-author\" itemprop=\"author\" itemscope=\"\" itemtype=\"http://schema.org/Person\"><b itemprop=\"name\">".$gall['performers'][0]['nomearte']."</b> <br />".implode("<br />",$stats)."</div></div></div>
	</div>
	</li>";
			$position++;
		}
	}

	if ($str) $str = "\n<ul id=\"listItems\" class=\"row\">\n".$str."</ul>\n";
	return $str;	
}

function writeGalleryDett($obj){
	
	$tmp2 = explode("?",$_SERVER ['REQUEST_URI']);
	$tmp2 = $tmp2[0];
	$tmp2 = explode("/",$tmp2);
	array_shift($tmp2);
	array_pop($tmp2);
	$tmp = array();
	$trovato = true;
	foreach($tmp2 as $f) {
		if ($f!=$obj['performers'][0]['login'] && $trovato) {
			$tmp[] = $f;
		} else {
			$trovato = false;
		}
	}
	if (is_array($obj['dettMedia'])) {
		//array_pop($tmp);
		$addToLink = "../";
		$title = "Gallery | ".$obj['titolo'].": ".$obj['dettMedia']['titolo'];
		//$str = "<h1>".$title."</h1>";
		$str = "";
		if ($obj['dettMedia']['type']=="img") {
			$str.="<div class=\"cntGallImgDett\" itemscope itemtype=\"http://schema.org/CreativeWork\"><img itemprop=\"associatedMedia\" class=\"img-responsive\" alt=\"".$obj['dettMedia']['titolo']."\" src=\"https://flxer.net/".$obj['dettMedia']['folder']."/".$obj['dettMedia']['name']."\"/></div>";
		} else {
			$str.="<div class=\"embed-responsive embed-responsive-16by9\" itemscope itemtype=\"http://schema.org/CreativeWork\"><iframe itemprop=\"associatedMedia\" class=\"embed-responsive-item\" width=\"1280\" height=\"720\" class=\"0\" src=\"https://flxer.net/_fp/?id=f".$obj['dettMedia']['id']."\"></iframe></div>";
		}
		//$str.= "https://flxer.net/".$obj['dettMedia']['folder']."/".$obj['dettMedia']['name']."";
	} else {
		$title = "Gallery: ".$obj['titolo'];
		//$str = "<h1>".$title."</h1>";
		$str = "";
	}
	global $post;
	//if ($post->post_type == "post") $tmp[0] = "gallery";
	$avnodeoptions = get_option('avnode_events')['folders_array'];
	if ($avnodeoptions && in_array($tmp[count($tmp)-1], explode(",",$avnodeoptions))) {
		$link = ($tmp2[0]!="wp-json" ? "/".$tmp2[0] : "")."/editions/".$tmp[5]."/".$tmp[6]."/".$obj['performers'][0]['login']."/gallery/".$obj['permalink']."/";
		//print_r($tmp);
		//if (strpos($link, "wp-json/wp/v2/gallery")>0) $link = "/editions".explode("wp-json/wp/v2/gallery",$link)[1];
	} else {
		$link = "";
	}
	if (get_query_var('popup')!=1) {
		/*
		$str.= "
		<script><!--
		var links = jQuery(), link;
		-->
		</script>";
		*/
		$str.= "
		<ul class=\"row thumbnails\">";
		$conta = 0;
		$position = 1;
		foreach($obj['sorted'] as $media){ 
			if ($media['type']=="img") {
				$mediaFull = "https://flxer.net/".$media['folder']."/".$media['name']."";
			} else {
				$mediaFull = "https://flxer.net/_fp/?id=f".$media['id']."";
			}
			$href = $link!="" ? $link.$media['permalink'] : $mediaFull;
			//print_r($addToLink.$media['permalink']);
			//$str.= "<li class=\"col-xs-6 col-md-4 col-lg-2\"><a class=\"thumbnail\" title=\"".$media['titolo']."\" style=\"position:relative;\" href=\"".$link.$media['permalink']."\" onclick=\"bella(".$conta.", '".$link.$media['permalink']."/');return false;\"><img class=\"cntGallImgImg\" src=\"https://flxer.net".$media['thumb']."\" alt=\"".$media['titolo']."\" /><img class=\"gallType\" src=\"https://flxer.net/_images/over_".$media['type'].".png\" alt=\"".$media['titolo']."\" /><span>".$media['titolo']."</span></a><script>
			$str.= "<li class=\"col-xs-12 col-md-4 col-lg-3 col-xl-2 nggthumbnail\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><meta itemprop=\"position\" content=\"".$position."\" /><div itemprop=\"item\" itemscope itemtype=\"http://schema.org/CreativeWork\"><a class=\"thumbnail".($obj['dettMedia']['id']==$media['id'] ? " current" : "")."\" title=\"".esc_html(($media['titolo']))."\" style=\"position:relative;\" itemprop=\"url\" href=\"".$href."/\"><span class=\"cntGallImgOver\"><img itemprop=\"image\" class=\"cntGallImgImg\" src=\"https://flxer.net".$media['thumb']."\" alt=\"".esc_html(($media['titolo']))."\" /><img class=\"gallType\" src=\"https://flxer.net/_images/over_".$media['type'].".png\" alt=\"".esc_html($media['titolo'])."\" /></span><span itemprop=\"name\" class=\"cntDida\">".esc_html($media['titolo'])."</span></a>";
			$position++;
			$str.= "<script>
			<!--
			if (typeof glink==='undefined') var glink = [];\n";
			$str.= "glink.push('".$mediaFull."');";
			//$str.= "//link = jQuery('<a/>', {href: 'https://flxer.net/".$media['folder']."/".$media['name']."',title: '".$media['titolo']."',text: '".$media['titolo']."'});links = links.add(link);
			$str.= "
			-->
		</script></div></li>";
			$conta++;
/*							
			if ($media['type']=="img") {
			} else {
				$str.= "<li class=\"col-sm-2\"><a class=\"thumbnail\" title=\"".$media['titolo']."\" style=\"position:relative;\" rel=\"lightbox[".$obj['id']."]\" href=\"https://flxer.net/".$media['folder']."/".$media['name']."\" onclick=\"history.pushState(null, null, '".$link.$media['permalink']."/');return true;\"><img class=\"cntGallImgImg\" src=\"https://flxer.net".$media['thumb']."\" alt=\"".$media['titolo']."\" /><img class=\"gallType\" src=\"https://flxer.net/_images/over_".$media['type'].".png\" alt=\"".$media['titolo']."\" /><span>".$media['titolo']."</span></a></li>";
				//$str.= "<li class=\"col-sm-2\"><a class=\"thumbnail\" title=\"".$media['titolo']."\" style=\"position:relative;\" href=\"".$addToLink.$media['permalink']."\"><img class=\"cntGallImgImg\" src=\"https://flxer.net".$media['thumb']."\" alt=\"".$media['titolo']."\" /><img class=\"gallType\" src=\"https://flxer.net/_images/over_".$media['type'].".png\" alt=\"".$media['titolo']."\" /><span>".$media['titolo']."</span></a></li>";
			}
*/								
		}
		$str.= "</ul><div class=\"hide\"></div>";
		$str = str_replace("128x96","400x300",$str);
	}
	return $str;	
}

function writeArtistDett($obj, $objperf, $title){
	$avnode_events_options = get_option('avnode_events');
	$str = "
			<div class=\"row artist-dett\">
				<div class=\"col-sm-9\">
					
					<div class=\"row\">
						<div class=\"col-sm-2\">
							<img itemprop=\"image\" class=\"img-responsive\" alt=\"".$obj['nomearte']." AVATAR\" src=\"https://flxer.net".$obj['avatar']."\">
						</div>
						<div class=\"col-sm-10\">
							<h1 itemprop=\"name\" class=\"entry-title\">".$obj['nomearte']."</h1>
							<div class=\"rientro\">";
						if ($obj['locations']) {
							$str.= "
								<p>".writeLocationsStr($obj['locations'])."</p>";
						}
						$str.= "
								<div itemprop=\"description\">".getMLFieldValue($obj['testo'])."</div>
								<p><strong>LINKS</strong></p>
								<ul class=\"squared10\">
									<li><a itemprop=\"url\" target=\"_blank\" href=\"https://flxer.net/".$obj['login']."/\">flxer.net/".$obj['login']."</a></li>";
									if($obj['websites']){
										foreach($obj['websites'] as $website) {
											$str.= "<li><a itemprop=\"url\" target=\"_blank\" href=\"".$website['url']."\">".$website['txt']."</a></li>";
										}
									}
										$str.= "
								</ul></div></div></div>
				</div>
				<div class=\"col-sm-3\" itemprop=\"owns\" itemscope itemtype=\"http://schema.org/ItemList\">
					<h2 itemprop=\"name\" class=\"grid-title h1like\">".__("Performance", 'avnode-events2')."</h2>
					<div>
						<ul class=\"lists\">";
	$exclude = array();
	$pp = array();
	foreach($objperf as $perfBlock){
		foreach($perfBlock as $perf){
			foreach($perf['performers'] as $auth){
				if ($auth['login']==$obj['login'] && !in_array($perf['permalink'], $exclude)) {
					if ($pp[$perf['permalink']]) {
						$pp[$perf['permalink']]['schedule'][$perf['data_i'].$perf['data_f']] = $perf;
					} else {
						$pp[$perf['permalink']] = $perf;
						$pp[$perf['permalink']]['schedule'] = array();
						$pp[$perf['permalink']]['schedule'][$perf['data_i'].$perf['data_f']] = array($perf);
					}
					//$exclude[] = $perf['permalink'];
				}
			}
		}
	}

	$position = 1;
	foreach($pp as $k=>$perf){
		$str.= "<li class=\"rientro\" style=\"margin-bottom: 10px;\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><meta itemprop=\"position\" content=\"".$position."\" /><div itemprop=\"item\" itemscope itemtype=\"http://schema.org/CreativeWork\"><a itemprop=\"url\" href=\"performances/".$perf['permalink']."/\" title=\"".$perf['titolo']."\"><img itemprop=\"image\" class=\"img-responsive\" src=\"https://flxer.net".str_replace("/90x68/", "/400x300/", $perf['img_arr'])."\" alt=\"".$perf['titolo']."\" /></a><h3 itemprop=\"name\"><a href=\"performances/".$perf['permalink']."/\" title=\"".$perf['titolo']."\">".$perf['titolo']."</a></h3><p class=\"type\">".$perf['typeStr']." </p>";
		$position++;
		if ($avnode_events_options['avnode_events_show_schedule']) {
			foreach($perf['schedule'] as $kk=>$schedule){
				$str.= "<p class=\"stit ".($schedule[0]['typeStr']!="Workshop" ? "schedule" : "")."\"><span itemprop=\"contentLocation\">".str_replace(array(" VJ-DJ Sets"," Lectures"),"", $schedule[0]['room'])."</span> ".writeDataPerf($schedule[0]['data_i'],$schedule[0]['data_f'],$schedule[0]['typeStr'])."</p>";
			}
		}
		$str.= "</div></li>";
	}
	$str.= "
						</ul></div></div></div>";
	return $str;	
}
function writePerformanceDett($obj, $title,$performances){
	$soldout = array(4451,4852,4850,5380);
	$cancelled = array(5361);
	//$title = $title.": ".$obj['titolo'];
	//$title = __('Performance', 'avnode-events2').": ".$obj['titolo'];
	//$title = $obj['titolo'];
	$pp = array();
	if (is_array($performances) && count($performances)) {
		foreach($performances as $k=>$perfBlock) {
			foreach($perfBlock as $perf){
				if($obj['id']==$perf['id']){
					if ($pp[$perf['permalink']]) {
						$pp[$perf['permalink']]['schedule'][$perf['data_i'].$perf['data_f']] = $perf;
					} else {
						$pp[$perf['permalink']] = $perf;
						$pp[$perf['permalink']]['schedule'] = array();
						$pp[$perf['permalink']]['schedule'][$perf['data_i'].$perf['data_f']] = $perf;
					}
					//print_r("aa");
					$obj['paypal'] = $perf['paypal'];
				}
			}
		}
	}
	
	
	$data = "<p class=\"stit\">";
	foreach($pp as $k=>$perf){
			foreach($perf['schedule'] as $kk=>$schedule){
				//print_r($perf['permalink'].$kk."\n");
				//foreach($ss as $schedule){
					$data.= "<span class=\"".($schedule['typeStr']!="Workshop" ? "schedule" : "")."\"><span itemprop=\"contentLocation\">".str_replace(array(" VJ-DJ Sets"," Lectures"),"", $schedule['room'])."</span> ".writeDataPerf($schedule['data_i'],$schedule['data_f'],$schedule['typeStr'])."<span class=\"hide\" itemprop=\"datePublished\">".date("c", strtotime($schedule['data_i']))."</span></span>";
				//}
			}
		if ($avnode_events_options['avnode_events_show_schedule']) {
		}
	}
	$data.= "</p>";
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	if($obj['paypal']){
		$paypal = "
							<div style=\"margin:top:10px;\" class=\"text-right\">
								<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\"".(in_array($perf['id'], $soldout) ? " onsubmit=\"alert('SOLD OUT!!!'); return false;\"" : "").">
									<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\"><input type=\"hidden\" name=\"quantity\" value=\"1\" /><input type=\"hidden\" name=\"hosted_button_id\" value=\"".$obj['paypal']."\"><input type=\"image\" src=\"https://www.paypalobjects.com/en_US/IT/i/btn/btn_buynowCC_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\"><img alt=\"\" border=\"0\" src=\"https://www.paypalobjects.com/en_US/i/scr/pixel.gif\" width=\"1\" height=\"1\" />
								</form>
							</div>
		";
	}
	$str = "
			<div class=\"row performance-dett\">
				<div class=\"col-sm-9\">
					<div class=\"row\">
						<div class=\"col-sm-3\">
							<img itemprop=\"image\" class=\"img-responsive\" alt=\"".urlencode($obj['titolo'])." MAIN IMAGE\" src=\"https://flxer.net".$obj['img_arr']."\">
						</div>
						<div class=\"col-sm-9\">
							<h1 itemprop=\"name\" class=\"entry-title\">".$obj['titolo']."</h1>
							<div class=\"rientro\">\n";
	if (in_array($obj['id'], $soldout)) $str.= "
								<div class=\"caption\"><div class=\"caption-soldout\">SOLD-OUT</div></div>";
	if (in_array($obj['id'], $cancelled)) $str.= "
								<div class=\"caption\"><div class=\"caption-soldout\">CANCELLED</div></div>";
	$str.= "

								<p class=\"type\">".$obj['typeStr']."</p>
								".$data."
							<div itemprop=\"description\">".getMLFieldValue($obj['testo'])."</div>";
	if($paypal){
		$str.= "

								<div>".$paypal."</div>";
	}

	$str.= "</div>\n";
	
	if($obj['gallery']){
		$str.= "
								<div itemprop=\"exampleOfWork\" itemscope itemtype=\"http://schema.org/ItemList\">
								<h3 itemprop=\"name\" class=\"grid-title h1like\">Gallery</h3>
								<div class=\"rientro\">".writeGalleryList($obj['gallery'])."</div>
								</div>";
	}

	$str.= "</div></div></div>
				<div class=\"col-sm-3\">
					<h2 class=\"grid-title h1like\">".__("Author", 'avnode-events2')."</h2>
					<div class=\"rientro\">
						<ul class=\"lists\" itemprop=\"author\" itemscope itemtype=\"http://schema.org/ItemList\">";
	$position = 1;
	foreach($obj['performers'] as $auth){
		$str.= "<li itemprop=\"itemListElement\" class=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">";
		$str.= "<meta itemprop=\"position\" content=\"".$position."\" />";
		$str.= "<div itemprop=\"item\" itemscope itemtype=\"http://schema.org/Person\">";
		$str.= "<div class=\"media\">";
		$str.= "<div class=\"media-left\">";
		$str.= "<a itemprop=\"url\" class=\"pull-left\" href=\"../../../".$auth['login']."/\" title=\"".$auth['nomearte']."\"><img itemprop=\"image\" class=\"media-object\" src=\"https://flxer.net".$auth['avatar']."\" alt=\"".$auth['nomearte']."\" /></a>";
		$str.= "</div>";
		$str.= "<div class=\"media-body\"><h2 itemprop=\"name\" class=\"media-heading\"><a href=\"../../../".$auth['login']."/\" title=\"".$auth['nomearte']."\">".$auth['nomearte']."</a></h2><p class=\"stit\">".($auth['locations'] ? "<span class=\"stit\">".writeLocationsStr($auth['locations'])."</span>" : "")."</p>";
		$str.= "</div>";
		$str.= "</div>";
		$str.= "<div itemprop=\"description\">".getMLFieldValue($auth['testo'])."</div>
				<p><strong>LINKS</strong></p>
				<ul class=\"squared10aa\">
					<li><a target=\"_blank\" href=\"https://flxer.net/".$auth['login']."/\">flxer.net/".$auth['login']."</a></li>";
					if($auth['websites']){
						foreach($auth['websites'] as $website) {
							$str.= "<li><a itemprop=\"url\" target=\"_blank\" href=\"".$website['url']."\">".$website['txt']."</a></li>";
						}
					}
					$str.= "
				</ul>";
		$str.= "</li>";
		$position++;
	}
	$str.= "
						</ul></div></div></div>";
	$str.= "
	<script type='text/javascript'>var id=".$obj['id'].";var tab='performance';</script>";
	wp_enqueue_script( 'setViewed', 		'https://flxer.net/_script/setViewed.js', 		array( 'jquery' ), false, true );
	return $str;	
}

function writeLocationsStr($locations,$city=true){
	$row1 = "";
	$locA = array();
	foreach($locations as $k=>$p){
		$locA[$p["country"]][] = $city ? "<span itemprop=\"addressLocality\">".$p["city"]."</span>" : "";
	}
	foreach($locA as $k=>$p){
		$row1.="<b itemprop=\"addressCountry\">".$k."</b>, ". (count($p) ? implode(", ",array_unique($p)).", " : "");
	}
	$row1 = substr($row1,0,strlen($row1)-2);
	$row1 = "<span itemprop=\"workLocation\" itemscope itemtype=\"http://schema.org/Place\"><span itemprop=\"address\" itemscope itemtype=\"http://schema.org/PostalAddress\">".$row1."</span></span>";
	return $row1;	
}

function writeUserBox($row,$basepath,$performances,$show_schedule=true,$position){
	$titolo = $row["nomearte"];
	//$url = "/".$row['performers'][0]["login"]."/".$basepath."/".$row['permalink']."/".$param;
	//$url = "../".$basepath."/".$row['login']."/";
	$url = $row['login']."/";
	$avnode_events_options = get_option('avnode_events');
	$thumb_size = $avnode_events_options['thumb_size'] ? $avnode_events_options['thumb_size'] : "55x55";
	$thumb_sizeA = explode("x", $thumb_size);
	$img = str_replace("90x68", $thumb_size,$row['avatar']);			
	$row1 = ($row['locations'] ? writeLocationsStr($row['locations']) : "");
	//$row1.= "<br />";
	$rowT = "";
	if ($performances) {
		foreach($performances as $k=>$p){
			if ($show_schedule && $p["room"]) $rowT.= strftime("%H:%M %A %e",strtotime($p["data_i"])).":<br />";
			$rowT.= "<b>".$p["titolo"]."</b><br />";
		}
		if ($rowT) $row2 = substr($rowT,0,strlen($rowT)-6);
	}
	//$str = "<p><a href=\"".$url."\" title=\"".$titolo."\"><span class=\"myClearIE\"><span class=\"thumb\"><img src=\"https://flxer.net".$img."\" alt=\"".$titolo_attr."\" /></span><span class=\"cnt\">".($sottotitolo ? "<span class=\"stit\">".$sottotitolo."</span>" : "")."<span class=\"tit\">".$titolo."</span><br /><span class=\"stit\">".$row1."".$row2."</span><span class=\"stit pt5\">".substr($rowT,0,strlen($rowT)-6)."</span></span><br class=\"myClear\" /></span></a></p><hr class=\"listHr\">";
	if($thumb_sizeA[0]>128) {
		$str = "<li class=\"itemListElement\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">";
		$str.= "<meta itemprop=\"position\" content=\"".$position."\" />";
		$str.= "<div itemprop=\"item\" itemscope itemtype=\"http://schema.org/Person\">";
		$str.= "<div><a itemprop=\"url\" href=\"".$url."\" title=\"".$titolo."\"><img itemprop=\"image\" class=\"img-responsive\" src=\"https://flxer.net".$img."\" alt=\"".$titolo."\" /></a></div>";
		$str.= "<div class=\"caption\">";
		//$str.= "<div class=\"caption-time\">".$sottotitolo."bbb</div>";
		$str.= "<div class=\"caption-body\">";
		$str.= "<h2 itemprop=\"name\" class=\"caption-title\">".$titolo."</h2>";
		$str.= "<div class=\"caption-location-artists\">".$row1."</div>";
		$str.= "</div>";
		$str.= "</div>";
		/*
		$str.= "<div class=\"caption-type\">";
		$str.= "<div class=\"caption-type-cnt\">";
		$str.= $row2;
		$str.= "</div>";
		$str.= "</div>";
		*/
		$str.= "</div>";
		$str.= "</li>";
	} else {
		$str = "<li class=\"media\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">";
		$str.= "<meta itemprop=\"position\" content=\"".$position."\" />";
		$str = "<div itemprop=\"item\" itemscope itemtype=\"http://schema.org/Person\">";
		$str.= "<div><a itemprop=\"url\" class=\"pull-left\" href=\"".$url."\" title=\"".$titolo."\"><img itemprop=\"image\" class=\"media-object\" src=\"https://flxer.net".$img."\" alt=\"".$titolo."\" /></a></div>";
		$str.= "<div class=\"media-body\">";
		$str.= "".($sottotitolo ? "<span class=\"stit\">".$sottotitolo."</span>" : "")."";
		$str.= "<h2 class=\"media-heading\"><a itemprop=\"name\" href=\"".$url."\" title=\"".$titolo."\">".$titolo."</a></h2>";
		$str.= "<p class=\"stit\">".$row1."</p>";
		$str.= "<p class=\"stit\">".$row2."</p>";
		$str.= "</div>";
		$str.= "</div>";
		$str.= "</li>";
	}
	return $str;	
}



function writeBlock($row,$basepath,$position,$param="",$callOpen=false){
	$soldout = array(4451,4852,4850,5380);
	$cancelled = array(5361);


	$titolo = $row["titolo"];
	$permalink = explode("/",$_SERVER['REQUEST_URI']);
	if ($permalink[1]=="it") array_splice($permalink, 1, 1);
	if ($permalink[1]=="wp-json") array_splice($permalink, 1, 1);
	if ($permalink[1]=="wp") array_splice($permalink, 1, 1);
	if ($permalink[1]=="v2") array_splice($permalink, 1, 1);
	//print_r($permalink);
	//$url = "/".$row['performers'][0]["login"]."/".$basepath."/".$row['permalink']."/".$param;
	global $q_config;
	$url = "";
	if (isset($q_config) && $q_config['language'] && $q_config['language']!=$q_config['default_language']) {
		$url.= "/".$q_config['language']."/".$permalink[1]."/".$permalink[3];
	} else {
		$url.= "/".$permalink[1]."/".$permalink[3];
	}
	$url.= "/artists/".$row['performers'][0]['login']."/".$basepath."/".$row['permalink']."/".$param;
	//print_r($row['performers'][0]);
	//echo($url."\n");
	$avnode_events_options = get_option('avnode_events');
	$thumb_size = $avnode_events_options['thumb_size'] ? $avnode_events_options['thumb_size'] : "55x55";
	$thumb_sizeA = explode("x", $thumb_size);
	$img = str_replace("90x68", $thumb_size,$row['img_arr']);			
	$row1 = "";
	if ($row['data_i'] && !$callOpen) {
		$sottotitolo="<span class=\"hide\" itemprop=\"datePublished\">".date("c", strtotime($row['data_i']))."</span><span class=\"stit\">".date("H:i", strtotime($row['data_i'])).(date("H:i", strtotime($row['data_i']))!=date("H:i", strtotime($row['data_f'])) ? " - ".date("H:i", strtotime($row['data_f'])) : "").""."</span>";
	}
	$position = 1;
	foreach($row['performers'] as $k=>$p){
		$row1.=($k==0 ? "" : "")."";
		$row1.= "<div class=\"caption-author\" itemprop=\"author\" itemscope itemtype=\"http://schema.org/Person\">";
		$row1.= "<b itemprop=\"name\">".$p["nomearte"]."</b>";
		$row1.= "".($p['locations'] ? " <span class=\"caption-location".(count($row['performers'])>3 ? " caption-location-inline" : " caption-location-block")."\">".writeLocationsStr($p['locations'],count($row['performers'])>3 ? false : true)."</span>" : "");
		$row1.= "</div> ";
	}
	$row2 = "<span class=\"glyphicon glyphicon-eye-open\"></span> ".$row['visite']." | ".$row['typeStr'];
	if($thumb_sizeA[0]>128) {
		$str = "<li class=\"itemListElement\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">";
		$str.= "<div itemprop=\"item\" itemscope itemtype=\"http://schema.org/CreativeWork\">";
		$str.= "<a itemprop=\"url\" href=\"".$url."\" title=\"".$titolo."\"><img itemprop=\"image\" class=\"img-responsive\" src=\"https://flxer.net".$img."\" alt=\"".$titolo_attr."\" /></a>";
		$str.= "<div class=\"caption\">";
		if (in_array($row['id'], $soldout)) $str.= "<div class=\"caption-soldout\">SOLD-OUT</div>";
		if (in_array($row['id'], $cancelled)) $str.= "<div class=\"caption-soldout\">CANCELLED</div>";
		$str.= "<div class=\"caption-time".($row['typeStr']!="Workshop" ? " schedule" : "")."\">".$sottotitolo."</div>";
		$str.= "<div class=\"caption-body\">";
		$str.= "<h2 itemprop=\"name\" class=\"caption-title\">".$titolo."</h2>";
		$str.= "".$row1."";
		$str.= "</div>";
		$str.= "<div class=\"caption-type\">";
		$str.= "<div class=\"caption-type-cnt\">";
		$str.= $row2;
		$str.= "</div>";
		$str.= "</div>";
		$str.= "</div>";
		$str.= "</div>";
		$str.= "</li>";
	} else {
		$str = "<li class=\"media\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">";
		$str = "<div itemprop=\"item\" itemscope itemtype=\"http://schema.org/CreativeWork\">";
		$str.= "<a itemprop=\"url\" href=\"".$url."\" title=\"".$titolo."\" class=\"pull-left\"><img itemprop=\"image\" class=\"media-object\" src=\"https://flxer.net".$img."\" alt=\"".$titolo_attr."\" /></a>";
		$str.= "<div class=\"media-body\">";
		$str.= "".$sottotitolo."";
		$str.= "<h2 itemprop=\"name\" class=\"media-heading\"><a href=\"".$url."\" title=\"".$titolo_attr."\">".$titolo."</a></h2>";
		$str.= "<p class=\"stit\">".$row1."</p>";
		$str.= "<p class=\"stit\">".$row2."</p>";
		$str.= "</div>";
		$str.= "</div>";
		$str.= "</li>";
	}
	return $str;	
}

function writeBlockLarge($row,$basepath,$position,$param="",$callOpen=false){
	$soldout = array(4451,4852,4850);
	$titolo = $row["titolo"];
	$permalink = explode("/",$_SERVER['REQUEST_URI']);
	//$url = "/".$row['performers'][0]["login"]."/".$basepath."/".$row['permalink']."/".$param;
	global $q_config;
	$url = "";
	$lang = "en";
	if (isset($q_config) && $q_config['language'] && $q_config['language']!=$q_config['default_language']) {
		$url.= "/".$q_config['language']."/".$permalink[2]."/".$permalink[3];
		$lang = $q_config['language'];
	} else {
		$url.= "/".$permalink[1]."/".$permalink[2];
	}
	$url.= "/artists/".$row['performers'][0]['login']."/".$basepath."/".$row['permalink']."/".$param."/";
	//print_r($row['performers'][0]);
	//echo($url."\n");
	$avnode_events_options = get_option('avnode_events');
	$thumb_size = $avnode_events_options['thumb_size'] ? $avnode_events_options['thumb_size'] : "55x55";
	$thumb_sizeA = explode("x", $thumb_size);
	$img = str_replace("90x68", $thumb_size,$row['img_arr']);			
	$row1 = "";
	if ($row['data_i'] && !$callOpen) {
		$sottotitolo="";
	}
	foreach($row['performers'] as $k=>$p){
		$row1.=($k==0 ? "" : "")."";
		$row1.= "<div class=\"caption-author\" itemprop=\"author\" itemscope itemtype=\"http://schema.org/Person\">";
		$row1.= "	<h3 itemprop=\"name\">".$p["nomearte"]."</h3>";
		$row1.= "	".($p['locations'] ? " <p class=\"caption-location".(count($row['performers'])>3 ? " caption-location-inline" : " caption-location-block")."\">".writeLocationsStr($p['locations'],count($row['performers'])>3 ? false : true)."</p>" : "");
		$row1.= "	<div class=\"caption-time2\">".$p["testo"][$lang]."</div>";
		$row1.= "</div> ";
	}
	$row2 = "<span class=\"glyphicon glyphicon-eye-open\"></span> ".$row['visite']." | ".$row['typeStr'];
	if($thumb_sizeA[0]>128) {
		$str = "<div itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">";
		$str.= "<div class=\"row\" itemprop=\"item\" itemscope itemtype=\"http://schema.org/CreativeWork\">";
		$str.= "	<div class=\"col-md-4\">";
		$str.= "		<a itemprop=\"url\" href=\"".$url."\" title=\"".$titolo."\"><img itemprop=\"image\" class=\"img-responsive\" src=\"https://flxer.net".$img."\" alt=\"".$titolo_attr."\" /></a>";
		$str.= "	</div>";
		$str.= "	<div class=\"col-md-8\">";
		if (in_array($row['id'], $soldout)) $str.= "		<div class=\"caption-soldout\">SOLD-OUT</div>";
		$str.= "		<div class=\"h1 entry-title\"><span class=\"hide\" itemprop=\"datePublished\">".date("c", strtotime($row['data_i']))."</span><div style=\"display: inline\" class=\"stit\">".date("H:i", strtotime($row['data_i'])).(date("H:i", strtotime($row['data_i']))!=date("H:i", strtotime($row['data_f'])) ? " - ".date("H:i", strtotime($row['data_f'])) : "").""."</div><h2 itemprop=\"name\" class=\"entry-title\" style=\"display: inline\">".$titolo."</h2></div>";
		$str.= "		<div class=\"caption-body2\">";
		$str.= "			";
		$str.= "			<p class=\"caption-type-cnt2\">";
		$str.= $row2;
		$str.= "			</p>";
		$str.= "			<div class=\"caption-time2\">".$row["testo"][$lang]."</div>";
		$str.= "".$row1."";
		$str.= "		</div>";
		$str.= "	</div>";
		$str.= "</div>";
		$str.= "</div>";
	} else {
		$str = "<div class=\"media\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">";
		$str.= "<div itemprop=\"item\" itemscope itemtype=\"http://schema.org/CreativeWork\">";
		$str.= "<a itemprop=\"url\" href=\"".$url."\" title=\"".$titolo."\" class=\"pull-left\"><img itemprop=\"image\" class=\"media-object\" src=\"https://flxer.net".$img."\" alt=\"".$titolo_attr."\" /></a>";
		$str.= "<div class=\"media-body\">";
		$str.= "".$sottotitolo."";
		$str.= "<h2 itemprop=\"name\" class=\"media-heading\"><a href=\"".$url."\" title=\"".$titolo_attr."\">".$titolo."</a></h2>";
		$str.= "<p class=\"stit\">".$row1."</p>";
		$str.= "<p class=\"stit\">".$row2."</p>";
		$str.= "</div>";
		$str.= "</div>";
		$str.= "</div>";
	}
	return $str;	
}

function writeBlock2($row,$basepath,$position,$param="",$callOpen=false){
	$permalink = explode("/",$_SERVER['REQUEST_URI']);
	global $q_config;
	$url = "";
	if (isset($q_config) && $q_config['language'] && $q_config['language']!=$q_config['default_language']) {
		$url.= "/".$q_config['language']."/".$permalink[2];
	} else {
		$url.= "/".$permalink[1];
	}
	$url.= "/artists/".$row['performers'][0]['login']."/".$basepath."/".$row['permalink']."/".$param."/";
	$avnode_events_options = get_option('avnode_events');
	$thumb_size = $avnode_events_options['thumb_size'] ? $avnode_events_options['thumb_size'] : "55x55";
	$thumb_sizeA = explode("x", $thumb_size);
	$img = str_replace("90x68", $thumb_size,$row['img_arr']);			

	$str = "		<tr>";
	$str.= "			<td colspan=\"2\" valign=\"top\">";
	$str.= "				<h2 itemprop=\"name\" class=\"media-heading\" style=\"margin-top:0\">".$row["titolo"]."</h2>";
	$str.= "				<p>".$row['typeStr']."</p>";
	$str.= "				<p><span class=\"".($row['typeStr']!="Workshop" ? "schedule" : "")."\"><span itemprop=\"contentLocation\">".str_replace(array(" VJ-DJ Sets"," Lectures"),"", $row['room'])."</span> ".writeDataPerf($row['data_i'],$row['data_f'],$row['typeStr'])."<span class=\"hide\" itemprop=\"datePublished\">".date("c", strtotime($row['data_i']))."</span></span></p>";
	$str.= "				<div>".getMLFieldValue($row['testo'])."</div>";
	$str.= "			</td>";
	$str.= "			<td valign=\"top\" align=\"right\">";
	$str.= "				<img itemprop=\"image\" class=\"img-responsive\" width=\"400\" src=\"https://flxer.net".$img."\" alt=\"".$row["titolo"]."\" />";
	$str.= "			</td>";
	$str.= "		</tr>";
	foreach($row['performers'] as $k=>$p){
		$avatar = str_replace("90x68", $thumb_size,$p['avatar']);			
		$str.= "		<tr>";
		$str.= "			<td valign=\"top\">";
		$str.= "				<img itemprop=\"image\" class=\"img-responsive\" src=\"https://flxer.net".str_replace("400x300","128x96",$avatar)."\" alt=\"".$p["nomearte"]."\" />";
		$str.= "			</td>";
		$str.= "			<td colspan=\"2\" valign=\"top\">";
		$str.= "				<h3 style=\"margin-top:0\">".$p["nomearte"]."</h3>".($p['locations'] ? "<p>".writeLocationsStr($p['locations'])."</p>" : "");
		$str.= "				<div>".getMLFieldValue($p['testo'])."</div>";
		if($p['websites']){
			$str.= "				<p><strong>LINKS</strong></p>";
			$str.= "				<ul class=\"squared10\">";
			$str.= "					<li><a itemprop=\"url\" target=\"_blank\" href=\"https://flxer.net/".$p['login']."/\">flxer.net/".$p['login']."</a></li>";
			foreach($p['websites'] as $website) {
				$str.= "					<li><a itemprop=\"url\" target=\"_blank\" href=\"".$website['url']."\">".$website['url']."</a></li>";
			}
			$str.= "				</ul>";
		}
		$str.= "			</td>";
		$str.= "		</tr>";
	}
	$str.= "		<tr><td colspan=\"3\"><hr /></td></tr>";
	return $str;	
}

function writeDataPerf($data_i,$data_f,$typeStr=""){
	//print_r(setlocale  (LC_ALL,"0"));
	//setlocale(LC_ALL, 'it_IT.UTF-8');
	$i = strtotime($data_i);
	$f = strtotime($data_f);
	$it = strftime("%H:%M",$i);
	$ft = strftime("%H:%M",$f);
	$conta = -1;
	if ($typeStr=="Video Installation" || $typeStr=="Lights Installation") {
		 $str.=$it." ".strftime("%A %e %B",$i)." &gt; ".$ft." ".strftime("%A %e %B",$f)." <br /> ";
	} else {
		for($a=$i; $a<=$f;$a+=(24*60*60)){
			$str.=(($it!=$ft ? $it." - ".$ft : $it)." ".strftime("%A %e %B",$a)." <br /> ");
			$conta++;
		}
	}
	$str = ($conta ? "<br />" : " | ").$str;

	return $str;	
}

?>