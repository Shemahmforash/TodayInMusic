#ThisDayInMusic
ThisDayInMusic is a simple implementation of a twitter bot which tweets facts about this day in music.

## Installation
This project is in Composer format, so you just need to run `php composer.phar install`, and all dependencies will be installed.

Fill in your database credentials in bootstrap.php and run:
`php vendor/bin/doctrine orm:schema-tool:create`
to create the Event table.

You will also need to fill in bootstrap.php your twitter app credentials.
