<?php
namespace Boxalino\IntelligenceFramework\Framework\Content\Subscriber;

use Boxalino\IntelligenceFramework\Framework\Content\Page\ApiCmsLoader;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ApiCmsLoaderSubscriber
 *
 * @package Boxalino\IntelligenceFramework\Framework\Content\Subscriber
 */
class ApiCmsLoaderSubscriber implements EventSubscriberInterface
{
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
     *
     * @param CmsPageLoadedEvent $event
     */
    public function addApiCmsContent(CmsPageLoadedEvent $event) : void
    {
        $this->logger->info("IN APICMSLOADERSUBSCRIBER");
        /** @var CmsPageEntity $element */
        foreach($event->getResult() as $element)
        {
            /** @var CmsSectionEntity $section */
            foreach($element->getSections() as $section)
            {
                /** @var CmsBlockEntity $block */
                foreach($section->getBlocks() as $block)
                {
                    if($block->getType() == 'narrative')
                    {
                        $data = $this->apiCmsLoader->load($event->getRequest(), $event->getSalesChannelContext(), $block->getSlots()->first());
                        $block->getSlots()->first()->setData($data);
                    }
                }
            }
        }
    }

}
