<?php
namespace SplitIO\Sdk\Impressions;

/**
 * Class ImpressionLabel
 * @package SplitIO\Sdk\Impressions
 */
class ImpressionLabel
{
    /**
     * Condition: Feature flag Was Killed
     * Treatment: Default treatment
     * Label: killed
     */
    const KILLED = "killed";

    /**
     * Condition: No condition matched
     * Treatment: Default Treatment
     * Label: no condition matched
     */
    const NO_CONDITION_MATCHED = "default rule";

    /**
     * Condition: Feature flag definition was not found
     * Treatment: control
     * Label: split not found
     */
    const SPLIT_NOT_FOUND = "definition not found";

    /**
     * Condition: The required matcher in condition was not found
     * Treatment: control
     * Label: matcher not found
     */
    const MATCHER_NOT_FOUND = "matcher not found";

    /**
     * Condition: Traffic allocation failed
     * Treatment: Default Treatment
     * Label: not in split
     */
    const NOT_IN_SPLIT = "not in split";

    /**
     * Condition: There was an exception
     * Treatment: control
     * Label: exception
     */
    const EXCEPTION = "exception";

    /**
     * Condition: unsupported matcher
     * Treatment: control
     * Label: targeting rule type unsupported by sdk
     */
    const UNSUPPORTED_MATCHER = "targeting rule type unsupported by sdk";
}
