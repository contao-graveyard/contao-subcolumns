<?php

declare(strict_types=1);

use HeimrichHannot\SubcolumnsBundle\Util\tl_subcolumnsCallback;

$GLOBALS['TL_DCA']['tl_form']['config']['oncopy_callback'][] = [tl_subcolumnsCallback::class, 'formCheck'];
