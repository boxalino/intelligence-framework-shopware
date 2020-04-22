<?php
namespace Boxalino\IntelligenceFramework\Framework\Request;

use Boxalino\IntelligenceFramework\Service\Api\Request\ContextInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Interface ShopwareContextInterface
 * @package Boxalino\IntelligenceFramework\Framework\Request
 */
interface ShopwareApiContextInterface extends ContextInterface
{
    /**
     * @param SalesChannelContext $salesChannelContext
     * @return mixed
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext);

}
