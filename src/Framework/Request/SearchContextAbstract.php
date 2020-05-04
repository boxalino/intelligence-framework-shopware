<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Framework\Request;

use Boxalino\IntelligenceFramework\Framework\Content\Listing\ApiFacetModel;
use Boxalino\IntelligenceFramework\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\IntelligenceFramework\Service\Api\Request\Context\SearchContextInterface;
use Boxalino\IntelligenceFramework\Service\Api\Request\Definition\SearchRequestDefinitionInterface;
use Boxalino\IntelligenceFramework\Service\Api\Request\ParameterFactory;
use Boxalino\IntelligenceFramework\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\IntelligenceFramework\Service\ErrorHandler\WrongDependencyTypeException;
use PhpParser\Error;
use Symfony\Component\HttpFoundation\Request;

/**
 * Boxalino Search Request handler
 * Allows to set the nr of subphrases and products returned on each subphrase hit
 *
 * @package Boxalino\IntelligenceFramework\Framework\Request
 */
abstract class SearchContextAbstract
    extends ContextAbstract
    implements SearchContextInterface, ShopwareApiContextInterface, ListingContextInterface
{

    /**
     * @var null | int
     */
    protected $subPhrasesCount;

    /**
     * @var null | int
     */
    protected $subPhrasesProductsCount;

    /**
     * Adding additional subphrases details for the search request
     *
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function get(Request $request) : RequestDefinitionInterface
    {
        parent::get($request);

        if(!is_null($this->subPhrasesCount))
        {
            $this->getApiRequest()->setMaxSubPhrases($this->getSubPhrasesCount());
        }

        if(!is_null($this->subPhrasesProductsCount))
        {
            $this->getApiRequest()->setMaxSubPhrasesHits($this->getSubPhrasesProductsCount());
        }

        $this->addFacets($request);

        return $this->getApiRequest();
    }

    /**
     * Filter the list of query parameters by either being a product property or a defined property used in filter
     *
     * @param Request $request
     * @return SearchContextAbstract
     */
    public function addFacets(Request $request): SearchContextAbstract
    {
        foreach($request->query->all() as $param => $values)
        {
            if(strpos($param, ApiFacetModel::BOXALINO_STORE_FACET_PREFIX)===0)
            {
                //it`s a store property
                $values = is_array($values) ? $values : explode("|", $values);
                $values = array_map("html_entity_decode", $values);
                $this->getApiRequest()->addFacets(
                    $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FACET)
                    ->addWithValues($param, $values)
                );
            }
        }

        $minPrice = $request->query->getInt('min-price', 0);
        $maxPrice = $request->query->getInt('max-price', 0);
        if($minPrice > 0 || $maxPrice > 0)
        {
            if($maxPrice==0)
            {
                $maxPrice = null;
            }
            $this->getApiRequest()->addFacets(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FACET)
                    ->addRange("discountedPrice", $minPrice, $maxPrice)
            );
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSubPhrasesCount(): ?int
    {
        return $this->subPhrasesCount;
    }

    /**
     * @param int $subPhrasesCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesCount(int $subPhrasesCount): SearchContextAbstract
    {
        $this->subPhrasesCount = $subPhrasesCount;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSubPhrasesProductsCount(): ?int
    {
        return $this->subPhrasesProductsCount;
    }

    /**
     * @param int $subPhrasesProductsCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesProductsCount(int $subPhrasesProductsCount): SearchContextAbstract
    {
        $this->subPhrasesProductsCount = $subPhrasesProductsCount;
        return $this;
    }

    /**
     * Enforce a dependency type for the ItemContext requests
     *
     * @param RequestDefinitionInterface $requestDefinition
     * @return self | Error
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition)
    {
        if($requestDefinition instanceof SearchRequestDefinitionInterface)
        {
            return parent::setRequestDefinition($requestDefinition);
        }

        throw new WrongDependencyTypeException("BoxalinoAPIContext: " . get_called_class() .
            " request definition must be an instance of SearchRequestDefinitionInterface");
    }

}
