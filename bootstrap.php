<?php

if (!@include __DIR__.'/vendor/autoload.php') {
    die('You must set up the project dependencies, refer to README.md.  Must run: \'php composer.phar install\'');
}
require_once(__DIR__.'/vendor/autoload.php');