<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoSubcolumnsBundle extends Bundle
{
    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
