<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Response;

use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\Block;
use Boxalino\IntelligenceFramework\Service\Api\Util\AccessorHandler;
use Boxalino\IntelligenceFramework\Service\Api\Util\AccessorHandlerInterface;
use Boxalino\IntelligenceFramework\Service\ErrorHandler\UndefinedPropertyError;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Boxalino\IntelligenceFramework\Service\ErrorHandler\UndefinedMethodError;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class ResponseDefinition
 *
 * @package Boxalino\IntelligenceFramework\Service\Api\Response
 */
class ResponseDefinition implements ResponseDefinitionInterface
{

    use ResponseHydratorTrait;

    /**
     * If the facets are declared on a certain position, they are isolated in a specific block
     * All the other content is located under "blocks"
     */
    const BOXALINO_RESPONSE_POSITION = ["left", "right", "main", "top", "bottom"];

    /**
     * @var string
     */
    protected $json;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var null | \StdClass
     */
    protected $data = null;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AccessorHandlerInterface
     */
    protected $accessorHandler = null;

    /**
     * @var null | \ArrayIterator
     */
    protected $blocks = null;

    public function __construct(LoggerInterface $logger, AccessorHandlerInterface $accessorHandler)
    {
        $this->logger = $logger;
        $this->accessorHandler = $accessorHandler;
    }

    /**
     * Allows accessing other parameters
     *
     * @param string $method
     * @param array $params
     * @return array
     */
    public function __call(string $method, array $params = [])
    {
        preg_match('/^(get)(.*?)$/i', $method, $matches);
        $prefix = $matches[1] ?? '';
        $element = $matches[2] ?? '';
        $element = strtolower($element);
        if ($prefix == 'get') {

            if (in_array($element, self::BOXALINO_RESPONSE_POSITION))
            {
                try {
                    $content = [];
                    foreach($this->get()->$element as $block)
                    {
                        $content[] = $this->getBlockObject($block);
                    }
                    return $content;
                } catch (\Exception $error)
                {
                    return [];
                }
            }

            throw new UndefinedMethodError("BoxalinoResponseAPI: the requested method $method is not supported by the Boxalino API ResponseServer");
        }
    }

    /**
     * @return int
     */
    public function getHitCount() : int
    {
        try{
            try {
                $hitCount = $this->get()->system->mainHitCount;
                if($hitCount == -1)
                {
                    //find the bx-hits block for item-context requests
                }

                return $hitCount;
            } catch(\Exception $exception)
            {
                foreach($this->getBlocks() as $block)
                {
                    try{
                        $object = $this->findObjectWithProperty($block, "productsCollection");
                        if(is_null($object))
                        {
                            return 0;
                        }

                        return $object->getProductsCollection()->getTotalHitCount();
                    } catch (\Exception $exception)
                    {
                        $this->logger->info($exception->getMessage());
                        continue;
                    }
                }

                return 0;
            }
        } catch(\Exception $exception)
        {
            return 0;
        }
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl() : ?string
    {
        try{
            $index = 0;
            return $this->get()->advanced->$index->redirect_url;
        } catch(\Exception $exception)
        {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function isCorrectedSearchQuery() : bool
    {
        try{
           return (bool) $this->get()->advanced->system->correctedSearchQuery;
        } catch(\Exception $exception)
        {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function hasSearchSubPhrases() : bool
    {
        try{
            return (bool) $this->get()->advanced->system->hasSearchSubPhrases;
        } catch(\Exception $exception)
        {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getRequestId() : string
    {
        try{
            $index = 0;
            return $this->get()->advanced->$index->_bx_request_id;
        } catch(\Exception $exception)
        {
            return "N/A";
        }
    }

    /**
     * @return string
     */
    public function getGroupBy() : string
    {
        try{
            $index = 0;
            return $this->get()->advanced->$index->_bx_group_by;
        } catch(\Exception $exception)
        {
            return "N/A";
        }
    }

    /**
     * @return string
     */
    public function getVariantId() : string
    {
        try{
            $index = 0;
            return $this->get()->advanced->$index->_bx_variant_uuid;
        } catch(\Exception $exception)
        {
            return "N/A";
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getBlocks() : \ArrayIterator
    {
        if(is_null($this->blocks))
        {
            $this->blocks = new \ArrayIterator();
            $blocks = $this->get()->blocks;
            try{
                foreach($blocks as $block)
                {
                    $this->blocks->append($this->getBlockObject($block));
                }
            } catch(\Exception $exception)
            {
                $this->logger->warning("BoxalinoResponseAPI: Something went wrong during BLOCKS generation: " . $exception->getMessage());
            }
        }

        return $this->blocks;
    }

    /**
     * @param \StdClass $block
     * @return AccessorInterface
     */
    public function getBlockObject(\StdClass $block) : AccessorInterface
    {
        return $this->toObject($block, $this->getAccessorHandler()->getAccessor("blocks"));
    }

    /**
     * Debug and performance information
     *
     * @return array
     */
    public function getAdvanced() : array
    {
        try{
            $index = 0;
            return array_merge($this->get()->performance, $this->get()->advanced->$index);
        } catch(\Exception $exception)
        {
            return $this->get()->performance;
        }
    }

    /**
     * @return \StdClass|null
     */
    public function get() : ?\StdClass
    {
        if(is_null($this->data))
        {
            $this->data = json_decode($this->json);
        }

        return $this->data;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->setJson($response->getBody()->getContents());

        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse() : Response
    {
        return $this->response;
    }

    /**
     * @param string $json
     * @return $this
     */
    public function setJson(string $json)
    {
        $this->json = $json;
        return $this;
    }

    /**
     * @return string
     */
    public function getJson() : string
    {
        return $this->json;
    }

    /**
     * @return AccessorHandlerInterface
     */
    public function getAccessorHandler(): AccessorHandlerInterface
    {
        return $this->accessorHandler;
    }

}
