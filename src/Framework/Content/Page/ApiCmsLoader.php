<?php declare(strict_types=1);
namespace Boxalino\IntelligenceFramework\Framework\Content\Page;

use Boxalino\IntelligenceFramework\Framework\Content\CreateFromTrait;
use Boxalino\IntelligenceFramework\Framework\Content\Listing\ApiCmsModel;
use Boxalino\IntelligenceFramework\Service\Api\ApiCallServiceInterface;
use Boxalino\IntelligenceFramework\Service\Api\Response\Accessor\Block;
use Boxalino\IntelligenceFramework\Service\ErrorHandler\UndefinedPropertyError;
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
    use CreateFromTrait;

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

        $sidebar = new \ArrayIterator();
        $blocks = $this->apiCallService->getApiResponse()->getBlocks();
        if($this->apiContextInterface->getProperty("sidebar"))
        {
            /** @var Block $block */
            foreach($blocks as $index=>$block)
            {
                try{
                    $section = $block->getSection();
                    if($section[0] == 'sidebar')
                    {
                        $sidebar->append($block);
                        $blocks->offsetUnset($index);
                    }
                } catch (UndefinedPropertyError $exception)
                {
                    continue;
                }
            }
        }

        $content = new ApiCmsModel();
        $content->setBlocks($blocks)
            ->setSidebar($sidebar)
            ->setRequestId($this->apiCallService->getApiResponse()->getRequestId())
            ->setGroupBy($this->getGroupBy())
            ->setVariantUuid($this->getVariantUuid())
            ->setNavigationId($request->get("navigationId", $salesChannelContext->getSalesChannel()->getNavigationCategoryId()))
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

    /**
     * Replicates the narrative content in order to generate the sidebar slots
     *
     * @param Struct $apiCmsModel
     * @return Struct
     */
    public function createSidebarFrom(Struct $apiCmsModel) : Struct
    {
        if($apiCmsModel instanceof ApiCmsModel)
        {
            $sidebarNarrativeBlock = $this->createFromObject($apiCmsModel, ['sidebar', 'blocks']);
            $sidebarNarrativeBlock->setBlocks($apiCmsModel->getSidebar());
            $sidebarNarrativeBlock->setSidebar(new \ArrayIterator());

            return $sidebarNarrativeBlock;
        }

        return new ApiCmsModel();
    }

}
