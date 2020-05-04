<?php
namespace Boxalino\IntelligenceFramework\Service\Api\Response\Accessor;

use ArrayIterator;

/**
 * @package Boxalino\IntelligenceFramework\Service\Api\Response\Accessor
 */
interface AccessorFacetModelInterface extends AccessorModelInterface
{

    /**
     * @return ArrayIterator
     */
    public function getFacets() : \ArrayIterator;

}
