<?php
namespace SplitIO\Sdk\Impressions;

/**
 * Class ImpressionLabel
 * @package SplitIO\Sdk\Impressions
 */
class ImpressionLabel
{
    /**
     * Condition: Split Was Killed
     * Treatment: Default treatment
     * Label: killed
     */
    const KILLED = "killed";

    /**
     * Condition: No condition matched
     * Treatment: Default Treatment
     * Label: no condition matched
     */
    const NO_CONDITION_MATCHED = "no condition matched";

    /**
     * Condition: Split definition was not found
     * Treatment: control
     * Label: split not found
     */
    const SPLIT_NOT_FOUND = "split not found";

    /**
     * Condition: There was an exception
     * Treatment: control
     * Label: exception
     */
    const EXCEPTION = "exception";
}
