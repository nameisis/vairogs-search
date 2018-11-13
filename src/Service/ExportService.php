<?php

namespace Vairogs\Utils\Search\Service;

use DateTime;
use Vairogs\Utils\Search\Elastic\Query\MatchAllQuery;
use Vairogs\Utils\Search\Elastic\Search;
use Vairogs\Utils\Search\Exception\DocumentParserException;
use Vairogs\Utils\Search\Result\RawIterator;
use Vairogs\Utils\Search\Service\Json\JsonWriter;
use ReflectionException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ExportService
{
    /**
     * @param Manager $manager
     * @param string $filename
     * @param array $types
     * @param int $chunkSize
     * @param OutputInterface $output
     * @param int $maxLinesInFile
     *
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function exportIndex(Manager $manager, $filename, $types, $chunkSize, OutputInterface $output, $maxLinesInFile = 300000): void
    {
        $search = new Search();
        $search->addQuery(new MatchAllQuery());
        $search->setSize($chunkSize);
        $queryParameters = [
            '_source' => true,
            'scroll' => '10m',
        ];
        $searchResults = $manager->search($types, $search->toArray(), $queryParameters);
        $results = new RawIterator($searchResults, $manager, [
            'duration' => $queryParameters['scroll'],
            '_scroll_id' => $searchResults['_scroll_id'],
        ]);
        $progress = new ProgressBar($output, $results->count());
        $progress->setRedrawFrequency(100);
        $progress->start();
        $counter = $fileCounter = 0;
        $count = $this->getFileCount($results->count(), $maxLinesInFile, $fileCounter);
        $date = \date(DateTime::ATOM);
        $metadata = [
            'count' => $count,
            'date' => $date,
        ];
        $filename = \str_replace('.json', '', $filename);
        $writer = $this->getWriter($this->getFilePath($filename.'.json'), $metadata);
        $file = [];
        foreach ($results as $data) {
            if ($counter >= $maxLinesInFile) {
                $writer->finalize();
                $writer = null;
                $fileCounter++;
                $count = $this->getFileCount($results->count(), $maxLinesInFile, $fileCounter);
                $metadata = [
                    'count' => $count,
                    'date' => $date,
                ];
                $writer = $this->getWriter($this->getFilePath($filename.'_'.$fileCounter.'.json'), $metadata);
                $counter = 0;
            }
            $doc = \array_intersect_key($data, \array_flip(['_id', '_type', '_source']));
            $writer->push($doc);
            $file[] = $doc;
            $progress->advance();
            $counter++;
        }
        $writer->finalize();
        $progress->finish();
        $output->writeln('');
    }

    /**
     * @param int $resultsCount
     * @param int $maxLinesInFile
     * @param int $fileCounter
     *
     * @return int
     */
    protected function getFileCount($resultsCount, $maxLinesInFile, $fileCounter): int
    {
        $leftToInsert = $resultsCount - ($fileCounter * $maxLinesInFile);
        if ($leftToInsert <= $maxLinesInFile) {
            $count = $leftToInsert;
        } else {
            $count = $maxLinesInFile;
        }

        return $count;
    }

    /**
     * @param string $filename
     * @param array $metadata
     *
     * @return JsonWriter
     */
    protected function getWriter($filename, $metadata): JsonWriter
    {
        return new JsonWriter($filename, $metadata);
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function getFilePath($filename): string
    {
        if ($filename{0} === '/' || false !== \strpos($filename, ':')) {
            return $filename;
        }

        return \getcwd().'/'.$filename;
    }
}
