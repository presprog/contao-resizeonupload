<?php

$GLOBALS['TL_DCA']['tl_files']['config']['onload_callback'][] = [
    'PresProg\\ContaoResizeOnUploadBundle\\DataContainer\\FilesContainer', 'addSizesToFoldersOnly'
];

$GLOBALS['TL_DCA']['tl_files']['list']['operations']['image_sizes'] = [
    'label' => $GLOBALS['TL_LANG']['tl_files']['sizes'],
    'icon' => 'sizes.svg',
    'button_callback' => [
        'PresProg\\ContaoResizeOnUploadBundle\\DataContainer\\FilesContainer', 'imageSizeOperationIcon'
    ]
];

$GLOBALS['TL_DCA']['tl_files']['fields']['sizes'] = [
    'inputType' => 'checkbox',
    'label' => $GLOBALS['TL_LANG']['tl_files']['sizes'],
    'foreignKey' => 'tl_image_size.name',
    'eval' => ['multiple' => true],
    'sql' => "blob NULL"
];

//$GLOBALS['TL_DCA']['tl_files']['list']['operations']['generate_thumbs'] = [];
