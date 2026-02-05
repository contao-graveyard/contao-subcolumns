<?php

declare(strict_types=1);

use HeimrichHannot\SubcolumnsBundle\Util\tl_subcolumnsCallback;

$GLOBALS['TL_DCA']['tl_page']['config']['oncopy_callback'][] = [tl_subcolumnsCallback::class, 'pageCheck'];
