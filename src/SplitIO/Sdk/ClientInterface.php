<?php
namespace SplitIO\Sdk;

interface ClientInterface
{
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
     * @param $attributes
     * @return string
     */
    public function getTreatment($key, $featureName, array $attributes = null);

    /**
     * Returns an object with the treatment to show this id for this feature
     * and the config provided.
     * The set of treatments and config for a feature can be configured
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
     *
     * This method returns null configuration if:
     * <ol>
     *     <li>config was not set up</li>
     * </ol>
     * @param $key
     * @param $featureName
     * @param $attributes
     * @return string|array|null
     */
    public function getTreatmentWithConfig($key, $featureName, array $attributes = null);

    /**
     * Returns an associative array which each key will be
     * the treatment result for each feature passed as parameter.
     * The set of treatments for a feature can be configured
     * on the Split web console.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>featureNames is invalid/li>
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
     * It also never returns null.
     *
     * @param $key
     * @param $featureNames
     * @param $attributes
     * @return array
     */
    public function getTreatments($key, $featureNames, array $attributes = null);

    /**
     * Returns an associative array which each key will be
     * the treatment result and the config for each
     * feature passed as parameter.
     * The set of treatments for a feature can be configured
     * on the Split web console and the config for
     * that treatment.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>featureNames is invalid/li>
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
     * It also never returns null.
     *
     * @param $key
     * @param $featureNames
     * @param $attributes
     * @return array
     */
    public function getTreatmentsWithConfig($key, $featureNames, array $attributes = null);

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
    public function isTreatment($key, $featureName, $treatment);

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
