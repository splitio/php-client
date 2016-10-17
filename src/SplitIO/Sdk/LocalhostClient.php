<?php
namespace SplitIO\Sdk;

use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

class LocalhostClient implements ClientInterface
{
    private $splits = null;

    /**
     * Try to get the user home directory
     * @return string|null
     * @codeCoverageIgnore
     */
    private function getUserHome()
    {
        $userData = posix_getpwuid(posix_getuid());
        return (isset($userData['dir'])) ? $userData['dir'] : null;
    }

    /**
     * Constructor of the FakeClient for development purpose
     * @param null $splitFilePath
     * @throws \Exception
     */
    public function __construct($splitFilePath = null)
    {
        //Try to load Splits file given by developer
        if ($splitFilePath !== null) {
            $this->loadSplits($splitFilePath);
        }

        // @codeCoverageIgnoreStart
        //Try to load Splits file from developer home if this has not given by developer
        if ($this->splits === null) {
            $homeFilePath = $this->getUserHome().'/.split';
            $this->loadSplits($homeFilePath);
        }

        if ($this->splits === null) {
            throw new \Exception("Splits file could not be found");
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param $splitFilePath
     */
    private function loadSplits($splitFilePath)
    {
        if (file_exists($splitFilePath)) {
            $fileContent = file_get_contents($splitFilePath);
            $this->splits = \SplitIO\parseSplitsFile($fileContent);
        }
    }

    public function getSplits()
    {
        return $this->splits;
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
    public function getTreatment($key, $featureName, array $attributes = null)
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
            }
        }

        return false;
    }
}
