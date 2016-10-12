<?php
namespace SplitIO\Service\Client\Resource;

use SplitIO\Service\Client\ClientBase;

class SdkTypeResource extends ClientBase
{
    /**
     * @return \SplitIO\Service\Client\Resource\ResourceTypeEnum $type
     */
    public function getResourceType()
    {
        return new ResourceTypeEnum(ResourceTypeEnum::SDK);
    }
}
