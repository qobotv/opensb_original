<?php

namespace openSB;

//this uploads and converts the video, should switch to a better solution!
global $betty;

use Orange\Templating;

require_once dirname(__DIR__) . '/private/class/common.php';

require_once dirname(__DIR__) . '/orange/classes/Pages/SubmissionUpload.php';

$page = new \Orange\Pages\SubmissionUpload($betty);

if (isset($_POST['upload']) or isset($_POST['upload_video']) and isset($userdata['name'])) {
    $page->postData($_POST, $_FILES);
}

$twig = new Templating($betty);

echo $twig->render('upload.twig');
