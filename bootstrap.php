<?php
    // bootstrap.php
    use Doctrine\ORM\Tools\Setup;
    use Doctrine\ORM\EntityManager;

    require_once "vendor/autoload.php";

    // Create a simple "default" Doctrine ORM configuration for Annotations
    $isDevMode = true;
    $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);

    // database configuration parameters
    $conn = array(
            'dbname'   => 'DayIn',
            'user'     => 'root',
            'password' => 'ZGKU.Bq!',
            'host'     => 'localhost',
            'driver'   => 'pdo_mysql',
        );

    // obtaining the entity manager
    $entityManager = EntityManager::create($conn, $config);

    //twitter credentials
    $twitter = array(
            'consumerKey'       => '',
            'consumerSecret'    => '',
            'accessToken'       => '',
            'accessTokenSecret' => '',
        );
