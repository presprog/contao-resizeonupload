<?php

namespace PresProg\ContaoResizeOnUploadBundle\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Image;
use Contao\ImageSizeModel;
use Contao\StringUtil;

class FilesContainer
{
    public function addSizesToFoldersOnly(DataContainer $dc)
    {
        if (!$dc->id) {
            return;
        }

        // when it is not a newly created entity, check its type
        if (basename($dc->id) !== '__new__') {
            $objFiles = FilesModel::findByPath($dc->id);
            if ($objFiles === null || $objFiles->type !== 'folder') {
                return;
            }
        }

        $pm = PaletteManipulator::create();
        $pm
            ->addField('sizes', 'meta')
            ->applyToPalette('default', 'tl_files');
    }

    public function imageSizeOperationIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $objFolder = FilesModel::findByPath($row['id']);

        if ($objFolder->type !== 'folder' || $objFolder->sizes === null) {
            return '';
        }

        $objSizes = ImageSizeModel::findMultipleByIds(StringUtil::deserialize($objFolder->sizes));

        if ($objSizes === null) {
            return '';
        }

        $sizes = [];

        foreach ($objSizes as $objSize) {
            $sizes[] = $objSize->name;
        }

        return '<span>' . Image::getHtml($icon, $label, sprintf('title="%s (%s)"', $title, implode(', ', $sizes))) . '</span>';
    }
}