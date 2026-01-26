<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_article']['config']['oncopy_callback'][] = ['HeimrichHannot\SubcolumnsBundle\tl_subcolumnsCallback', 'articleCheck'];
