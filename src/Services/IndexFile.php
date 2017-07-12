<?php

namespace Services;


use MakinaCorpus\Bloom\BloomFilter;
use SplFileObject;

class IndexFile
{
    static function buildIndex($fileName)
    {
        $bloomFile = $fileName . '.filter';

        if (file_exists(__DIR__ . $bloomFile)) {
            unlink(__DIR__ . $bloomFile);
        }

        $file = new \SplFileObject($fileName, 'r');
        $file->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE);

        $probability = 0.0000001;
        $maxSize = self::getLines($file);

        $bloomFilter = new BloomFilter($maxSize, $probability);
        foreach ($file as $line) {
            $bloomFilter->set(strtolower($line));
        }
        file_put_contents($bloomFile, serialize($bloomFilter));
        touch($fileName);
    }

    static function getLines(SplFileObject $file)
    {
        $file->seek(PHP_INT_MAX);
        $lines = $file->key() + 1;
        $file->rewind();
        return $lines;
    }

    /**
     * Returns true for good filter
     *
     * @param $fileName
     * @return bool
     */
    static function checkIntegrity($fileName)
    {
        return (filemtime($fileName) === filemtime($fileName . '.filter'));
    }

}