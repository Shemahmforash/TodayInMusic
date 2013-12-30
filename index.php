<?php
    require_once "bootstrap.php";

    $now = new DateTime("now");

    $json = file_get_contents( $webservice . "?tweeted=0&results=all&fields[]=date&fields[]=id&fields[]=description");

    $response = json_decode( $json, true );

    $events = $response['response']['events'];
    $eventNumber = count( $events );

    //choose a random unpublished event and tweet it
    if( $eventNumber ) {
        $random = rand(0, $eventNumber - 1);
        $event = $events[$random];

        $tweet = new Twitter(
                $twitter['consumerKey'],
                $twitter['consumerSecret'],
                $twitter['accessToken'],
                $twitter['accessTokenSecret']
            );

        $date   = new DateTime( $event['date'] );

        $message = sprintf('%s - %s', $date->format('Y'), $event['description'] );
        if( strlen( $message ) + strlen( ' #thisdayinmusic' ) < 140 )
            $message .= ' #thisdayinmusic';

        $error = null;
        try {
            $tweet->send( $message );
        } catch (TwitterException $e) {
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

    //send a put request to the service
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
