<?php

function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	return $pageURL;
}

function mxp_fb_comment_callback($item) {
	$parent_id = isset($item['parent_id']) ? $item['parent_id'] : "";
	$comment_id = isset($item['comment_id']) ? $item['comment_id'] : "";
	$message = isset($item['message']) ? $item['message'] : "";
	$post_id = isset($item['post_id']) ? $item['post_id'] : "";
	$sender_name = isset($item['sender_name']) ? $item['sender_name'] : "";
	$verb = isset($item['verb']) ? $item['verb'] : ""; //must be "add"
	if (false === ($send = get_transient($comment_id))) {
		//存個 $parent_id 避免重複回應
		set_transient($comment_id, 'no_repeat', 30 * MINUTE_IN_SECONDS);
	}
	if ($send !== false || $parent_id != $post_id || $verb != "add" || $message == "" || $comment_id == "") {
		//不是第一則留言 或 不是新增留言 或 留言不是文字 就不回覆了！
		return;
	}
	$page_id = explode('_', $post_id)[0];

	$api_url = "https://graph.facebook.com/v2.10/{$comment_id}/comments?access_token=ACCSEE_TOKEN";
	$api_private_url = "https://graph.facebook.com/v2.10/{$comment_id}/private_replies?access_token=ACCSEE_TOKEN";
	// 不是測試粉絲頁，那就是正式的拉！
	if ($page_id != "000000000000000") {
		$api_url = "https://graph.facebook.com/v2.10/{$comment_id}/comments?access_token=ACCSEE_TOKEN";
		$api_private_url = "https://graph.facebook.com/v2.10/{$comment_id}/private_replies?access_token=ACCSEE_TOKEN";
	}
	$msg = "";
	$ans_arr = [
		"公開回覆範例Ａ",
		"公開回覆範例Ｂ",
		"公開回覆範例Ｃ",
		"公開回覆範例Ｄ"];
	$private_ans = [
		"私訊回覆範例Ａ",
		"私訊回覆範例Ｂ",
		"私訊回覆範例Ｃ",
		"私訊回覆範例Ｄ"];
	$bingo = time() % count($ans_arr);

	if (preg_match("/我該注意什麼/i", $message)) {
		$msg = $ans_arr[$bingo];
	}
	if (preg_match("/我要注意什麼/i", $message)) {
		$msg = $ans_arr[$bingo];
	}
	if ($msg != "") {
		$type = $bingo + 1;
		$url = 'https://mozilla.undo.im/type' . $type . '/?t=' . time();
		$response = wp_remote_post($api_url, array(
			'method' => 'POST',
			'timeout' => 5,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
			'cookies' => array(),
			'body' => array('message' => "{$msg}\n" . bitly_shorter($url)),
		)
		);
		logger('r1', json_encode($response));
		$response = wp_remote_post($api_private_url, array(
			'method' => 'POST',
			'timeout' => 5,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
			'cookies' => array(),
			'body' => array('message' => $private_ans[$bingo]),
		)
		);
		logger('r2', json_encode($response));
		reload($url);
	}
	//射後不理拉～
}
add_filter('fb2wp_comment_event', 'mxp_fb_comment_callback', 10, 1);

add_filter('fb2wp_messenger_full_respond_call', function ($resp_data, $match_resp, $origin_input_msg) {
	if (isset($match_resp['key']) && $match_resp['key'] == '一起來當隻自由的狐狸吧') {
		// 附件圖片模式
		$resp_data[] = $resp_data[0];
		$resp_data[1]['message']['attachment'] = array(
			'type' => 'image',
			'payload' => array(
				'url' => 'https://i.imgur.com/ltW9xvz.jpg',
				'is_reusable' => true,
			));
		unset($resp_data[1]['message']['text']);

		// 卡片模式
		//"subtitle":"We\'ve got the right hat for everyone.",
		// $text = $resp_data[0]['message']['text'];
		// $resp_data[0]['message'] = json_decode('{
		// "attachment":{
		//   "type":"template",
		//   "payload":{
		//     "template_type":"generic",
		//     "elements":[
		//        {
		//         "title":"' . $text . '",
		//         "image_url":"https://i.imgur.com/bx3Dbqs.jpg",

		//         "default_action": {
		//           "type": "web_url",
		//           "url": "https://mozilla.undo.im",
		//           "messenger_extensions": true,
		//           "webview_height_ratio": "tall",
		//           "fallback_url": "https://mozilla.undo.im/fb"
		//         },
		//         "buttons":[
		//           {
		//             "type":"web_url",
		//             "url":"https://mozilla.undo.im/wu",
		//             "title":"前往下載 Firefox!"
		//           }
		//         ]
		//       }
		//     ]
		//   }
		// }
		// }');

	}
	// if ($origin_input_msg == 'GO!') {
	// 	$resp_data[0]['message']['attachment'] = array(
	// 		'type' => 'image',
	// 		'payload' => array(
	// 			'url' => 'https://i.imgur.com/ltW9xvz.jpg',
	// 			'is_reusable' => true,
	// 		));
	// 	unset($resp_data[0]['message']['text']);
	// }
	// unset($resp_data[1]);
	// $resp_data[0]['message']['text'] = "";
	// 尚未開放
	return $resp_data;
}, 10, 3);

add_filter('fb2wp_fuzzy_respond_call', function ($value, $key, $msg) {
	if ($key == '一起來當隻自由的狐狸吧') {
		$auto_reply = array(
			"訊息回覆範例Ａ",
			"訊息回覆範例Ｂ",
			"訊息回覆範例Ｃ",
			"訊息回覆範例Ｄ",
		);
		return $auto_reply[time() % count($auto_reply)];
	}
	return $value;
}, 10, 3);

