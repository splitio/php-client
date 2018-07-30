<?php

namespace SplitIO\Sdk;

// Declaration of ImpressionListener interface 
interface ImpressionListener
{
    /*
     * Provides impressions, attributes, sdk-version, instance-id to be used by the client.
     *
     * @param   $data   mixed   Object that contains the information obtained from the sdk:
     *  $data['$impression']                =>  Impression
     *  $data['attributes']                 =>  List of attributes
     *  $data['instance-id']                =>  Instance ID used in sdk
     *  $data['sdk-language-version']       =>  Sdk's version used
     *
     */
    public function logImpression($data);
}
