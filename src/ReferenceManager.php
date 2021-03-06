<?php

declare(strict_types=1);

namespace Shapin\Datagen;

use Shapin\Datagen\Exception\DuplicateReferenceException;
use Shapin\Datagen\Exception\UnknownReferenceException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ReferenceManager
{
    private $references;

    public function __construct()
    {
        $this->references = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
    }

    public function add(string $fixture, string $name, $data): void
    {
        if (!$this->references->offsetExists($fixture)) {
            $this->references[$fixture] = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        }
        if ($this->references[$fixture]->offsetExists($name)) {
            throw new DuplicateReferenceException($fixture, $name);
        }

        if (\is_array($data)) {
            $data = new \ArrayObject($data, \ArrayObject::ARRAY_AS_PROPS);
        }

        $this->references[$fixture][$name] = $data;
    }

    public function findAndReplace(array $data): array
    {
        $keys = array_keys($data);
        for ($i = 0; $i < \count($data); ++$i) {
            $value = $data[$keys[$i]];
            if (\is_string($value) && $this->isReference($value)) {
                $data[$keys[$i]] = $this->resolveReference($value);
            }
            if (\is_array($value)) {
                $data[$keys[$i]] = $this->findAndReplace($value);
            }
        }

        return $data;
    }

    private function isReference(string $value): bool
    {
        return 'REF:' === substr($value, 0, 4);
    }

    private function resolveReference(string $value)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $parts = explode(':', $value);

        $ref = $parts[1];

        try {
            return $propertyAccessor->getValue($this->references, $ref);
        } catch (NoSuchPropertyException $e) {
            throw new UnknownReferenceException("Unable to resolve Reference \"$value\".", 0, $e);
        }
    }
}
