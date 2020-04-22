<?php  declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Service\Api\Response\Accessor;

/**
 * Class FacetValue
 * Model for a facet value response element
 * @package Boxalino\IntelligenceFramework\Service\Api\Response\Accessor
 */
class FacetValue extends Accessor
    implements AccessorInterface
{

    /**
     * @var string
     */
    protected $value;

    /**
     * @var null | string
     */
    protected $label = null;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $hitCount = 0;

    /**
     * @var bool
     */
    protected $show = true;

    /**
     * @var bool
     */
    protected $selected = false;

    /**
     * @var string
     */
    protected $minValue = 0;

    /**
     * @var string
     */
    protected $maxValue = 0;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return FacetValue
     */
    public function setValue(string $value): FacetValue
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param array $label
     * @return FacetValue
     */
    public function setLabel(array $label): FacetValue
    {
        $this->label = $label[0] ?? null;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return FacetValue
     */
    public function setId(string $id): FacetValue
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getHitCount(): int
    {
        return $this->hitCount;
    }

    /**
     * @param int $hitCount
     * @return FacetValue
     */
    public function setHitCount(int $hitCount): FacetValue
    {
        $this->hitCount = $hitCount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShow(): bool
    {
        return $this->show;
    }

    /**
     * @param bool $show
     * @return FacetValue
     */
    public function setShow(bool $show): FacetValue
    {
        $this->show = $show;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * @param bool $selected
     * @return FacetValue
     */
    public function setSelected(bool $selected): FacetValue
    {
        $this->selected = $selected;
        return $this;
    }

    /**
     * @return string
     */
    public function getMinValue(): string
    {
        return $this->minValue;
    }

    /**
     * @param string $minValue
     * @return FacetValue
     */
    public function setMinValue(string $minValue): FacetValue
    {
        $this->minValue = $minValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxValue(): string
    {
        return $this->maxValue;
    }

    /**
     * @param string $maxValue
     * @return FacetValue
     */
    public function setMaxValue(string $maxValue): FacetValue
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return FacetValue
     */
    public function setUrl(?string $url): FacetValue
    {
        $this->url = $url;
        return $this;
    }

}
