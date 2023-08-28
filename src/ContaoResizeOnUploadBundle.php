<?php declare(strict_types=1);

/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Massolle <bm@presentprogressive.de>
 */

namespace PresProg\ResizeOnUpload;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoResizeOnUploadBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
