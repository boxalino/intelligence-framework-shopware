<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Request\Context;

use Boxalino\IntelligenceFramework\Framework\Request\AutocompleteContextAbstract;
use Boxalino\IntelligenceFramework\Framework\Request\ContextAbstract;
use Boxalino\IntelligenceFramework\Service\Api\Request\ContextInterface;

/**
 * Interface SearchContextInterface
 * @package Boxalino\IntelligenceFramework\Service\Api\Request
 */
interface AutocompleteContextInterface extends ContextInterface
{
    /**
     * @param int $count
     * @return $this|ContextAbstract
     */
    public function setSuggestionCount(int $count) : ContextAbstract;

    /**
     * @return int
     */
    public function getSuggestionsCount() : int;

    /**
     * @return bool
     */
    public function isAcHighlight(): bool;

    /**
     * @param bool $acHighlight
     * @return AutocompleteContextAbstract
     */
    public function setHighlight(bool $acHighlight): AutocompleteContextAbstract;

    /**
     * @return string
     */
    public function getHighlightPrefix(): string;

    /**
     * @param string $acHighlightPre
     * @return AutocompleteContextAbstract
     */
    public function setHighlightPrefix(string $acHighlightPre): AutocompleteContextAbstract;

    /**
     * @return string
     */
    public function getHighlightPostfix(): string;

    /**
     * @param string $acHighlightPost
     * @return AutocompleteContextAbstract
     */
    public function setHighlightPostfix(string $acHighlightPost): AutocompleteContextAbstract;


}
