<?php
namespace SplitIO\Sdk;

interface ClientInterface
{
    /**
     * Returns the treatment to show this id for this feature flag.
     * The set of treatments for a feature flag can be configured
     * on the Split user interface.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>Any of the parameters were null</li>
     *     <li>There was an exception</li>
     *     <li>The SDK does not know this feature flag</li>
     *     <li>The feature flag was deleted through the Split user interface.</li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature flag if:
     * <ol>
     *     <li>The feature flag was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature flag roll-out plan</li>
     * </ol>
     * The default treatment of a feature flag is set on the Split user
     * interface.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never returns null.
     *
     * @param $key
     * @param $featureFlagName
     * @param $attributes
     * @return string
     */
    public function getTreatment($key, $featureFlagName, array $attributes = null);

    /**
     * Returns an object with the treatment to show this id for this feature
     * flag and the config provided.
     * The set of treatments and config for a feature flag can be configured
     * on the Split user interface.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>Any of the parameters were null</li>
     *     <li>There was an exception</li>
     *     <li>The SDK does not know this feature flag</li>
     *     <li>The feature flag was deleted through the Split user interface.</li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature flag if:
     * <ol>
     *     <li>The feature flag was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature flag roll-out plan</li>
     * </ol>
     * The default treatment of a feature flag is set on the Split user
     * interface.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never returns null.
     *
     * This method returns null configuration if:
     * <ol>
     *     <li>config was not set up</li>
     * </ol>
     * @param $key
     * @param $featureFlagName
     * @param $attributes
     * @return array
     */
    public function getTreatmentWithConfig($key, $featureFlagName, array $attributes = null);

    /**
     * Returns an associative array which each key will be
     * the treatment result for each feature flag passed as parameter.
     * The set of treatments for a feature flag can be configured
     * on the Split user interface.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>featureFlagNames is invalid/li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature flag if:
     * <ol>
     *     <li>The feature flag was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature flag roll-out plan</li>
     * </ol>
     * The default treatment of a feature flag is set on the Split user
     * interface.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never returns null.
     *
     * @param $key
     * @param $featureFlagNames
     * @param $attributes
     * @return array
     */
    public function getTreatments($key, $featureFlagNames, array $attributes = null);

    /**
     * Returns an associative array which each key will be
     * the treatment result and the config for each
     * feature flag passed as parameter.
     * The set of treatments for a feature flag can be configured
     * on the Split user interface and the config for
     * that treatment.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>featureFlagNames is invalid/li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature flag if:
     * <ol>
     *     <li>The feature flag was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature flag roll-out plan</li>
     * </ol>
     * The default treatment of a feature flag is set on the Split user
     * interface.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never returns null.
     *
     * @param $key
     * @param $featureFlagNames
     * @param $attributes
     * @return array
     */
    public function getTreatmentsWithConfig($key, $featureFlagNames, array $attributes = null);

    /**
     * A short-hand for
     * <pre>
     *     (getTreatment(key, featureFlagName) == treatment) ? true : false;
     * </pre>
     *
     * This method never throws exceptions.
     * Instead of throwing  exceptions, it returns false.
     *
     * @param $key
     * @param $featureFlagName
     * @param $treatment
     * @return bool
     */
    public function isTreatment($key, $featureFlagName, $treatment);

    /**
     * Method to send events
     *
     * @param $key
     * @param $trafficType
     * @param $eventType
     * @param null $value
     * @return boolean
     */
    public function track($key, $trafficType, $eventType, $value = null);
}
