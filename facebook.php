<?php

define('FACEBOOK', 'facebook-live-streaming');
add_action('admin_menu', 'facebook_config_page');

function facebook_config_page() {
	if (function_exists('add_submenu_page')) {
		$page = add_submenu_page('options-general.php',
		__('Facebook Live Streaming', 'facebook_live_streaming'),
		__('Facebook Live Streaming', 'facebook-live-streaming'),
		'manage_options',FACEBOOK, 'facebooklive_conf');
	}
}
function facebooklive_conf(){
	$url= admin_url("options-general.php?page=" . FACEBOOK);
	lowerthird_mirge_chat();
	$request = new WP_Http;
	facebooklive_submit();
	$options=facebooklive_option();
	// print_r($options);
	echo "<h3>Facebook Live Streaming</h3>";
	get_page_info();
	if($options['app-id'] && $options['app-secret'] && $options['access-token']!=null)
		echo '<p><a href="'.$url.'&init_acces=true" >Delete Facebook API token »</a></p>';	
	
	if(isset($_GET['init_acces']))
		facebooklive_init_acces();	
	
	if($options['expires']==null && $options['access-token']==null && $options['app-id']!=null)
		echo '<p><a href="'.$url.'&init_api=true" >Clear the API keys »</a></p>';
	
	if(isset($_GET['init_api']))
		facebooklive_init_api();
	
	$options=facebooklive_option();
	
	if($options["page-info"]!=null && $options["access-token"]!=null){
		$d=json_decode($options["page-info"]);
		echo "<h4>Page Info</h4>";
		echo '<p><img  style="float: left; margin: 0px 9px 0px 0px;" src="'.$d->picture->data->url.'"> ';
		echo "Name :<b>".$d->name."</b> <br>";
		echo "About :".$d->about." </p>";
		}
		
	
	if($options['app-id'] !=null && $options['app-secret']!=null && $options['access-token']==null)
		echo '<a href="https://www.facebook.com/dialog/oauth?
		client_id='.$options['app-id'].'
		&redirect_uri='.$url.'
		&response_type=code&scope=public_profile" >Connect to Facebook »</a>';

	echo facebooklive_form_api();

	// print_r(_get_video_emoji(645282285682218));
	

}

function facebooklive_submit(){
	$options=facebooklive_option();
	$request = new WP_Http;
	$url= admin_url("options-general.php?page=" . FACEBOOK);
	if (isset($_GET['code'])) {
		$options['code']=$_GET['code'];
		$result = $request->request('https://graph.facebook.com/v2.9/oauth/access_token', array(
			'method' => 'GET',
			'body' => array(
				"client_id"=>$options['app-id'],
				"client_secret"=>$options['app-secret'],
				"redirect_uri"=>$url,
				"code"=>$options['code'] )));
			if(isset($result['body'])){
				$d=json_decode($result["body"]);
				$options['expires']=$d->expires_in;
				$options['access-token']=$d->access_token;
				$options['code']=null;
				
				update_option('facebooklive', $options);
				echo '<script>window.location = \''.$url.'\';</script>';
			}
		
	}
	
	
	if (isset($_POST['submit'])) {
		if (isset($_POST['facebook-app-id'])) {
			$options['app-id'] = $_POST['facebook-app-id'];
		}
		if (isset($_POST['facebook-app-secret'])) {
			$options['app-secret'] = $_POST['facebook-app-secret'];
		}
		if (isset($_POST['facebook-page-id'])) {
			$options['page-id'] = $_POST['facebook-page-id'];
		}
		if (isset($_POST['live_id'])) {
			$options['live_id'] = $_POST['live_id'];
		}
		update_option('facebooklive', $options);
	}
}
function facebooklive_reflesh_token(){
	$request = new WP_Http;
	$options=facebooklive_option();
		$access = $request->request('https://graph.facebook.com/oauth/access_token', array(
			'method' => 'GET',
			'body' => array(
				"client_id"=>$options['app-id'],
				"client_secret"=>$options['app-secret'],
				"grant_type"=>"fb_exchange_token",
				"fb_exchange_token"=>$options['access-token'] )
			));
		
			if(isset($access['body'])){
				$d=json_decode($access["body"]);
				$options['expires']=$d->expires_in;
				$options['access-token']=$d->access_token;
				update_option('facebooklive', $options);
				return $d->access_token;
			}
			
}

