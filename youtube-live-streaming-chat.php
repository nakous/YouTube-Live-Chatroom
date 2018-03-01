<?php
/*
Plugin Name: Youtube Live Streaming Chat
Plugin URI: http://howdevelopment.com
Description: Retrieves and displays the LiveChat for each post by linking to your Youtube account.
Author: Nakous Mustapha
Author URI: http://webmobi.ma
Version: 1.1.0
*/


define('GAPP_SLUG', 'youtube-live-streaming-chat');
require_once  dirname(__FILE__). '/lowerthird.php';
require_once  dirname(__FILE__). '/facebook.php';



if (function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain('youtube-live-streaming-chat', false, dirname(plugin_basename(__FILE__)).'/languages' );
}

add_action('admin_menu', 'gapp_config_page');

function gapp_config_page() {

	if (function_exists('add_submenu_page')) {

		add_submenu_page('options-general.php',
		__('Live Streaming', 'youtube-live-streaming-chat'),
		__('Live Streaming', 'youtube-live-streaming-chat'),
		'manage_options', GAPP_SLUG, 'gapp_conf');

	}

}

// Function live chat  api format XML 

function youtube_live_chat_messages_xml_download_endpoint() {
	add_rewrite_tag( '%live_chat_xml%', '([^&]+)' );
	add_rewrite_rule( 'gifs/([^&]+)/?', 'index.php?live_chat_xml=$matches[1]', 'top' );
}
add_action( 'init', 'youtube_live_chat_messages_xml_download_endpoint' );

function xml_download_template( $template ) {
	global $wp_query;
	$gif_tag = $wp_query->get( 'live_chat_xml' );

	if ( ! $gif_tag ) {
		return $template;
	}
	// header("Content-type: application/x-msdownload",true,200);
	header("Content-Disposition: attachment; filename=data.xml");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	$request = new WP_Http;
	$chats_item =array();
	$options = gapp_options();


	if(_youtube_live_comment()){
			$chats_item=json_decode(_youtube_live_comment());
			$chats_item = json_decode(json_encode($chats_item), true);
			print array_to_xml($chats_item, new SimpleXMLElement('<root/>'))->asXML();			

	}else{
		$error["message"]='Live chat ID not found';
	}

	
}
add_filter('template_include', 'xml_download_template');


function _youtube_live_comment(){
	$request = new WP_Http;
	$chats_item =array();
	$options = gapp_options();

	if(isset($options['gapp_wid'])){		
		$chats = $request->request('https://www.googleapis.com/youtube/v3/liveChat/messages', array(
			'method' => 'GET',
			'body' => array("part"=>'id,snippet,authorDetails',"liveChatId"=>$options['gapp_wid'],"key"=>$options['gapp_apikey'] )
		));
		if(isset($chats["body"])){
			return $chats["body"] ;
				
		}
	}
	return null;
	
}

function youtube_live_chat_messages_json_endpoint() {
	add_rewrite_tag( '%live_chat%', '([^&]+)' );
	add_rewrite_rule( 'gifs/([^&]+)/?', 'index.php?live_chat=$matches[1]', 'top' );

}
add_action( 'init', 'youtube_live_chat_messages_json_endpoint' );

function yjson_print_template( $template ) {
	global $wp_query;
	$gif_tag = $wp_query->get( 'live_chat' );

	if ( ! $gif_tag ) {
		return $template;
	}
	// header("Content-type: application/x-msdownload",true,200);
	// header("Content-Disposition: attachment; filename=data.json");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	$request = new WP_Http;
	$chats_item =array();
	$options = gapp_options();

	
	if(isset($options['gapp_wid'])){
		
		$chats = $request->request('https://www.googleapis.com/youtube/v3/liveChat/messages', array(
		'method' => 'GET',
		'body' => array("part"=>'id,snippet,authorDetails',"liveChatId"=>$options['gapp_wid'],"key"=>$options['gapp_apikey'] )
		));
		// ,"maxResults"=>20
		if(isset($chats["body"])){
			$chats_item=json_decode( $chats["body"]);
			if($chats_item->items)
			foreach($chats_item->items as $itm){					
				add_chat_toDB('',$options['gapp_wid'],$itm->snippet->publishedAt, json_encode($itm),$itm->id,$options['chname'],'youtube');
			}
		}
	}else{

		$error["message"]='Live chat ID not found';
	}
	if(!isset($error))
	wp_send_json( $chats_item);
	else
	wp_send_json( $error);
	
}

