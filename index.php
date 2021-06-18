<?php

require __DIR__ . '/src/Users.php';

$getTiktokUser = new TikTok\Users();

echo $getTiktokUser->details('@evikDDDDDDDDDza');
