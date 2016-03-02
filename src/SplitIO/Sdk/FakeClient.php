<?php
namespace SplitIO\Sdk;

use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

class FakeClient implements ClientInterface
{
    private $splits = null;

    /**
     * Try to get the user home directory
     * @return string|null
     */
    private function getUserHome()
    {
        $userData = posix_getpwuid(posix_getuid());
        return (isset($userData['dir'])) ? $userData['dir'] : null;
    }

    /**
     * Parse the .splits file, returning an array of feature=>treatment pairs
     * @param $fileContent
     * @return array
     */
    private function parseSplitsFile($fileContent)
    {
        $re = "/([a-zA-Z]+[-_a-zA-Z0-9]*)\\s+([a-zA-Z]+[-_a-zA-Z0-9]*)/";

        $lines = explode(PHP_EOL, $fileContent);

        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (isset($line[0]) && $line[0] != '#') {
                $matches = [];
                if (preg_match($re, $line, $matches)) {
                    if (isset($matches[1]) && isset($matches[2])) {
                        $result[$matches[1]] = $matches[2];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Constructor of the FakeClient for development purpose
     * @param null $splitFilePath
     * @throws \Exception
     */
    public function __construct($splitFilePath = null)
    {
        $splitFile = null;

        //Try to load Splits file given by developer
        if ($splitFilePath !== null) {
            if (file_exists($splitFilePath)) {
                $splitFile = file_get_contents($splitFilePath);
            }
        }

        //Try to load Splits file from developer home if this has not given by developer
        if ($splitFile === null) {
            $homeFilePath = $this->getUserHome().'/.splits';
            if (file_exists($homeFilePath)) {
                $splitFile = file_get_contents($homeFilePath);
            }
        }

        if ($splitFile === null) {
            throw new \Exception("Splits file could not be found");
        }

        $this->splits = $this->parseSplitsFile($splitFile);
    }

    /**
     * Returns the treatment to show this id for this feature.
     * The set of treatments for a feature can be configured
     * on the Split web console.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>Any of the parameters were null</li>
     *     <li>There was an exception</li>
     *     <li>The SDK does not know this feature</li>
     *     <li>The feature was deleted through the web console.</li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature if:
     * <ol>
     *     <li>The feature was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature roll-out plan</li>
     * </ol>
     * The default treatment of a feature is set on the Split web
     * console.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never  returns null.
     *
     * @param $key
     * @param $featureName
     * @return string
     */
    public function getTreatment($key, $featureName)
    {
        if (isset($this->splits[$featureName])) {
            return $this->splits[$featureName];
        }

        return TreatmentEnum::CONTROL;
    }

    /**
     * A short-hand for
     * <pre>
     *     (getTreatment(key, feature) == treatment) ? true : false;
     * </pre>
     *
     * This method never throws exceptions.
     * Instead of throwing  exceptions, it returns false.
     *
     * @param $key
     * @param $featureName
     * @param $treatment
     * @return bool
     */
    public function isTreatment($key, $featureName, $treatment)
    {
        $calculatedTreatment = $this->getTreatment($key, $featureName);

        if ($calculatedTreatment !== TreatmentEnum::CONTROL) {
            if ($treatment == $calculatedTreatment) {
                return true;
            } else {
                return false;
            }
        }
    }
}