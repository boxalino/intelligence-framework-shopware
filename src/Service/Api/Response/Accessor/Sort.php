<?php
namespace Boxalino\IntelligenceFramework\Service\Api\Response\Accessor;

/**
 * Class Sort
 * Model of the BX-SORT response accessor
 * "score" is the default field
 *
 * @package Boxalino\IntelligenceFramework\Service\Api\Response\Accessor
 */
class Sort extends Accessor
    implements AccessorInterface
{
    /**
     * @var array
     */
    protected $sortings = [];

    /**
     * @var string
     */
    protected $field = "score";

    /**
     * @var bool
     */
    protected $reverse;

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return Sort
     */
    public function setField(string $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReverse()
    {
        return $this->reverse;
    }

    /**
     * When true, sort direction is DESC
     * When false, sort direction is ASC
     *
     * @param bool $reverse
     * @return Sort
     */
    public function setReverse(bool $reverse)
    {
        $this->reverse = $reverse;
        return $this;
    }

    /**
     * @param $key
     * @param $field
     */
    public function addSort($key, $field)
    {
        $this->sortings[$key] = $field;
    }

    /**
     * @return array
     */
    public function getSortings() : array
    {
        return $this->sortings;
    }

}