function get_page_info(){
	$request = new WP_Http;
	$options=facebooklive_option();
	if(isset($options['page-id']) ||  isset($options['access-token'])){
		$result = $request->request('https://graph.facebook.com/v2.9/'.$options['page-id'], array(
				'method' => 'GET',
				'body' => array(
					"fields"=>'about,name,picture,cover',
					"access_token"=>facebooklive_reflesh_token())
				));
		if(isset($result["body"])){
			$options["page-info"] =  $result["body"];
			update_option('facebooklive', $options);
		}
	}
	return false;
}

function list_live_video(){
	$request = new WP_Http;
	$options=facebooklive_option();

	$result = $request->request('https://graph.facebook.com/v2.9/'.$options['page-id'], array(
			'method' => 'GET',
			'body' => array(
				"fields"=>'video_broadcasts{status,embed_html,description}',
				"access_token"=>facebooklive_reflesh_token())
			));
	if(isset($result["body"])){
		return json_decode( $result["body"]);
	}
	return false;
}

function _live_video_likes($id){
	$request = new WP_Http;
	$result = $request->request('https://graph.facebook.com/v2.9/'.$id, array(
			'method' => 'GET',
			'body' => array(
				"fields"=>'live_views,likes{username}',
				"access_token"=>facebooklive_reflesh_token())
			));
	return json_decode($result['body']);
}
function _live_video_comments($id){
	$request = new WP_Http;
	$result = $request->request('https://graph.facebook.com/v2.9/'.$id, array(
			'method' => 'GET',
			'body' => array(
				"fields"=>'live_views,comments',
				"access_token"=>facebooklive_reflesh_token())
			));
	return json_decode($result['body']);
}
function _user_picture_comment($id){
	$val=get_comment_image_profil($id);
	if($val)
		return $val;
	
	$request = new WP_Http;
	$result = $request->request('https://graph.facebook.com/v2.9/'.$id, array(
			'method' => 'GET',
			'body' => array(
				"fields"=>'picture',
				"access_token"=>facebooklive_reflesh_token())
			));

	if( isset($result['body'])){
		$bdy= json_decode($result['body']);
		return $bdy->picture->data->url;
	} 
	return "" ;
}

function _get_video_emoji(){
	$options=facebooklive_option();
	$emoji=array();
	if(!isset($options['live_id']))
		return $emoji;
	$request = new WP_Http;
	$result = $request->request('https://graph.facebook.com/v2.9/'.$options['live_id'], array(
			'method' => 'GET',
			'body' => array(
				"fields"=>'live_views,reactions.type(LIKE).summary(total_count).limit(0).as(like),reactions.type(LOVE).summary(total_count).limit(0).as(love),reactions.type(WOW).summary(total_count).limit(0).as(wow),reactions.type(HAHA).summary(total_count).limit(0).as(haha),reactions.type(SAD).summary(total_count).limit(0).as(sad),reactions.type(ANGRY).summary(total_count).limit(0).as(angry)',
				"access_token"=>facebooklive_reflesh_token())
			));

	if( isset($result['body'])){
		$bdy= json_decode($result['body']);
		// print_r($bdy);
		foreach($bdy as $key=>$item){
			if($key!='id'){
				// $emoji[]=array($key=>$item->summary->total_count);
					if($key=='live_views')
						$emoji["live_views"]=$item;
					if(isset($item->summary->total_count))
						$emoji[$key]=$item->summary->total_count;
					}	
				}
	} 
	return array($emoji) ;
}
// description

function facebooklive_init_api(){
	$options = get_option('facebooklive');
	$options['app-id'] = null;
	$options['app-secret'] = null;
	$options['page-id'] = null;
	$options['page-info'] = null;
	liveDB_mirgechat_remove_database('facebook');
	update_option('facebooklive', $options);
}
function facebooklive_init_acces(){
	$options = get_option('facebooklive');
	$options['access-token'] = null;
	$options['expires'] = null;
	update_option('facebooklive', $options);
}
function facebooklive_option(){
	$options = get_option('facebooklive');
	if (!isset($options['app-id'])) $options['app-id'] = null;
	if (!isset($options['app-secret'])) $options['app-secret'] = null;
	if (!isset($options['page-id'])) $options['page-id'] = null;
	if (!isset($options['access-token'])) $options['access-token'] = null;
	if (!isset($options['expires'])) $options['expires'] = null;
	if (!isset($options['live_id'])) $options['live_id'] = null;
	if (!isset($options['page-info'])) $options['page-info'] = null;
	return $options;
}