add_filter('template_include', 'yjson_print_template');
//Convert Array to XML
function array_to_xml(array $arr, SimpleXMLElement $xml) {
	foreach ($arr as $k => $v) {

		$attrArr = array();
		$kArray = explode(' ',$k);
		$tag = array_shift($kArray);

		if (count($kArray) > 0) {
			foreach($kArray as $attrValue) {
				$attrArr[] = explode('=',$attrValue);                   
			}
		}

		if (is_array($v)) {
			if (is_numeric($k)) {
				array_to_xml($v, $xml);
			} else {
				$child = $xml->addChild($tag);
				if (isset($attrArr)) {
					foreach($attrArr as $attrArrV) {
						$child->addAttribute($attrArrV[0],$attrArrV[1]);
					}
				}                   
				array_to_xml($v, $child);
			}
		} else {
			$child = $xml->addChild($tag, $v);
			if (isset($attrArr)) {
				foreach($attrArr as $attrArrV) {
					$child->addAttribute($attrArrV[0],$attrArrV[1]);
				}
			}
		}               
	}

	return $xml;
}

// Function live chat  api format JSON 

function youtube_live_chat_messages_json_download_endpoint() {
	add_rewrite_tag( '%live_chat_download%', '([^&]+)' );
	add_rewrite_rule( 'gifs/([^&]+)/?', 'index.php?live_chat_download=$matches[1]', 'top' );

}
add_action( 'init', 'youtube_live_chat_messages_json_download_endpoint' );

function json_download_template( $template ) {
	global $wp_query;
	$gif_tag = $wp_query->get( 'live_chat_download' );

	if ( ! $gif_tag ) {
		return $template;
	}
	header("Content-type: application/x-msdownload",true,200);
	// header("Content-Disposition: attachment; filename=data.json");
	header("Pragma: no-cache");
	header("Expires: 0");
	$oldc=list_old_chat_array();
	if(isset($_GET['live_chat_download'])){
		$items=$oldc[$_GET['live_chat_download']];
		if(!isset($_GET['type'])){
			header("Content-Disposition: attachment; filename=data.json");
			wp_send_json($items );
			}
			
		else{
			header("Content-Disposition: attachment; filename=data.xml");
			print array_to_xml($items, new SimpleXMLElement('<root/>'))->asXML();	
		
		}
			// print_r($items);
			
		}
	
}


add_filter('template_include', 'json_download_template');






function gapp_channel_info() {
	$request = new WP_Http;
	$options = gapp_options();

	$statistics = $request->request('https://www.googleapis.com/youtube/v3/channels', array(
	'method' => 'GET',
	'body' => array("part"=>'statistics,status,snippet',"id"=>$options['channelId'],"key"=>$options['gapp_apikey'] )
	));
	
	$tjson = json_decode($statistics["body"]);
	// print_r($tjson);
	$options['gapp_channelname']=$tjson->items[0]->snippet->title;
	$options['gapp_viewcount']=$tjson->items[0]->statistics->viewCount;
	$options['gapp_commentcount']=$tjson->items[0]->statistics->commentCount;
	$options['gapp_subscribercount']=$tjson->items[0]->statistics->subscriberCount;
	$options['gapp_videocount']=$tjson->items[0]->statistics->videoCount;

	update_option('gapp', $options);
}


function gapp_refresh_token() {

	$options = gapp_options();
	/* If the token has expired, we create it again */
	if (time() >= $options['gapp_expires']) {
		if (!empty($options['gapp_token_refresh'])) {
			$request = new WP_Http;
			$result = $request->request('https://accounts.google.com/o/oauth2/token', array(
			'method' => 'POST',
			'body' => array(
			'client_id' => $options['gapp_clientid'],
			'client_secret' => $options['gapp_psecret'],
			'refresh_token' => $options['gapp_token_refresh'],
			'grant_type' => 'refresh_token',
			),
			));
			$options['gapp_error'] = null;
			if ( is_array( $result ) && isset( $result['response']['code'] ) && 200 === $result['response']['code'] ) {
				$tjson = json_decode($result['body']);
				$options['gapp_token'] = $tjson->access_token;

				if (isset($tjson->refresh_token) && !empty($tjson->refresh_token)) {
					$options['gapp_token_refresh'] = $tjson->refresh_token;
				}
				
				
				$options['gapp_expires'] = time() + $tjson->expires_in;
				update_option('gapp', $options);
			} 

		}
	}								
	return $options;
}

