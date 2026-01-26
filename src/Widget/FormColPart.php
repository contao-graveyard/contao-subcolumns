<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Widget;

use Contao\BackendTemplate;
use Contao\FrontendTemplate;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\SubcolumnsBundle\Util\SubcolumnTypes;

class FormColPart extends Widget
{
    /**
     * Template.
     * @var string
     */
    protected $strTemplate = 'form_colset';

    protected $strColTemplate = 'ce_colsetPart';

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
        $container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];

        $objTemplate = new FrontendTemplate($this->strColTemplate);

        if (1 === $this->fsc_gapuse) {
            $gap_value = '' !== $this->fsc_gap ? $this->fsc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?: 12);
            $gap_unit = 'px';

            if (2 === \count($container)) {
                $objTemplate->gap = [
                    'left' => floor(0.5 * $gap_value).$gap_unit,
                ];
            }
            elseif (3 === \count($container)) {
                switch ($this->fsc_sortid) {
                    case 1:
                        $objTemplate->gap = [
                            'right' => floor(0.333 * $gap_value).$gap_unit,
                            'left' => floor(0.333 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 2:
                        $objTemplate->gap = [
                            'left' => ceil(0.666 * $gap_value).$gap_unit,
                        ];
                        break;
                }
            }
            elseif (4 === \count($container)) {
                switch ($this->fsc_sortid) {
                    case 1:
                        $objTemplate->gap = [
                            'right' => floor(0.5 * $gap_value).$gap_unit,
                            'left' => floor(0.25 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 2:
                        $objTemplate->gap = [
                            'right' => floor(0.25 * $gap_value).$gap_unit,
                            'left' => ceil(0.5 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 3:
                        $objTemplate->gap = [
                            'left' => ceil(0.75 * $gap_value).$gap_unit,
                        ];
                        break;
                }
            }
            elseif (5 === \count($container)) {
                switch ($this->fsc_sortid) {
                    case 1:
                        $objTemplate->gap = [
                            'right' => floor(0.6 * $gap_value).$gap_unit,
                            'left' => floor(0.2 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 2:
                        $objTemplate->gap = [
                            'right' => floor(0.4 * $gap_value).$gap_unit,
                            'left' => ceil(0.4 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 3:
                        $objTemplate->gap = [
                            'right' => floor(0.2 * $gap_value).$gap_unit,
                            'left' => ceil(0.6 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 4:
                        $objTemplate->gap = [
                            'left' => ceil(0.8 * $gap_value).$gap_unit,
                        ];
                        break;
                }
            }
        }

        $objTemplate->column = $container[$this->fsc_sortid][0].' col_'.($this->fsc_sortid + 1).($this->fsc_sortid === \count($container) - 1 ? ' last' : '');
        $objTemplate->inside = $container[$this->fsc_sortid][1] ?? '';
        $objTemplate->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        return $objTemplate->parse();
    }

    protected function generateBackend(): string
    {
        switch ($this->fsc_sortid) {
            case 1:
                $colID = $GLOBALS['TL_LANG']['MSC']['sc_second'];
                break;
            case 2:
                $colID = $GLOBALS['TL_LANG']['MSC']['sc_third'];
                break;
            case 3:
                $colID = $GLOBALS['TL_LANG']['MSC']['sc_fourth'];
                break;
            case 4:
                $colID = $GLOBALS['TL_LANG']['MSC']['sc_fifth'];
                break;
        }

        $arrColor = unserialize($this->fsc_color);

        if (2 === \count($arrColor) && empty($arrColor[1])) {
            $arrColor = '';
        }
        else {
            $arrColor = $this->compileColor($arrColor);
        }

        if (!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? null)) {
            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
            // $this->Template->visualSet = $strMiniset;
            $this->Template->hint = \sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'];

        $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = \count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type] ?? []);

        $strMiniset = '<div class="colsetexample '.$strSCClass.'">';

        for ($i = 0; $i < $intCountContainers; ++$i) {
            $arrPresentColset = $arrColset[$i];
            $strMiniset .= '<div class="'.$arrPresentColset[0].($i === $this->fsc_sortid ? ' active' : '').'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i + 1).($blnInside ? '</div>' : '').'</div>';
        }

        $strMiniset .= '</div>';

        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $arrColor;
        $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
        $this->Template->visualSet = $strMiniset;
        $this->Template->hint = \sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

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
        if (empty($color[1])) {
            return "#{$color[0]}";
        }

        return 'rgba('.implode(',', $this->convertHexColor($color[0], $blnWriteToFile ?? false, $vars ?? [])).','.($color[1] / 100).')';
    }

    /**
     * Convert hex colors to rgb.
     * @param string $color
     * @see http://de3.php.net/manual/de/function.hexdec.php#99478
     */
    protected function convertHexColor($color, bool $blnWriteToFile = false, array $vars = []): array
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
