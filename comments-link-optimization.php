<?php
/**
 * @package comments-link-optimization
 */
/*
Plugin Name: Comments Link Optimization
Plugin URI: http://www.litefeel.com/comments-link-optimization/
Description: Comments Link Optimization waht prevent all search engine crawl your comments link. 
Version: 1.8.3.2
Author: lite3
Author URI: http://www.litefeel.com/

Copyright (c) 2011
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt
*/

//comments link redirect
add_filter('comment_text', 'add_redirect_comment_text', 99);
function add_redirect_comment_text($text = ''){
	$home = get_option('home');
	$len = strlen($home);
	$pos = stripos($text, 'href=');
	while($pos !== FALSE){
		if('"' == $text[$pos+5] || '\'' == $text[$pos+5]){
			// for reply
			if('#' == $text[$pos+6]){
				$pos += 7;
			}
			// for self link
			else if(substr($text, $pos+6, $len) == $home){
				$pos += len + 7;
			}
			// for other link
			else {
				$text = substr($text, 0, $pos+6) . $home . '/?r=' . substr($text, $pos + 6);
				$pos += len + 7;
			}
		}else{
			$pos += 7;
		}
		$pos = stripos($text, 'href=', $pos);
	}
	return $text;
}

add_filter('get_comment_author_url', 'add_redirect_get_comment_author_url', 99);
function add_redirect_get_comment_author_url($url){
	$home = get_option('home');
	if($url && stripos($url, $home) !== 0 && !is_admin()){
		$url = $home . '/?r=' . $url;
	}
	return $url;
}

add_action('init', 'redirect_comment_link');
function redirect_comment_link(){
	$redirect = isset($_GET['r']) ? $_GET['r'] : false;
	if($redirect){
		if('' == $_SERVER['HTTP_REFERER'] || strpos($_SERVER['HTTP_REFERER'],get_option('home')) !== false){
			//header("Location: $redirect");
			output_redirect($redirect);
		}else {
			header('Location: ' . get_option('home'));
		}
		exit;
	}
	
	check_robots();
}

function output_redirect($url)
{
echo '<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>重新转向通知</title>
<meta name="robots" content="noindex, nofollow"><style><!--
body,td,div,.p,a{font-family:arial,sans-serif}
div,td{color:#000}
.f{color:#6f6f6f}
a:link{color:#00c}
a:visited{color:#551a8b}
a:active{color:red}
div.a{border-top:1px solid #bbb;border-bottom:1px solid #bbb;background:#f2f2f2;margin-top:1em;width:100%}
div.b{padding:0.5em 0;margin-left:10px}
div.c{margin-top:35px;margin-left:35px}
--></style>
<script>function go_back() {window.history.go(-1);return false;}
setTimeout(function(){ window.location.href="'.$url.'";},3000);
</script></head>';
echo '<body topmargin=3 bgcolor=#ffffff marginheight=3><div class=a><div class=b><font size=+1><b>重新转向通知</b></font></div></div><div class=c>&nbsp;您所在的网页3秒后自动您带往 <a href="' . $url . '">' .$url . '</a> 。<br><br>&nbsp;如果您不想造访该网页，您可以<a href="#" onclick="return go_back();">返回前一页</a>。<br><br><br></div></body></html>';
}

function check_robots(){
	$path = parse_url(get_option('home') . '/');
	$root = str_replace( $_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"] );
	$robots_path = $root . '/robots.txt';
	
	$m_time = -1;
	if(file_exists($robots_path)){
		$m_time = filemtime($robots_path);
	}
	
	$old_time = get_option('robots_modify_time');
	if(FALSE == $old_time || $old_time != $m_time || $m_time <= 0){
		$disallow = 'Disallow: ' . $path['path'] . '?r=*';
		$str = -1 == $m_time ? '' : file_get_contents($robots_path);
		if(FALSE === strpos($str, $disallow)){
			$str .= "\n" . $disallow;
			file_put_contents($robots_path, $str);
			$m_time = filemtime($robots_path);
		}
		
		if(FALSE == $old_time){
			add_option('robots_modify_time', $m_time);
		}else{
			update_option('robots_modify_time', $m_time);
		}
	}
}

?>