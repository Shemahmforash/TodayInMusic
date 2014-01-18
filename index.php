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

    $now = new DateTime("now");

    $json = file_get_contents( $webservice . "?tweeted=0&results=all&fields[]=date&fields[]=id&fields[]=description");

    $response = json_decode( $json, true );

    $events = $response['response']['events'];
    $eventNumber = count( $events );

    //choose a random unpublished event and tweet it
    if( $eventNumber ) {
        $random = rand(0, $eventNumber - 1);
        $event = $events[$random];

        // Third, instantiate your Twitter client.
        $tweet = new Twitter($oauth, $token);

        $date   = new DateTime( $event['date'] );

        $message = sprintf('%s - %s', $date->format('Y'), $event['description'] );
        if( strlen( $message ) + strlen( ' #thisdayinmusic' ) < 140 )
            $message .= ' #thisdayinmusic';

        $error = null;
        try {
            $request = new StatusesUpdateRequest($message);
            $send = $tweet->send($request);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        //update event tweeted status in webservice
        if( !$error ) {
            $url = $webservice . "event/" . $event['id'];

            $status = doPut($url, array('tweeted' => '1'));
            if( $status != 200 ) {
                $error = 'Bar request';
            }
        }

        if( $error ) {
            header('HTTP/1.1 400 Bad Request');
            return;
        }
        else {
            header('Content-type: application/json');

            echo json_encode(array("status" => "ok", "message" => "Event " . $event['id'] . " tweeted successfully."));
        }
    }

    //send a put request to the webservice
    function doPut($url, $fields) { 
        if( !is_array( $fields ) )
            return false;

        $fields = json_encode( $fields );

        if($ch = curl_init($url))  { 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields))); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields); 
            curl_exec($ch); 

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); 

            curl_close($ch); 

            return (int) $status; 
        } 
        else { 
            return false; 
        } 
    }

?>
