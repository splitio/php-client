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
                $featureFlagName = $splitted[0];
                $split = $splits[$concatenated];
                if (isset($splitDefinitions[$featureFlagName])) {
                    array_push(
                        $splitDefinitions[$featureFlagName]["treatments"],
                        $split["treatment"]
                    );
                    $splitDefinitions[$featureFlagName]["treatments"] = array_unique(
                        $splitDefinitions[$featureFlagName]["treatments"]
                    );
                    if (isset($split["config"])) {
                        $splitDefinitions[$featureFlagName]["config"][ $split["treatment"]] = $split["config"];
                    }
                } else {
                    $splitDefinitions[$featureFlagName] = array(
                        "treatments" => array($split["treatment"]),
                    );
                    if (isset($split["config"])) {
                        $splitDefinitions[$featureFlagName]["config"] = array($split["treatment"] => $split["config"]);
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
            foreach (array_keys($this->splits) as $featureFlagName) {
                $configs = isset($this->splits[$featureFlagName]["config"]) ?
                    $this->splits[$featureFlagName]["config"] : new StdClass;
                $_splits[] = new SplitView(
                    $featureFlagName,
                    null,
                    false,
                    $this->splits[$featureFlagName]["treatments"],
                    0,
                    $configs
                );
            }
        }

        return $_splits;
    }

    public function split($featureFlagName)
    {
        $featureFlagName = InputValidator::validateFeatureFlagName($featureFlagName, 'split');
        if (is_null($featureFlagName)) {
            return null;
        }

        if (isset($this->splits[$featureFlagName])) {
            $configs = isset($this->splits[$featureFlagName]["config"]) ?
                    $this->splits[$featureFlagName]["config"] : new StdClass;
            return new SplitView(
                $featureFlagName,
                null,
                false,
                $this->splits[$featureFlagName]["treatments"],
                0,
                $configs
            );
        }

        return null;
    }
}
