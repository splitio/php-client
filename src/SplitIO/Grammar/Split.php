<?php
namespace SplitIO\Grammar;

/*
{
      "orgId": "bf083ab0-b402-11e5-b7d5-024293b5d101",
      "environment": "bf9d9ce0-b402-11e5-b7d5-024293b5d101",
      "name": "myFeature",
      "trafficTypeId": "u",
      "trafficTypeName": "User",
      "seed": 93590075,
      "status": "ACTIVE",
      "killed": false,
      "conditions": [
        {
          "matcherGroup": {
            "combiner": "AND",
            "matchers": [
              {
                "matcherType": "ALL_KEYS",
                "negate": false,
                "userDefinedSegmentMatcherData": null,
                "whitelistMatcherData": null
              }
            ]
          },
          "partitions": [
            {
              "treatment": "on",
              "size": 50
            },
            {
              "treatment": "control",
              "size": 50
            }
          ]
        }
      ]
    }
*/

class Split
{
    private $orgId = null;

    private $environment = null;

    private $name = null;

    private $trafficTypeId = null;

    private $trafficTypeName = null;

    private $seed = null;

    private $status = null;

    private $killed = false;

    private $conditions = null;

    public function __construct(array $split)
    {
        $this->orgId = $split['orgId'];
        $this->environment = $split['environment'];
        $this->name = $split['name'];
        $this->trafficTypeId = $split['trafficTypeId'];
        $this->trafficTypeName = $split['trafficTypeName'];
        $this->seed = $split['seed'];
        $this->status = $split['status'];
        $this->killed = $split['killed'];

        if (isset($split['conditions'])) {
            $this->conditions = array();
            foreach ($conditions as $condition) {
                $this->conditions[] = new Condition($condition);
            }
        }

    }
}