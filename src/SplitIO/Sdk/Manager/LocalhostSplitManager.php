<?php
namespace SplitIO\Sdk\Manager;

class LocalhostSplitManager implements SplitManagerInterface
{
    private $splits;

    public function __construct(array $splits)
    {
        $this->splits = $splits;
    }

    public function splits()
    {
        $_splits = array();

        if ($this->splits) {
            foreach ($this->splits as $featureName => $treatment) {
                $_splits[] = new SplitView(
                    $featureName,
                    null,
                    false,
                    array($treatment),
                    0
                );
            }
        }

        return $_splits;
    }

    public function split($featureName)
    {
        if (isset($this->splits[$featureName])) {
            return new SplitView(
                $featureName,
                null,
                false,
                array($this->splits[$featureName]),
                0
            );
        }

        return null;
    }
}
