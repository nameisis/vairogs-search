<?php

namespace Vairogs\Utils\Search\Mapping;

use Doctrine\Common\Annotations\Reader;
use Vairogs\Utils\Core\Util\Text;
use Vairogs\Utils\Search\Annotation\Document;
use Vairogs\Utils\Search\Annotation\Embedded;
use Vairogs\Utils\Search\Annotation\HashMap;
use Vairogs\Utils\Search\Annotation\Id;
use Vairogs\Utils\Search\Annotation\MetaField;
use Vairogs\Utils\Search\Annotation\NestedType;
use Vairogs\Utils\Search\Annotation\ObjectType;
use Vairogs\Utils\Search\Annotation\ParentDocument;
use Vairogs\Utils\Search\Annotation\Property;
use Vairogs\Utils\Search\Annotation\Routing;
use Vairogs\Utils\Search\Annotation\Version;
use Vairogs\Utils\Search\Exception\MissingDocumentAnnotationException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class DocumentParser
{
    public const PROPERTY_ANNOTATION = Property::class;
    public const EMBEDDED_ANNOTATION = Embedded::class;
    public const DOCUMENT_ANNOTATION = Document::class;
    public const OBJECT_ANNOTATION = ObjectType::class;
    public const NESTED_ANNOTATION = NestedType::class;
    public const ID_ANNOTATION = Id::class;
    public const PARENT_ANNOTATION = ParentDocument::class;
    public const ROUTING_ANNOTATION = Routing::class;
    public const VERSION_ANNOTATION = Version::class;
    public const HASH_MAP_ANNOTATION = HashMap::class;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var DocumentFinder
     */
    private $finder;

    /**
     * @var array
     */
    private $objects = [];

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @var array
     */
    private $properties = [];

    /**
     * @var array
     */
    private $documents = [];

    /**
     * @param Reader $reader
     * @param DocumentFinder $finder
     */
    public function __construct(Reader $reader, DocumentFinder $finder)
    {
        $this->reader = $reader;
        $this->finder = $finder;
    }

    /**
     * @param ReflectionClass $class
     *
     * @return array|null|bool
     * @throws MissingDocumentAnnotationException
     * @throws ReflectionException
     */
    public function parse(ReflectionClass $class)
    {
        $className = $class->getName();
        if ($class->isTrait()) {
            return false;
        }
        if (!isset($this->documents[$className])) {
            $document = $this->reader->getClassAnnotation($class, self::DOCUMENT_ANNOTATION);
            /** @var Document $document */
            if ($document === null) {
                throw new MissingDocumentAnnotationException(\sprintf('"%s" class cannot be parsed as document because @Document annotation is missing.', $class->getName()));
            }
            $fields = [];
            $aliases = $this->getAliases($class, $fields);
            $this->documents[$className] = [
                'type' => $document->type ?: Text::toSnakeCase($class->getShortName()),
                'properties' => $this->getProperties($class),
                'fields' => \array_filter(\array_merge($document->dump(), $fields)),
                'aliases' => $aliases,
                'analyzers' => $this->getAnalyzers($class),
                'objects' => $this->getObjects(),
                'namespace' => $class->getName(),
                'class' => $class->getShortName(),
            ];
        }

        return $this->documents[$className];
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param array $metaFields
     *
     * @return array
     * @throws ReflectionException
     */
    private function getAliases(ReflectionClass $reflectionClass, array &$metaFields = null): array
    {
        $reflectionName = $reflectionClass->getName();
        if ($metaFields === null && \array_key_exists($reflectionName, $this->aliases)) {
            return $this->aliases[$reflectionName];
        }
        $alias = [];
        /** @var ReflectionProperty[] $properties */
        $properties = $this->getDocumentPropertiesReflection($reflectionClass);
        foreach ($properties as $name => $property) {
            $directory = $this->guessDirName($property->getDeclaringClass());
            $type = $this->getPropertyAnnotationData($property);
            $type = $type ?? $this->getEmbeddedAnnotationData($property);
            $type = $type ?? $this->getHashMapAnnotationData($property);
            if ($type === null && $metaFields !== null && ($metaData = $this->getMetaFieldAnnotationData($property, $directory)) !== null) {
                $metaFields[$metaData['name']] = $metaData['settings'];
                $type = new stdClass();
                $type->name = $metaData['name'];
            }
            if ($type !== null) {
                $alias[$type->name] = [
                    'propertyName' => $name,
                ];
                if ($type instanceof Property) {
                    $alias[$type->name]['type'] = $type->type;
                }
                if ($type instanceof HashMap) {
                    $alias[$type->name]['type'] = HashMap::NAME;
                }
                $alias[$type->name][HashMap::NAME] = $type instanceof HashMap;
                switch (true) {
                    case $property->isPublic():
                        $propertyType = 'public';
                        break;
                    case $property->isProtected():
                    case $property->isPrivate():
                        $propertyType = 'private';
                        $alias[$type->name]['methods'] = $this->getMutatorMethods($reflectionClass, $name, $type instanceof Property ? $type->type : null);
                        break;
                    default:
                        $message = \sprintf('Wrong property %s type of %s class types cannot '.'be static or abstract.', $name, $reflectionName);
                        throw new \LogicException($message);
                }
                $alias[$type->name]['propertyType'] = $propertyType;
                if ($type instanceof Embedded) {
                    $child = new ReflectionClass($this->finder->getNamespace($type->class, $directory));
                    $alias[$type->name] = \array_merge($alias[$type->name], [
                        'type' => $this->getObjectMapping($type->class, $directory)['type'],
                        'multiple' => $type->multiple,
                        'aliases' => $this->getAliases($child, $metaFields),
                        'namespace' => $child->getName(),
                    ]);
                }
            }
        }
        $this->aliases[$reflectionName] = $alias;

        return $this->aliases[$reflectionName];
    }

    /**
     * @param ReflectionClass $reflectionClass
     *
     * @return array
     */
    private function getDocumentPropertiesReflection(ReflectionClass $reflectionClass): array
    {
        if (\in_array($reflectionClass->getName(), $this->properties, true)) {
            return $this->properties[$reflectionClass->getName()];
        }
        $properties = [];
        foreach ($reflectionClass->getProperties() as $property) {
            if (!\in_array($property->getName(), $properties, true)) {
                $properties[$property->getName()] = $property;
            }
        }
        $parentReflection = $reflectionClass->getParentClass();
        if ($parentReflection !== false) {
            $properties = \array_merge($properties, \array_diff_key($this->getDocumentPropertiesReflection($parentReflection), $properties));
        }
        $this->properties[$reflectionClass->getName()] = $properties;

        return $properties;
    }

    /**
     * @param ReflectionClass $reflection
     *
     * @return string
     */
    private function guessDirName(ReflectionClass $reflection): string
    {
        return \substr($directory = $reflection->getName(), $start = \strpos($directory, '\\') + 1, \strrpos($directory, '\\') - $start);
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return Property|stdClass|null
     */
    private function getPropertyAnnotationData(ReflectionProperty $property)
    {
        $result = $this->reader->getPropertyAnnotation($property, self::PROPERTY_ANNOTATION);
        if ($result !== null && $result->name === null) {
            $result->name = Text::toSnakeCase($property->getName());
        }

        return $result;
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return Embedded|stdClass|null
     */
    private function getEmbeddedAnnotationData(ReflectionProperty $property)
    {
        $result = $this->reader->getPropertyAnnotation($property, self::EMBEDDED_ANNOTATION);
        if ($result !== null && $result->name === null) {
            $result->name = Text::toSnakeCase($property->getName());
        }

        return $result;
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return HashMap|stdClass|null
     */
    private function getHashMapAnnotationData(ReflectionProperty $property)
    {
        $result = $this->reader->getPropertyAnnotation($property, self::HASH_MAP_ANNOTATION);
        if ($result !== null && $result->name === null) {
            $result->name = Text::toSnakeCase($property->getName());
        }

        return $result;
    }

    /**
     * @param ReflectionProperty $property
     * @param string $directory
     *
     * @return array
     * @throws ReflectionException
     */
    private function getMetaFieldAnnotationData($property, $directory): array
    {
        /** @var MetaField $annotation */
        $annotation = $this->reader->getPropertyAnnotation($property, self::ID_ANNOTATION);
        $annotation = $annotation ?: $this->reader->getPropertyAnnotation($property, self::PARENT_ANNOTATION);
        $annotation = $annotation ?: $this->reader->getPropertyAnnotation($property, self::ROUTING_ANNOTATION);
        $annotation = $annotation ?: $this->reader->getPropertyAnnotation($property, self::VERSION_ANNOTATION);
        if ($annotation === null) {
            return null;
        }
        $data = [
            'name' => $annotation->getName(),
            'settings' => $annotation->getSettings(),
        ];
        if ($annotation instanceof ParentDocument) {
            $data['settings']['type'] = $this->getDocumentType($annotation->class, $directory);
        }

        return $data;
    }

    /**
     * @param $document
     * @param $directory
     *
     * @return string
     * @throws ReflectionException
     */
    private function getDocumentType($document, $directory): string
    {
        $namespace = $this->finder->getNamespace($document, $directory);
        $reflectionClass = new ReflectionClass($namespace);
        $document = $this->getDocumentAnnotationData($reflectionClass);

        /** @var Document $document */

        return empty($document->type) ? Text::toSnakeCase($reflectionClass->getShortName()) : $document->type;
    }

    /**
     * @param ReflectionClass $document
     *
     * @return Document|stdClass|null
     */
    private function getDocumentAnnotationData($document)
    {
        return $this->reader->getClassAnnotation($document, self::DOCUMENT_ANNOTATION);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string $property
     *
     * @param $propertyType
     *
     * @return array
     */
    private function getMutatorMethods(ReflectionClass $reflectionClass, $property, $propertyType): array
    {
        $camelCaseName = \ucfirst(Text::toCamelCase($property));
        $setterName = 'set'.$camelCaseName;
        if (!$reflectionClass->hasMethod($setterName)) {
            $message = \sprintf('Missing %s() method in %s class. Add it, or change property to public.', $setterName, $reflectionClass->getName());
            throw new LogicException($message);
        }
        if ($reflectionClass->hasMethod('get'.$camelCaseName)) {
            return [
                'getter' => 'get'.$camelCaseName,
                'setter' => $setterName,
            ];
        }
        if ($propertyType === 'boolean') {
            if ($reflectionClass->hasMethod('is'.$camelCaseName)) {
                return [
                    'getter' => 'is'.$camelCaseName,
                    'setter' => $setterName,
                ];
            }
            $message = \sprintf('Missing %s() or %s() method in %s class. Add it, or change property to public.', 'get'.$camelCaseName, 'is'.$camelCaseName, $reflectionClass->getName());
            throw new LogicException($message);
        }
        $message = \sprintf('Missing %s() method in %s class. Add it, or change property to public.', 'get'.$camelCaseName, $reflectionClass->getName());
        throw new LogicException($message);
    }

    /**
     * @param string $className
     * @param string $directory
     *
     * @return array
     * @throws ReflectionException
     */
    private function getObjectMapping($className, $directory): array
    {
        $namespace = $this->finder->getNamespace($className, $directory);
        if (\array_key_exists($namespace, $this->objects)) {
            return $this->objects[$namespace];
        }
        $reflectionClass = new ReflectionClass($namespace);
        switch (true) {
            case $this->reader->getClassAnnotation($reflectionClass, self::OBJECT_ANNOTATION):
                $type = 'object';
                break;
            case $this->reader->getClassAnnotation($reflectionClass, self::NESTED_ANNOTATION):
                $type = 'nested';
                break;
            default:
                throw new LogicException(\sprintf('%s should have @Object or @Nested annotation to be used as embeddable object.', $className));
        }
        $this->objects[$namespace] = [
            'type' => $type,
            'properties' => $this->getProperties($reflectionClass),
        ];

        return $this->objects[$namespace];
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param array $properties
     * @param bool $flag
     *
     * @return array
     * @throws ReflectionException
     */
    private function getProperties(ReflectionClass $reflectionClass, array $properties = [], $flag = false): array
    {
        $mapping = [];
        foreach ($this->getDocumentPropertiesReflection($reflectionClass) as $name => $property) {
            /** @var ReflectionProperty $property */
            $directory = $this->guessDirName($property->getDeclaringClass());
            $type = $this->getPropertyAnnotationData($property);
            $type = $type ?? $this->getEmbeddedAnnotationData($property);
            $type = $type ?? $this->getHashMapAnnotationData($property);
            if ($type === null || (\in_array($name, $properties, true) && !$flag) || (!\in_array($name, $properties, true) && $flag)) {
                continue;
            }
            $map = $type->dump();
            if ($type instanceof Embedded) {
                $map = \array_replace_recursive($map, $this->getObjectMapping($type->class, $directory));
            }
            if ($type instanceof HashMap) {
                $map = \array_replace_recursive($map, [
                    'type' => NestedType::NAME,
                    'dynamic' => true,
                ]);
            }
            if (isset($map['options'])) {
                $options = $map['options'];
                unset($map['options']);
                $map = \array_merge($map, $options);
            }
            $mapping[$type->name] = $map;
        }

        return $mapping;
    }

    /**
     * @param ReflectionClass $reflectionClass
     *
     * @return array
     * @throws ReflectionException
     */
    private function getAnalyzers(ReflectionClass $reflectionClass): array
    {
        $analyzers = [];
        foreach ($this->getDocumentPropertiesReflection($reflectionClass) as $name => $property) {
            $directory = $this->guessDirName($property->getDeclaringClass());
            $type = $this->getPropertyAnnotationData($property);
            $type = $type ?? $this->getEmbeddedAnnotationData($property);
            if ($type instanceof Embedded) {
                $analyzers = \array_merge($analyzers, $this->getAnalyzers(new ReflectionClass($this->finder->getNamespace($type->class, $directory))));
            }
            if ($type instanceof Property) {
                if (isset($type->options['analyzer'])) {
                    $analyzers[] = $type->options['analyzer'];
                }
                if (isset($type->options['search_analyzer'])) {
                    $analyzers[] = $type->options['search_analyzer'];
                }
                if (isset($type->options['fields'])) {
                    foreach ($type->options['fields'] as $field) {
                        if (isset($field['analyzer'])) {
                            $analyzers[] = $field['analyzer'];
                        }
                        if (isset($field['search_analyzer'])) {
                            $analyzers[] = $field['search_analyzer'];
                        }
                    }
                }
            }
        }

        return \array_unique($analyzers);
    }

    /**
     * @return array
     */
    private function getObjects(): array
    {
        return \array_keys($this->objects);
    }
}
