<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Request\Context;

use Boxalino\IntelligenceFramework\Framework\Request\SearchContextAbstract;
use Boxalino\IntelligenceFramework\Service\Api\Request\ContextInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface SearchContextInterface
 * @package Boxalino\IntelligenceFramework\Service\Api\Request
 */
interface ListingContextInterface extends ContextInterface
{

    /**
     * Adds facets to the request
     *
     * @param Request $request
     * @return SearchContextAbstract
     */
    public function addFacets(Request $request) : SearchContextAbstract;

}
