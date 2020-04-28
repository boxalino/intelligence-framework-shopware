<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Request\Definition;

use Boxalino\IntelligenceFramework\Service\Api\Request\RequestDefinitionInterface;

/**
 * Boxalino API Request definition interface for search
 *
 * @package Boxalino\IntelligenceFramework\Service\Api\Request
 */
interface SearchRequestDefinitionInterface extends RequestDefinitionInterface
{

    /**
     * @return int
     */
    public function getMaxSubPhrases(): int;

    /**
     * @param int $maxSubPhrases
     * @return SearchRequestDefinition
     */
    public function setMaxSubPhrases(int $maxSubPhrases): SearchRequestDefinition;

    /**
     * @return int
     */
    public function getMaxSubPhrasesHits(): int;

    /**
     * @param int $maxSubPhrasesHits
     * @return SearchRequestDefinition
     */
    public function setMaxSubPhrasesHits(int $maxSubPhrasesHits): SearchRequestDefinition;

}