function gapp_options() {
	$options = get_option('gapp');

	if (!isset($options['gapp_clientid'])) {
		if (isset($options['gapp_pnumber'])) {
			$options['gapp_clientid'] = $options['gapp_pnumber'] . '.apps.googleusercontent.com';
		} else {
			$options['gapp_clientid'] = null;
		}
	}

	if (isset($options['gapp_pnumber'])) unset($options['gapp_pnumber']);
	if (!isset($options['gapp_psecret'])) $options['gapp_psecret'] = null;
	if (!isset($options['gapp_apikey'])) $options['gapp_apikey'] = null;
	if (!isset($options['gapp_gid'])) $options['gapp_gid'] = null;
	if (!isset($options['gapp_vid'])) $options['gapp_vid'] = null;
	
	if (!isset($options['gapp_token'])) $options['gapp_token'] = null;
	if (!isset($options['gapp_defaultval'])) $options['gapp_defaultval'] = 0;
	if (!isset($options['gapp_token_refresh'])) $options['gapp_token_refresh'] = null;
	if (!isset($options['gapp_expires'])) $options['gapp_expires'] = null;
	if (!isset($options['gapp_wid'])) $options['gapp_wid'] = null;
	return $options;

}

function gapp_conf() {

	/** @var $wpdb WPDB */
	global $wpdb;
	$request = new WP_Http;
	$options = gapp_options();
	$updated = false;

	if (isset($_GET['state']) && $_GET['state'] == 'livechat' && $_GET['code']) {
		$oldc=list_old_chat();
		echo '<style>
			#chatroom{}
			#chatroom .chatroom-chat {
				border: 1px solid #ddd;
				 background: #fff none repeat scroll 0 0;
				padding: 1px;
			}
			#chatroom .image-author{
			border-radius: 16px;
				flex: 0 0 auto;
				float: left;
				height: 32px;
				margin-right: 16px;
				width: 32px;
			}
			#chatroom .chatroom-box {
				height: 410px;
				overflow: auto;
			}
			#chatroom .chat-item{
				clear: both;
			margin: 14px 9px 0;
				overflow: hidden;
			}
			#chatroom textarea {
				height: 50px;
			}
			#chatroom .chat-content{
				align-self: center;
				color: hsla(0, 0%, 6.7%, 0.6);
			}
			#chatroom .author-name{
				color: hsl(40, 76%, 55%);
				
				font-weight: 500;
				margin-right: 8px;

			}
			#chatroom .message{
				color: hsl(0, 0%, 6.7%);
				line-height: 16px;
				overflow-wrap: break-word;
			}
			</style>
			';
		echo '<div id="chatroom" ><div class="chatroom-chat" ><h3>Chatroom</h3>';
		echo '<div class="chatroom-box">';
		foreach($oldc[$_GET['code']] as $item){
		// print_r($item);
			if($item->typechat=="youtube"){
			 if($item->itemdata['authorDetails']['displayName'])
					echo '<div class="chat-item  youtube-item" >
								
								<img class="image-author" src="'.$item->itemdata['authorDetails']['profileImageUrl'].'">
								<div class="chat-content">
									<span class="author-name" >'.$item->itemdata['authorDetails']['displayName'] .'</span>
									<span class="message" >'.$item->itemdata['snippet']['displayMessage'].'</span>
								</div>
							</div>';
				}else{
				   if($item->itemdata['from']['name'])
					echo '<div class="chat-item facebook-item" >
								
								<img class="image-author" src="'._user_picture_comment($item->itemdata['from']['id']).'">
								<div class="chat-content">
									<span class="author-name" >'.$item->itemdata['from']['name'] .'</span>
									<span class="message" >'.$item->itemdata['message'].'</span>
								</div>
							</div>';
				}
		

		}
		echo "</div></div></div>";
		// }
		
		exit;
	}
	if (isset($_GET['state']) && $_GET['state'] == 'init' && $_GET['code']) {
		$request = new WP_Http;
		
		$result = $request->request('https://accounts.google.com/o/oauth2/token', array(
		'method' => 'POST',
		'body' => array(
		'code' => $_GET['code'],
		'client_id' => $options['gapp_clientid'],
		'client_secret' => $options['gapp_psecret'],
		'redirect_uri' => admin_url('options-general.php?page=' . GAPP_SLUG),
		'grant_type' => 'authorization_code',
		)
		));
		$tjson = json_decode($result['body']);

		$options['gapp_token'] = $tjson->access_token;
		$options['gapp_token_refresh'] = $tjson->refresh_token;
		$options['gapp_expires'] = time() + $tjson->expires_in;
		// print_r($options);
		
		update_option('gapp', $options);
		
		if ( !is_array( $result ) || !isset( $result['response']['code'] ) && 200 !== $result['response']['code'] ) {

			echo '<div id="message" class="error"><p>';
			_e('There was something wrong with Google.', 'youtube-live-streaming-chat');
			echo "</p></div>";
		}

		if (!empty($options['gapp_token'])) {
			echo '<script>window.location = \''.admin_url('options-general.php?page=' . GAPP_SLUG).'\';</script>';
			exit;

		}

	} elseif (isset($_GET['state']) && $_GET['state'] == 'reset') {

		$options['gapp_gid'] = null;
		$options['gapp_vid'] = null;
		$options['gapp_wid'] = null;
		$options['channelId'] = null;
		$options['chname'] = null;
		
		$options['gapp_channelname']=null;
		$options['gapp_viewcount']=null;
		$options['gapp_commentcount']=null;
		$options['gapp_subscribercount']=null;
		$options['gapp_videocount']=null;
		
		$options['gapp_token'] = null;
		$options['gapp_token_refresh'] = null;
		$options['gapp_expires'] = null;
		$options['gapp_defaultval'] = 0;
liveDB_mirgechat_remove_database("youtube");
		update_option('gapp', $options);

		$updated = true;

	} elseif (isset($_GET['state']) && $_GET['state'] == 'clear') {

		$options['gapp_clientid'] = null;
		$options['gapp_psecret'] = null;
		$options['gapp_apikey'] = null;

		update_option('gapp', $options);

		$updated = true;

	} elseif (isset($_GET['refresh'])) {

		gapp_refresh_token();

		$options = gapp_options();

		$updated = true;

	} elseif (isset($_GET['reset'])) {

		$wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE `option_name` LIKE '_transient_gapp-transient-%'");
		$wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE `option_name` LIKE '_transient_timeout_gapp-transient-%'");

		set_transient('gapp-namespace-key', uniqid(), 86400 * 365);

		$updated = true;

	}

	if (isset($_POST['submit'])) {

		// check_admin_referer('gapp', 'gapp-admin');

		if (isset($_POST['gapp_clientid'])) {
			$options['gapp_clientid'] = $_POST['gapp_clientid'];
		}

		if (isset($_POST['gapp_psecret'])) {
			$options['gapp_psecret'] = $_POST['gapp_psecret'];
		}       
		
		if (isset($_POST['gapp_apikey'])) {
			$options['gapp_apikey'] = $_POST['gapp_apikey'];
		}

		if (isset($_POST['gapp_wid'])) {
			$pieces = explode(",",$_POST['gapp_wid']);
			$options['gapp_wid'] = $pieces[0];
			$options['gapp_vid'] = $pieces[1];
			$options['channelId'] = $pieces[2];
			$options['chname'] = $pieces[3];
		}

		update_option('gapp', $options);

		$updated = true;

	}

	echo '<div class="wrap">';

	if ($updated) {

		echo '<div id="message" class="updated fade"><p>';
		_e('Configuration updated.', 'youtube-live-streaming-chat');
		echo '</p></div>';

	}


	
	if (empty($options['gapp_token'])) {

		if (empty($options['gapp_clientid']) || empty($options['gapp_psecret']) || empty($options['gapp_apikey'])) {

			echo '<p>'.__('In order to connect to your Youtube Account, you need to create a new project in the <a href="https://console.developers.google.com/project" target="_blank">Google API Console</a> and activate the Analytics API in "APIs &amp; auth &gt; APIs".', 'youtube-live-streaming-chat').'</p>';

			echo '<form action="'.admin_url('options-general.php?page=' . GAPP_SLUG).'" method="post" id="gapp-conf">';

			echo '<p>'.__('Then, create an OAuth Client ID in "APIs &amp; auth &gt; Credentials". Enter this URL for the Redirect URI field:', 'youtube-live-streaming-chat').'<br/>';
			echo admin_url('options-general.php?page=' . GAPP_SLUG);
			echo '</p>';

			echo '<p>'.__('You also have to fill the Product Name field in "APIs & auth" -> "Consent screen" — you need to select e-mail address as well.').'</p>';

			echo '<h3><label for="gapp_clientid">'.__('Client ID:', 'youtube-live-streaming-chat').'</label></h3>';
			echo '<p><input type="text" id="gapp_clientid" name="gapp_clientid" value="'.$options['gapp_clientid'].'" style="width: 400px;" /></p>';

			echo '<h3><label for="gapp_psecret">'.__('Client secret:', 'youtube-live-streaming-chat').'</label></h3>';
			echo '<p><input type="text" id="gapp_psecret" name="gapp_psecret" value="'.$options['gapp_psecret'].'" style="width: 400px;" /></p>';
			
			echo '<h3><label for="gapp_apikey">'.__('Key API:', 'youtube-live-streaming-chat').'</label></h3>';
			echo '<p><input type="text" id="gapp_apikey" name="gapp_apikey" value="'.$options['gapp_apikey'].'" style="width: 400px;" /></p>';

			echo '<p class="submit" style="text-align: left">';
			wp_nonce_field('gapp', 'gapp-admin');
			echo '<input type="submit" name="submit" value="'.__('Save', 'youtube-live-streaming-chat').' &raquo;" /></p></form></div>';

		} else {

			$url_auth = 'https://accounts.google.com/o/oauth2/auth?client_id='.$options['gapp_clientid'].'&redirect_uri=';
			$url_auth .= admin_url('options-general.php?page=' . GAPP_SLUG);
			$url_auth .= '&scope=https://www.googleapis.com/auth/youtube&response_type=code&access_type=offline&state=init&approval_prompt=force';

			echo '<p><a href="'.$url_auth.'">'.__('Connect to Youtube', 'youtube-live-streaming-chat').'</a></p>';

			echo '<p><a href="'.admin_url('options-general.php?page=' . GAPP_SLUG).'&state=clear">'.__('Clear the API keys').' &raquo;</a></p>';

		}

	} else {
		echo "<h3>Welcome to the YouTube Live Chatroom API Manager for Wordpress!</h3>";
		echo '<img src="'.plugins_url( '/youtube_logo.png', __FILE__ ).'">';
		
		echo "<p>This plugin creates a live data link for managing your YouTube Live Chat room and connecting this data to your video production software such as vMix, Wirecast, Livestream or the NewTek TriCaster. The plugin will store your live show chat room messages in your wordpress database so you can keep them for your records and download them when needed. The plugin also provides a live data link accessible via JSON or XML that you can use to link these chatroom messages to your video production software. </p>";
		echo '<p><strong>Download the Setup Guide <a href="http://www.youtubeliveapi.com/wp-content/uploads/2017/05/YouTube-Chat-Room-Manager-Setup-Guide.pdf" target="_block">Here</a>.</strong></p>';
		
		$options = gapp_options();
		if (time() >= $options['gapp_expires']) {
			$options = gapp_refresh_token();
		}
		gapp_channel_info() ;
		// print_r($options);
		$options = gapp_options();
		if(isset($options['gapp_channelname']))
		echo "<h3>Channel statistics : ".$options['gapp_channelname']."</h3>";
		echo '<ul>';
		if(isset($options['gapp_viewcount']))
		echo "<li><b>View Count:</b> ". $options['gapp_viewcount']."</li>";
		
		if(isset($options['gapp_commentcount']))
		echo "<li><b>Comment Count:</b> ". $options['gapp_commentcount']."</li>";
		
		if(isset($options['gapp_subscribercount']))
		echo "<li><b>Subscriber Count:</b> ". $options['gapp_subscribercount']."</li>";	
		
		if(isset($options['gapp_videocount']))
		echo "<li><b>Video Count:</b> ". $options['gapp_videocount']."</li>" ;
		
		echo '</ul>';

		echo '<p>'.__('Your token expires on:', 'youtube-live-streaming-chat').' '.date_i18n( 'Y/m/d \a\t g:ia', $options['gapp_expires'] + ( get_option( 'gmt_offset' ) * 3600 ) , 1 ).'.</p>';

		echo '<p><a href="'.admin_url('options-general.php?page=' . GAPP_SLUG . '&state=reset').'">'.__('Disconnect from Youtube api', 'youtube-live-streaming-chat').' &raquo;</a></p>';

		echo '<p><a href="'.admin_url('options-general.php?page=' . GAPP_SLUG . '&refresh').'">'.__('Refresh Google API token', 'youtube-live-streaming-chat').' &raquo;</a></p>';

		echo '<form action="'.admin_url('options-general.php?page=' . GAPP_SLUG).'" method="post" id="gapp-conf">';

		echo '<h3><label for="gapp_wid">'.__('Select yout Live Streaming:', 'youtube-live-streaming-chat').'</label></h3>';
		
		$result = $request->request('https://www.googleapis.com/youtube/v3/liveBroadcasts', array(
		'method' => 'GET',
		'body' => array("part"=>'id, snippet, contentDetails',"mine"=>"true","access_token"=>$options['gapp_token'])
		));
		// print_r($result);
		$broadcasts=json_decode($result["body"]);
		// print_r($broadcasts->items);
		if(isset($broadcasts->items)){
			echo '<p><select id="gapp_wid" name="gapp_wid" style="width: 400px;" />
			<option value="">select live chat </option>
			';	
			
			foreach($broadcasts->items as $item){				 
				if(isset($item->snippet->liveChatId)){
					echo '<option value="'.$item->snippet->liveChatId.','.$item->id.','.$item->snippet->channelId.','.$item->snippet->title.'"';
					if ($options['gapp_wid'] == $item->snippet->liveChatId) echo ' SELECTED';
					echo '>'.$item->name.' ('.$item->snippet->title.')</option>';
				}
			}
			echo '</select></p>';
			if (!empty($options['gapp_wid'])) {
				echo '<p><a target="_block" href="'.site_url('/?live_chat=chat.json').'">'.__('This is link to live chat Json ', 'youtube-live-streaming-chat').' &raquo;</a></p>';
				echo '<p><a target="_block" href="'.site_url('/?live_chat_xml=chat.xml').'">'.__('This is link to live chat XML ', 'youtube-live-streaming-chat').' &raquo;</a></p>';		
				
				} 
				
		}else{
			echo '<p>'.'Live Chat not found !'.'</p>'; 
		}
		
		
		echo '<h3><label  >Chat History  : </label></h3>';
		$oldc=list_old_chat();
		
		foreach($oldc as  $key => $el){	

			echo '<p> History of live chat reference : 
			<a href="'.admin_url('options-general.php?page=' . GAPP_SLUG).'&state=livechat&code='.$key.'" target="_block">'.$el[0]->chname.'</a>  - 
			<a href="'.site_url('/?live_chat_download='.$key).'"  target="_block">Download XML</a>- 
			<a href="'.site_url('/?live_chat_download='.$key.'&type=xml').'"  target="_block">Download Json</a></p>';
		}
		echo '<p class="submit" style="text-align: left">';
		// wp_nonce_field('gapp', 'gapp-admin');
		echo '<input type="submit" name="submit" value="'.__('Save', 'youtube-live-streaming-chat').' &raquo;" /></p></form></div>';

	}

}


