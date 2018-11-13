<?php

namespace Vairogs\Utils\Search\Result;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Vairogs\Utils\Search\Annotation\NestedType;
use Vairogs\Utils\Search\Annotation\ObjectType;
use Vairogs\Utils\Search\Exception\DocumentParserException;
use Vairogs\Utils\Search\Mapping\MetadataCollector;
use Vairogs\Utils\Search\Service\Manager;
use LogicException;
use ReflectionException;
use stdClass;

class Converter
{
    /**
     * @var MetadataCollector
     */
    private $metadataCollector;

    /**
     * @param MetadataCollector $metadataCollector
     */
    public function __construct($metadataCollector)
    {
        $this->metadataCollector = $metadataCollector;
    }

    /**
     * @param array $rawData
     * @param Manager $manager
     *
     * @return stdClass
     *
     * @throws ReflectionException
     */
    public function convertToDocument($rawData, Manager $manager): stdClass
    {
        $types = $this->metadataCollector->getMappings($manager->getConfig()['mappings']);
        if (isset($types[$rawData['_type']])) {
            $metadata = $types[$rawData['_type']];
        } else {
            throw new LogicException("Got document of unknown type '{$rawData['_type']}'.");
        }
        switch (true) {
            case isset($rawData['_source']):
                $rawData = \array_merge($rawData, $rawData['_source']);
                break;
            case isset($rawData['fields']):
                $rawData = \array_merge($rawData, $rawData['fields']);
                break;
            default:
                break;
        }
        $object = $this->assignArrayToObject($rawData, new $metadata['namespace'](), $metadata['aliases']);

        return $object;
    }

    /**
     * @param array $array
     * @param stdClass $object
     * @param array $aliases
     *
     * @return stdClass
     */
    public function assignArrayToObject(array $array, $object, array $aliases): stdClass
    {
        foreach ($array as $name => $value) {
            if (!isset($aliases[$name])) {
                continue;
            }
            if (isset($aliases[$name]['type'])) {
                switch ($aliases[$name]['type']) {
                    case 'date':
                        if (null === $value || (\is_object($value) && $value instanceof DateTimeInterface)) {
                            continue 2;
                        }
                        if (\is_numeric($value) && (int)$value === $value) {
                            $time = $value;
                            $value = new \DateTime();
                            $value->setTimestamp($time);
                        } else {
                            $value = new DateTime($value);
                        }
                        break;
                    case ObjectType::NAME:
                    case NestedType::NAME:
                        if ($aliases[$name]['multiple']) {
                            $value = new ObjectIterator($this, $value, $aliases[$name]);
                        } else {
                            if ($value === null) {
                                break;
                            }
                            $value = $this->assignArrayToObject($value, new $aliases[$name]['namespace'](), $aliases[$name]['aliases']);
                        }
                        break;
                    case 'boolean':
                        if (!\is_bool($value)) {
                            $value = (bool)$value;
                        }
                        break;
                    default:
                        break;
                }
            }
            if ($aliases[$name]['propertyType'] === 'private') {
                $object->{$aliases[$name]['methods']['setter']}($value);
            } else {
                $object->{$aliases[$name]['propertyName']} = $value;
            }
        }

        return $object;
    }

    /**
     * @param mixed $object
     * @param array $aliases
     * @param array $fields
     *
     * @return array
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function convertToArray($object, array $aliases = [], array $fields = []): array
    {
        if (empty($aliases)) {
            $aliases = $this->getAlias($object);
            if (\count($fields) > 0) {
                $aliases = \array_intersect_key($aliases, \array_flip($fields));
            }
        }
        $array = [];
        foreach ($aliases as $name => $alias) {
            if ($aliases[$name]['propertyType'] === 'private') {
                $value = $object->{$aliases[$name]['methods']['getter']}();
            } else {
                $value = $object->{$aliases[$name]['propertyName']};
            }
            if ($value !== null) {
                if (\array_key_exists('aliases', $alias)) {
                    $new = [];
                    if ($alias['multiple']) {
                        $this->isCollection($aliases[$name]['propertyName'], $value);
                        foreach ($value as $item) {
                            $this->checkVariableType($item, [$alias['namespace']]);
                            $new[] = $this->convertToArray($item, $alias['aliases']);
                        }
                    } else {
                        $this->checkVariableType($value, [$alias['namespace']]);
                        $new = $this->convertToArray($value, $alias['aliases']);
                    }
                    $value = $new;
                }
                if ($value instanceof DateTime) {
                    $value = $value->format($alias['format'] ?? DateTime::ATOM);
                }
                if (isset($alias['type'])) {
                    switch ($alias['type']) {
                        case 'float':
                            if (\is_array($value)) {
                                foreach ($value as $key => $item) {
                                    $value[$key] = (float)$item;
                                }
                            } else {
                                $value = (float)$value;
                            }
                            break;
                        case 'integer':
                            if (\is_array($value)) {
                                foreach ($value as $key => $item) {
                                    $value[$key] = (int)$item;
                                }
                            } else {
                                $value = (int)$value;
                            }
                            break;
                        default:
                            break;
                    }
                }
                $array[$name] = $value;
            }
        }

        return $array;
    }

    /**
     * @param stdClass $document
     *
     * @return array
     * @throws ReflectionException
     * @throws DocumentParserException
     */
    private function getAlias($document): array
    {
        $class = \get_class($document);
        $documentMapping = $this->metadataCollector->getMapping($class);

        return $documentMapping['aliases'];
    }

    /**
     * @param string $property
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     */
    private function isCollection($property, $value): void
    {
        if (!$value instanceof Collection) {
            $got = \is_object($value) ? \get_class($value) : \gettype($value);
            throw new InvalidArgumentException(\sprintf('Value of "%s" property must be an instance of Collection, got %s.', $property, $got));
        }
    }

    /**
     * @param stdClass $object
     * @param array $expectedClasses
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function checkVariableType($object, array $expectedClasses): void
    {
        if (!\is_object($object)) {
            $msg = 'Expected variable of type object, got '.\gettype($object).". (field isn't multiple)";
            throw new InvalidArgumentException($msg);
        }
        $classes = \class_parents($object);
        $classes[] = $class = \get_class($object);
        if (empty(\array_intersect($classes, $expectedClasses))) {
            throw new InvalidArgumentException("Expected object of type {$expectedClasses[0]}, got {$class}.");
        }
    }
}
