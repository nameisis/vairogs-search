<?php

namespace Vairogs\Utils\Search\Service;

use DateTime;

class IndexSuffixFinder
{
    /**
     * @param Manager $manager
     * @param null|DateTime $time
     *
     * @return string
     */
    public function setNextFreeIndex(Manager $manager, \DateTime $time = null): string
    {
        if ($time === null) {
            $time = new DateTime();
        }
        $date = $time->format('Y.m.d');
        $indexName = $manager->getIndexName();
        $nameBase = $indexName.'-'.$date;
        $name = $nameBase;
        $i = 0;
        $manager->setIndexName($name);
        while ($manager->indexExists()) {
            $i++;
            $name = "{$nameBase}-{$i}";
            $manager->setIndexName($name);
        }

        return $name;
    }
}
