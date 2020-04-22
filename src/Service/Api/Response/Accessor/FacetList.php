<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Response\Accessor;

use Boxalino\IntelligenceFramework\Service\Api\Util\AccessorHandlerInterface;

/**
 * @package Boxalino\IntelligenceFramework\Service\Api\Response\Accessor
 */
class FacetList extends Accessor
    implements AccessorInterface
{
    /**
     * @var \ArrayIterator
     */
    protected $facets;

    /**
     * @var \ArrayIterator
     */
    protected $selectedFacets;

    /**
     * @return \ArrayIterator
     */
    public function getFacets() :  \ArrayIterator
    {
        return $this->facets;
    }

    /**
     * @return \ArrayIterator
     */
    public function getSelectedFacets() : \ArrayIterator
    {
        return $this->selectedFacets;
    }

    /**
     * @param array $facets
     * @return $this
     */
    public function setFacets(array $facets) : self
    {
        $this->facets = new \ArrayIterator();
        foreach($facets as $facet)
        {
            $facet = $this->toObject($facet, $this->getAccessorHandler()->getAccessor("facet"));
            $this->facets->append($facet);
            if($facet->isSelected())
            {
                $this->addSelectedFacet($facet);
            }
        }

        return $this;
    }

    /**
     * @param AccessorInterface $facet
     * @return $this
     */
    public function addSelectedFacet(AccessorInterface $facet) : self
    {
        $this->selectedFacets->append($facet);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSelectedFacets() : bool
    {
        return (bool) $this->selectedFacets->count();
    }

    /**
     * @return bool
     */
    public function hasFacets() : bool
    {
        return (bool) $this->facets->count();
    }

}
