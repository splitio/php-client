<?php
namespace SplitIO\Grammar\Semver;

class SemverComparer
{
    public static function equals($version, $toCompare)
    {
        if (strcmp($version->getVersion(), $toCompare->getVersion()) == 0) {
            return true;
        }

        return false;
    }

    public static function do($version, $toCompare)
    {
        if (self::equals($version, $toCompare)) {
            return 0;
        }

        // Compare major, minor, and patch versions numerically
        if ($version->getMajor() !== $toCompare->getMajor()) {
            return $version->getMajor() > $toCompare->getMajor() ? 1 : -1;
        }

        if ($version->getMinor() !== $toCompare->getMinor()) {
            return $version->getMinor() > $toCompare->getMinor() ? 1 : -1;
        }

        if ($version->getPatch() !== $toCompare->getPatch()) {
            return $version->getPatch() > $toCompare->getPatch() ? 1 : -1;
        }

        if (!$version->isStable() && $toCompare->isStable()) {
            return -1;
        } elseif ($version->isStable() && !$toCompare->isStable()) {
            return 1;
        }

        // Compare pre-release versions lexically
        $vPreRelease = $version->getPreRelease();
        $tcPreRelease = $toCompare->getPreRelease();
        $minLength = min(count($vPreRelease), count($tcPreRelease));
        for ($i = 0; $i < $minLength; $i++) {
            if ($vPreRelease[$i] == $tcPreRelease[$i]) {
                continue;
            }

            if (is_numeric($vPreRelease[$i]) && is_numeric($tcPreRelease[$i])) {
                return $vPreRelease[$i] > $tcPreRelease[$i] ? 1 : -1;
            }

            return strcmp($vPreRelease[$i], $tcPreRelease[$i]);
        }

        // Compare lengths of pre-release versions
        return count($vPreRelease) <=> count($tcPreRelease);
    }
}
