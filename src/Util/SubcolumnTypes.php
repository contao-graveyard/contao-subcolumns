<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Util;

use Contao\Config;

class SubcolumnTypes
{
    public const DEFAULT_TYPE = 'yaml3';

    protected static string $strSet;

    public static function compatSetType(string $default = self::DEFAULT_TYPE): string
    {
        if (isset(static::$strSet)) {
            return static::$strSet;
        }

        static::$strSet = Config::get('subcolumns') ?: $default;

        return static::$strSet;
    }
}
