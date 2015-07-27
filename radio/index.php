<?php
require('functions.php');
require('Pusher.php');
require('Song.php');
$pusher = new Pusher();
$pusher->push();
?>