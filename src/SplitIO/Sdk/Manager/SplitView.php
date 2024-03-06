<?php

namespace SplitIO\Sdk\Manager;

class SplitView
{
    private string $name;
    private string $trafficType; // the name of the traffic type
    private bool $killed;
    private array $treatments;
    private int $changeNumber;
    private array|\StdClass $configs;
    private string $defaultTreatment;
    private ?array $sets;

    /**
     * SplitView constructor.
     * @param string $name
     * @param string $trafficType
     * @param bool $killed
     * @param array $treatments
     * @param int $changeNumber
     * @param array|\StdClass $configurations
     * @param string $defaultTreatment
     * @param ?array $sets
     */
    public function __construct(
        string $name,
        string $trafficType,
        bool $killed,
        array $treatments,
        int $changeNumber,
        array|\StdClass $configs,
        string $defaultTreatment,
        ?array $sets
    ) {
        $this->name = $name;
        $this->trafficType = $trafficType;
        $this->killed = $killed;
        $this->treatments = $treatments;
        $this->changeNumber = $changeNumber;
        $this->configs = $configs;
        $this->defaultTreatment = $defaultTreatment;
        $this->sets = $sets;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTrafficType(): string
    {
        return $this->trafficType;
    }

    /**
     * @param string $trafficType
     */
    public function setTrafficType(string $trafficType)
    {
        $this->trafficType = $trafficType;
    }

    /**
     * @return bool
     */
    public function getKilled(): bool
    {
        return $this->killed;
    }

    /**
     * @param bool $killed
     */
    public function setKilled(bool $killed)
    {
        $this->killed = $killed;
    }

    /**
     * @return array
     */
    public function getTreatments(): array
    {
        return $this->treatments;
    }

    /**
     * @param array $treatments
     */
    public function setTreatments(array $treatments)
    {
        $this->treatments = $treatments;
    }

    /**
     * @return int
     */
    public function getChangeNumber(): int
    {
        return $this->changeNumber;
    }

    /**
     * @param int $changeNumber
     */
    public function setChangeNumber(int $changeNumber)
    {
        $this->changeNumber = $changeNumber;
    }

    /**
     * @return array|\StdClass
     */
    public function getConfigs(): array|\StdClass
    {
        return $this->configs;
    }

    /**
     * @param array|\StdClass $configs
     */
    public function setConfigs(array|\StdClass $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @param string $defaultTreatment
     */
    public function setDefaultTreatment(string $defaultTreatment)
    {
        $this->defaultTreatment = $defaultTreatment;
    }

    /**
     * @return string
     */
    public function getDefaultTreatment(): string
    {
        return $this->defaultTreatment;
    }

    /**
     * @param array|null $sets
     */
    public function setSets(?array $sets)
    {
        $this->sets = $sets;
    }

    /**
     * @return array|null
     */
    public function getSets(): ?array
    {
        return $this->sets;
    }
}
