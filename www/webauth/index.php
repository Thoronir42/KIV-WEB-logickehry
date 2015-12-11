<?php
$user = filter_input(INPUT_SERVER, "WEBAUTH_USER") ? : "kiwi";
echo $user;