<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Framework\Content\Listing;

use Boxalino\IntelligenceFramework\Framework\SalesChannelContextTrait;
use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\Accessor;
use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\AccessorFacetModelInterface;
use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\AccessorModelInterface;
use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\FacetList;
use Boxalino\IntelligenceFramework\Service\Api\Response\ResponseHydratorTrait;
use Boxalino\IntelligenceFramework\Service\Api\Util\AccessorHandlerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ApiFacetModel
 *
 * Item refers to any data model/logic that is desired to be rendered/displayed
 * The integrator can decide to either use all data as provided by the Narrative API,
 * or to design custom data layers to represent the fetched content
 *
 * @package Boxalino\IntelligenceFramework\Service\Api\Content
 */
class ApiFacetModel implements AccessorFacetModelInterface
{
    use ResponseHydratorTrait;
    use SalesChannelContextTrait;

    public const BOXALINO_STORE_FACET_PREFIX = "products_";
    public const BOXALINO_SYSTEM_FACET_PREFIX = "bx_";

    /**
     * @var \ArrayIterator
     */
    protected $facets;

    /**
     * @var \ArrayIterator
     */
    protected $selectedFacets;

    /**
     * @var AccessorHandlerInterface
     */
    protected $accessorHandler;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var null | string
     */
    protected $defaultLanguageId = null;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->selectedFacets = new \ArrayIterator();
    }

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
            if($facet->getLabel() === "")
            {
                $facet->setLabel($this->getLabel($facet->getField()));
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
     * Accessing property name from DB
     *
     * @param string $propertyName
     * @return string
     */
    protected function getLabel(string $propertyName) : string
    {
        $this->getDefaultLanguageId();
        $channelSelectedLanguage = $this->getSalesChannelContext()->getContext()->getLanguageId();
        $propertyId = $this->getPropertyIdByFieldName($propertyName);
        if(!$propertyId)
        {
            return ucwords(str_replace("_", " ", $propertyName));
        }

        $query = $this->connection->createQueryBuilder()
            ->select(["IF(property_group_translation.name IS NULL, pgt.name, property_group_translation.name) AS name"])
            ->from("property_group_translation")
            ->leftJoin("property_group_translation", "property_group_translation", "pgt",
                "property_group_translation.property_group_id = pgt.property_group_id AND pgt.language_id=:defaultLanguageId")
            ->where("property_group_translation.langauge_id = :languageId")
            ->groupBy("property_group_translation.property_group_id")
            ->setParameter("languageId", Uuid::fromHexToBytes($channelSelectedLanguage), ParameterType::STRING)
            ->setParameter("defaultLanguageId", Uuid::fromHexToBytes($this->getDefaultLanguageId()), ParameterType::STRING)
            ->setMaxResults(1);

        return $query->execute()->fetchColumn();
    }

    /**
     * @param string $propertyName
     * @return false|string
     * @throws \Shopware\Core\Framework\Uuid\Exception\InvalidUuidException
     */
    protected function getPropertyIdByFieldName(string $propertyName)
    {
        $prefix = self::BOXALINO_STORE_FACET_PREFIX;
        $propertyIdQuery = $this->connection->createQueryBuilder()
            ->select(["property_group_id"])
            ->from("property_group_translation")
            ->where("language_id = :defaultLanguageId")
            ->where("CONCAT('$prefix', name) = :propertyName")
            ->setParameter("defaultLanguageId", Uuid::fromHexToBytes($this->getDefaultLanguageId()), ParameterType::STRING)
            ->setParameter("propertyName", $propertyName)
            ->setMaxResults(1);

        return $propertyIdQuery->execute()->fetchColumn();
    }

    /**
     * @return string
     * @throws \Shopware\Core\Framework\Uuid\Exception\InvalidUuidException
     */
    protected function getDefaultLanguageId() : string
    {
        if(is_null($this->defaultLanguageId))
        {
            $query = $this->connection->createQueryBuilder()
                ->select(["LOWER(HEX(language_id)) as language_id"])
                ->from('sales_channel')
                ->where('id = :channelId')
                ->setParameter("channelId", Uuid::fromHexToBytes($this->getSalesChannelContext()->getSalesChannel()->getId()), ParameterType::STRING)
                ->setMaxResults(1);
            $this->defaultLanguageId = $query->execute()->fetchColumn();
        }

        return $this->defaultLanguageId;
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

    /**
     * Sets the facets
     * Sets the accesor handler to be able to run toObject construct
     *
     * @param null | AccessorInterface $context
     * @return AccessorModelInterface
     */
    public function addAccessorContext(?AccessorInterface $context = null): AccessorModelInterface
    {
        $this->setSalesChannelContext($context->getAccessorHandler()->getSalesChannelContext());
        $this->setAccessorHandler($context->getAccessorHandler());
        $this->setFacets($context->getFacetsList());
        return $this;
    }

    /**
     * @return AccessorHandlerInterface
     */
    public function getAccessorHandler(): AccessorHandlerInterface
    {
        return $this->accessorHandler;
    }

    /**
     * @param AccessorHandlerInterface $accessorHandler
     * @return $this
     */
    public function setAccessorHandler(AccessorHandlerInterface $accessorHandler)
    {
        $this->accessorHandler = $accessorHandler;
        return $this;
    }

}
