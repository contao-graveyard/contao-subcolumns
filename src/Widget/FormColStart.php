<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Widget;

use Contao\BackendTemplate;
use Contao\FrontendTemplate;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\SubcolumnsBundle\Util\SubcolumnTypes;

class FormColStart extends Widget
{
    public $strSet;

    public $fsc_type;

    public $fsc_gapuse;

    public $fsc_gap;

    public $fsc_color;

    public $Template;

    /**
     * Template.
     * @var string
     */
    protected $strTemplate = 'form_colset';

    protected $strColTemplate = 'ce_colsetStart';

    /**
     * Do not validate.
     */
    #[\Override]
    public function validate(): void
    {
    }

    /**
     * Generate the widget and return it as string.
     * @return string
     */
    public function generate()
    {
        $this->strSet = SubcolumnTypes::compatSetType();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest())) {
            return $this->generateBackend();
        }

        /**
         * CSS Code in das Pagelayout einfügen.
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?: '';
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['ie'] ?? false;

        $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        $GLOBALS['TL_HEAD']['subcolumns'] = $IEHacksCSS ? '<!--[if lte IE 7]><link href="'.$IEHacksCSS.'" rel="stylesheet" type="text/css" /><![endif]--> ' : '';

        $container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];
        $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'];

        $objTemplate = new FrontendTemplate($this->strColTemplate);

        if (1 === $this->fsc_gapuse && $useGap) {
            $gap_value = '' !== $this->fsc_gap ? $this->fsc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?: 12);
            $gap_unit = 'px';

            if (2 === \count($container)) {
                $objTemplate->gap = [
                    'right' => ceil(0.5 * $gap_value).$gap_unit,
                ];
            }
            elseif (3 === \count($container)) {
                $objTemplate->gap = [
                    'right' => ceil(0.666 * $gap_value).$gap_unit,
                ];
            }
            elseif (4 === \count($container)) {
                $objTemplate->gap = [
                    'right' => ceil(0.75 * $gap_value).$gap_unit,
                ];
            }
            elseif (5 === \count($container)) {
                $objTemplate->gap = [
                    'right' => ceil(0.8 * $gap_value).$gap_unit,
                ];
            }
        }

        // $container = unserialize($this->sc_container);
        $objTemplate->column = $container[0][0].' col_1 first';
        $objTemplate->inside = $container[0][1] ?? '';
        $objTemplate->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $scTypeClass = ' col-'.$this->fsc_type;

        $objTemplate->scclass = ($this->fsc_equalize ? 'equalize ' : '').$GLOBALS['TL_SUBCL'][$this->strSet]['scclass'].' colcount_'.\count($container).' '.$this->strSet.$scTypeClass.' sc-type-'.$this->sc_type.($this->class ? ' '.$this->class : '');

        return $objTemplate->parse();
    }

    protected function generateBackend(): string
    {
        $arrColor = unserialize($this->fsc_color);

        if (2 === \count($arrColor) && empty($arrColor[1])) {
            $arrColor = '';
        }
        else {
            $arrColor = $this->compileColor($arrColor);
        }

        if (!$GLOBALS['TL_SUBCL'][$this->strSet]['files']['css']) {
            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
            $this->Template->hint = \sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first']);

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?: false;

        $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = \count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type]);

        $strMiniset = '';

        if ($GLOBALS['TL_CSS']['subcolumns_set']) {
            $strMiniset = '<div class="colsetexample '.$strSCClass.'">';

            for ($i = 0; $i < $intCountContainers; ++$i) {
                $arrPresentColset = $arrColset[$i];
                $strMiniset .= '<div class="'.$arrPresentColset[0].(0 === $i ? ' active' : '').'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i + 1).($blnInside ? '</div>' : '').'</div>';
            }

            $strMiniset .= '</div>';
        }

        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $arrColor;
        $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
        $this->Template->visualSet = $strMiniset;
        $this->Template->hint = \sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first']);

        return $this->Template->parse();
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
        if (!isset($color[1]) || empty($color[1])) {
            return "#{$color[0]}";
        }

        return 'rgba('.implode(',', $this->convertHexColor($color[0], $blnWriteToFile, $vars)).','.($color[1] / 100).')';
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