// Shortcode Function [ChatRoom]

function chatroom_creation(){
	wp_register_script('angular-core', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.4.1/angular.js', array(), null, false);

	// register our app.js, which has a dependency on angular-core
	wp_register_script('angular-app', plugins_url( '/app.js', __FILE__ ) , array('angular-core'), null, false);

	// enqueue all scripts
	wp_enqueue_script('angular-core');
	wp_enqueue_script('angular-app');

	// we need to create a JavaScript variable to store our API endpoint...   
	// wp_localize_script( 'angular-core', 'AppAPI', array( 'url' => get_bloginfo('wpurl').'/api/') ); // this is the API address of the JSON API plugin
	// ... and useful information such as the theme directory and website url
	wp_localize_script( 'angular-core', 'BlogInfo', array( 'api' => site_url()) );
	// }
	// add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts');
	$options = gapp_options();
	$url = site_url();
	$url = str_replace('http://','',$url);
	$url = str_replace('https://','',$url);
	$url = str_replace('wwww.','',$url);
	echo '
		<div id="chatroom" ng-app="chatroom">
		<div ng-controller="ChatController">
		<iframe allowfullscreen="" 
		frameborder="0"  
		style="height:320px !important " 
		height="320" 
		src="https://www.youtube.com/live_chat?v='.$options['gapp_vid'].'&embed_domain='.$url.'" width="100%"></iframe><br />
		</div>
		</div>
	';
	
}
add_shortcode('chatroom', 'chatroom_creation');




//Get Live chat with API an save chat item in DB

add_action( 'wp_ajax_add_myfunc', 'prefix_ajax_add_myfunc' );
add_action( 'wp_ajax_nopriv_add_myfunc', 'prefix_ajax_add_myfunc' );

function prefix_ajax_add_myfunc() {	

	$request = new WP_Http;
	$chats_item =array();
	$options = gapp_options();
	
	if(isset($options['gapp_wid'])){
		
		$chats = $request->request('https://www.googleapis.com/youtube/v3/liveChat/messages', array(
		'method' => 'GET',
		'body' => array("part"=>'id,snippet,authorDetails',"liveChatId"=>$options['gapp_wid'],"key"=>$options['gapp_apikey'] )
		));			
		if(isset($chats["body"])){
			$chats_item=json_decode( $chats["body"]);	
			foreach($chats_item->items as $itm){					
				add_chat_toDB('',$options['gapp_wid'],$itm->snippet->publishedAt,$chats["body"],$itm->id,$options['chname'],'youtube');
			}
		}
	}else{
		$error["message"]='Live chat ID not found';
	}
	
	if(!isset($error))
	wp_send_json( $chats_item);
	else
	wp_send_json( $error);

}

//*******************
//   DATABASE
//*******************

register_activation_hook( __FILE__, 'chatroom_create_db' );
function chatroom_create_db() {


	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'yt_chatroom';


	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		typechat varchar(20) DEFAULT 'youtube' NULL,
		videoid varchar(20) DEFAULT '' NOT NULL,
		chatid varchar(20) DEFAULT '' NOT NULL,
		itemid varchar(255) DEFAULT '' NOT NULL,
		chname varchar(255) DEFAULT '' NOT NULL,
		publisheat datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		itemdata text DEFAULT '' NOT NULL,		
		UNIQUE KEY id (id)
	) $charset_collate;";	
		
	$table_name2 = $wpdb->prefix . 'mirgechat';
	$sql2 = "CREATE TABLE $table_name2 (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		idt varchar(255) DEFAULT '' NULL,
		username varchar(255) DEFAULT ''  NULL,
		userimage varchar(255) DEFAULT ''  NULL,
		userid varchar(255) DEFAULT ''  NULL,
		typechat varchar(255) DEFAULT '' NOT NULL,
		message varchar(255) DEFAULT '' NOT NULL,
		publishat datetime DEFAULT '0000-00-00 00:00:00' NULL,	
		UNIQUE KEY id (id)
	) $charset_collate;";
			
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	dbDelta( $sql2 );
}
function add_livechat_DB($idt,$username,$userimage,$userid,$typechat,$message ,$publishat,$vid){
	global $wpdb;
	$table_name = $wpdb->prefix . 'mirgechat';
	$querystr = "SELECT  * 
	FROM $table_name 
	WHERE idt like '$idt'";
	$pageposts = $wpdb->get_results($querystr, OBJECT);

	if(!$pageposts){
		$wpdb->insert( 
			$table_name, 
			array( 
				'idt' => $idt, 
				'username' => $username, 
				'userimage' => $userimage, 
				'userid' => $userid, 
				'typechat' => $typechat, 
				'message' => $message, 
				'videoid' => $vid, 
				'publishat' => $publishat
			) 
		);		
	}

}
function get_comment_image_profil($idt){
	global $wpdb;
	$table_name = $wpdb->prefix . 'mirgechat';
	$querystr = "SELECT  userimage 
	FROM $table_name 
	WHERE userid like '$idt'";
	$pageposts = $wpdb->get_results($querystr, OBJECT);
	if($pageposts)
		return $pageposts[0]->userimage;
	
	return null;
}
function list_old_chat(){
	global $wpdb;
	$gl_chat=array();
	$table_name = $wpdb->prefix . 'yt_chatroom';
	$querystr = "SELECT  * 
		FROM $table_name where typechat='youtube'";
	
	$chats = $wpdb->get_results($querystr,  OBJECT);
	foreach($chats as $chat){
		$chat->itemdata = json_decode($chat->itemdata, true);
		$gl_chat[$chat->chatid][]=$chat;
	}
	return $gl_chat;
}
function list_old_chat_array(){
	global $wpdb;
	$gl_chat=array();
	$table_name = $wpdb->prefix . 'yt_chatroom';
	$querystr = "SELECT  * 
		FROM $table_name";
	
	$chats = $wpdb->get_results($querystr,  ARRAY_A);
	foreach($chats as $chat){
		$chat['itemdata'] = json_decode($chat['itemdata'], true);
		$gl_chat[$chat['chatid']][]=$chat;
	}
	return $gl_chat;
}


