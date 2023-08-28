<?php declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_files']['list']['operations']['image_sizes'] = [
    'icon' => 'sizes.svg',
];

$GLOBALS['TL_DCA']['tl_files']['fields']['sizes'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_image_size.name',
    'eval' => ['multiple' => true],
    'sql' => "blob NULL"
];

//$GLOBALS['TL_DCA']['tl_files']['list']['operations']['generate_thumbs'] = [];
