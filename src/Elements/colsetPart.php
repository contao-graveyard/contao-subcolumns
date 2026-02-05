<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Elements;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\System;
use HeimrichHannot\SubcolumnsBundle\Util\SubcolumnTypes;

class colsetPart extends ContentElement
{
    /**
     * Template.
     * @var string
     */
    protected $strTemplate = 'ce_colsetPart';

    /**
     * Set-Type.
     */
    protected $strSet;

    /**
     * Display a wildcard in the back end.
     * @return string
     */
    #[\Override]
    public function generate()
    {
        $this->strSet = SubcolumnTypes::compatSetType();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest())) {
            $colID = null;

            switch ($this->sc_sortid) {
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

            $arrColor = unserialize($this->sc_color);

            if (!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false)) {
                $this->Template = new BackendTemplate('be_subcolumns');
                $this->Template->setColor = $this->compileColor($arrColor);
                $this->Template->colsetTitle = '### COLUMNSET START '.$this->sc_type.' <strong>'.$this->sc_name.'</strong> ###';
                // $this->Template->visualSet = $strMiniset;
                $this->Template->hint = \sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'] ?? '', $colID);

                return $this->Template->parse();
            }

            $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/contaosubcolumns/assets/be_style.css';
            $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false;

            $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type];
            $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
            $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

            $intCountContainers = \count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]);

            $strMiniset = '<div class="colsetexample '.$strSCClass.'">';

            for ($i = 0; $i < $intCountContainers; ++$i) {
                $arrPresentColset = $arrColset[$i];
                $strMiniset .= '<div class="'.$arrPresentColset[0].($i === $this->sc_sortid ? ' active' : '').'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i + 1).($blnInside ? '</div>' : '').'</div>';
            }

            $strMiniset .= '</div>';

            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->sc_type.' <strong>'.$this->sc_name.'</strong> ###';
            $this->Template->visualSet = $strMiniset;
            $this->Template->hint = \sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

            return $this->Template->parse();
        }

        return parent::generate();
    }

    /**
     * Generate content element.
     * @return string
     */
    protected function compile()
    {
        $arrCounts = [
            '1' => 'second',
            '2' => 'third',
            '3' => 'fourth',
            '4' => 'fifth',
        ];
        $container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? null;
        $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
        $blnUseInner = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        if (!$container) {
            return;
        }

        if (1 === (int) $this->sc_gapdefault && $useGap) {
            $gap_value = '' !== $this->sc_gap ? $this->sc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 12);
            $gap_unit = 'px';

            if (2 === \count($container)) {
                $this->Template->gap = [
                    'left' => floor(0.5 * $gap_value).$gap_unit,
                ];
            }
            elseif (3 === \count($container)) {
                switch ($this->sc_sortid) {
                    case 1:
                        $this->Template->gap = [
                            'right' => floor(0.333 * $gap_value).$gap_unit,
                            'left' => floor(0.333 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 2:
                        $this->Template->gap = [
                            'left' => ceil(0.666 * $gap_value).$gap_unit,
                        ];
                        break;
                }
            }
            elseif (4 === \count($container)) {
                switch ($this->sc_sortid) {
                    case 1:
                        $this->Template->gap = [
                            'right' => floor(0.5 * $gap_value).$gap_unit,
                            'left' => floor(0.25 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 2:
                        $this->Template->gap = [
                            'right' => floor(0.25 * $gap_value).$gap_unit,
                            'left' => ceil(0.5 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 3:
                        $this->Template->gap = [
                            'left' => ceil(0.75 * $gap_value).$gap_unit,
                        ];
                        break;
                }
            }
            elseif (5 === \count($container)) {
                switch ($this->sc_sortid) {
                    case 1:
                        $this->Template->gap = [
                            'right' => floor(0.6 * $gap_value).$gap_unit,
                            'left' => floor(0.2 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 2:
                        $this->Template->gap = [
                            'right' => floor(0.4 * $gap_value).$gap_unit,
                            'left' => ceil(0.4 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 3:
                        $this->Template->gap = [
                            'right' => floor(0.2 * $gap_value).$gap_unit,
                            'left' => ceil(0.6 * $gap_value).$gap_unit,
                        ];
                        break;
                    case 4:
                        $this->Template->gap = [
                            'left' => ceil(0.8 * $gap_value).$gap_unit,
                        ];
                        break;
                }
            }
        }
        else {
            $blnUseInner = false;
        }

        $this->Template->colID = $arrCounts[$this->sc_sortid] ?? '';
        $this->Template->useInside = $blnUseInner;
        $this->Template->column = $container[$this->sc_sortid][0].' col_'.($this->sc_sortid + 1).($this->sc_sortid === \count($container) - 1 ? ' last' : '');
        $this->Template->inside = $this->Template->useInside ? $container[$this->sc_sortid][1] : '';
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
