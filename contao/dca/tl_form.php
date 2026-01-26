<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_form']['config']['oncopy_callback'][] = ['HeimrichHannot\SubcolumnsBundle\tl_subcolumnsCallback', 'formCheck'];
