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
     * @param string|Key $key
     * @param string $feature
     * @param array|null $attributes
     * @return string
     */
    public function getTreatment(string|Key $key, string $feature, ?array $attributes): string;

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
     * @param string $key
     * @param string $feature
     * @param array|null $attributes
     * @return string
     */
    public function getTreatmentWithConfig(string|Key $key, string $feature, ?array $attributes): array;

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
     * @param string|Key $key
     * @param array $features
     * @param array|null $attributes
     * @return array
     */
    public function getTreatments(string|Key $key, array $features, ?array $attributes): array;

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
     * @param string|Key $key
     * @param array $features
     * @param array|null $attributes
     * @return array
     */
    public function getTreatmentsWithConfig(string|Key $key, array $features, ?array $attributes): array;

        /**
     * Returns an associative array which each key will be
     * the treatment result for each feature associated with
     * flag set passed as parameter.
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
     * @param string|Key $key
     * @param array|null string $flagSet
     * @param $attributes
     * @return array
     */
    public function getTreatmentsByFlagSet(string|Key $key, string $flagSet, ?array $attributes): array;

        /**
     * Returns an associative array which each key will be
     * the treatment result and the config for each
     * feature associated with flag sets passed as parameter.
     * The set of treatments for a feature can be configured
     * on the Split web console and the config for
     * that treatment.
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
     * @param string|Key $key
     * @param string $flagSet
     * @param array|null $attributes
     * @return array
     */
    public function getTreatmentsWithConfigByFlagSet(string|Key $key, string $flagSet, ?array $attributes);

    /**
     * Returns an associative array which each key will be
     * the treatment result and the config for each
     * feature associated with flag sets passed as parameter.
     * The set of treatments for a feature can be configured
     * on the Split web console and the config for
     * that treatment.
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
     * @param string|Key $key
     * @param array $flagSets
     * @param array|null $attributes
     * @return array
     */
    public function getTreatmentsByFlagSets(string|Key $key, array $flagSets, ?array $attributes);

    /**
     * Returns an associative array which each key will be
     * the treatment result and the config for each
     * feature associated with flag sets passed as parameter.
     * The set of treatments for a feature can be configured
     * on the Split web console and the config for
     * that treatment.
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
     * @param string|Key $key
     * @param array $flagSets
     * @param array|null $attributes
     * @return array
     */
    public function getTreatmentsWithConfigByFlagSets(string|Key $key, array $flagSets, ?array $attributes);

    /**
     * A short-hand for
     * <pre>
     *     (getTreatment(key, featureFlagName) == treatment) ? true : false;
     * </pre>
     *
     * This method never throws exceptions.
     * Instead of throwing  exceptions, it returns false.
     *
     * @param string $key
     * @param string $featureFlagName
     * @param string $treatment
     * @return bool
     */
    public function isTreatment(string $key, string $featureFlagName, string $treatment);

    /**
     * Method to send events
     *
     * @param string $key
     * @param string $trafficType
     * @param string $eventType
     * @param float|null $value
     * @param array|null $properties
     * @return boolean
     */
    public function track(string $key, string $trafficType, string $eventType, ?float $value = null, ?array $properties = null): bool;
}
