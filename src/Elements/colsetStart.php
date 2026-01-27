<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Elements;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\SubcolumnsBundle\Util\SubcolumnTypes;

class colsetStart extends ContentElement
{
    /**
     * Template.
     * @var string
     */
    protected $strTemplate = 'ce_colsetStart';

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
            $arrColor = StringUtil::deserialize($this->sc_color);
            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->sc_type.' <strong>'.$this->sc_name.'</strong> ###';
            $this->Template->hint = \sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first']);

            if (!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? null)) {
                return $this->Template->parse();
            }

            $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/subcolumns/assets/be_style.css';
            $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? null ?: false;

            $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? null;
            if (null === $arrColset) {
                return $this->Template->parse();
            }

            $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
            $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

            $intCountContainers = \count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]);

            $strMiniset = '';

            if ($GLOBALS['TL_CSS']['subcolumns_set']) {
                $strMiniset = '<div class="colsetexample '.$strSCClass.'">';

                for ($i = 0; $i < $intCountContainers; ++$i) {
                    $arrPresentColset = $arrColset[$i];
                    $strMiniset .= '<div class="'.$arrPresentColset[0].(0 === $i ? ' active' : '').'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i + 1).($blnInside ? '</div>' : '').'</div>';
                }

                $strMiniset .= '</div>';
            }

            $this->Template->visualSet = $strMiniset;

            return $this->Template->parse();
        }

        return parent::generate();
    }

    /**
     * Generate content element.
     * @throws \Exception
     */
    protected function compile(): void
    {
        $this->strSet = SubcolumnTypes::compatSetType();

        if (!isset($GLOBALS['TL_SUBCL'][$this->strSet])) {
            throw new \Exception('The requested column set type could not be found. Type \''.$this->strSet."' was requested, but no such type is defined. ".'Maybe your configuration is not correct?');
        }

        /**
         * CSS Code in das Pagelayout einfügen.
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false;
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['ie'] ?? false;

        if ($mainCSS) {
            $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        }
        if ($IEHacksCSS) {
            $GLOBALS['TL_HEAD']['subcolumns'] = '<!--[if lte IE 7]><link href="'.$IEHacksCSS.'" rel="stylesheet" type="text/css" /><![endif]--> ';
        }

        if (!isset($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type])) {
            throw new \Exception('The requested column type could not be found. '.$this->sc_type.' was requested, but no such type is defined in '.$this->strSet.'.');
        }
        $container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type];
        $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
        $equalize = $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] && $this->sc_equalize ? $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'].' ' : '';

        $blnUseInner = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        if (1 === (int) $this->sc_gapdefault && $useGap) {
            $gap_value = '' !== $this->sc_gap ? $this->sc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 12);
            $gap_unit = 'px';

            if (2 === \count($container)) {
                $this->Template->gap = [
                    'right' => ceil(0.5 * $gap_value).$gap_unit,
                ];
            }
            elseif (3 === \count($container)) {
                $this->Template->gap = [
                    'right' => ceil(0.666 * $gap_value).$gap_unit,
                ];
            }
            elseif (4 === \count($container)) {
                $this->Template->gap = [
                    'right' => ceil(0.75 * $gap_value).$gap_unit,
                ];
            }
            elseif (5 === \count($container)) {
                $this->Template->gap = [
                    'right' => ceil(0.8 * $gap_value).$gap_unit,
                ];
            }
        }
        else {
            $blnUseInner = false;
        }

        // $container = unserialize($this->sc_container);
        $this->Template->useInside = $blnUseInner;

        $scTypeClass = ' col-'.$this->sc_type;

        $this->Template->scclass = $equalize.$GLOBALS['TL_SUBCL'][$this->strSet]['scclass'].' colcount_'.\count($container).' '.$this->strSet.$scTypeClass.' sc-type-'.$this->sc_type;
        $this->Template->column = $container[0][0].' col_1 first';
        $this->Template->inside = $this->Template->useInside ? $container[0][1] : '';
    }

    /**
     * Compile a color value and return a hex or rgba color.
     * @param bool $color
     * @param array
     */
    protected function compileColor($color): string
    {
        if (!\is_array($color)) {
            return "#{$color}";
        }
        if (empty($color[1])) {
            return "#{$color[0]}";
        }

        return 'rgba('.implode(',', $this->convertHexColor($color[0], $blnWriteToFile ?? false, $vars ?? [])).','.($color[1] / 100).')';
    }

    /**
     * Convert hex colors to rgb.
     * @param string $color
     * @param bool   $blnWriteToFile
     * @param array  $vars
     * @see http://de3.php.net/manual/de/function.hexdec.php#99478
     */
    protected function convertHexColor($color, $blnWriteToFile = false, $vars = []): array
    {
        // Support global variables
        if (str_starts_with($color, '$')) {
            if (!$blnWriteToFile) {
                return [$color];
            }

            $color = str_replace(array_keys($vars), array_values($vars), $color);
        }

        $rgb = [];

        // Try to convert using bitwise operation
        if (6 === \strlen($color)) {
            $dec = hexdec($color);
            $rgb['red'] = 0xFF & ($dec >> 0x10);
            $rgb['green'] = 0xFF & ($dec >> 0x8);
            $rgb['blue'] = 0xFF & $dec;
        }

        // Shorthand notation
        elseif (3 === \strlen($color)) {
            $rgb['red'] = hexdec(str_repeat(substr($color, 0, 1), 2));
            $rgb['green'] = hexdec(str_repeat(substr($color, 1, 1), 2));
            $rgb['blue'] = hexdec(str_repeat(substr($color, 2, 1), 2));
        }

        return $rgb;
    }
}
