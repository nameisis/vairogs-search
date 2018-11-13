<?php

namespace Vairogs\Utils\Search\Service;

use Vairogs\Utils\Search\Exception\BulkWithErrorsException;
use Vairogs\Utils\Search\Service\Json\JsonReader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ImportService
{
    /**
     * @param Manager $manager
     * @param string $filename
     * @param OutputInterface $output
     * @param array $options
     *
     * @throws BulkWithErrorsException
     */
    public function importIndex(Manager $manager, $filename, OutputInterface $output, $options): void
    {
        $reader = $this->getReader($manager, $this->getFilePath($filename), $options);
        $progress = new ProgressBar($output, $reader->count());
        $progress->setRedrawFrequency(100);
        $progress->start();
        $bulkSize = $options['bulk-size'];
        foreach ($reader as $key => $document) {
            $data = $document['_source'];
            $data['_id'] = $document['_id'];
            if (\array_key_exists('fields', $document)) {
                $data = \array_merge($document['fields'], $data);
            }
            $manager->bulk('index', $document['_type'], $data);
            if (($key + 1) % $bulkSize === 0) {
                $manager->commit();
            }
            $progress->advance();
        }
        $manager->commit();
        $progress->finish();
        $output->writeln('');
    }

    /**
     * @param Manager $manager
     * @param string $filename
     * @param array $options
     *
     * @return JsonReader
     */
    protected function getReader($manager, $filename, $options): JsonReader
    {
        return new JsonReader($manager, $filename, $options);
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

        return \realpath(\getcwd().'/'.$filename);
    }
}
