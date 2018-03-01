<?php
define('LOWERTHIRD', 'lowerthird-title-creator');
add_action('admin_menu', 'lowerthird_config_page');
function lowerthird_config_page() {
	if (function_exists('add_submenu_page')) {
		$page = add_submenu_page('options-general.php',
		__('Lower Third Title Creator', 'lowerthird_title_creator'),
		__('Lower Third Title Creator', 'lowerthird-title-creator'),
		'manage_options',LOWERTHIRD, 'lowerthird_conf');
	}
	add_action('admin_print_scripts-' . $page, 'lowerthird_admin_scripts');
}
function lowerthird_admin_scripts() {
	wp_register_script( 'lowerthird-script', plugins_url('/interact.js', __FILE__) );
	wp_enqueue_script( 'lowerthird-script' ); 
	wp_register_script( 'lowerthird-app', plugins_url('/js/drag.js', __FILE__) );
	wp_enqueue_script( 'lowerthird-app' );
	wp_enqueue_style('lowerthird-stl',plugins_url('/css/style.css', __FILE__) ,array(  ),null);
	wp_enqueue_style( 'lowerthird-stl' );
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_register_style('wptuts-jquery-ui-style', 'http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css');
	wp_enqueue_style('wptuts-jquery-ui-style');
}
function lowerthird_conf(){
	global $wpdb;
	$request = new WP_Http;
	gapp_refresh_token() ;
	$options = lowerthird_option();
	$options2 = gapp_options();
	$updated = false;
	// print_r($options);
	if (isset($_GET['stats']) && $_GET['stats']=="rooms_chat" ) {
		lowerthird_rooms_chat_fb_yt();
		exit;
	}
	if (isset($_POST['submit'])) {
		if ( isset( $_FILES['lowerthird_img'] ) ) {		
			if ( !empty( $_FILES['lowerthird_img']['name'] ) ) {		
				$file = wp_upload_bits( $_FILES['lowerthird_img']['name'], null, @file_get_contents( $_FILES['lowerthird_img']['tmp_name'] ) );				
				$options['lowerthird_img'] =$file["url"];
			}
		}
		if (isset($_POST['lowerthird_livestream'])) {
			$options['lowerthird_livestream'] = $_POST['lowerthird_livestream'];
		}
		if (isset($_POST['lowerthird_livestreamfb'])) {
			$options['lowerthird_livestreamfb'] = $_POST['lowerthird_livestreamfb'];
		}
		if (isset($_POST['lowerthird_width'])) {
			$options['lowerthird_width'] = $_POST['lowerthird_width'];
		}	
		if (isset($_POST['lowerthird_height'])) {
			$options['lowerthird_height'] = $_POST['lowerthird_height'];
		}	
		if (isset($_POST['lowerthird_text'])) {
			$options['lowerthird_text'] = $_POST['lowerthird_text'];
		}
		if (isset($_POST['lowerthird_loop'])) {
			$options['lowerthird_loop'] = $_POST['lowerthird_loop'];
		}
		if (isset($_POST['lowerthird_username'])) {
			$options['lowerthird_username'] = $_POST['lowerthird_username'];
		}
		if (isset($_POST['lowerthird_message'])) {
			$options['lowerthird_message'] = $_POST['lowerthird_message'];
		}
		if (isset($_POST['lowerthird_subscribers'])) {
			$options['lowerthird_subscribers'] = $_POST['lowerthird_subscribers'];
		}	
		if (isset($_POST['lowerthird_viewers'])) {
			$options['lowerthird_viewers'] = $_POST['lowerthird_viewers'];
		}	
		if (isset($_POST['lowerthird_custem_text'])) {
			$options['lowerthird_custem_text'] = $_POST['lowerthird_custem_text'];
		}	
		if (isset($_POST['lowerthird_profil_img'])) {
			$options['lowerthird_profil_img'] = $_POST['lowerthird_profil_img'];
		}
		if (isset($_POST['lowerthird_body'])) {
			$options['lowerthird_body'] = $_POST['lowerthird_body'];
		}
		if (isset($_POST['style'])) {
			$options['lowerthird_style'] = $_POST['style'];
		}
		if (isset($_POST['lowerthird_loop_enable'])) {
			$options['lowerthird_loop_enable'] = $_POST['lowerthird_loop_enable'];
		}else{
			$options['lowerthird_loop_enable'] = null;
		}
		update_option('lowerthird', $options);
		$updated = true;
	}
	echo "<h3>Wordpress Lower Third Title Creator</h3>";
	// echo '<p><a target="_block" href="'.site_url('/?lowerthird_display=true').'">Click here</a> to go the page display Youtube </p>';
	echo '<p><a target="_block" href="'.site_url('/?lowerthird_display2=true').'">Click here</a> to go the page display Facebook & Youtube</p>';
	echo '<p><a target="_block" href="'.admin_url('options-general.php?page=' . LOWERTHIRD.'&stats=rooms_chat').'">Click here</a> to go the page Rooms Facebook & Youtube</p>';
	echo '<form  enctype="multipart/form-data" action="'.admin_url('options-general.php?page=' . LOWERTHIRD).'" method="post" id="lowerthird-conf">';
	echo '<div id="tabs">
				<ul>
					<li class="active"><a href="#tabs-1">Settings</a></li>
					<li><a href="#tabs-2">Drop Zone</a></li>
					<li><a href="#tabs-3">Field style</a></li>
				</ul>
				<div id="tabs-1">';
	echo '<table class="form-table">
	<tr>
		<td scope="row"><label for="lowerthird_livestream">Live stream Youtube</label></td>
		<td>';
	$result = $request->request('https://www.googleapis.com/youtube/v3/liveBroadcasts', array(
	'method' => 'GET',
	'body' => array("part"=>'id, snippet, contentDetails',"mine"=>"true","access_token"=>$options2['gapp_token'])
	));
	$broadcasts=json_decode($result["body"]);
	// print_r($broadcasts->items);
	if(isset($broadcasts->items)){
		echo '<select name="lowerthird_livestream" id="lowerthird_livestream" />';		 
		foreach($broadcasts->items as $item){				 
			if(isset($item->snippet->liveChatId)){
			// .','.$item->id.','.$item->snippet->channelId.','.$item->snippet->title
				echo '<option value="'.$item->snippet->liveChatId.'"';
				if ($options['lowerthird_livestream'] == $item->snippet->liveChatId) echo ' SELECTED';
				echo '>'.$item->name.' ('.$item->snippet->title.')</option>';
			}
		}
		echo '</select> ';
	}else{
		echo "Live stream not found!";
	}
	echo '</td>
	</tr>
	<tr>
		<td scope="row"><label for="lowerthird_livestreamfb">Live stream Facebook</label></td>
		<td>';
	$lv=list_live_video();
	if(isset($lv->video_broadcasts->data)){
				echo '<select name="lowerthird_livestreamfb">';
				foreach($lv->video_broadcasts->data as $vd){
					echo '<option ';
					if ($options['lowerthird_livestreamfb'] == $vd->id) echo ' SELECTED ';
					echo ' value="'.$vd->id.'">'.$vd->status.'-'.$vd->description.'</option>';
				}
				echo "</select>";
	}else{
		echo "Live stream not found!";
	}
	echo '</td>
	</tr>
	<tr>
		<td scope="row"><label for="lowerthird_text">Custom text field </label></td>
		<td>
			<textarea name="lowerthird_text" id="lowerthird_text" >'.$options['lowerthird_text'].'</textarea>
		</td>
	</tr>
	<tr>
		<td scope="row"><label for="lowerthird_loop">Time Loop /sec </label></td>
		<td>
			<input name="lowerthird_loop" id="lowerthird_loop"   min="0" value="'.$options['lowerthird_loop'].'" class="regular-text ltr" type="number">
			<label for="lowerthird_loop_enable">Enable Loop </label>
			<input name="lowerthird_loop_enable" id="lowerthird_loop_enable" '.(($options['lowerthird_loop_enable'] == "on") ? 'checked' : "" ).' class="regular-text ltr" type="checkbox">
		</td>
	</tr>	
	<tr>
		<td scope="row"><label for="lowerthird_height">Window size</label></td>
		<td>	
			<input name="lowerthird_height" id="lowerthird_height"  min="0" value="'.$options['lowerthird_height'].'"   class="regular-text ltr" type="number"> H  
			<input name="lowerthird_width" id="lowerthird_width"  min="0" value="'.$options['lowerthird_width'].'"  class="regular-text ltr" type="number"> W
		</td>
	</tr>	
	<tr>
		<td scope="row"><label for="lowerthird_img">Upload BG</label></td>
		<td>';
	if(!empty($options['lowerthird_img'] ))
	echo '<img width="30"  src="'.$options['lowerthird_img'].'"> ';
	echo '<input name="lowerthird_img" id="lowerthird_img" class="regular-text ltr" type="file">
		</td>
	</tr>
	</table>';
	// wp_nonce_field( plugin_basename( __FILE__ ), 'lowerthird_img' ); 
	echo	'</div>
				<div id="tabs-2">';
	$html =stripslashes( html_entity_decode($options['lowerthird_body'], ENT_QUOTES, 'UTF-8') );
	echo '<input id="lowerthird_body" name="lowerthird_body" type="hidden" value="'.htmlentities($html).'">';
	if(!empty($options['lowerthird_img'] ))
		echo ' <style>
						#content-dropzone{background: transparent url("'.$options['lowerthird_img'].'") no-repeat scroll 0% 0% !important;	}
					</style>';
	if(!empty($options['lowerthird_width']) &&  !empty($options['lowerthird_height'])){
		$wd=intval($options['lowerthird_width'])+265;
		echo ' <style>
						#content-dropzone{
							width:'.$options['lowerthird_width'].'px !important;
							height:'.$options['lowerthird_height'].'px  !important;
						}
						#space-dropzone {
								width:'.$wd.'px !important;
								}
					</style>';
	}		
	echo lowerthird_field_loadcss($options['lowerthird_style'],".can-drop");
	if ($options['lowerthird_body']!= null){
		echo '<div id="space-dropzone" >'.$html.'</div>' ;
	} else{
		echo '
	<div id="space-dropzone" >			
		<div id="element-dropzone" >
		<h3>draggable Fields : </h3>
				<div class="element-box" >	
					<div  id="username" class="draggable drag-drop" data-x="0" data-y="0"   data-example="User Display Name" data-title="User Display Name">User Display Name</div>
					<input id="username-val" name="lowerthird_username" type="hidden" value="">	
				</div>
				<div class="element-box" >
					<div id="message" class="draggable drag-drop" data-x="0" data-y="0" data-example="Hello world this is my message :)" data-title="Chat Message">Chat Message</div>
					<input id="message-val" name="lowerthird_message" type="hidden" value="">	
				</div>
				<div class="element-box" >
					<div id="subscribers" class="draggable drag-drop" data-x="0" data-y="0" data-example="10 000 Subscribers" data-title="Total  Subscribers">Total  Subscribers</div>
					<input id="subscribers-val" name="lowerthird_subscribers" type="hidden" value="">	
				</div>
				<div class="element-box" >
					<div id="viewers" class="draggable drag-drop" data-x="0" data-y="0" data-example="120 010 Viewers" data-title="Total Channel Views">Total Channel Views</div>
					<input id="viewers-val" name="lowerthird_viewers" type="hidden" value="">	
				</div>
				<div class="element-box" >
					<div id="custem-text"  class="draggable drag-drop" data-x="0" data-y="0" data-example="Here Custom text field " data-title="Custom text field">Custom text field </div>
					<input id="custem-text-val"  name="lowerthird_custem_text" type="hidden" value="">	
				</div>
				<div class="element-box" >
					<div id="profil-img" class="draggable drag-drop" data-x="0" data-y="0" data-example="<img width=\'50\' height=\'50\' src=\'/wp-content/plugins/youtube-live-streaming-chat/manager.png\' >" data-title="Profile Image">Profile Image </div>
					<input id="profil-img-val" name="lowerthird_profil_img" type="hidden" value="">	
				</div>
	</div>
		<div id="content-dropzone" class="dropzone">
		</div>
	</div>
';
	}	
	echo '</div>
				<div id="tabs-3">
				'.lowerthird_field_css_font().'
				</div>
			</div>';
	echo '<p class="submit" style="text-align: left">';
	echo '<input type="submit" name="submit" value="Save &raquo;" /></p></form>';
}
function lowerthird_reset_option(){
	$options['lowerthird_livestream'] = null;
	$options['lowerthird_livestreamfb'] = null;
	$options['lowerthird_width'] = 1800;
	$options['lowerthird_height'] = 1600;
	$options['lowerthird_text'] = "";
	$options['lowerthird_img'] = null;
	$options['lowerthird_loop'] = 5;
	$options['lowerthird_body'] = null;
	$options['lowerthird_style'] = null;
	//Position the element in Drop Zone
	$options['lowerthird_username'] = null;
	$options['lowerthird_message'] = null;
	$options['lowerthird_subscribers'] = null;
	$options['lowerthird_viewers'] = null;
	$options['lowerthird_custem_text'] = null;
	$options['lowerthird_profil_img'] = null;
	update_option('lowerthird', $options);
}
function lowerthird_option(){
	$options = get_option('lowerthird');  
	if (!isset($options['lowerthird_livestream'])) $options['lowerthird_livestream'] = null;
	if (!isset($options['lowerthird_livestreamfb'])) $options['lowerthird_livestreamfb'] = null;
	if (!isset($options['lowerthird_width'])) $options['lowerthird_width'] = 1800;
	if (!isset($options['lowerthird_height'])) $options['lowerthird_height'] = 1600;
	if (!isset($options['lowerthird_text'])) $options['lowerthird_text'] = "";
	if (!isset($options['lowerthird_img'])) $options['lowerthird_img'] = null;
	if (!isset($options['lowerthird_loop'])) $options['lowerthird_loop'] = 5;
	if (!isset($options['lowerthird_body'])) $options['lowerthird_body'] = null;
	if (!isset($options['lowerthird_style'])) $options['lowerthird_style'] = null;
	//Position the element in Drop Zone
	if (!isset($options['lowerthird_username'])) $options['lowerthird_username'] = null;
	if (!isset($options['lowerthird_message'])) $options['lowerthird_message'] = null;
	if (!isset($options['lowerthird_subscribers'])) $options['lowerthird_subscribers'] = null;
	if (!isset($options['lowerthird_viewers'])) $options['lowerthird_viewers'] = null;
	if (!isset($options['lowerthird_custem_text'])) $options['lowerthird_custem_text'] = null;
	if (!isset($options['lowerthird_profil_img'])) $options['lowerthird_profil_img'] = null;
	return $options;
}
function lowerthird_field_loadcss($op,$class=""){
	$output="";
	if(empty($op))
		return '';
	foreach($op as $key=>$field){
		// print_r($field);
		if($key=="profil-img")
			$output .='#'.$key.$class.' img{';
		else
			$output .='#'.$key.$class.'{';
		$output .= (isset($field['size'])) ? ' font-size:'.$field['size'].'px;' : "" ;
		$output .= (isset($field['width'])) ? ' width:'.$field['width'].'px;' : "" ;
		$output .= (isset($field['height'])) ? ' height:'.$field['height'].'px;' : "" ;
		$output .= (isset($field['font'])) ? ' font-family:'.$field['font'].',serif;' : "" ;
		$output .= (isset($field['bold'])) ? ' font-weight: bold;' : "" ;
		$output .= (isset($field['italic'])) ? ' font-style: italic;' : "" ;
		$output .= (isset($field['color'])) ? ' color:'.$field['color'].';' : "" ;
		$output .='}' ;
	}
	return '<style>'.$output.'</style>';
}
function lowerthird_field_css_font(){
	$output = '<div id="accordion">';
	$fields=array(
	"username" =>"user name",
	"message" =>"Message",
	"subscribers" =>"Subscribers",
	"viewers" =>"Viewers",
	"custem-text" =>"Custom text",
	// "profil-img" =>"Profil Image",
	);
	$op=lowerthird_option();
	foreach($fields as $key=>$field){
		$output .='<h3>Style For field : '.$field.'</h3>';
		$output .= '<div><p><label for="style_'.$key.'_id">Id : </label>style_'.$key.'_id</p>';
		$size = (isset($op['lowerthird_style'][$key]['size'])) ? $op['lowerthird_style'][$key]['size'] : 12 ;
		$font = (isset($op['lowerthird_style'][$key]['font'])) ? $op['lowerthird_style'][$key]['font'] : "Arial" ;
		$bold = (isset($op['lowerthird_style'][$key]['bold'])) ? " checked " : "" ;
		$italic = (isset($op['lowerthird_style'][$key]['italic'])) ? " checked " : ""  ;
		$color = (isset($op['lowerthird_style'][$key]['color'])) ? $op['lowerthird_style'][$key]['color'] : "#000" ;
		$output .= '<p><label for="style_'.$key.'_size">Size : </label>
		<!--input name="style['.$key.'][size]" type="number" value="'.$size.'" -->
		<select name="style['.$key.'][size]"   >';
		$list_size=array(9,10,12,14,16,18,20,24,28,30,32,38,40,44,48,52,64,78,80,100);
		foreach($list_size as $sz)
			$output .= ($size == $sz) ?  '<option SELECTED value="'.$sz.'" >'.$sz.'</option>' : '<option value="'.$sz.'" >'.$sz.'</option>';
		$output .='</select></p>';
		$output .= '<p><label for="style_'.$key.'_font">Font : </label><input name="style['.$key.'][font]" type="text" value="'.$font.'"></p>';		
		$output .= '<p><label for="style_'.$key.'_bold">Bold : </label><input name="style['.$key.'][bold]" '.$bold.' type="checkbox" ></p>';
		$output .= '<p><label for="style_'.$key.'_italic">Italic : </label><input name="style['.$key.'][italic]" '.$italic.' type="checkbox" ></p>';
		$output .= '<p><label for="style_'.$key.'_color">color : </label><input name="style['.$key.'][color]" type="text" value="'.$color.'"></p></div>';
	}
	$output .='<h3>Style For field : Profile Image</h3>';
	$height = (isset($op['lowerthird_style']['profil-img']['height'])) ? $op['lowerthird_style']['profil-img']['height'] : 50 ;
	$width = (isset($op['lowerthird_style']['profil-img']['width'])) ? $op['lowerthird_style']['profil-img']['width'] : 50 ;
	$output .= '<div><p><label for="style_profil-img_width">width : </label><input name="style[profil-img][width]" type="number" value="'.$width.'">px</p>';
	$output .= '<p><label for="style_profil-img_height">height : </label><input name="style[profil-img][height]" type="number" value="'.$height.'">px</p></div>';
	return $output."</div>";
}
// Here page Display 
// flush_rewrite_rules();
function lowerthird_display_endpoint() {
	add_rewrite_tag( '%lowerthird_display%', '([^&]+)' );
	add_rewrite_rule( 'gifs/([^&]+)/?', 'index.php?lowerthird_display=$matches[1]', 'top' );
}
add_action( 'init', 'lowerthird_display_endpoint' );
function lowerthird_display_template( $template ) {
	global $wp_query;
	$gif_tag = $wp_query->get( 'lowerthird_display' );
	if ( ! $gif_tag ) {
		return $template;
	}
	$options = lowerthird_option();
	if ($options['lowerthird_loop_enable'] == "on") {
			$sec= intval($options['lowerthird_loop'])*1000;
		}else{
			$sec= 2000;
		}
	echo ' <html>
	<head>
	<link rel="stylesheet" href="'.plugins_url('/css/style.css', __FILE__).'" type="text/css" media="all" />
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.1/angular.js"></script>
	<script>
	var myapp = angular.module("lowerthird", []);
	myapp.controller("LowerController", ["$scope", "$http", function($scope, $http) {
		$scope.data=[];
		$scope.index=0;
		$scope.loadlive = function(){
			var fd = new FormData();
			fd.append("live_chat", "chat.json");
			$http.post("/",fd, {
					transformRequest: angular.identity,
					headers: {"Content-Type": undefined}
			}).then(function (result) {		
				if(result.data.items.length>0){
					if ($scope.index==0){
						$scope.index=result.data.items.length-1;
					}else{
						if($scope.index < result.data.items.length-1 )
							$scope.index=$scope.index+1;
					}
					$scope.data=result.data.items[$scope.index];
				}
			});
			setTimeout(function(){ $scope.loadlive(); }, '.$sec.');
		}
		$scope.loadlive();
	}]);
	</script>
	</head>
	<body style="margin: 0;padding: 0;" ng-app="lowerthird">';
	echo '<style>
				#content-dropzone{}
				#content-dropzone div{ display: inline-block;position: absolute;}
			</style>';
	if(!empty($options['lowerthird_img'] ))
		echo ' <style>
						#content-dropzone{background: transparent url("'.$options['lowerthird_img'].'") no-repeat scroll 0% 0% !important;	}
					</style>';
	if(!empty($options['lowerthird_width']) &&  !empty($options['lowerthird_height'])){
		echo ' <style>
						#content-dropzone{
							width:'.$options['lowerthird_width'].'px !important;
							height:'.$options['lowerthird_height'].'px  !important;
						}
					</style><div id="content-dropzone" class="dropzone"  ng-controller="LowerController">';
	}	
	$opts = gapp_options();
	echo lowerthird_field_loadcss($options['lowerthird_style']);
	if ( $options['lowerthird_username']!= "0,0")
		echo '<div ng-if="data.authorDetails.displayName" id="username" '.lowerthird_postion($options['lowerthird_username']).'>{{data.authorDetails.displayName}}</div>';
	if ( $options['lowerthird_message']!= "0,0")
		echo '<div ng-if="data.snippet.displayMessage" id="message" '.lowerthird_postion($options['lowerthird_message']).'>{{data.snippet.displayMessage}}</div>';
	if ( $options['lowerthird_subscribers']!= "0,0")
		echo '<div  id="subscribers" '.lowerthird_postion($options['lowerthird_subscribers']).'>'.$opts['gapp_subscribercount'].' Subscriber </div>';
	if ( $options['lowerthird_viewers']!= "0,0")
		echo '<div  id="viewers" '.lowerthird_postion($options['lowerthird_viewers']).'>'.$opts['gapp_viewcount'].' viewers</div>';
	if ( $options['lowerthird_custem_text']!= "0,0")
		echo '<div  id="custem-text" '.lowerthird_postion($options['lowerthird_custem_text']).'>'.$options['lowerthird_text'].'</div>';
	if ( $options['lowerthird_profil_img']!= "0,0" )
		echo '<div ng-if="data.authorDetails.profileImageUrl" id="profil-img" '.lowerthird_postion($options['lowerthird_profil_img']).'><img src="{{data.authorDetails.profileImageUrl}}"> </div>';
	echo '</div>';
	echo "</body><html>";
}
add_filter('template_include', 'lowerthird_display_template');
function lowerthird_mirge_chat(){
	$sortar=array();
	gapp_refresh_token();
	$options = gapp_options();
	$optionsf=facebooklive_option();
	$facebbokchat=_live_video_comments($optionsf['live_id']);
	// print_r($facebbokchat);
	if(isset($facebbokchat->comments->data))
	foreach($facebbokchat->comments->data as $fc){
		add_livechat_DB($fc->id,$fc->from->name,_user_picture_comment($fc->from->id),$fc->from->id,'facebook',$fc->message ,$fc->created_time,$optionsf['live_id']);
	}
	 $chats=_youtube_live_comment();
 // print_r($chats);
	if(isset($chats)){
		$chats=json_decode($chats);
	}	
	if(isset($chats->items)){
		$youtubechat=$chats->items ;
		foreach($youtubechat as $yc){
			add_livechat_DB($yc->id,$yc->authorDetails->displayName,$yc->authorDetails->profileImageUrl,"",'youtube',$yc->snippet->displayMessage ,$yc->snippet->publishedAt,$options['gapp_wid']);
		}
	}	
	return  '' ;
}
function lowerthird_postion($pos){
	$pc=explode(",",$pos);
	if($pc[1]!=null || $pc[0]!=null)
		return 'style="top:'.$pc[1].'px;left:'.$pc[0].'px"';
	return 'style="display:none"';
}
function ajax_live_chat_db() {
	add_rewrite_tag( '%ajax_live_chat_db%', '([^&]+)' );
	add_rewrite_rule( 'gifs/([^&]+)/?', 'index.php?ajax_live_chat_db=$matches[1]', 'top' );
}
add_action( 'init', 'ajax_live_chat_db' );
function ajax_live_chat_download_template( $template ) {
	global $wp_query;
	global $wpdb;
	$gif_tag = $wp_query->get( 'ajax_live_chat_db' );
	if ( ! $gif_tag ) {
		return $template;
	}
	$table_name = $wpdb->prefix . 'mirgechat';
	$querystr = "SELECT * FROM (SELECT * FROM
	$table_name ORDER BY `publishat` DESC LIMIT 20) 
	as `g` ORDER BY g.publishat  ASC";
	$pageposts = $wpdb->get_results($querystr, OBJECT);
