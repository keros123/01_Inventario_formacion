<?php

require_once __DIR__ . '/app/core/App.php';

header('Location: ' . App::baseUrl() . '/public/');
exit;
