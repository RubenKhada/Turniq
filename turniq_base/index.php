<?php
require_once 'config/app.php';
redirect(isLoggedIn() ? 'dashboard.php' : 'login.php');