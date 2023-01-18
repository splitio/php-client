<?php
namespace SplitIO\Sdk\Manager;

use \stdClass;
use SplitIO\Sdk\Validator\InputValidator;

class LocalhostSplitManager implements SplitManagerInterface
{
    private $splits;

    public function __construct(array $splits)
    {
        $splitDefinitions = array();
        if ($splits) {
            foreach (array_keys($splits) as $concatenated) {
                $splitted = explode(":", $concatenated);
                $featureName = $splitted[0];
                $split = $splits[$concatenated];
                if (isset($splitDefinitions[$featureName])) {
                    array_push(
                        $splitDefinitions[$featureName]["treatments"],
                        $split["treatment"]
                    );
                    $splitDefinitions[$featureName]["treatments"] = array_unique(
                        $splitDefinitions[$featureName]["treatments"]
                    );
                    if (isset($split["config"])) {
                        $splitDefinitions[$featureName]["config"][ $split["treatment"]] = $split["config"];
                    }
                } else {
                    $splitDefinitions[$featureName] = array(
                        "treatments" => array($split["treatment"]),
                    );
                    if (isset($split["config"])) {
                        $splitDefinitions[$featureName]["config"] = array($split["treatment"] => $split["config"]);
                    }
                }
            }
        }

        $this->splits = $splitDefinitions;
    }

    public function splitNames()
    {
        $_splits = array();

        if ($this->splits) {
            return array_keys($this->splits);
        }

        return $_splits;
    }

    public function splits()
    {
        $_splits = array();

        if ($this->splits) {
            foreach (array_keys($this->splits) as $featureName) {
                $configs = isset($this->splits[$featureName]["config"]) ?
                    $this->splits[$featureName]["config"] : new StdClass;
                $_splits[] = new SplitView(
                    $featureName,
                    null,
                    false,
                    $this->splits[$featureName]["treatments"],
                    0,
                    $configs
                );
            }
        }

        return $_splits;
    }

    /**
     * Return split
     * @param mixed $featureName
     * @return SplitView|null
     */
    public function split($featureName)
    {
        $featureName = InputValidator::validateFeatureName($featureName, 'split');
        if (is_null($featureName)) {
            return null;
        }

        if (isset($this->splits[$featureName])) {
            $configs = isset($this->splits[$featureName]["config"]) ?
                    $this->splits[$featureName]["config"] : new StdClass;
            return new SplitView(
                $featureName,
                null,
                false,
                $this->splits[$featureName]["treatments"],
                0,
                $configs
            );
        }

        return null;
    }
}
