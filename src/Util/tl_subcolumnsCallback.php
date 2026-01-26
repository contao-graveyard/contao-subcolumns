<?php

declare(strict_types=1);

namespace HeimrichHannot\SubcolumnsBundle\Util;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;

class tl_subcolumnsCallback extends Backend
{
    /*
     * Get all sets from the configuration array
     */
    public function getSets(): array
    {
        $arrSets = [];

        foreach ($GLOBALS['TL_SUBCL'] as $k => $v) {
            $arrSets[$k] = $v['label'];
        }

        return $arrSets;
    }

    public function pageCheck($intId = 0): string|null
    {
        if (0 === $intId) {
            return '';
        }

        if (!Input::get('childs')) {
            $objArticle = $this->Database->prepare('SELECT id FROM tl_article WHERE pid=?')
                ->execute($intId)
            ;
            if ($objArticle->numRows > 0) {
                while ($objArticle->next()) {
                    $this->copyCheck($objArticle->id);
                }
            }
        }
        elseif (1 === Input::get('childs')) {
            $arrPages = Database::getInstance()->getChildRecords($intId, 'tl_page');

            foreach ($arrPages as $id) {
                $objArticle = $this->Database->prepare('SELECT id FROM tl_article WHERE pid=?')
                    ->execute($id)
                ;

                if ($objArticle->numRows > 0) {
                    while ($objArticle->next()) {
                        $this->copyCheck($objArticle->id);
                    }
                }
            }
        }

        return null;
    }

    public function articleCheck($intId = 0): string|null
    {
        if (0 === $intId) {
            return '';
        }
        $this->copyCheck($intId);

        return null;
    }

    /**
     * HOOK: $GLOBALS['TL_HOOKS']['clipboardCopyAll'].
     *
     * @param array $arrIds
     */
    public function clipboardCopyAll($arrIds): void
    {
        $arrIds = array_keys(array_flip($arrIds));

        $objDb = $this->Database->execute('SELECT DISTINCT pid FROM tl_content WHERE id IN ('.implode(',', $arrIds).')');

        if ($objDb->numRows > 0) {
            while ($objDb->next()) {
                $this->copyCheck($objDb->pid);
            }
        }
    }

    /**
     * Copy a colset.
     *
     * @param int $pid
     */
    public function copyCheck($pid): void
    {
        $row = $this->Database->prepare('SELECT id, sc_childs, sc_parent FROM tl_content WHERE pid=? AND type=? ORDER BY sorting')
            ->execute($pid, 'colsetStart')
        ;

        if ($row->numRows < 1) {
            return;
        }

        $typeToNameMap = [
            'colsetStart' => 'Start',
            'colsetPart' => 'Part',
            'colsetEnd' => 'End',
        ];

        while ($row->next()) {
            $parent = $row->id;
            $oldParent = $row->sc_parent;
            $newSCName = "colset.{$row->id}";
            $oldChilds = unserialize($row->sc_childs);

            if (!\is_array($oldChilds)) {
                continue;
            }

            $this->Database->prepare('UPDATE tl_content %s WHERE pid=? AND sc_parent=?')
                ->set([
                    'sc_parent' => $parent,
                ])
                ->execute($pid, $oldParent)
            ;

            $child = $this->Database->prepare('SELECT id, type FROM tl_content WHERE pid=? AND sc_parent=? AND id!=? ORDER BY sorting')
                ->execute($pid, $parent, $parent)
            ;

            if ($child->numRows < 1) {
                continue;
            }

            $childIds = [];

            while ($child->next()) {
                $childIds[] = $child->id;
                $childTypes[$child->id] = $child->type;
            }
            sort($childIds);

            $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                ->set([
                    'sc_name' => $newSCName,
                    'sc_childs' => $childIds,
                ])
                ->execute($parent)
            ;

            $partNum = 1;

            foreach ($childTypes as $id => $type) {
                $newchildSCName = $newSCName."-{$typeToNameMap[$type]}".('colsetPart' === $type ? '-'.$partNum++ : '');
                $this->Database->prepare('UPDATE tl_content %s WHERE id=?')
                    ->set([
                        'sc_name' => $newchildSCName,
                    ])
                    ->execute($id)
                ;
            }
        }
    }

    public function formCheck(int|string $intId, DataContainer $dc): string|null
    {
        if (0 === $intId) {
            return '';
        }

        $objElements = $this->Database->prepare('SELECT id,fsc_parent FROM tl_form_field WHERE pid=? AND type=?')->execute($intId, 'formcolstart');

        if (0 === $objElements->numRows) {
            return '';
        }

        while ($objElements->next()) {
            $strName = 'colset.'.$objElements->id;
            $this->Database->prepare('UPDATE tl_form_field %s WHERE pid=? AND fsc_parent=?')
                ->set([
                    'fsc_parent' => $objElements->id,
                    'fsc_name' => $strName,
                ])
                ->execute($intId, $objElements->fsc_parent)
            ;

            $objParts = $this->Database->prepare('SELECT * FROM tl_form_field WHERE fsc_parent=? AND type!=? ORDER BY fsc_sortid')->execute($objElements->id, 'formcolstart');

            $arrChilds = [];

            while ($objParts->next()) {
                $strName = 'formcolend' === $objParts->type ? 'colset.'.$objElements->id.'-End' : 'colset.'.$objElements->id.'-Part-'.$objParts->fsc_sortid;
                $this->Database->prepare('UPDATE tl_form_field %s WHERE id=?')
                    ->set([
                        'fsc_name' => $strName,
                    ])
                    ->execute($objParts->id)
                ;

                $arrChilds[] = $objParts->id;
            }

            $this->Database->prepare('UPDATE tl_form_field %s WHERE id=?')
                ->set([
                    'fsc_childs' => $arrChilds,
                ])
                ->execute($objElements->id)
            ;
        }

        return null;
    }
}
