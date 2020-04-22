<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Request;

use Symfony\Component\HttpFoundation\Request;

interface ContextInterface
{
    /**
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function get(Request $request) : RequestDefinitionInterface;

    /**
     * @param string $widget
     * @return mixed
     */
    public function setWidget(string $widget);

    /**
     * @param RequestDefinitionInterface $requestDefinition
     * @return mixed
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition);

    /**
     * @return RequestDefinitionInterface
     */
    public function getApiRequest() : RequestDefinitionInterface;
}
