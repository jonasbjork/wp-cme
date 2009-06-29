<?php
/*
Plugin Name: cMe (See Me)
Plugin URI: http://www.jonasbjork.net/projects/wordpress-cme-see-me/
Description: Tell the world about your posts.
Version: 0.03
Author: Jonas Björk
Author URI: http://www.jonasbjork.net/

License: GPL v2, with sole exeption that license does not hold.

*/

// Post to Linuxportalen.se
function jb_doDrupalAPIPost($content) {

	$appkey		= "0123456789ABCDEF";
	$rpcurl		= get_option('drupal-account-rpc');
	$blogid		= get_option('drupal-account-blogid');
	$username	= get_option('drupal-account-username');
	$password	= get_option('drupal-account-password');
	$publish	= "true";

$data = <<<EOF
<?xml version="1.0"?>
<methodCall>
<methodName>blogger.newPost</methodName>
<params>
<param><value><string>$appkey</string></value></param>
<param><value><string>$blogid</string></value></param>
<param><value><string>$username</string></value></param>
<param><value><string>$password</string></value></param>
<param><value><string>$content</string></value></param>
<param><value><boolean>$publish</boolean></value></param>
</params>
</methodCall>
EOF;

	$header[] = "Content-type: text/xml";
	$header[] = "Content-length: ".strlen($data)."\r\n";
	$header[] = $data;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $rpcurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_POST, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}

// Uodate Facebook status
function jb_doPostFacebook($status_msg) {
	$username = get_option('facebook-account-username');
	$password = get_option('facebook-account-password');
	$firstname = get_option('facebook-account-firstname');

	$cookiejar = get_temp_dir().$firstname."-".sha1(mt_rand())."-cookiejar.txt";
	$fp = fopen( $cookiejar ,"w+") or die("<BR><B>Unable to open cookie file $cookiejar for write!<BR>");
	fclose($fp);

 	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://login.facebook.com/login.php?m&amp;next=http%3A%2F%2Fm.facebook.com%2Fhome.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS,'email='.urlencode($username).'&pass='.urlencode($password).'&login=Login');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
	curl_exec($ch);

	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch, CURLOPT_URL, 'http://m.facebook.com/home.php');
	$page = curl_exec($ch);
	curl_setopt($ch, CURLOPT_POST, 1);
	preg_match('/name="post_form_id" value="(.*)" \/>'.ucfirst($firstname).'/', $page, $form_id);
	curl_setopt($ch, CURLOPT_POSTFIELDS,'post_form_id='.$form_id[1].'&status='.urlencode($status_msg).'&update=Update');
	curl_setopt($ch, CURLOPT_URL, 'http://m.facebook.com/home.php');
	curl_exec($ch);

	unlink($cookiejar); // Delete cookiefile

}

// Update Twitter.com status
function jb_doPostTwitter($title) {

	$twitUser	= get_option('twitter-account-username');
	$twitPass 	= get_option('twitter-account-password');

	//TODO: Denna text skall vara valbar..
	$data = "Published a new post: ".$title;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://twitter.com/statuses/update.xml");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "status=".$data);
	curl_setopt($ch, CURLOPT_USERPWD, "$twitUser:$twitPass");
	$buffer = curl_exec($ch);
	curl_close($ch);

	return $response;
}

function jb_createShortLink($url) {
	
	switch( get_option('use-shortlink-service') ) {

		case 'zz':
			$tinyUrl = file_get_contents("http://zz.gd/api-create.php?url=" . $url);
			break;
		case 'tinyurl':
		default:
			$tinyUrl = file_get_contents("http://tinyurl.com/api-create.php?url=" . $url);
			break;
	}

	return $tinyUrl;	
}

function jb_wp_cMe($post_ID) {
	$post_link = get_permalink($post_ID);
	$post_title = htmlspecialchars($_POST['post_title']);
	$post_content = htmlspecialchars($_POST['content']);

	if( get_option('use-shortlinks') == 1 ) {
		$post_link = jb_createShortLink($post_link);
	}

	if( get_option('cme-last-id') == $post_ID ) {
		return $post_ID;
	} else {
		update_option('cme-last-id', $post_ID);
	}
	$content = $post_title."\n".$post_content."\n\nRead more: ".$post_link;
	
	if( $_POST['prev_status'] == 'draft' ){ 
		if( $_POST['publish'] == 'Publish' ) {
			if( get_option('drupal-site-enable') == 1 ) {
				jb_doDrupalAPIPost($content);
			}
			if( get_option('twitter-enable') == 1 ) {
				$twitmsg = $post_title." (" . $post_link . ")";
				jb_doPostTwitter($twitmsg);
			}
			if( get_option('facebook-enable') == 1 ) {
				$facemsg = $post_title." (" . $post_link . ")";
				jb_doPostFacebook($facemsg);
			}
		}
	}
	return $post_ID;	
}

function jb_addcMeAdminPages() {
	if( function_exists('add_management_page') ) {
		add_management_page('cMe Config', 'cMe Config', 8, __FILE__, 'jb_cMe_manage_page');
	}
}

function jb_cMe_manage_page() {
	include( dirname(__FILE__).'/wp_cMe_manage.php' );
}
// Hook
add_action('publish_post', 'jb_wp_cMe');
add_action('admin_menu', 'jb_addcMeAdminPages');
?>
