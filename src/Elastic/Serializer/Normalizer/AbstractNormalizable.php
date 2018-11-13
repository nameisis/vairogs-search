<?php

namespace Vairogs\Utils\Search\Elastic\Serializer\Normalizer;

use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

abstract class AbstractNormalizable implements NormalizableInterface
{
    use ParametersTrait {
        ParametersTrait::hasParameter as hasReference;
        ParametersTrait::getParameter as getReference;
        ParametersTrait::getParameters as getReferences;
        ParametersTrait::addParameter as addReference;
        ParametersTrait::removeParameter as removeReference;
        ParametersTrait::setParameters as setReferences;
    }
}
