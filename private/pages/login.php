<?php

namespace OpenSB;

global $orange;

use SquareBracket\Pages\AccountLogin;
use SquareBracket\Templating;

$error = '';

$page = new AccountLogin($orange);

if (isset($_POST["loginsubmit"])) {
    $page->postData($_POST);
}

$twig = new Templating($orange);

echo $twig->render('login.twig', [
    'error' => $error,
    'resetted' => isset($_GET['resetted']),
    'new_pass' => isset($_GET['new_pass']),
    'new_token' => isset($_GET['new_token']),
]);
