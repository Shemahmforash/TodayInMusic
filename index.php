<?php
    /**
        Ideia:
        1. Se a DB n tiver dados para o dia de hoje, correr o Dayin\Music para ir buscar os dados do dia de hoje e guardá-los na DB.
        2. Ir buscar uma entrada aleatória da DB correspondente ao dia de hoje e fazer tweet dela. (de forma a q possa correr o script hora a hora e, de cada vez, envia um tweet).
    */

    use Doctrine\ORM\Tools\Setup;
    use Doctrine\ORM\EntityManager;

    require_once "vendor/autoload.php";

    // Create a simple "default" Doctrine ORM configuration for Annotations
    $isDevMode = true;
    $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);
    // or if you prefer yaml or XML
    //$config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);
    //$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);

    // database configuration parameters
    $conn = array(
            'dbname' => 'DayInMusic',
            'user' => 'wanderer',
            'password' => '11111',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );

    // obtaining the entity manager
    $entityManager = EntityManager::create($conn, $config);
?>
