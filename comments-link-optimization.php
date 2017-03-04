<?php
/**
 * @package comments-link-optimization
 */
/*
Plugin Name: Comments Link Optimization
Plugin URI: https://www.litefeel.com/comments-link-optimization/
Description: Comments Link Optimization what prevent all search engine crawl your comments link. 
Version: 1.10
Author: lite3
Author URI: https://www.litefeel.com/
License: GPLv2 or later
Text Domain: comments-link-optimization
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


class CommentsLinkOptimization
{

function __construct() {
	add_action('init', array($this, 'init'));
	add_filter('comment_text', array($this, 'modifyCommentText'), 99);
	add_filter('get_comment_author_url', array($this, 'modifyCommentAuthorUrl'), 99);
}

function init() {
	load_plugin_textdomain( 'comments-link-optimization' );
	$this->checkRedirect();
}

function modifyCommentAuthorUrl($url){
	// global $wp;
	// $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

	if (!is_admin()) {
		$home = home_url();
		if(!empty($url) && stripos($url, $home) !== 0){
			$url = "$home/?r=$url";
		}
	}
	return $url;
}

function modifyCommentText($text){
	if (!is_admin()) {
		$home = home_url();
		$text = preg_replace_callback(
			'/(<a [^>]*?href=[\'"])\s*([^\s#]\S+)\s*([\'"][^>]*?>)/',
			function($matchs) use ($home) {
				$url = $matchs[2];
				if (stripos($url, $home) !== 0) {
					return "${matchs[1]}$home/?r=$url${matchs[3]}";
				}
				return $url;
			},
			$text
		);
	}
	return $text;
}

// 
function checkRedirect() {
	$redirect = isset($_GET['r']) ? $_GET['r'] : FALSE;
	if($redirect){
		$home = home_url();
		error_log(  __CLASS__ . ": " . $_SERVER['HTTP_REFERER'] );
		if(!empty($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $home) !== FALSE){
			//header("Location: $redirect");
			$this->printHTML($redirect);
		}else {
			header("Location: $home");
		}
		exit;
	}
}

function printHTML($url)
{
	$title = __('Redirecting', 'comments-link-optimization');
	$jumpContent = sprintf(
		/* translators: %s: Html tag <a> */
		__('The page will jump to %s after 3 seconds.', 'comments-link-optimization'),
		"<a href=\"$url\">$url</a>"
	);

	$backContent = sprintf(
		/* translators: 1: The begin of html tag <a> 2: The end of html tag <a> */
		__('If you do not want to visit the page, you can %1$s return to the previous page %2$s .',
			'comments-link-optimization'),
		'<a href="#" onclick="return goback();">',
		'</a>'
	);
	
echo <<<EOT
<html>
<head>
	<meta name="robots" content="noindex, nofollow">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>$title</title>
	<style type="text/css">
		body,td,div,.p,a{font-family:arial,sans-serif}
		div,td{color:#000}
		.f{color:#6f6f6f}
		a:link{color:#00c}
		a:visited{color:#551a8b}
		a:active{color:red}
		div.a{border-top:1px solid #bbb;border-bottom:1px solid #bbb;background:#f2f2f2;margin-top:1em;width:100%}
		div.b{padding:0.5em 0;margin-left:10px}
		div.c{margin-top:35px;margin-left:35px}
	</style>
	<script type="text/javascript">
		function goback() {window.history.go(-1);return false;}
		setTimeout(function(){window.location.href="$url";},3000);
	</script>
</head>
<body topmargin=3 bgcolor=#ffffff marginheight=3>
<div class=a><div class=b><font size=+1><b>$title</b></font></div></div><div class=c>&nbsp;$jumpContent
<br><br>&nbsp;$backContent<br><br><br></div>
</body>
</html>
EOT;
}

}

$commentsLinkOptimization = new CommentsLinkOptimization();
