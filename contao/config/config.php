<?php

declare(strict_types=1);

use HeimrichHannot\SubcolumnsBundle\Elements\colsetEnd;
use HeimrichHannot\SubcolumnsBundle\Elements\colsetPart;
use HeimrichHannot\SubcolumnsBundle\Elements\colsetStart;
use HeimrichHannot\SubcolumnsBundle\Util\tl_subcolumnsCallback;
use HeimrichHannot\SubcolumnsBundle\Widget\FormColEnd;
use HeimrichHannot\SubcolumnsBundle\Widget\FormColPart;
use HeimrichHannot\SubcolumnsBundle\Widget\FormColStart;

$GLOBALS['TL_CTE']['subcolumn'] = [
    'colsetStart' => colsetStart::class,
    'colsetPart' => colsetPart::class,
    'colsetEnd' => colsetEnd::class,
];

array_splice(
    $GLOBALS['FE_MOD']['application'],
    4,
    0,
    [
        'subcolumns' => 'Subcolumns\\ModuleSubcolumns',
    ],
);

/*
 * Form fields
 */
$GLOBALS['TL_FFL']['formcolstart'] = FormColStart::class;
$GLOBALS['TL_FFL']['formcolpart'] = FormColPart::class;
$GLOBALS['TL_FFL']['formcolend'] = FormColEnd::class;

/*
 * Hooks
 */
// $GLOBALS['TL_HOOKS']['clipboardContentTitle'][] = array('SemanticHTML5Helper', 'clipboardContentTitle');
$GLOBALS['TL_HOOKS']['clipboardCopy'][] = [tl_content_sc::class, 'clipboardCopy'];
$GLOBALS['TL_HOOKS']['clipboardCopyAll'][] = [tl_subcolumnsCallback::class, 'clipboardCopyAll'];

/*
 * Einrücken von Elementen
 */
$GLOBALS['TL_WRAPPERS']['start'][] = 'colsetStart';
$GLOBALS['TL_WRAPPERS']['separator'][] = 'colsetPart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'colsetEnd';

/*
 * Spaltensets
 */
