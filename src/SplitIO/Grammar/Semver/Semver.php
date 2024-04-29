<?php
namespace SplitIO\Grammar\Semver;

use SplitIO\Exception\SemverParseException;
use SplitIO\Split as SplitApp;

class Semver
{
    const METADATA_DELIMITER = '+';
    const PRERELEASE_DELIMITER = '-';
    const VALUE_DELIMITER = '.';

    private $major = null;
    private $minor = null;
    private $patch = null;
    private $preRelease = null;
    private $isStable = null;
    private $metadata = null;
    private $version = null;

    public static function build($version)
    {
        try {
            return new Semver($version);
        } catch (\Exception $e) {
            SplitApp::logger()->error($e->getMessage());
            return null;
        }
    }

    public function getMajor()
    {
        return $this->major;
    }

    public function getMinor()
    {
        return $this->minor;
    }

    public function getPatch()
    {
        return $this->patch;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function isStable()
    {
        return (bool) $this->isStable;
    }

    public function getPreRelease()
    {
        return $this->preRelease;
    }

    private function __construct($version)
    {
        $vWithoutMetadata = $this->setAndRemoveMetadataIfExists($version);
        $vWithoutPreRelease = $this->setAndRemovePreReleaseIfExists($vWithoutMetadata);
        $this->setMajorMinorAndPatch($vWithoutPreRelease);
        $this->version = $this->setVersion();
    }

    private function setAndRemoveMetadataIfExists($version)
    {
        $index = strpos($version, self::METADATA_DELIMITER);
        if ($index === false) {
            return $version;
        }

        $this->metadata = substr($version, $index + 1);
        if ($this->metadata === null || $this->metadata === '') {
            throw new SemverParseException("Unable to convert to Semver, incorrect medatada");
        }

        return substr($version, 0, $index);
    }

    private function setAndRemovePreReleaseIfExists($vWithoutMetadata)
    {
        $index = strpos($vWithoutMetadata, self::PRERELEASE_DELIMITER);
        if ($index === false) {
            $this->isStable = true;
            return $vWithoutMetadata;
        }

        $preReleaseData = substr($vWithoutMetadata, $index + 1);
        $this->preRelease = explode(self::VALUE_DELIMITER, $preReleaseData);
        if ($this->preRelease === null || array_reduce($this->preRelease, function ($carry, $item) {
            return $carry || $item === null || $item === '';
        }, false)) {
            throw new SemverParseException("Unable to convert to Semver, incorrect pre release data");
        }

        return substr($vWithoutMetadata, 0, $index);
    }

    private function setMajorMinorAndPatch($version)
    {
        $vParts = explode(self::VALUE_DELIMITER, $version);

        if (count($vParts) !== 3 || !is_numeric($vParts[0]) || !is_numeric($vParts[1]) || !is_numeric($vParts[2])) {
            throw new SemverParseException("Unable to convert to Semver, incorrect format: " . $version);
        }

        $this->major = (int)$vParts[0];
        $this->minor = (int)$vParts[1];
        $this->patch = (int)$vParts[2];
    }

    private function setVersion()
    {
        $toReturn = $this->major . self::VALUE_DELIMITER . $this->minor . self::VALUE_DELIMITER . $this->patch;

        if ($this->preRelease != null && count($this->preRelease) > 0)
        {
            foreach ($this->preRelease as $index => $item) 
            {
                if (is_numeric($item))
                {
                    $this->preRelease[$index] = (int)$this->preRelease[$index];
                }
            }

            $toReturn = $toReturn . self::PRERELEASE_DELIMITER . implode(self::VALUE_DELIMITER, $this->preRelease);
        }

        if ($this->metadata != null && $this->metadata != "")
        {
            $toReturn = $toReturn . self::METADATA_DELIMITER . $this->metadata;
        }

        return $toReturn;
    }
}