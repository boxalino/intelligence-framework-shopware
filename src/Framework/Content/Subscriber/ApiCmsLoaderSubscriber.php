<?php
namespace Boxalino\IntelligenceFramework\Framework\Content\Subscriber;

use Boxalino\IntelligenceFramework\Framework\Content\CreateFromTrait;
use Boxalino\IntelligenceFramework\Framework\Content\Listing\ApiCmsModel;
use Boxalino\IntelligenceFramework\Framework\Content\Page\ApiCmsLoader;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Shopware\Core\Framework\Struct\Struct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ApiCmsLoaderSubscriber
 * Adds CMS content to the Shopware Experience pages
 * Can be extended in order to alter the provided logic/behavior
 *
 * If the Boxalino Narrative CMS block is being configured for a "sidebar layout",
 * the root block with section=sidebar will be appended to the sidebar section of the page
 *
 * The class can be used as a base to be extended & customized
 * (ex: adding segments of the narrative response to other sections of the Shopware Experience - top, bottom)
 *
 * @package Boxalino\IntelligenceFramework\Framework\Content\Subscriber
 */
class ApiCmsLoaderSubscriber implements EventSubscriberInterface
{
    use CreateFromTrait;

    /**
     * @var ApiCmsLoader
     */
    private $apiCmsLoader;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(
        ApiCmsLoader $apiCmsLoader,
        LoggerInterface $logger
    ){
        $this->logger = $logger;
        $this->apiCmsLoader = $apiCmsLoader;
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CmsPageLoadedEvent::class => 'addApiCmsContent'
        ];
    }

    /**
     * Adds API CMS content as configured
     * Mirrors the Shopware6 structure of a Shopping Experience component
     *
     * @param CmsPageLoadedEvent $event
     */
    public function addApiCmsContent(CmsPageLoadedEvent $event) : void
    {
        /** @var CmsPageEntity $element */
        foreach($event->getResult() as $element)
        {
            /** @var CmsSectionEntity $section */
            foreach($element->getSections() as $sectionNr => $section)
            {
                /** @var CmsBlockEntity $block */
                foreach($section->getBlocks() as $block)
                {
                    try{
                        if($block->getType() == 'narrative')
                        {
                            $slot = $block->getSlots()->first();
                            $narrativeResponse = $this->apiCmsLoader->load($event->getRequest(), $event->getSalesChannelContext(), $slot);
                            $block->getSlots()->first()->setData($narrativeResponse);
                            if($slot->getConfig()['sidebar']['value'])
                            {
                                $this->updateSidebar($narrativeResponse, $section, $block);
                            }
                        }
                    } catch (\Throwable $exception)
                    {
                        $this->logger->warning("Boxalino ApiCmsLoaderSubscriber: " . $exception->getMessage());
                        continue;
                    }
                }
            }
        }
    }

    /**
     * @param Struct $data
     * @param CmsSectionEntity $section
     * @param CmsBlockEntity $narrativeBlock
     */
    protected function updateSidebar(Struct $data, CmsSectionEntity $section, CmsBlockEntity $narrativeBlock) : void
    {
        if($section->getType() == 'sidebar' && $narrativeBlock->getSectionPosition() == 'main')
        {
            $slot = $this->createCmsSlotEntity($narrativeBlock, $data);
            $slots = $this->createCmsSlotCollection($slot);
            $sidebarBlock = $this->createCmsBlockEntity($narrativeBlock, $slots, "sidebar", count($section->getBlocks()));

            $section->getBlocks()->add($sidebarBlock);
        }
    }

    /**
     * @param CmsBlockEntity $blockEntity
     * @param Struct $slotData
     * @return CmsSlotEntity
     */
    protected function createCmsSlotEntity(CmsBlockEntity $blockEntity, Struct $slotData) : CmsSlotEntity
    {
        /** @var CmsSlotEntity $slot */
        $slot = $this->createFromObject($blockEntity->getSlots()->first(), ['data', '_uniqueIdentifier']);
        $slot->setUniqueIdentifier(uniqid("boxalino_narrative_"));
        $slot->setData($this->apiCmsLoader->createSectionFrom($slotData, 'left'));

        return $slot;
    }

    /**
     * @param CmsSlotEntity $slot
     * @return CmsSlotCollection
     */
    protected function createCmsSlotCollection(CmsSlotEntity $slot) : CmsSlotCollection
    {
        $slotsCollection = new CmsSlotCollection();
        $slotsCollection->add($slot);

        return $slotsCollection;
    }

    /**
     * @param CmsBlockEntity $originalBlock
     * @param int $position
     * @return Struct
     */
    protected function createCmsBlockEntity(CmsBlockEntity $originalBlock, CmsSlotCollection $slots, string $sectionPosition, int $position=0) : CmsBlockEntity
    {
        /** @var CmsBlockEntity $block */
        $block = $this->createFromObject($originalBlock, ['data', '_uniqueIdentifier', 'sectionId', 'id']);
        $block->setSectionPosition($sectionPosition);
        $block->setUniqueIdentifier(uniqid("boxalino_sidebar_"));
        $block->setSectionId(uniqid());
        $block->setId(uniqid("boxalino_block_"));
        $block->setPosition($position);
        $block->setSlots($slots);

        return $block;
    }

}
