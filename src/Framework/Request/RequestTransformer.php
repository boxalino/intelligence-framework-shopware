<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Framework\Request;

use Boxalino\IntelligenceFramework\Framework\SalesChannelContextTrait;
use Boxalino\IntelligenceFramework\Service\Api\Request\ParameterFactory;
use Boxalino\IntelligenceFramework\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\IntelligenceFramework\Service\Api\Request\RequestTransformerInterface;
use Boxalino\IntelligenceFramework\Service\Api\Util\Configuration;
use Boxalino\IntelligenceFramework\Service\ErrorHandler\MissingDependencyException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class RequestTransformer
 *
 * Adds system-specific (Shopware6) request parameters toa boxalino request
 * Sets request variables dependent on the channel
 * (account, credentials, environment details -- language, dev, test, session, header parameters, etc)
 *
 * @package Boxalino\IntelligenceFramework\Service\Api
 */
class RequestTransformer implements RequestTransformerInterface
{
    use SalesChannelContextTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var RequestDefinitionInterface
     */
    protected $requestDefinition;

    /**
     * @var ParameterFactory
     */
    protected $parameterFactory;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * RequestTransformer constructor.
     * @param Connection $connection
     * @param ParameterFactory $parameterFactory
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     */
    public function __construct(
        Connection $connection,
        ParameterFactory $parameterFactory,
        Configuration $configuration,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->configuration = $configuration;
        $this->parameterFactory = $parameterFactory;
        $this->logger = $logger;
    }

    /**
     * Sets context parameters (credentials, server, etc)
     * Adds parameters per request query elements
     *
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function transform(Request $request): RequestDefinitionInterface
    {
        if(!$this->salesChannelContext)
        {
            throw new MissingDependencyException(
                "BoxalinoAPI: the SalesChannelContext has not been set on the RequestTransformer"
            );
        }

        if(!$this->requestDefinition)
        {
            throw new MissingDependencyException(
                "BoxalinoAPI: the RequestDefinitionInterface has not been set on the RequestTransformer"
            );
        }

        $salesChannelId = $this->getSalesChannelId();
        $this->configuration->setChannelId($salesChannelId);
        $this->requestDefinition
            ->setUsername($this->configuration->getUsername($salesChannelId))
            ->setApiKey($this->configuration->getApiKey($salesChannelId))
            ->setApiSecret($this->configuration->getApiSecret($salesChannelId))
            ->setDev($this->configuration->getIsDev($salesChannelId))
            ->setTest($this->configuration->getIsTest($salesChannelId))
            ->setSessionId($this->getSessionId($request))
            ->setProfileId($this->getProfileId($request))
            ->setCustomerId($this->getCustomerId($request))
            ->setLanguage(substr($request->getLocale(), 0, 2));

        $this->addHitCount();
        $this->addOffset(1);
        $this->addParameters($request);

        return $this->requestDefinition;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getCustomerId(Request $request) : string
    {
        if(is_null($this->getSalesChannelContext()->getCustomer()))
        {
            return $this->getProfileId($request);
        }

        return $this->getSalesChannelContext()->getCustomer()->getId();
    }

    /**
     * @return string
     */
    public function getSalesChannelId() : string
    {
        return $this->getSalesChannelContext()->getSalesChannel()->getId();
    }

    /**
     * The value stored in the CEMS cookie
     */
    public function getSessionId(Request $request)
    {
        if($request->cookies->has("cems"))
        {
            return $request->cookies->get("cems");
        }

        $cookieValue = Uuid::uuid4()->toString();
        $request->cookies->set("cems", $cookieValue);

        return $cookieValue;
    }

    /**
     * The value stored in the CEMV cookie
     */
    public function getProfileId(Request $request)
    {
        if($request->cookies->has("cemv"))
        {
            return $request->cookies->get("cemv");
        }

        $cookieValue = Uuid::uuid4()->toString();
        $request->cookies->set("cemv", $cookieValue);

        return $cookieValue;
    }

    /**
     * @param Request $request
     */
    public function addParameters(Request $request) : void
    {
        $this->requestDefinition->addHeaderParameters(
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)->add("User-Host", $request->getClientIp()),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)->add("User-Agent", $request->headers->get('user-agent')),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)->add("User-Referer", $request->headers->get('referer')),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)->add("User-Url", $request->getUri())
        )
            ->addParameters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_USER)->add(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID,
                    [$request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID)]),
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_USER)->add(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID,
                    [$request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID)])
            );

        $queryString = $request->getQueryString();
        parse_str($queryString, $params);
        foreach($params as $param => $value)
        {
            if($param == "sort")
            {
                $this->addSort($value);
                continue;
            }

            if($param == 'p')
            {
                $this->addOffset((int)$value);
                continue;
            }

            if($param == 'limit')
            {
                $this->addHitCount($value);
                continue;
            }

            if($param == 'search')
            {
                $this->requestDefinition->setQuery($value);
                continue;
            }

            $value = is_array($value) ? $value : [$value];
            $this->requestDefinition->addParameters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_USER)->add($param, $value)
            );
        }
    }

    /**
     * @param string $value
     */
    public function addSort(string $value)
    {
        $hasDirection = strstr($value, "-");
        $direction = '';
        if($hasDirection)
        {
            $direction =  mb_substr(strstr($value, "-"), 1, 4);
        }
        $field = strstr($value, "-", true);
        if($field === 'score')
        {
            return ;
        }
        $reverse = $direction === FieldSorting::DESCENDING ?? false;

        $this->requestDefinition->addSort(
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_SORT)
            ->add($this->getFieldName($field), $reverse)
        );
    }

    /**
     * @param $field
     * @return $this|string
     */
    public function getFieldName($field)
    {
        if(in_array($field, array_keys($this->getSortFields())))
        {
            return $this->getSortFields()[$field];
        }

        return "products_" . $field;
    }

    /**
     * @param int $page
     * @return int
     */
    public function addOffset(int $page = 1) : RequestTransformer
    {
        $this->requestDefinition->setOffset(($page-1) * $this->getLimit());
        return $this;
    }

    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php
     *
     * @return int
     */
    public function getLimit() : int
    {
        return 24;
    }

    /**
     * @param int $hits
     * @return $this
     */
    public function addHitCount(int $hits = 0) : RequestTransformer
    {
        if(!$hits)
        {
            $hits = $this->getLimit();
        }

        $this->requestDefinition->setHitCount($hits);
        return $this;
    }

    /**
     * @param RequestDefinitionInterface $requestDefinition
     * @return $this
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition)
    {
        $this->requestDefinition = $requestDefinition;
        return $this;
    }

    /**
     * @return RequestDefinitionInterface
     */
    public function getRequestDefinition() : RequestDefinitionInterface
    {
        return $this->requestDefinition;
    }

    /**
     * @return array
     */
    public function getSortFields() : array
    {
        return [
            'name'      => 'products_bx_parent_title',
            'price'     => 'products_bx_grouped_price',
            'id       ' => 'products_group_id'
        ];
    }

}
