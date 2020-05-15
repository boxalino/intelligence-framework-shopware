<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Framework\Content\Listing;

use Shopware\Core\Framework\Struct\Struct;

class ApiCmsModel extends Struct
{
    /**
     * @var \ArrayIterator
     */
    protected $blocks;

    /**
     * @var string
     */
    protected $requestId;

    /**
     * @var string
     */
    protected $groupBy;

    /**
     * @var string
     */
    protected $variantUuid;

    /**
     * @var int
     */
    protected $totalHitCount = 0;

    /**
     * @var null | string
     */
    protected $navigationId = null;

    /**
     * @var \ArrayIterator
     */
    protected $sidebar;

    /**
     * @return \ArrayIterator
     */
    public function getBlocks(): \ArrayIterator
    {
        return $this->blocks;
    }

    /**
     * @param \ArrayIterator $blocks
     * @return ApiCmsModel
     */
    public function setBlocks(\ArrayIterator $blocks): ApiCmsModel
    {
        $this->blocks = $blocks;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     * @return ApiCmsModel
     */
    public function setRequestId(string $requestId): ApiCmsModel
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupBy(): string
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     * @return ApiCmsModel
     */
    public function setGroupBy(string $groupBy): ApiCmsModel
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariantUuid(): string
    {
        return $this->variantUuid;
    }

    /**
     * @param string $variantUuid
     * @return ApiCmsModel
     */
    public function setVariantUuid(string $variantUuid): ApiCmsModel
    {
        $this->variantUuid = $variantUuid;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalHitCount(): int
    {
        return $this->totalHitCount;
    }

    /**
     * @param int $totalHitCount
     * @return ApiCmsModel
     */
    public function setTotalHitCount(int $totalHitCount): ApiCmsModel
    {
        $this->totalHitCount = $totalHitCount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNavigationId(): ?string
    {
        return $this->navigationId;
    }

    /**
     * @param string|null $navigationId
     * @return ApiCmsModel
     */
    public function setNavigationId(?string $navigationId): ApiCmsModel
    {
        $this->navigationId = $navigationId;
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getSidebar() : \ArrayIterator
    {
        return $this->sidebar;
    }

    /**
     * @param null $sidebar
     * @return ApiCmsModel
     */
    public function setSidebar(\ArrayIterator $sidebar)
    {
        $this->sidebar = $sidebar;
        return $this;
    }



}
