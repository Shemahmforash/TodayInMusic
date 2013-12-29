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
            //$tweet->send( $message );

        } catch (TwitterException $e) {
            //echo 'Error: ' . $e->getMessage();
            $error = $e->getMessage();
        }

        //update event tweeted status in webservice
        if( !$error ) {
            //TODO: use PUT method
        }
    }
?>
