<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Request\Context;

use Boxalino\IntelligenceFramework\Framework\Request\SearchContextAbstract;
use Boxalino\IntelligenceFramework\Service\Api\Request\ContextInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface SearchContextInterface
 * @package Boxalino\IntelligenceFramework\Service\Api\Request
 */
interface SearchContextInterface extends ContextInterface
{

    /**
     * @return int|null
     */
    public function getSubPhrasesCount(): ?int;

    /**
     * @param int $subPhrasesCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesCount(int $subPhrasesCount): SearchContextAbstract;

    /**
     * @return int|null
     */
    public function getSubPhrasesProductsCount(): ?int;

    /**
     * @param int $subPhrasesProductsCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesProductsCount(int $subPhrasesProductsCount): SearchContextAbstract;
}