function googl_shorter($url) {
	$api = "https://www.googleapis.com/urlshortener/v1/url?key=ACCSEE_TOKEN";
	$response = wp_remote_post($api, array(
		'method' => 'POST',
		'timeout' => 5,
		'redirection' => 5,
		'httpversion' => '1.1',
		'blocking' => true,
		'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
		'body' => json_encode(array(
			'longUrl' => $url,
		)),
		'cookies' => array(),
	)
	);
	logger('r4', json_encode($response));
	$resp = json_decode($response['body'], true);
	if (isset($resp['id'])) {
		return $resp['id'];
	} else {
		return $url;
	}
}

function bitly_shorter($url) {
	$resp = bitly_v3_shorten($url, 'mzl.la');
	logger('bitly_v3_shorten', json_encode($resp));
	$surl = $resp;
	if (isset($surl['url'])) {
		return $surl['url'];
	} else {
		return $url;
	}
}
function reload($url) {
	$api = 'https://graph.facebook.com/v2.10/?access_token=ACCSEE_TOKEN';
	$response = wp_remote_post($api, array(
		'method' => 'POST',
		'timeout' => 5,
		'redirection' => 5,
		'httpversion' => '1.1',
		'blocking' => true,
		'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
		'body' => json_encode(array(
			'id' => $url,
			'scrape' => 'true',
		)),
		'cookies' => array(),
	)
	);
	logger('r3', json_encode($response));
}

// Ref: https://davidwalsh.name/wordpress-publish-post-hook
// Listen for publishing of a new post
function mxp_update_facebook_url_cache($new_status, $old_status, $post) {
	// 發佈文章事件
	if ('publish' === $new_status && $post->post_type === 'post') {
		$post_url = get_permalink($post->ID);
		reload($post_url);
		logger('mxp_update_facebook_url_cache', "發佈文章事件" . $post_url);
	}
}
// Add the hook action
add_action('transition_post_status', 'mxp_update_facebook_url_cache', 10, 3);

function embed_ga() {
	echo "<script> (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
	//ga('create', '', 'auto');
	//ga('send', 'pageview'); </script>";
}

function page_redirect() {
	echo '<script> window.location.href = "https://goo.gl/86TEUA"; </script>';
}

function logger($file, $data) {
	file_put_contents(
		get_template_directory() . "/logs/{$file}.txt",
		'===' . time() . '===' . PHP_EOL . $data . PHP_EOL,
		FILE_APPEND
	);
}

/**
 * @file
 * Simple PHP library for interacting with the v3 BITLY API - using OAuth
 * REQUIREMENTS: PHP, Curl, JSON
 *
 * @author Andrew Pinzler <ap@bitly.com>
 */

/**
 * The URI of the bitly OAuth endpoints.
 */
define('bitly_oauth_api', 'https://api-ssl.bit.ly/v3/');

/**
 * The bitly access token assigned to your bit.ly account.
 *(http://bit.ly/a/oauth_apps or http://github.com/pinzler/bitly-php-oauth)
 */
define('bitlyAccessToken', 'ACCSEE_TOKEN');

/**
 * Given a longUrl, get the bit.ly shortened version -  using Ouath
 *
 *
 * @param $longUrl
 *   Long URL to be shortened.
 * @param $domain
 *   Uses bit.ly (default), j.mp, or a bit.ly pro domain.
 * @param $access_token
 *   User's Access Token.
 *
 * @return
 *   An associative array containing:
 *   - url: The unique shortened link that should be used, this is a unique
 *     value for the given bit.ly account.
 *   - hash: A bit.ly identifier for long_url which is unique to the given
 *     account.
 *   - global_hash: A bit.ly identifier for long_url which can be used to track
 *     aggregate stats across all matching bit.ly links.
 *   - long_url: An echo back of the longUrl request parameter.
 *   - new_hash: Will be set to 1 if this is the first time this long_url was
 *     shortened by this user. It will also then be added to the user history.
 *
 * @see http://dev.bitly.com/links.html#v3_shorten
 */
function bitly_v3_shorten($longUrl, $domain = '') {
	$result = array();
	$url = bitly_oauth_api . "shorten?access_token=" . bitlyAccessToken . "&format=json&longUrl=" . urlencode($longUrl);
	if ($domain != '') {
		$url .= "&domain=" . $domain;
	}
	$output = json_decode(bitly_get_curl($url));
	if (isset($output->{'data'}->{'hash'})) {
		$result['url'] = $output->{'data'}->{'url'};
		$result['hash'] = $output->{'data'}->{'hash'};
		$result['global_hash'] = $output->{'data'}->{'global_hash'};
		$result['long_url'] = $output->{'data'}->{'long_url'};
		$result['new_hash'] = $output->{'data'}->{'new_hash'};
	}
	return $result;
}
/**
 * Make a GET call to the bitly API.
 *
 * @param $uri
 *   URI to call.
 */
function bitly_get_curl($uri) {
	$output = "";
	try {
		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 25);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$output = curl_exec($ch);
	} catch (Exception $e) {
	}
	return $output;
}

/**
 * Make a POST call to the bitly API.
 *
 * @param $uri
 *   URI to call.
 * @param $fields
 *   Array of fields to send.
 */
function bitly_post_curl($uri, $fields) {
	$output = "";
	$fields_string = "";
	foreach ($fields as $key => $value) {$fields_string .= $key . '=' . $value . '&';}
	rtrim($fields_string, '&');
	try {
		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_TIMEOUT, 25);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$output = curl_exec($ch);
	} catch (Exception $e) {
	}
	return $output;
}
