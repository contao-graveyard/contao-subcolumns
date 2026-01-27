<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Modules;

use Contao\BackendTemplate;
use Contao\Module;
use Contao\System;
use HeimrichHannot\SubcolumnsBundle\Util\SubcolumnTypes;

class ModuleSubcolumns extends Module
{
    /**
     * Template.
     * @var string
     */
    protected $strTemplate = 'mod_subcolumns';

    /**
     * Set-Type.
     */
    protected $strSet;

    /**
     * Display a wildcard in the back end.
     */
    #[\Override]
    public function generate(): string
    {
        $this->strSet = SubcolumnTypes::compatSetType();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest())) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### MODULE SUBCOLUMNS ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            // ToDo: Check
            $objTemplate->href = 'contao/main.php?do=modules&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate module.
     */
    protected function compile(): void
    {
        /**
         * CSS Code in das Pagelayout einfügen.
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?: '';
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['ie'] ?: false;

        $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        $GLOBALS['TL_HEAD']['subcolumns'] = $IEHacksCSS ? '<!--[if lte IE 7]><link href="'.$IEHacksCSS.'" rel="stylesheet" type="text/css" /><![endif]--> ' : '';

        $arrSet = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type];
        $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
        $equalize = $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] && $this->sc_equalize ? $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'].' ' : '';

        $arrColumns = unserialize($this->sc_modules);

        if (1 === (int) $this->sc_gapdefault && $useGap) {
            $gap_value = '' !== $this->sc_gap ? $this->sc_gap : '12';
            $gap_unit = 'px';

            if (2 === \count($arrSet)) {
                $arrSet[0][] = [
                    'right' => ceil(0.5 * $gap_value).$gap_unit,
                ];
                $arrSet[1][] = [
                    'left' => floor(0.5 * $gap_value).$gap_unit,
                ];
            }
            elseif (3 === \count($arrSet)) {
                $arrSet[0][] = [
                    'right' => ceil(0.666 * $gap_value).$gap_unit,
                ];
                $arrSet[1][] = [
                    'right' => floor(0.333 * $gap_value).$gap_unit,
                    'left' => floor(0.333 * $gap_value).$gap_unit,
                ];
                $arrSet[2][] = [
                    'left' => ceil(0.666 * $gap_value).$gap_unit,
                ];
            }
            elseif (4 === \count($arrSet)) {
                $arrSet[0][] = [
                    'right' => ceil(0.75 * $gap_value).$gap_unit,
                ];
                $arrSet[1][] = [
                    'right' => floor(0.5 * $gap_value).$gap_unit,
                    'left' => floor(0.25 * $gap_value).$gap_unit,
                ];
                $arrSet[2][] = [
                    'right' => floor(0.25 * $gap_value).$gap_unit,
                    'left' => ceil(0.5 * $gap_value).$gap_unit,
                ];
                $arrSet[3][] = [
                    'left' => ceil(0.75 * $gap_value).$gap_unit,
                ];
            }
            elseif (5 === \count($arrSet)) {
                $arrSet[0][] = [
                    'right' => ceil(0.8 * $gap_value).$gap_unit,
                ];
                $arrSet[1][] = [
                    'right' => floor(0.6 * $gap_value).$gap_unit,
                    'left' => floor(0.2 * $gap_value).$gap_unit,
                ];
                $arrSet[2][] = [
                    'right' => floor(0.4 * $gap_value).$gap_unit,
                    'left' => ceil(0.4 * $gap_value).$gap_unit,
                ];
                $arrSet[3][] = [
                    'right' => floor(0.2 * $gap_value).$gap_unit,
                    'left' => ceil(0.6 * $gap_value).$gap_unit,
                ];
                $arrSet[4][] = [
                    'left' => ceil(0.8 * $gap_value).$gap_unit,
                ];
            }
        }

        foreach ($arrColumns as $row) {
            $strMod = $this->getFrontendModule($row['module']);

            switch ($row['column']) {
                case 'first':
                    $arrSet[0]['modules'][] = $strMod;
                    break;

                case 'second':
                    $arrSet[1]['modules'][] = $strMod;
                    break;

                case 'third':
                    $arrSet[2]['modules'][] = $strMod;
                    break;

                case 'fourth':
                    $arrSet[3]['modules'][] = $strMod;
                    break;

                case 'fifth':
                    $arrSet[4]['modules'][] = $strMod;
                    break;
            }
        }

        /* Add class "first" and "last" to the corresponding tables */
        $i = 0;
        $l = \count($arrSet);

        foreach ($arrSet as $k => $v) {
            $arrSet[$k][0] = $v[0].(0 === $i++ ? ' first' : '').' col_'.$i.($i === $l ? ' last' : '');
        }

        $this->Template->intCols = \count($arrSet);
        $this->Template->inside = $container[0][1] ?? null;
        $this->Template->arrSet = $arrSet;
        $this->Template->scclass = $equalize.$GLOBALS['TL_SUBCL'][$this->strSet]['scclass'].' colcount_'.$l.' '.$this->strSet.' col-'.$this->sc_type;
        $this->Template->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];
    }
}
