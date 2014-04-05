<?php
    require_once "bootstrap.php";

    use Widop\HttpAdapter\CurlHttpAdapter;
    use Widop\Twitter\OAuth;
    use Widop\Twitter\OAuth\Token;
    use Widop\Twitter\Rest\Statuses\StatusesUpdateRequest;
    use Widop\Twitter\Rest\Twitter;
    use OAuth\Token\OAuthToken;
    
    // First, instantiate your OAuth client.
    $oauth = new OAuth\OAuth(
        new CurlHttpAdapter(),
        new OAuth\OAuthConsumer($twitter['consumerKey'], $twitter['consumerSecret']),
        new OAuth\Signature\OAuthHmacSha1Signature()
    );

    // Second, instantiate your OAuth access token.
    $token = new OAuth\Token\OAuthToken($twitter['accessToken'], $twitter['accessTokenSecret']);

    $now = new DateTime("now", new DateTimeZone("Europe/Lisbon"));
    
    $json = file_get_contents( $webservice . "playlist/");

    $response = json_decode( $json, true );

    $tracks = $response['response']['tracks'];
    $trackNumber = count( $tracks );

    if( $trackNumber ) {
    	$url = sprintf('http://www.thisdayinmusic.net/playlist/%s/%d', $now->format('F'), $now->format('j') );
        $message = sprintf('Playlist a Day for %s %s featuring ', $now->format('Y-m-d'), $url  );

        $artists = array();
        foreach ($tracks as $track) {
        	array_push($artists, $track['artist']);
        }

        $indexes = array_rand($artists, 5);
        $arts = array( $artists[$indexes[0]], $artists[$indexes[1]], $artists[$indexes[2]], $artists[$indexes[3]], $artists[$indexes[4]]);

	    $message .= implode(', ', $arts) . '...';

        $tweet = new Twitter($oauth, $token);
	    $error = null;
	    try {
	        $request = new StatusesUpdateRequest($message);
	        $send = $tweet->send($request);
	    } catch (Exception $e) {
	        $error = $e->getMessage();
	    }

	    if( $error ) {
	        header('HTTP/1.1 400 Bad Request');
	        return;
	    }
	    else {
	        header('Content-type: application/json');

	        echo json_encode(array("status" => "ok", "message" => "Playlist for " . $now->format('Y-m-d') . " tweeted successfully."));
	    }
    }
?>
