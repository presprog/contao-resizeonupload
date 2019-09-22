<?php

$GLOBALS['TL_HOOKS']['postUpload'][] = [
    'PresProg\\ContaoResizeOnUploadBundle\\EventListener\\FileUploadListener', 'resizeOnUpload'
];