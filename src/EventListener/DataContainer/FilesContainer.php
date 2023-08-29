<?php declare(strict_types=1);

/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Massolle <bm@presentprogressive.de>
 */

namespace PresProg\ResizeOnUpload\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Image;
use Contao\ImageSizeModel;
use Contao\StringUtil;

final class FilesContainer
{
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @Callback(table="tl_files", target="config.onload")
     */
    public function addSizesToFoldersOnly(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        // when it is not a newly created entity, check its type
        if (basename($dc->id) !== '__new__') {
            $filesModel = $this->framework->getAdapter(FilesModel::class);
            $files      = $filesModel->findByPath($dc->id);

            if ($files === null || $files->type !== 'folder') {
                return;
            }
        }

        $pm = PaletteManipulator::create();
        $pm
            ->addField('sizes', 'meta')
            ->applyToPalette('default', 'tl_files');
    }

    /**
     * @Callback(table="tl_files", target="list.operations.sizes.button")
     */
    public function imageSizeOperationIcon($row, $href, $label, $title, $icon, $attributes): string
    {
        $filesModel     = $this->framework->getAdapter(FilesModel::class);
        $imageSizeModel = $this->framework->getAdapter(ImageSizeModel::class);
        $folder         = $filesModel->findByPath($row['id']);

        if ($folder === null || $folder->type !== 'folder' || $folder->sizes === null) {
            return '';
        }

        $sizes = $imageSizeModel->findMultipleByIds(StringUtil::deserialize($folder->sizes));

        if ($sizes === null) {
            return '';
        }

        $sizeNames = [];

        foreach ($sizes as $size) {
            $sizeNames[] = $size->name;
        }

        return '<span>' . Image::getHtml($icon, $label, sprintf('title="%s (%s)"', $title, implode(', ', $sizeNames))) . '</span>';
    }
}