wp_send_json( $pageposts);
}
add_filter('template_include', 'ajax_live_chat_download_template');
//   DISPLAY TWO
// flush_rewrite_rules();
function lowerthird_display2_endpoint() {
	add_rewrite_tag( '%lowerthird_display2%', '([^&]+)' );
	add_rewrite_rule( 'gifs/([^&]+)/?', 'index.php?lowerthird_display2=$matches[1]', 'top' );
}
add_action( 'init', 'lowerthird_display2_endpoint' );
function lowerthird_display2_template( $template ) {
	global $wp_query;
	$gif_tag = $wp_query->get( 'lowerthird_display2' );
	if ( ! $gif_tag ) {
		return $template;
	}
	$options = lowerthird_option();
	if ($options['lowerthird_loop_enable'] == "on") {
			$sec= intval($options['lowerthird_loop'])*1000;
		}else{
			$sec= 2000;
		}
	echo ' <html>
	<head>
	<link rel="stylesheet" href="'.plugins_url('/css/style.css', __FILE__).'" type="text/css" media="all" />
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.1/angular.js"></script>
	<script>
	var myapp = angular.module("lowerthird", []);
	myapp.controller("LowerController", ["$scope", "$http", function($scope, $http) {
		$scope.data=[];
		$scope.index=0;
		$scope.loadlive = function(){
			var fd = new FormData();
			fd.append("ajax_live_chat_db", "chat.json");
			$http.post("/",fd, {
					transformRequest: angular.identity,
					headers: {"Content-Type": undefined}
			}).then(function (result) {		
				// console.log(result.data);
				if(result.data.length>0){
					if ($scope.index==0){
						$scope.index=result.data.length-1;
					}else{
					 // $scope.index =result.data.indexOf($scope.data.id)
					 console
					// result.data.forEach(function(entry) {
						// if(prop2map[entry.prop2] == entry.id)
					// });
						$scope.index = result.data.findIndex(x => x.id==$scope.data.id);
						if($scope.index < result.data.length-1 )
							$scope.index=$scope.index+1;
					}
					$scope.data=result.data[$scope.index];
				}
			});
			setTimeout(function(){ $scope.loadlive(); }, '.$sec.');
		}
		$scope.loadlive();
	}]);
	</script>
	</head>
	<body style="margin: 0;padding: 0;" ng-app="lowerthird">';
	echo '<style>
				#content-dropzone{}
				#content-dropzone div{ display: inline-block;position: absolute;}
			</style>';
	if(!empty($options['lowerthird_img'] ))
		echo ' <style>
						#content-dropzone{background: transparent url("'.$options['lowerthird_img'].'") no-repeat scroll 0% 0% !important;	}
					</style>';
	if(!empty($options['lowerthird_width']) &&  !empty($options['lowerthird_height'])){
		echo ' <style>
						#content-dropzone{
							width:'.$options['lowerthird_width'].'px !important;
							height:'.$options['lowerthird_height'].'px  !important;
						}
					</style><div id="content-dropzone" class="dropzone"  ng-controller="LowerController">';
	}	
	$opts = gapp_options();
	echo lowerthird_field_loadcss($options['lowerthird_style']);
	if ( $options['lowerthird_username']!= "0,0")
		echo '<div ng-if="data.username" id="username" '.lowerthird_postion($options['lowerthird_username']).'>{{data.username}}</div>';
	if ( $options['lowerthird_message']!= "0,0")
		echo '<div ng-if="data.message" id="message" '.lowerthird_postion($options['lowerthird_message']).'>{{data.message}}</div>';
	if ( $options['lowerthird_subscribers']!= "0,0")
		echo '<div  id="subscribers" '.lowerthird_postion($options['lowerthird_subscribers']).'>'.$opts['gapp_subscribercount'].' Subscriber </div>';
	if ( $options['lowerthird_viewers']!= "0,0")
		echo '<div  id="viewers" '.lowerthird_postion($options['lowerthird_viewers']).'>'.$opts['gapp_viewcount'].' viewers</div>';
	if ( $options['lowerthird_custem_text']!= "0,0")
		echo '<div  id="custem-text" '.lowerthird_postion($options['lowerthird_custem_text']).'>'.$options['lowerthird_text'].'</div>';
	if ( $options['lowerthird_profil_img']!= "0,0" )
		echo '<div ng-if="data.userimage" id="profil-img" '.lowerthird_postion($options['lowerthird_profil_img']).'><img src="{{data.userimage}}"> </div>';
	echo '</div>';
	echo "</body></html>";
}
add_filter('template_include', 'lowerthird_display2_template');
function lowerthird_rooms_chat_fb_yt(){
		 
	global $wpdb;
	$ytb=array();
	$fcb=array();
	$options = lowerthird_option();

	
	$table_name = $wpdb->prefix . 'mirgechat';
	// echo $options['lowerthird_livestream'];
	if(isset($options['lowerthird_livestream'])){
			$querystr = "SELECT * FROM (SELECT * FROM
			$table_name where typechat like 'youtube'   ORDER BY `publishat` DESC LIMIT 100) 
			as `g` ORDER BY g.publishat  ASC";
			$ytb = $wpdb->get_results($querystr, OBJECT);
			// print_r($ytb);
		}
	
	if(isset($options['lowerthird_livestreamfb'])){
			$querystr2 = "SELECT * FROM (SELECT * FROM
			$table_name where typechat like 'facebook' and videoid like '".$options['lowerthird_livestreamfb']."' ORDER BY `publishat` DESC LIMIT 100) 
			as `g` ORDER BY g.publishat  ASC";
			$fcb = $wpdb->get_results($querystr2, OBJECT);
		}
	
 
 
	echo '<style>
			.chatroom_rt{
				width:49%;
				float:left;
			}
			.chatroom_rt .chatroom-chat {
				border: 1px solid #ddd;
				 background: #fff none repeat scroll 0 0;
				padding: 1px;
			}
			.chatroom_rt .image-author{
			border-radius: 16px;
				flex: 0 0 auto;
				float: left;
				height: 32px;
				margin-right: 16px;
				width: 32px;
			}
			.chatroom_rt .chatroom-box {
				height: 410px;
				overflow: auto;
			}
			.chatroom_rt .chat-item{
				clear: both;
			margin: 14px 9px 0;
				overflow: hidden;
			}
			.chatroom_rt textarea {
				height: 50px;
			}
			.chatroom_rt .chat-content{
				align-self: center;
				color: hsla(0, 0%, 6.7%, 0.6);
			}
			.chatroom_rt .author-name{
				color: hsl(40, 76%, 55%);
				font-weight: 500;
				margin-right: 8px;
			}
			.chatroom_rt .message{
				color: hsl(0, 0%, 6.7%);
				line-height: 16px;
				overflow-wrap: break-word;
			}
			</style>
			';
		echo '<div id="chatroom_yt" class="chatroom_rt"><div class="chatroom-chat" ><h3>Chatroom youtube</h3>';
		echo '<div class="chatroom-box">';
		foreach($ytb as $item){
	
			 if($item->username)
					echo '<div class="chat-item  youtube-item" >
								<img class="image-author" src="'.$item->userimage.'">
								<div class="chat-content">
									<span class="author-name" >'.$item->username .'</span>
									<span class="message" >'.$item->message.'</span>
								</div>
							</div>';
				
		}
		echo "</div></div></div>";
		
		
		echo '<div id="chatroom_fb" class="chatroom_rt" ><div class="chatroom-chat" ><h3>Chatroom Facebook</h3>';
		echo '<div class="chatroom-box">';
		foreach($fcb as $item){
				   if($item->username)
					echo '<div class="chat-item facebook-item" >
								<img class="image-author" src="'. $item->userimage.'">
								<div class="chat-content">
									<span class="author-name" >'.$item->username .'</span>
									<span class="message" >'.$item->message.'</span>
								</div>
							</div>';
				
		}
		echo "</div></div></div>";
}