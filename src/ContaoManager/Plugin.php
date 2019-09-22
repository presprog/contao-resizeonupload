<?php

/*
 * This file is part of Contao Resize On Upload Bundle.
 *
 * (c) John Doe
 *
 * @license LGPL-3.0-or-later
 */

namespace PresProg\ContaoResizeOnUploadBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use PresProg\ContaoResizeOnUploadBundle\ContaoResizeOnUploadBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoResizeOnUploadBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
