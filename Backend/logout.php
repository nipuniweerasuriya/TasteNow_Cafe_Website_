<?php
session_start();
session_unset();
session_destroy();
header("Location: ../Backend/index.php");
exit();