function facebooklive_form_api(){
	$options=facebooklive_option();
	$output ="";
	
	echo '<form  enctype="multipart/form-data" action="'.admin_url('options-general.php?page=' . FACEBOOK).'" method="post" id="facebook-conf">';
	
	if($options['app-id']==null || $options['app-secret']==null || $options['page-id']==null){
		$output .= '<p><label for="facebook-app-id">APP ID </label>';
		$output .= '<input name="facebook-app-id" id="facebook-id" value="'.$options['app-id'].'" type="text"></p>';
		
		$output .= '<p><label for="facebook-app-secret">APP secret </label>';
		$output .= '<input name="facebook-app-secret" value="'.$options['app-secret'].'" id="facebook-secret"  type="text"></p>';

		$output .= '<p><label for="facebook-page-id">Page ID </label>';
		$output .= '<input name="facebook-page-id" value="'.$options['page-id'].'" id="facebook-page-id"  type="text"></p>';
	}
	
	if($options['access-token']!=null){
		$lv=list_live_video();
		
		if(isset($lv->video_broadcasts->data)){
					$output .= '<p><label for="live_id">Videos broadcasts</label>';
				$output .= '<select name="live_id">
				<option value="">Select live video</option>
				';
				foreach($lv->video_broadcasts->data as $vd){
					$output .= '<option';
					$output .= ($options['live_id'] == $vd->id) ? ' SELECTED' : '';
					$output .=' value="'.$vd->id.'">'.$vd->status.'-'.$vd->description.'</option>';
				}
				$output .= "</select></p>";
				if($options['live_id']!=null){
					$output .= '<p><a target="_block" href="'.site_url('/?fblive_chat_xml=json').'">This is link to live chat Json »</a></p>';
					$output .= '<p><a target="_block" href="'.site_url('/?fblive_chat_xml=xml').'">This is link to live chat XML »</a></p>';
					$output .= '<p><a target="_block" href="'.site_url('/?fblive_chat_xml=emoji').'">This is link to Emoji chat Json »</a></p>';
				}
			}else{
				$output .= '<p>Facebook Live not Found</p>';
				$options['live_id'] = null;
				update_option('facebooklive', $options);
			}
		
	}
	$output .= '<p class="submit" style="text-align: left">';
	$output .= '<input type="submit" name="submit" value="Save &raquo;" /></p></form>';
	return $output;
}


// Function live chat  api format XML 

function fblive_chat_xml_endpoint() {
	add_rewrite_tag( '%fblive_chat_xml%', '([^&]+)' );
	add_rewrite_rule( 'gifs/([^&]+)/?', 'index.php?fblive_chat_xml=$matches[1]', 'top' );
}
add_action( 'init', 'fblive_chat_xml_endpoint' );

function fbxml_download_template( $template ) {
	global $wp_query;
	$gif_tag = $wp_query->get( 'fblive_chat_xml' );

	if ( ! $gif_tag ) {
		return $template;
	}
	// header("Content-type: application/x-msdownload",true,200);
	// header("Content-Disposition: attachment; filename=data.xml");
	// header("Pragma: no-cache");
	// header("Expires: 0");
	$options=facebooklive_option();
	$id=$options['live_id'];

	$output=array();
	// $output["likes"]=_live_video_likes($id);
	// $output["comments"]=_live_video_comments($id);

	$output=_live_video_comments($id);
	if($output=$output->comments->data)
		foreach($output as $out){
			$out->username=$out->from->name;
			$out->profil_image='http://graph.facebook.com/'.$out->from->id.'/picture?type=square';
		}
	
	if($gif_tag=="emoji"){
		$output=_get_video_emoji();
		header('Content-Type: application/json');
		print json_encode($output);
	}
	 elseif($gif_tag=="xml"){
		header("Content-type: text/xml");
		$output = json_decode(json_encode($output), true);
		print array_to_xml($output, new SimpleXMLElement('<root/>'))->asXML();
	}else{
		header('Content-Type: application/json');
		print json_encode($output);
	}
	
	
}

add_filter('template_include', 'fbxml_download_template');


//Cron to DB
function  facebooklive_cron_db(){
	$options=facebooklive_option();
	$id=$options['live_id'];
	if ($id != null){
		$comments=_live_video_comments($id);
		
		if(isset($comments->comments->data)){
			foreach ($comments->comments->data as $comment)
					add_chat_toDB($options['live_id'],$options['live_id'], $comment->created_time,json_encode($comment),$comment->id,$comment->from->name,"facebook");
			}
		}

}
