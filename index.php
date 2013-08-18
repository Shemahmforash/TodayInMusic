<?php
    require_once "bootstrap.php";

    $now = new DateTime("now");
    $eventRepository = $entityManager->getRepository('Event');

    /*find events in the same day/month as today*/
    //TODO: change this query builder to criteria matching
    $qb = $entityManager->createQueryBuilder();
    $qb->select('e')
        ->from('Event', 'e')
        ->where('e.date like :date')
        ->setParameters(array(
                'date' => '%' . $now->format('m-d')
            ));
    $events = $qb->getQuery()->getArrayResult();

    //no events for today, get them
    if( !count( $events) ) {
        $dim = new ThisDayIn\Music( "\HTML_Parser_HTML5" );
        $evs = $dim->getEvents();
        
        foreach($evs as $ev ) {
            $date   = new DateTime( $ev['date'] );

            if( $ev['type'] !== 'Event') {
               $ev['description'] = sprintf('%s, %s', $ev['name'], $ev['description']);
            }

            //unlike the death events, the birth events do not include in the text information
            if( $ev['type'] === 'Birth') {
               $ev['description'] = sprintf('%s, was born', $ev['name'], $ev['description']);
            }

            //set current event
            $event = new Event(); 
            $event->setDate( $date );
            $event->setDescription( $ev['description'] ); 
            $entityManager->persist( $event );
        }

        //insert all events to db
        if( count( $evs ) )
            $entityManager->flush();
    }

    /*find today's unpublished events */
    /*TODO: convert this to criteria search*/
    $qb = $entityManager->createQueryBuilder();
    $qb->select('e')
        ->from('Event', 'e')
        ->where('e.date like :date')
        ->andWhere('e.is_published = 0')
        ->setParameters(array(
                'date' => '%' . $now->format('m-d')
            ));
    $events = $qb->getQuery()->getArrayResult();

    $eventNumber = count( $events );

    //choose a random unpublished event and tweet it
    if( $eventNumber ) {
        $random = rand(0, $eventNumber - 1);
        $event = $events[$random];

        $twitter = new Twitter(
                $twitter['consumerKey'],
                $twitter['consumerSecret'],
                $twitter['accessToken'],
                $twitter['accessTokenSecret']
            );

        $date = $event['date'];

        $message = sprintf('%s - %s', $date->format('Y'), $event['description'] );
        if( strlen( $message ) + strlen( ' #thisdayinmusic' ) < 140 )
            $message .= ' #thisdayinmusic';

        //update the event's published status
        //TODO: if using criteria, here I would have had the object. Now as I must get it...
        $event = $eventRepository->findOneBy(array('description' => $event['description'], 'date' => $date ));
        $event->setIsPublished( 1 );
        $entityManager->flush();

        try {
            $tweet = $twitter->send( $message );

        } catch (TwitterException $e) {
            //echo 'Error: ' . $e->getMessage();
        }
    }


?>
