<?php

namespace SplitIO\Sdk\Manager;

use stdClass;
use SplitIO\Sdk\Validator\InputValidator;

class LocalhostSplitManager implements SplitManagerInterface
{
    private array $splits;

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

    public function splitNames(): array
    {
        $_splits = array();

        if ($this->splits) {
            return array_keys($this->splits);
        }

        return $_splits;
    }

    private function createSplitView(string $name, array $treatments, array|StdClass $configs): SplitView
    {
        return new SplitView(
            $name,
            "user",
            false,
            $treatments,
            0,
            $configs,
            "",
            array()
        );
    }

    public function splits(): array
    {
        $_splits = array();

        if ($this->splits) {
            foreach (array_keys($this->splits) as $featureFlagName) {
                $configs = isset($this->splits[$featureFlagName]["config"]) ?
                    $this->splits[$featureFlagName]["config"] : new StdClass();
                $_splits[] = $this->createSplitView(
                    $featureFlagName,
                    $this->splits[$featureFlagName]["treatments"],
                    $configs
                );
            }
        }

        return $_splits;
    }

    /**
     * Return split
     * @param string $featureName
     * @return SplitView|null
     */
    public function split(string $featureFlagName): ?SplitView
    {
        $featureFlagName = InputValidator::validateFeatureFlagName($featureFlagName, 'split');
        if (is_null($featureFlagName)) {
            return null;
        }

        if (isset($this->splits[$featureFlagName])) {
            $configs = isset($this->splits[$featureFlagName]["config"]) ?
                    $this->splits[$featureFlagName]["config"] : new StdClass();
            return $this->createSplitView(
                $featureFlagName,
                $this->splits[$featureFlagName]["treatments"],
                $configs
            );
        }

        return null;
    }
}
