<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Elements;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\System;
use HeimrichHannot\SubcolumnsBundle\Util\SubcolumnTypes;

class colsetEnd extends ContentElement
{
    /**
     * Template.
     * @var string
     */
    protected $strTemplate = 'ce_colsetEnd';

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

        if (!$scopeMatcher->isBackendRequest($requestStack->getCurrentRequest())) {
            return parent::generate();
        }

        $arrColor = unserialize($this->sc_color);
        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $this->compileColor($arrColor);
        $this->Template->colsetTitle = '### COLUMNSET END '.$this->sc_type.' <strong>'.$this->sc_name.'</strong> ###';

        if (!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? null)) {
            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/contaosubcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'];

        $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? null;
        if (null === $arrColset) {
            return $this->Template->parse().'<div>HALLO</div>';
        }

        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = \count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]);

        $strMiniset = '<div class="colsetexample final '.$strSCClass.'">';

        for ($i = 0; $i < $intCountContainers; ++$i) {
            $arrPresentColset = $arrColset[$i];
            $strMiniset .= '<div class="'.$arrPresentColset[0].'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i + 1).($blnInside ? '</div>' : '').'</div>';
        }

        $strMiniset .= '</div>';

        $this->Template->visualSet = $strMiniset;

        return $this->Template->parse();
    }

    /**
     * Generate content element.
     * @return string
     */
    protected function compile()
    {
        $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
        $blnUseInner = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        if (1 !== (int) $this->sc_gapdefault || !$useGap) {
            $blnUseInner = false;
        }

        $this->Template->useInside = $blnUseInner;
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