$GLOBALS['TL_SUBCL'] = [
    'yaml3' => [
        'label' => 'YAML 3 Standard', // Label for the selectmenu
        'scclass' => 'subcolumns', // Class for the wrapping container
        'equalize' => 'equalize', // Is a equilize heights class included and what is it's name?
        'inside' => true, // Are inside containers used?
        'gap' => true, // A gap between the columns can be entered in backend
        'files' => [
            // Enter the location of the css files
            'css' => 'bundles/subcolumns/assets/yaml3/subcols.css|static',
            'ie' => 'bundles/subcolumns/assets/yaml3/subcolsIEHacks.css',
        ],
        /*
         * Define the sets that can be used as an array.
         * Each array contains two or more arrays with the definition for the single columns.
         * The key is used as the label in the select menu.
         * Example: '50x50' => array(array([class(es) for the first column],[optional classes for the inside container]),array([class(es) for the second column],[optional classes for the inside container]))
         */
        'sets' => [
            '20x20x20x20x20' => [['c20l', 'subcl'], ['c20l', 'subc'], ['c20l', 'subc'], ['c20l', 'subc'], ['c20r', 'subcr']],
            '25x25x25x25' => [['c25l', 'subcl'], ['c25l', 'subc'], ['c25l', 'subc'], ['c25r', 'subcr']],
            '50x16x16x16' => [['c50l', 'subcl'], ['c16l', 'subc'], ['c16l', 'subc'], ['c16r', 'subcr']],
            '33x33x33' => [['c33l', 'subcl'], ['c33l', 'subc'], ['c33r', 'subcr']],
            '50x25x25' => [['c50l', 'subcl'], ['c25l', 'subc'], ['c25r', 'subcr']],
            '25x50x25' => [['c25l', 'subcl'], ['c50l', 'subc'], ['c25r', 'subcr']],
            '25x25x50' => [['c25l', 'subcl'], ['c25l', 'subc'], ['c50r', 'subcr']],
            '40x30x30' => [['c40l', 'subcl'], ['c30l', 'subc'], ['c30r', 'subcr']],
            '30x40x30' => [['c30l', 'subcl'], ['c40l', 'subc'], ['c30r', 'subcr']],
            '30x30x40' => [['c30l', 'subcl'], ['c30l', 'subc'], ['c40r', 'subcr']],
            '20x40x40' => [['c20l', 'subcl'], ['c40l', 'subc'], ['c40r', 'subcr']],
            '40x20x40' => [['c40l', 'subcl'], ['c20l', 'subc'], ['c40r', 'subcr']],
            '40x40x20' => [['c40l', 'subcl'], ['c40l', 'subc'], ['c20r', 'subcr']],
            '85x15' => [['c85l', 'subcl'], ['c15r', 'subcr']],
            '80x20' => [['c80l', 'subcl'], ['c20r', 'subcr']],
            '75x25' => [['c75l', 'subcl'], ['c25r', 'subcr']],
            '70x30' => [['c70l', 'subcl'], ['c30r', 'subcr']],
            '66x33' => [['c66l', 'subcl'], ['c33r', 'subcr']],
            '62x38' => [['c62l', 'subcl'], ['c38r', 'subcr']],
            '60x40' => [['c60l', 'subcl'], ['c40r', 'subcr']],
            '55x45' => [['c55l', 'subcl'], ['c45r', 'subcr']],
            '50x50' => [['c50l', 'subcl'], ['c50r', 'subcr']],
            '45x55' => [['c45l', 'subcl'], ['c55r', 'subcr']],
            '40x60' => [['c40l', 'subcl'], ['c60r', 'subcr']],
            '38x62' => [['c38l', 'subcl'], ['c62r', 'subcr']],
            '33x66' => [['c33l', 'subcl'], ['c66r', 'subcr']],
            '30x70' => [['c30l', 'subcl'], ['c70r', 'subcr']],
            '25x75' => [['c25l', 'subcl'], ['c75r', 'subcr']],
            '20x80' => [['c20l', 'subcl'], ['c80r', 'subcr']],
            '15x85' => [['c15l', 'subcl'], ['c85r', 'subcr']],
        ],
    ],
    'yaml3_additional' => [
        'label' => 'YAML 3 Erweitert',
        'scclass' => 'subcolumns',
        'equalize' => 'equalize',
        'inside' => true,
        'gap' => true,
        'files' => [
            'css' => 'bundles/subcolumns/assets/yaml3/subcols_extended.css||static',
            'ie' => 'bundles/subcolumns/assets/yaml3/subcolsIEHacks_extended.css',
        ],
        'sets' => [
            '20x20x20x20x20' => [['c20l', 'subcl'], ['c20l', 'subc'], ['c20l', 'subc'], ['c20l', 'subc'], ['c20r', 'subcr']],
            '25x25x25x25' => [['c25l', 'subcl'], ['c25l', 'subc'], ['c25l', 'subc'], ['c25r', 'subcr']],
            '50x16x16x16' => [['c50l', 'subcl'], ['c16l', 'subc'], ['c16l', 'subc'], ['c16r', 'subcr']],
            '33x33x33' => [['c33l', 'subcl'], ['c33l', 'subc'], ['c33r', 'subcr']],
            '50x25x25' => [['c50l', 'subcl'], ['c25l', 'subc'], ['c25r', 'subcr']],
            '25x50x25' => [['c25l', 'subcl'], ['c50l', 'subc'], ['c25r', 'subcr']],
            '25x25x50' => [['c25l', 'subcl'], ['c25l', 'subc'], ['c50r', 'subcr']],
            '40x30x30' => [['c40l', 'subcl'], ['c30l', 'subc'], ['c30r', 'subcr']],
            '30x40x30' => [['c30l', 'subcl'], ['c40l', 'subc'], ['c30r', 'subcr']],
            '30x30x40' => [['c30l', 'subcl'], ['c30l', 'subc'], ['c40r', 'subcr']],
            '20x40x40' => [['c20l', 'subcl'], ['c40l', 'subc'], ['c40r', 'subcr']],
            '40x20x40' => [['c40l', 'subcl'], ['c20l', 'subc'], ['c40r', 'subcr']],
            '40x40x20' => [['c40l', 'subcl'], ['c40l', 'subc'], ['c20r', 'subcr']],
            '85x15' => [['c85l', 'subcl'], ['c15r', 'subcr']],
            '80x20' => [['c80l', 'subcl'], ['c20r', 'subcr']],
            '75x25' => [['c75l', 'subcl'], ['c25r', 'subcr']],
            '70x30' => [['c70l', 'subcl'], ['c30r', 'subcr']],
            '66x33' => [['c66l', 'subcl'], ['c33r', 'subcr']],
            '62x38' => [['c62l', 'subcl'], ['c38r', 'subcr']],
            '60x40' => [['c60l', 'subcl'], ['c40r', 'subcr']],
            '55x45' => [['c55l', 'subcl'], ['c45r', 'subcr']],
            '50x50' => [['c50l', 'subcl'], ['c50r', 'subcr']],
            '45x55' => [['c45l', 'subcl'], ['c55r', 'subcr']],
            '40x60' => [['c40l', 'subcl'], ['c60r', 'subcr']],
            '38x62' => [['c38l', 'subcl'], ['c62r', 'subcr']],
            '33x66' => [['c33l', 'subcl'], ['c66r', 'subcr']],
            '30x70' => [['c30l', 'subcl'], ['c70r', 'subcr']],
            '25x75' => [['c25l', 'subcl'], ['c75r', 'subcr']],
            '20x80' => [['c20l', 'subcl'], ['c80r', 'subcr']],
            '15x85' => [['c15l', 'subcl'], ['c85r', 'subcr']],
        ],
    ],
    'yaml4' => [
        'label' => 'YAML 4 Standard',
        'scclass' => 'ym-grid',
        'equalize' => 'ym-equalize',
        'inside' => true,
        'gap' => true,
        'files' => [
            'css' => 'bundles/subcolumns/assets/yaml4/subcols.css||static',
            'ie' => 'bundles/subcolumns/assets/yaml4/subcolsIEHacks.css',
        ],
        'sets' => [
            '20x20x20x20x20' => [['ym-g20 ym-gl', 'ym-gbox-left'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g20 ym-gr', 'ym-gbox-right']],
            '50x16x16x16' => [['ym-g50 ym-gl', 'ym-gbox-left'], ['ym-g16 ym-gl', 'ym-gbox'], ['ym-g16 ym-gl', 'ym-gbox'], ['ym-g16 ym-gr', 'ym-gbox-right']],
            '25x25x25x25' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '25x25x50' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g50 ym-gr', 'ym-gbox-right']],
            '25x50x25' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g50 ym-gl', 'ym-gbox'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '50x25x25' => [['ym-g50 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '40x40x20' => [['ym-g40 ym-gl', 'ym-gbox-left'], ['ym-g40 ym-gl', 'ym-gbox'], ['ym-g20 ym-gr', 'ym-gbox-right']],
            '40x20x40' => [['ym-g40 ym-gl', 'ym-gbox-left'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g40 ym-gr', 'ym-gbox-right']],
            '20x40x40' => [['ym-g20 ym-gl', 'ym-gbox-left'], ['ym-g40 ym-gl', 'ym-gbox'], ['ym-g40 ym-gr', 'ym-gbox-right']],
            '33x33x33' => [['ym-g33 ym-gl', 'ym-gbox-left'], ['ym-g33 ym-gl', 'ym-gbox'], ['ym-g33 ym-gr', 'ym-gbox-right']],
            '85x15' => [['ym-g85 ym-gl', 'ym-gbox-left'], ['ym-g15 ym-gr', 'ym-gbox-right']],
            '80x20' => [['ym-g80 ym-gl', 'ym-gbox-left'], ['ym-g20 ym-gr', 'ym-gbox-right']],
            '75x25' => [['ym-g75 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '70x30' => [['ym-g70 ym-gl', 'ym-gbox-left'], ['ym-g30 ym-gr', 'ym-gbox-right']],
            '66x33' => [['ym-g66 ym-gl', 'ym-gbox-left'], ['ym-g33 ym-gr', 'ym-gbox-right']],
            '65x35' => [['ym-g65 ym-gl', 'ym-gbox-left'], ['ym-g35 ym-gr', 'ym-gbox-right']],
            '60x40' => [['ym-g60 ym-gl', 'ym-gbox-left'], ['ym-g40 ym-gr', 'ym-gbox-right']],
            '55x45' => [['ym-g55 ym-gl', 'ym-gbox-left'], ['ym-g45 ym-gr', 'ym-gbox-right']],
            '50x50' => [['ym-g50 ym-gl', 'ym-gbox-left'], ['ym-g50 ym-gr', 'ym-gbox-right']],
            '45x55' => [['ym-g45 ym-gl', 'ym-gbox-left'], ['ym-g55 ym-gr', 'ym-gbox-right']],
            '40x60' => [['ym-g40 ym-gl', 'ym-gbox-left'], ['ym-g60 ym-gr', 'ym-gbox-right']],
            '35x65' => [['ym-g35 ym-gl', 'ym-gbox-left'], ['ym-g65 ym-gr', 'ym-gbox-right']],
            '33x66' => [['ym-g33 ym-gl', 'ym-gbox-left'], ['ym-g66 ym-gr', 'ym-gbox-right']],
            '30x70' => [['ym-g30 ym-gl', 'ym-gbox-left'], ['ym-g70 ym-gr', 'ym-gbox-right']],
            '25x75' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g75 ym-gr', 'ym-gbox-right']],
            '20x80' => [['ym-g20 ym-gl', 'ym-gbox-left'], ['ym-g80 ym-gr', 'ym-gbox-right']],
            '15x85' => [['ym-g15 ym-gl', 'ym-gbox-left'], ['ym-g85 ym-gr', 'ym-gbox-right']],
        ],
    ],
    'yaml4_additional' => [
        'label' => 'YAML 4 Erweitert',
        'scclass' => 'ym-grid',
        'equalize' => 'ym-equalize',
        'inside' => true,
        'gap' => true,
        'files' => [
            'css' => 'bundles/subcolumns/assets/yaml4/subcols_extended.css||static',
        ],
        'sets' => [
            '20x20x20x20x20' => [['ym-g20 ym-gl', 'ym-gbox-left'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g20 ym-gr', 'ym-gbox-right']],
            '50x16x16x16' => [['ym-g50 ym-gl', 'ym-gbox-left'], ['ym-g16 ym-gl', 'ym-gbox'], ['ym-g16 ym-gl', 'ym-gbox'], ['ym-g16 ym-gr', 'ym-gbox-right']],
            '25x25x25x25' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '25x25x50' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g50 ym-gr', 'ym-gbox-right']],
            '25x50x25' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g50 ym-gl', 'ym-gbox'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '50x25x25' => [['ym-g50 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gl', 'ym-gbox'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '40x40x20' => [['ym-g40 ym-gl', 'ym-gbox-left'], ['ym-g40 ym-gl', 'ym-gbox'], ['ym-g20 ym-gr', 'ym-gbox-right']],
            '40x20x40' => [['ym-g40 ym-gl', 'ym-gbox-left'], ['ym-g20 ym-gl', 'ym-gbox'], ['ym-g40 ym-gr', 'ym-gbox-right']],
            '20x40x40' => [['ym-g20 ym-gl', 'ym-gbox-left'], ['ym-g40 ym-gl', 'ym-gbox'], ['ym-g40 ym-gr', 'ym-gbox-right']],
            '33x33x33' => [['ym-g33 ym-gl', 'ym-gbox-left'], ['ym-g33 ym-gl', 'ym-gbox'], ['ym-g33 ym-gr', 'ym-gbox-right']],
            '85x15' => [['ym-g85 ym-gl', 'ym-gbox-left'], ['ym-g15 ym-gr', 'ym-gbox-right']],
            '80x20' => [['ym-g80 ym-gl', 'ym-gbox-left'], ['ym-g20 ym-gr', 'ym-gbox-right']],
            '75x25' => [['ym-g75 ym-gl', 'ym-gbox-left'], ['ym-g25 ym-gr', 'ym-gbox-right']],
            '70x30' => [['ym-g70 ym-gl', 'ym-gbox-left'], ['ym-g30 ym-gr', 'ym-gbox-right']],
            '66x33' => [['ym-g66 ym-gl', 'ym-gbox-left'], ['ym-g33 ym-gr', 'ym-gbox-right']],
            '65x35' => [['ym-g65 ym-gl', 'ym-gbox-left'], ['ym-g35 ym-gr', 'ym-gbox-right']],
            '60x40' => [['ym-g60 ym-gl', 'ym-gbox-left'], ['ym-g40 ym-gr', 'ym-gbox-right']],
            '55x45' => [['ym-g55 ym-gl', 'ym-gbox-left'], ['ym-g45 ym-gr', 'ym-gbox-right']],
            '50x50' => [['ym-g50 ym-gl', 'ym-gbox-left'], ['ym-g50 ym-gr', 'ym-gbox-right']],
            '45x55' => [['ym-g45 ym-gl', 'ym-gbox-left'], ['ym-g55 ym-gr', 'ym-gbox-right']],
            '40x60' => [['ym-g40 ym-gl', 'ym-gbox-left'], ['ym-g60 ym-gr', 'ym-gbox-right']],
            '35x65' => [['ym-g35 ym-gl', 'ym-gbox-left'], ['ym-g65 ym-gr', 'ym-gbox-right']],
            '33x66' => [['ym-g33 ym-gl', 'ym-gbox-left'], ['ym-g66 ym-gr', 'ym-gbox-right']],
            '30x70' => [['ym-g30 ym-gl', 'ym-gbox-left'], ['ym-g70 ym-gr', 'ym-gbox-right']],
            '25x75' => [['ym-g25 ym-gl', 'ym-gbox-left'], ['ym-g75 ym-gr', 'ym-gbox-right']],
            '20x80' => [['ym-g20 ym-gl', 'ym-gbox-left'], ['ym-g80 ym-gr', 'ym-gbox-right']],
            '15x85' => [['ym-g15 ym-gl', 'ym-gbox-left'], ['ym-g85 ym-gr', 'ym-gbox-right']],
        ],
    ],
];
