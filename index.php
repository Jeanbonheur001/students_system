<?php
require_once __DIR__ . '/config.php';
db(); // makes sure the database and tables exist
redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
