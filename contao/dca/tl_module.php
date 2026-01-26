<?php

declare(strict_types=1);

use Contao\DataContainer;

/*
 * Add selectors to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = ['tl_module_sc', 'createPalette'];

/*
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['sc_type'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['sc_type'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['tl_module_sc', 'getAllTypes'],
    'eval' => [
        'submitOnChange' => true,
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['sc_modules'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['sc_modules'],
    'exclude' => true,
    'inputType' => 'rowWizard',
    'fields' => [
        'module' => [
            'label' => &$GLOBALS['TL_LANG']['tl_module']['module'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => ['tl_module_sc', 'getAllModules'],
        ],
        'column' => [
            'label' => &$GLOBALS['TL_LANG']['tl_module']['column'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => ['tl_module_sc', 'getColumns'],
        ],
    ],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['sc_gap'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['sc_gap'],
    'inputType' => 'text',
    'default' => ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 0),
    'eval' => [
        'maxlength' => '4',
        'regxp' => 'digit',
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['sc_gapdefault'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['sc_gapdefault'],
    'default' => 1,
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50',
    ],
    'sql' => "char(1) NOT NULL default '1'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['sc_equalize'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['sc_equalize'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
    ],
    'sql' => "char(1) NOT NULL default ''",
];

/**
 * Erweiterung für die tl_module-Klasse.
 */
class tl_module_sc extends tl_module
{
    /*
     * Create the palette for the startelement
     */
    public function createPalette(DataContainer $dc): void
    {
        $strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ?? 'yaml3' ?: 'yaml3';

        if (empty($GLOBALS['TL_SUBCL'][$strSet])) {
            return;
        }

        $strGap = $GLOBALS['TL_SUBCL'][$strSet]['gap'] ? ',sc_gapdefault,sc_gap' : false;
        $strEquilize = isset($GLOBALS['TL_SUBCL'][$strSet]['equalize']) && $GLOBALS['TL_SUBCL'][$strSet]['equalize'] ? ',sc_equalize;' : false;

        $GLOBALS['TL_DCA']['tl_module']['palettes']['subcolumns'] = '{title_legend},name,headline,type;{subcolumns_legend},sc_type,sc_modules;'.($strGap || $strEquilize ? '{subcolumns_settings_legend}'.$strGap.$strEquilize.';' : '').'{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID,space';
    }

    /*
     * Get the colsets depending on the selection from the settings
     */
    public function getAllTypes()
    {
        $strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ?? 'yaml3' ?: 'yaml3';

        return array_keys($GLOBALS['TL_SUBCL'][$strSet]['sets']);
    }

    /*
     * Get all modules included in the same theme
     */
    public function getAllModules()
    {
        $arrModules = [];
        $objModules = $this->Database->prepare('SELECT id, name FROM tl_module WHERE pid=(SELECT pid FROM tl_module WHERE id=?) AND id!=? ORDER BY name')->execute($this->Input->get('id'), $this->Input->get('id'));

        while ($objModules->next()) {
            $arrModules[$objModules->id] = $objModules->name.' (ID '.$objModules->id.')';
        }

        return $arrModules;
    }

    /*
     * Get possible columns
     */
    public function getColumns($dc)
    {
        $objTypes = $this->Database->prepare('SELECT sc_type FROM tl_module WHERE id=?')->execute($this->Input->get('id'));

        $cols = [];
        $count = count(explode('x', (string) $objTypes->sc_type));

        switch ($count) {
            case '2':
                $cols['first'] = $GLOBALS['TL_LANG']['MSC']['sc_first'];
                $cols['second'] = $GLOBALS['TL_LANG']['MSC']['sc_second'];
                break;

            case '3':
                $cols['first'] = $GLOBALS['TL_LANG']['MSC']['sc_first'];
                $cols['second'] = $GLOBALS['TL_LANG']['MSC']['sc_second'];
                $cols['third'] = $GLOBALS['TL_LANG']['MSC']['sc_third'];
                break;

            case '4':
                $cols['first'] = $GLOBALS['TL_LANG']['MSC']['sc_first'];
                $cols['second'] = $GLOBALS['TL_LANG']['MSC']['sc_second'];
                $cols['third'] = $GLOBALS['TL_LANG']['MSC']['sc_third'];
                $cols['fourth'] = $GLOBALS['TL_LANG']['MSC']['sc_fourth'];
                break;

            case '5':
                $cols['first'] = $GLOBALS['TL_LANG']['MSC']['sc_first'];
                $cols['second'] = $GLOBALS['TL_LANG']['MSC']['sc_second'];
                $cols['third'] = $GLOBALS['TL_LANG']['MSC']['sc_third'];
                $cols['fourth'] = $GLOBALS['TL_LANG']['MSC']['sc_fourth'];
                $cols['fifth'] = $GLOBALS['TL_LANG']['MSC']['sc_fifth'];
                break;
        }

        return $cols;
    }
}
