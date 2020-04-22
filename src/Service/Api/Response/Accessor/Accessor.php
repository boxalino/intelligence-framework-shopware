<?php
namespace Boxalino\IntelligenceFramework\Service\Api\Response\Accessor;

use Boxalino\IntelligenceFramework\Service\Api\Response\ResponseHydratorTrait;
use Boxalino\IntelligenceFramework\Service\Api\Util\AccessorHandlerInterface;

/**
 * @package Boxalino\IntelligenceFramework\Service\Api\Response\Accessor
 */
class Accessor implements AccessorInterface
{
    use ResponseHydratorTrait;

    /**
     * @var AccessorHandlerInterface
     */
    protected $accessorHandler;

    public function __construct(AccessorHandlerInterface $accessorHandler)
    {
        $this->accessorHandler = $accessorHandler;
    }

    /**
     * Dynamically add properties to the object
     *
     * @param string $methodName
     * @param null $params
     * @return $this
     */
    public function __call(string $methodName, $params = null)
    {
        $methodPrefix = substr($methodName, 0, 3);
        $key = strtolower(substr($methodName, 3));
        if($methodPrefix == 'get')
        {
            return $this->$key;
        }

        throw new \BadMethodCallException(
            "BoxalinoApiAccessor: the accessor does not have a property defined for $key . Please contact Boxalino."
        );
    }

    /**
     * Sets either accessor objects or accessor fields to the response object
     *
     * @param string $propertyName
     * @param $content
     * @return $this
     */
    public function set(string $propertyName, $content)
    {
        $this->$propertyName = $content;
        return $this;
    }

    /**
     * Sets either accessor objects or accessor fields to the response object
     *
     * @param string $propertyName
     * @return $this
     */
    public function get(string $propertyName)
    {
        return $this->$propertyName;
    }

    /**
     * @return AccessorHandlerInterface
     */
    public function getAccessorHandler(): AccessorHandlerInterface
    {
        return $this->accessorHandler;
    }

}
