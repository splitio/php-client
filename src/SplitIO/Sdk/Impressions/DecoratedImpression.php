<?php
namespace SplitIO\Sdk\Impressions;

/**
 * Class DecoratedImpression
 * Wraps an Impression with its disabled flag for routing purposes.
 * @package SplitIO\Sdk\Impressions
 */
class DecoratedImpression
{
    /**
     * @var Impression
     */
    private $impression;

    /**
     * @var bool
     */
    private $disabled;

    /**
     * DecoratedImpression constructor.
     * @param Impression $impression
     * @param bool $disabled
     */
    public function __construct(Impression $impression, $disabled)
    {
        $this->impression = $impression;
        $this->disabled = (bool) $disabled;
    }

    /**
     * @return Impression
     */
    public function getImpression()
    {
        return $this->impression;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
}
