<?php

declare(strict_types=1);

use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\SubcolumnsBundle\Util\Polyfill;
use HeimrichHannot\SubcolumnsBundle\Util\SubcolumnTypes;

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_name'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_name'],
    'inputType' => 'text',
    'save_callback' => [['tl_content_sc', 'setColsetName']],
    'eval' => [
        'maxlength' => '255',
        'unique' => true,
        'spaceToUnderscore' => true,
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_gap'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_gap'],
    'default' => ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 0),
    'inputType' => 'text',
    'eval' => [
        'maxlength' => '4',
        'regxp' => 'digit',
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_type'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_type'],
    'inputType' => 'select',
    'options_callback' => ['tl_content_sc', 'getAllTypes'],
    'eval' => [
        'includeBlankOption' => true,
        'mandatory' => true,
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_gapdefault'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_gapdefault'],
    'default' => 1,
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr m12 w50',
    ],
    'sql' => "char(1) NOT NULL default '1'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_equalize'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_equalize'],
    'inputType' => 'checkbox',
    'eval' => [],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_color'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_color'],
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 6,
        'multiple' => true,
        'size' => 2,
        'colorpicker' => true,
        'isHexColor' => true,
        'decodeEntities' => true,
        'tl_class' => 'w50 wizard',
    ],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_parent'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_parent'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_childs'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_childs'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['sc_sortid'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_sortid'],
    'sql' => "int(2) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'][] = ['tl_content_sc', 'toggleAdditionalElements'];

$GLOBALS['TL_DCA']['tl_content']['palettes']['colsetPart'] = 'cssID';
$GLOBALS['TL_DCA']['tl_content']['palettes']['colsetEnd'] = $GLOBALS['TL_DCA']['tl_content']['palettes']['default'];

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = ['tl_content_sc', 'createPalette'];
$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = ['tl_content_sc', 'scUpdate'];
$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = ['tl_content_sc', 'setElementProperties'];
$GLOBALS['TL_DCA']['tl_content']['config']['ondelete_callback'][] = ['tl_content_sc', 'scDelete'];
$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = ['tl_content_sc', 'scCopy'];

/**
 * Erweiterung für die tl_content-Klasse.
 */
class tl_content_sc extends tl_content
{
    /*
     * Get the colsets depending on the selection from the settings
     */
    public function getAllTypes()
    {
        $strSet = SubcolumnTypes::compatSetType();

        return array_keys($GLOBALS['TL_SUBCL'][$strSet]['sets']);
    }

    /*
     * Create the palette for the startelement
     */
    public function createPalette(DataContainer $dc): void
    {
        $strSet = SubcolumnTypes::compatSetType();

        if (empty($GLOBALS['TL_SUBCL'][$strSet])) {
            dump("Subcolumns profile '{$strSet}' not found. Please check your settings.");

            return;
        }

        $strGap = empty($GLOBALS['TL_SUBCL'][$strSet]['gap']) ? '' : ',sc_gapdefault,sc_gap';

        $strEquilize = '';
        if (!empty($GLOBALS['TL_SUBCL'][$strSet]['equalize'])) {
            $strEquilize = '{colheight_legend:collapsed},sc_equalize;';
        }

        $GLOBALS['TL_DCA']['tl_content']['palettes']['colsetStart'] = '{type_legend},type;{colset_legend},sc_name,sc_type,sc_color'.$strGap.';'.$strEquilize.'{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,invisible,cssID,space';
    }

    /**
     * Autogenerate an name for the colset if it has not been set yet.
     * @param  object $varValue
     * @return string
     */
    public function setColsetName($varValue, DataContainer $dc)
    {
        $autoName = false;

        // Generate alias if there is none
        if ('' === (string) $varValue) {
            $autoName = true;
            $varValue = 'colset.'.$dc->id;
        }

        return $varValue;
    }

    /**
     * Write the other Sets.
     */
    public function scUpdate(DataContainer $dc)
    {
        if ($dc->activeRecord->sc_columnset ?? false) {
            // let this be handled by subcolumns-bootstrap-bundle
            return false;
        }

        if ('colsetStart' !== $dc->activeRecord->type || '' === $dc->activeRecord->sc_type) {
            return false;
        }

        $strSet = SubcolumnTypes::compatSetType();

        $sc_type = $dc->activeRecord->sc_type;

        $arrColset = $GLOBALS['TL_SUBCL'][$strSet]['sets'][$sc_type] ?? null;

        $arrChilds = '' !== $dc->activeRecord->sc_childs ? unserialize($dc->activeRecord->sc_childs) : '';

        return $this->createColset($dc->activeRecord, $sc_type, $arrColset, $arrChilds);
    }

    /**
     * Write the other Sets.
     * @param  object $dc
     * @return string
     */
    public function setElementProperties(DataContainer $dc)
    {
        if ('colsetStart' !== $dc->activeRecord->type || '' === $dc->activeRecord->sc_type) {
            return '';
        }

        $objEnd = $this->Database->prepare('SELECT sorting FROM tl_content WHERE sc_name=?')->execute($dc->activeRecord->sc_name.'-End');

        $arrSet = [
            'protected' => $dc->activeRecord->protected,
            'groups' => $dc->activeRecord->groups,
            'guests' => $dc->activeRecord->guests,
        ];

        $this->Database->prepare('UPDATE tl_content %s WHERE pid=? AND sorting > ? AND sorting <= ?')->set($arrSet)->execute($dc->activeRecord->pid, $dc->activeRecord->sorting, $objEnd->sorting);

        return null;
    }

    public function scDelete(DataContainer $dc)
    {
        if ($dc->activeRecord->sc_columnset ?? false) {
            // let this be handled by subcolumns-bootstrap-bundle
            return false;
        }

        $delRecord = $this->Database->prepare('SELECT * FROM tl_content WHERE id=?')
                                                ->execute($dc->id)
                                                ->fetchAssoc()
        ;

        if ('colsetStart' === $delRecord['type'] || 'colsetPart' === $delRecord['type'] || 'colsetEnd' === $delRecord['type']) {
            /*
             * Wird ein Startelement gelöscht, werden alle Kindelemente in ein Array geschrieben
             * und ebenfalls gelöscht
             */
            if ('colsetStart' === $delRecord['type']) {
                $eraseArray = '' !== $delRecord['sc_childs'] ? unserialize($delRecord['sc_childs']) : [];
            }

            /*
             * Wird ein Teiler oder das Endelement gelöscht
             */
            if ('colsetPart' === $delRecord['type'] || 'colsetEnd' === $delRecord['type']) {
                $parent = Database::getInstance()
                    ->prepare('SELECT sc_childs FROM tl_content WHERE id=?')
                    ->execute($delRecord['sc_parent'])
                    ->fetchAssoc()
                ;

                if (!$parent) {
                    return false;
                }

                $childs = '' !== $parent['sc_childs'] ? unserialize($parent['sc_childs']) : [];

                $eraseArray[] = $delRecord['sc_parent'];

                foreach ($childs as $wert) {
                    if ($wert !== $delRecord['id']) {
                        $eraseArray[] = $wert;
                    }
                }
            }

            if (count($eraseArray) > 0) {
                $counter = count($eraseArray);

                for ($i = 0; $i < $counter; ++$i) {
                    Database::getInstance()
                        ->prepare('DELETE FROM tl_content WHERE id=?')
                        ->execute($eraseArray[$i])
                    ;
                }
            }
        }

        return null;
    }

    /* Bearbeiten-Icon für Trenn- und Endelemente ausblenden */
    public function showEditOperation($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
    {
        if ('colsetPart' !== $arrRow['type'] && 'colsetEnd' !== $arrRow['type']) {
            $href .= '&id='.$arrRow['id'];

            return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
        }

        return null;
    }

    /* Kopier-Icon für Trenn- und Endelemente ausblenden */
    public function showCopyOperation($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
    {
        if ('colsetPart' !== $arrRow['type'] && 'colsetEnd' !== $arrRow['type']) {
            $href .= '&id='.$arrRow['id'];

            return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
        }

        return null;
    }

    /* Kopier-Icon für Trenn- und Endelemente ausblenden */
    public function showDeleteOperation($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
    {
        if ('colsetPart' !== $arrRow['type'] && 'colsetEnd' !== $arrRow['type']) {
            $href .= '&id='.$arrRow['id'];

            return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
        }

        return null;
    }

    /* Toggle-Status auf Trenn und End-elemente anwenden */
    public function toggleAdditionalElements($varValue, $dc)
    {
        if (0 !== $dc->id) {
            $objEntry = $this->Database->prepare('UPDATE tl_content SET tstamp='.time().", invisible='".($varValue ? 1 : '')."' WHERE sc_parent=? AND type!=?")->execute($dc->id, 'colsetStart');

            return $varValue;
        }

        return $varValue;
    }

    public function scCopy($intId, DataContainer $dc): void
    {
        $dc->activeRecord = $this->Database->prepare('SELECT * FROM tl_content WHERE id=?')->execute($intId)->first();

        if ('colsetStart' !== $dc->activeRecord->type && 'colsetPart' !== $dc->activeRecord->type && 'colsetEnd' !== $dc->activeRecord->type) {
            return;
        }

        if ('copy' === $this->Input->get('act') && 'colsetStart' === $dc->activeRecord->type) {
            $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                        ->set([
                            'sc_parent' => 0,
                            'sc_childs' => '',
                        ])
                        ->execute($intId)
            ;
        }

        if ('copyAll' === $this->Input->get('act')) {
            // Startelement mit aktuellen Daten besetzen und Session mit alten Daten füllen
            if ('colsetStart' === $dc->activeRecord->type) {
                $arrSession = [
                    'parentId' => $intId,
                    'count' => 1,
                    'childs' => [],
                ];

                // $this->Session->set('sc'.$dc->activeRecord->sc_parent,$arrSession);
                $GLOBALS['scglobal']['sc'.$dc->activeRecord->sc_parent] = $arrSession;

                $arrSet = [
                    'sc_name' => 'colset.'.$intId,
                    'sc_parent' => $intId,
                ];

                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrSet)
                                            ->execute($intId)
                ;
            }

            if ('colsetPart' === $dc->activeRecord->type) {
                // $arrSession = $this->Session->get('sc'.$dc->activeRecord->sc_parent);
                $arrSession = $GLOBALS['scglobal']['sc'.$dc->activeRecord->sc_parent];

                $intNewParent = $arrSession['parentId'];
                $intCount = $arrSession['count'];
                $arrChilds = $arrSession['childs'];

                $arrSet = [
                    'sc_name' => 'colset.'.$intNewParent.'-Part-'.$intCount,
                    'sc_parent' => $intNewParent,
                ];

                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrSet)
                                            ->execute($intId)
                ;

                $arrChilds[] = $intId;

                $arrSession['count'] = ++$intCount;
                $arrSession['childs'] = $arrChilds;

                // $this->Session->set('sc'.$dc->activeRecord->sc_parent,$arrSession);
                $GLOBALS['scglobal']['sc'.$dc->activeRecord->sc_parent] = $arrSession;
            }

            if ('colsetEnd' === $dc->activeRecord->type) {
                // $arrSession = $this->Session->get('sc'.$dc->activeRecord->sc_parent);
                $arrSession = $GLOBALS['scglobal']['sc'.$dc->activeRecord->sc_parent];

                $intNewParent = $arrSession['parentId'];
                $intCount = $arrSession['count'];
                $arrChilds = $arrSession['childs'];

                $arrSet = [
                    'sc_name' => 'colset.'.$intNewParent.'-End',
                    'sc_parent' => $intNewParent,
                ];

                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrSet)
                                            ->execute($intId)
                ;

                $arrChilds[] = $intId;

                $arrSet = [
                    'sc_childs' => $arrChilds,
                ];

                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrSet)
                                            ->execute($intNewParent)
                ;
            }
        }
    }

    /**
     * HOOK: $GLOBALS['TL_HOOKS']['clipboardCopy'].
     *
     * @param int  $intId
     * @param bool $isGrouped
     */
    public function clipboardCopy($intId, DataContainer $dc, $isGrouped): void
    {
        if (!$isGrouped) {
            $objActiveRecord = $this->Database
                    ->prepare('SELECT * FROM tl_content WHERE id = ?')
                    ->executeUncached($intId)
            ;

            if ('colsetStart' === $objActiveRecord->type) {
                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                            ->set([
                                'sc_childs' => '',
                                'sc_parent' => '',
                                'sc_name' => 'colset.'.$objActiveRecord->id,
                            ])
                            ->execute($intId)
                ;

                $objContent = $this->Database
                            ->prepare('Select * FROM tl_content WHERE id=?')
                            ->execute($intId)
                ;

                $strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ?? null ?: 'yaml3';

                $sc_type = $objContent->sc_type;

                $arrColset = $GLOBALS['TL_SUBCL'][$strSet]['sets'][$sc_type];

                $logger = System::getContainer()->get('logger');

                $logger->info('Values: sc-Type='.$sc_type.' Values: sc-Colset-Count='.count($arrColset).' :: SpaltensetHilfe clipboardCopy()');

                $this->createColset($objContent, $sc_type, $arrColset);
            }
        }
    }

    private function createColset($objElement, $sc_type, $arrColset, $arrChilds = ''): bool|null
    {
        if (!is_array($arrColset)) {
            return false;
        }

        $intColcount = count($arrColset) - 2;

        $logger = System::getContainer()->get('logger');
        $logger->info('ID= '.$objElement->id.' :: SpaltensetHilfe createColset()');

        /* Neues Spaltenset anlegen */
        if ('' === $arrChilds) {
            $arrChilds = [];
            $this->moveRows($objElement->pid, $objElement->ptable, $objElement->sorting, 128 * (count($arrColset) + 1));

            $arrSet = [
                'pid' => $objElement->pid,
                'ptable' => $objElement->ptable,
                'tstamp' => time(),
                'sorting' => 0,
                'type' => 'colsetPart',
                'sc_name' => '',
                'sc_type' => $sc_type,
                'sc_parent' => $objElement->id,
                'sc_sortid' => 0,
                'sc_gap' => $objElement->sc_gap,
                'sc_gapdefault' => $objElement->sc_gapdefault,
                'sc_color' => $objElement->sc_color,
            ];

            if (in_array('GlobalContentelements', Polyfill::legacyPolyfill_getActiveModules(), true)) {
                $arrSet['do'] = $this->Input->get('do');
            }

            for ($i = 1; $i <= $intColcount + 1; ++$i) {
                $arrSet['sorting'] = $objElement->sorting + ($i + 1) * 64;
                $arrSet['sc_name'] = $objElement->sc_name.'-Part-'.$i;
                $arrSet['sc_sortid'] = $i;

                $insertElement = $this->Database->prepare('INSERT INTO tl_content %s')
                                                ->set($arrSet)
                                                ->execute()
                                                ->insertId
                ;

                $arrChilds[] = $insertElement;
            }

            $arrSet['sorting'] = $objElement->sorting + ($i + 1) * 64;
            $arrSet['type'] = 'colsetEnd';
            $arrSet['sc_name'] = $objElement->sc_name.'-End';
            $arrSet['sc_sortid'] = $intColcount + 2;

            $insertElement = $this->Database->prepare('INSERT INTO tl_content %s')
                                            ->set($arrSet)
                                            ->execute()
                                            ->insertId
            ;

            $arrChilds[] = $insertElement;

            $insertElement = $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set([
                                                'sc_childs' => $arrChilds,
                                                'sc_parent' => $objElement->id,
                                            ])
                                            ->execute($objElement->id)
            ;

            return true;
        }

        /* Gleiche Spaltenzahl */
        if (count($arrChilds) === count($arrColset)) {
            $intLastElement = array_pop($arrChilds);

            $i = 1;

            foreach ($arrChilds as $v) {
                $arrSet = [
                    'sc_type' => $sc_type,
                    'sc_gap' => $objElement->sc_gap,
                    'sc_gapdefault' => $objElement->sc_gapdefault,
                    'sc_sortid' => $i,
                    'sc_name' => $objElement->sc_name.'-Part-'.($i++),
                    'sc_color' => $objElement->sc_color,
                ];

                $this->Database->prepare('UPDATE tl_content %s WHERE id='.$v)
                                            ->set($arrSet)
                                            ->execute()
                ;
            }

            $arrSet = [
                'sc_type' => $sc_type,
                'sc_gap' => $objElement->sc_gap,
                'sc_sortid' => $i,
                'sc_name' => $objElement->sc_name.'-End',
                'sc_color' => $objElement->sc_color,
            ];

            $this->Database->prepare('UPDATE tl_content %s WHERE id='.$intLastElement)
                                        ->set($arrSet)
                                        ->execute()
            ;

            return true;
        }

        /* Weniger Spalten */
        if (count($arrChilds) > count($arrColset)) {
            $intDiff = count($arrChilds) - count($arrColset);

            for ($i = 1; $i <= $intDiff; ++$i) {
                $intChildId = array_pop($arrChilds);
                $this->Database->prepare('DELETE FROM tl_content WHERE id=?')
                                            ->execute($intChildId)
                ;
            }

            $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set([
                                                'sc_childs' => $arrChilds,
                                            ])
                                            ->execute($objElement->id)
            ;

            /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
            $arrSet = [
                'sc_type' => $sc_type,
                'sc_gap' => $objElement->sc_gap,
                'sc_gapdefault' => $objElement->sc_gapdefault,
                'sc_color' => $objElement->sc_color,
            ];

            foreach ($arrChilds as $value) {
                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrSet)
                                            ->execute($value)
                ;
            }

            /*  Den Typ des letzten Elements auf End-ELement umsetzen und FSC-namen anpassen */
            $intChildId = array_pop($arrChilds);

            $arrSet['sc_name'] = $objElement->sc_name.'-End';
            $arrSet['type'] = 'colsetEnd';

            $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrSet)
                                            ->execute($intChildId)
            ;

            return true;
        }

        /* Mehr Spalten */
        if (count($arrChilds) < count($arrColset)) {
            $intDiff = count($arrColset) - count($arrChilds);

            $objEnd = $this->Database->prepare('SELECT id,sorting,sc_sortid FROM tl_content WHERE id=?')->execute($arrChilds[count($arrChilds) - 1]);

            $this->moveRows($objElement->pid, $objElement->ptable, $objEnd->sorting, 64 * $intDiff);

            /*  Den Typ des letzten Elements auf End-ELement umsetzen und SC-namen anpassen */
            $intChildId = count($arrChilds);
            $arrSet['sc_name'] = $objElement->sc_name.'-Part-'.$intChildId;
            $arrSet['type'] = 'colsetPart';

            $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrSet)
                                            ->execute($objEnd->id)
            ;

            $intFscSortId = $objEnd->sc_sortid;
            $intSorting = $objEnd->sorting;

            $arrSet = [
                'type' => 'colsetPart',
                'pid' => $objElement->pid,
                'ptable' => $objElement->ptable,
                'tstamp' => time(),
                'sorting' => 0,
                'sc_name' => '',
                'sc_type' => $sc_type,
                'sc_parent' => $objElement->id,
                'sc_sortid' => 0,
                'sc_gap' => $objElement->sc_gap,
                'sc_gapdefault' => $objElement->sc_gapdefault,
                'sc_color' => $objElement->sc_color,
            ];

            if (in_array('GlobalContentelements', $this->Config->getActiveModules(), true)) {
                $arrSet['do'] = $this->Input->get('do');
            }

            if ($intDiff > 0) {
                /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
                for ($i = 1; $i < $intDiff; ++$i) {
                    ++$intChildId;
                    ++$intFscSortId;
                    $intSorting += 64;
                    $arrSet['sc_name'] = $objElement->sc_name.'-Part-'.$intChildId;
                    $arrSet['sc_sortid'] = $intFscSortId;
                    $arrSet['sorting'] = $intSorting;

                    $objInsertElement = $this->Database->prepare('INSERT INTO tl_content %s')
                                            ->set($arrSet)
                                            ->execute()
                    ;

                    $insertElement = $objInsertElement->insertId;

                    $arrChilds[] = $insertElement;
                }
            }

            /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
            $arrData = [
                'sc_type' => $sc_type,
                'sc_gap' => $objElement->sc_gap,
                'sc_gapdefault' => $objElement->sc_gapdefault,
                'sc_color' => $objElement->sc_color,
            ];

            foreach ($arrChilds as $value) {
                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set($arrData)
                                            ->execute($value)
                ;
            }

            /* Neues End-element erzeugen */
            $arrSet['sorting'] = $intSorting + 64;
            $arrSet['type'] = 'colsetEnd';
            $arrSet['sc_name'] = $objElement->sc_name.'-End';
            $arrSet['sc_sortid'] = ++$intFscSortId;

            $insertElement = $this->Database->prepare('INSERT INTO tl_content %s')
                                            ->set($arrSet)
                                            ->execute()
                                            ->insertId
            ;

            $arrChilds[] = $insertElement;

            /* Kindelemente in Startelement schreiben */
            $insertElement = $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                                            ->set([
                                                'sc_childs' => $arrChilds,
                                            ])
                                            ->execute($objElement->id)
            ;

            return true;
        }

        return null;
    }

    private function moveRows($pid, $ptable, $sorting, int $ammount = 128): void
    {
        $this->Database->prepare('UPDATE tl_content SET sorting = sorting + ? WHERE pid=? AND ptable=? AND sorting > ?')
                                    ->execute($ammount, $pid, $ptable, $sorting)
        ;
    }
}
