<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Framework\Content\Page;

use Boxalino\IntelligenceFramework\Framework\Content\Listing\ApiCmsModel;
use Boxalino\IntelligenceFramework\Service\Api\ApiCallServiceInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiCmsLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\IntelligenceFramework\Service\Api\Content\Page
 */
class ApiCmsLoader extends ApiLoader
{

    /**
     * Loads the content of an API Response page
     */
    public function load(Request $request, SalesChannelContext $salesChannelContext, CmsSlotEntity $slot): Struct
    {
        $this->addProperties($slot);
        $this->call($request, $salesChannelContext);

        if($this->apiCallService->isFallback())
        {
            throw new \Exception($this->apiCallService->getFallbackMessage());
        }

        $content = new ApiCmsModel();
        $content->setBlocks($this->apiCallService->getApiResponse()->getBlocks())
            ->setRequestId($this->apiCallService->getApiResponse()->getRequestId())
            ->setGroupBy($this->getGroupBy())
            ->setVariantUuid($this->getVariantUuid())
            ->setTotalHitCount($this->apiCallService->getApiResponse()->getHitCount());

        return $content;
    }

    /**
     * Adds properties to the CmsContextAbstract
     * @param CmsSlotEntity $slot
     */
    protected function addProperties(CmsSlotEntity $slot)
    {
        foreach($slot->getConfig() as $key => $configuration)
        {
            $value = $configuration['value'];
            if($key == 'widget')
            {
                $this->apiContextInterface->setWidget($value);
                continue;
            }
            if($key == 'hitCount')
            {
                $this->apiContextInterface->setHitCount((int) $value);
                continue;
            }
            if($key == 'groupBy')
            {
                $this->apiContextInterface->setGroupBy($value);
                continue;
            }

            if(!is_null($value) && !empty($value))
            {
                $this->apiContextInterface->set($key, $configuration['value']);
            }
        }
    }

}
