<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

echo $_SESSION['ajax_progress_egh_refresh'];