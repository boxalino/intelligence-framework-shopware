<?php
namespace Boxalino\IntelligenceFramework\Service\ErrorHandler;

class UndefinedPropertyError extends \Error
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $message)
    {
        parent::__construct($message, 0, null);
    }

}