function add_chat_toDB($vid,$cid,$pub,$data,$itemid,$chname="",$type){
	global $wpdb;
	$table_name = $wpdb->prefix . 'yt_chatroom';
	$querystr = "SELECT  * 
	FROM $table_name 
	WHERE itemid like '$itemid'";
	$pageposts = $wpdb->get_results($querystr, OBJECT);

	if(!$pageposts){
		$wpdb->insert( 
			$table_name, 
			array( 
				'videoid' => $vid, 
				'chatid' => $cid, 
				'publisheat' => $pub, 
				'itemid' => $itemid, 
				'chname' => $chname, 
				'typechat' => $type, 
				'itemdata' => $data
			) 
		);		
	}

}
register_deactivation_hook( __FILE__, 'youtube_live_streaming_chat_remove_database' );
function youtube_live_streaming_chat_remove_database() {
     global $wpdb;
     $table_name = $wpdb->prefix . 'yt_chatroom';
     $table_name2 = $wpdb->prefix . 'mirgechat';
     $sql = "DROP TABLE IF EXISTS $table_name;";
     $sql2 = "DROP TABLE IF EXISTS $table_name2;";
     $wpdb->query($sql);
     $wpdb->query($sql2);
     delete_option("gapp");
     delete_option("facebooklive");
     delete_option("lowerthird");
}  
function liveDB_mirgechat_remove_database($type) {
     global $wpdb;
     $table_name = $wpdb->prefix . 'mirgechat';
     $sql = "DELETE FROM $table_name WHERE `typechat` like '$type'";   
     $wpdb->query($sql);
}
// Cron  5 sec

function live_streaming_add_cron_recurrence_interval( $schedules ) {
 
    $schedules['every_one_minutes'] = array(
            'interval'  => 5,
            'display'   => __( 'Every 1 Minutes', 'textdomain' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'live_streaming_add_cron_recurrence_interval' );

if ( ! wp_next_scheduled( 'one_minute_action_hook' ) ) {
    wp_schedule_event( time(), 'every_one_minutes', 'one_minute_action_hook' );
}
add_action('one_minute_action_hook', 'cron_glb_chat');
function cron_glb_chat(){
	lowerthird_mirge_chat();
}
// cron 1 min
function live_streaming_add_cron2_recurrence_interval( $schedules ) {
 
    $schedules['five_sec_minutes'] = array(
            'interval'  => 60,
            'display'   => __( 'Every 1 Minutes', 'textdomain' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'live_streaming_add_cron2_recurrence_interval' );

if ( ! wp_next_scheduled( 'five_sec_action_hook' ) ) {
    wp_schedule_event( time(), 'five_sec_minutes', 'five_sec_action_hook' );
}
add_action('five_sec_action_hook', 'cron2_glb_chat');
function cron2_glb_chat(){
	facebooklive_cron_db();
	prefix_ajax_add_myfunc();
}