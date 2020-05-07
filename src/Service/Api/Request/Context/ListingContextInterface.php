<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Request\Context;

use Boxalino\IntelligenceFramework\Framework\Request\ListingContextAbstract;
use Boxalino\IntelligenceFramework\Service\Api\Request\ContextInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface SearchContextInterface
 * @package Boxalino\IntelligenceFramework\Service\Api\Request
 */
interface ListingContextInterface extends ContextInterface
{

    /**
     * Adds facets to the request (field-values)
     *
     * @param Request $request
     * @return ListingContextAbstract
     */
    public function addFacets(Request $request) : ListingContextAbstract;

    /**
     * Adds range facets to the request
     *
     * @param Request $request
     * @return ListingContextAbstract
     */
    public function addRangeFacets(Request $request) : ListingContextAbstract;

    /**
     * Returns an array with the following structure:
     * [propertyName => ["to"=>requestParameterForToValue, "from"=>requestParameterForFromValue]]
     *
     * This list is being used in the addRangeFacets() function
     *
     * @return mixed
     */
    public function getRangeProperties() : array;

}
