<?php
//namespace SplitIO\Cache\Storage\Mock;
namespace SplitIO\Test\Mock;

class RedisMock
{
    private $host;

    private $port;

    private $timeout;

    private $mockData = [];

    private $sets = [];

    /**
     * Connects to a Redis instance.
     *
     * @param string    $host       can be a host, or the path to a unix domain socket
     * @param int       $port       optional
     * @param float     $timeout    value in seconds (optional, default is 0.0 meaning unlimited)
     * @return bool                 TRUE on success, FALSE on error.
     * <pre>
     * $redis->connect('127.0.0.1', 6379);
     * $redis->connect('127.0.0.1');            // port 6379 by default
     * $redis->connect('127.0.0.1', 6379, 2.5); // 2.5 sec timeout.
     * $redis->connect('/tmp/redis.sock');      // unix domain socket.
     * </pre>
     */
    public function connect($host, $port = 6379, $timeout = 0.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    /**
     * Get the value related to the specified key
     *
     * @param   string  $key
     * @return  string|bool: If key didn't exist, FALSE is returned. Otherwise, the value related to this key is returned.
     * @link    http://redis.io/commands/get
     * @example $redis->get('key');
     */
    public function get($key)
    {
        if (isset($this->mockData[$key])) {
            $timegap = time() - $this->mockData[$key]['setat'];
            if ($this->mockData[$key]['timeout'] == 0 || $timegap < $this->mockData[$key]['timeout']) {
                return (string) $this->mockData[$key]['value'];
            }
        }

        return false;
    }

    /**
     * Removes all entries from all databases.
     *
     * @return  bool: Always TRUE.
     * @link    http://redis.io/commands/flushall
     * @example $redis->flushAll();
     */
    public function flushAll()
    {
        $this->mockData = [];
        $this->sets = [];
        return true;
    }

    /**
     * Remove specified keys.
     *
     * @param   int|array   $key1 An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @param   string      $key2 ...
     * @param   string      $key3 ...
     * @return int Number of keys deleted.
     * @link    http://redis.io/commands/del
     * @example
     * <pre>
     * $redis->set('key1', 'val1');
     * $redis->set('key2', 'val2');
     * $redis->set('key3', 'val3');
     * $redis->set('key4', 'val4');
     * $redis->delete('key1', 'key2');          // return 2
     * $redis->delete(array('key3', 'key4'));   // return 2
     * </pre>
     */
    public function del($key1, $key2 = null, $key3 = null)
    {
        $keysNumber = func_num_args();
        $deletedKeys = 0;

        for ($i=0; $i < $keysNumber; $i++) {
            $key = func_get_arg($i);
            if (isset($this->mockData[$key])) {
                unset($this->mockData[$key]);
                $deletedKeys++;
            }
        }

        return $deletedKeys;
    }

    /**
     * @see del()
     * @param $key1
     * @param null $key2
     * @param null $key3
     * @return int
     */
    public function delete($key1, $key2 = null, $key3 = null)
    {

        $keysNumber = func_num_args();
        $deletedKeys = 0;

        for ($i=0; $i < $keysNumber; $i++) {
            $key = func_get_arg($i);
            if (isset($this->mockData[$key])) {
                unset($this->mockData[$key]);
                $deletedKeys++;
            }
        }

        return $deletedKeys;
    }

    /**
     * Set the string value in argument as value of the key.
     *
     * @param   string  $key
     * @param   string  $value
     * @param   int   $timeout [optional] Calling setex() is preferred if you want a timeout.
     * @return  bool:   TRUE if the command is successful.
     * @link    http://redis.io/commands/set
     * @example $redis->set('key', 'value');
     */
    public function set($key, $value, $timeout = 0)
    {
        $this->mockData[$key] = ['value' => $value, 'timeout' => $timeout, 'setat' => time()];
        return true;
    }

    /**
     * Adds a values to the set value stored at key.
     * If this value is already in the set, FALSE is returned.
     *
     * @param   string  $key        Required key
     * @param   string  $value1     Required value
     * @param   string  $value2     Optional value
     * @param   string  $valueN     Optional value
     * @return  int     The number of elements added to the set
     * @link    http://redis.io/commands/sadd
     * @example
     * <pre>
     * $redis->sAdd('k', 'v1');                // int(1)
     * $redis->sAdd('k', 'v1', 'v2', 'v3');    // int(2)
     * </pre>
     */
    public function sAdd($key, $value1, $value2 = null, $valueN = null)
    {
        $valuesNumber = func_num_args();
        $key = func_get_arg(0);
        $addedValues = 0;

        if (!isset($this->sets[$key])) {
            $this->sets[$key] = [];
        }

        for ($i=1; $i < $valuesNumber; $i++) {
            $value = func_get_arg($i);
            if (!in_array($value, $this->sets[$key])) {
                $this->sets[$key][] = $value;
                $addedValues++;
            }
        }

        return $addedValues;
    }

    /**
     * Removes the specified members from the set value stored at key.
     *
     * @param   string  $key
     * @param   string  $member1
     * @param   string  $member2
     * @param   string  $memberN
     * @return  int     The number of elements removed from the set.
     * @link    http://redis.io/commands/srem
     * @example
     * <pre>
     * var_dump( $redis->sAdd('k', 'v1', 'v2', 'v3') );    // int(3)
     * var_dump( $redis->sRem('k', 'v2', 'v3') );          // int(2)
     * var_dump( $redis->sMembers('k') );
     * //// Output:
     * // array(1) {
     * //   [0]=> string(2) "v1"
     * // }
     * </pre>
     */
    public function sRem($key, $member1, $member2 = null, $memberN = null)
    {
        $memberNumber = func_num_args();
        $key = func_get_arg(0);
        $removedValues = 0;

        if (!isset($this->sets[$key])) {
            return $removedValues;
        }

        for ($i=1; $i < $memberNumber; $i++) {
            $value = func_get_arg($i);
            $idx = array_search($value, $this->sets[$key]);
            if ($idx !== null && $idx !== false) {
                unset($this->sets[$key][$idx]);
                $removedValues++;
            }
        }

        return $removedValues;
    }

    /**
     * @see sRem()
     * @link    http://redis.io/commands/srem
     * @param   string  $key
     * @param   string  $member1
     * @param   string  $member2
     * @param   string  $memberN
     */
    public function sRemove($key, $member1, $member2 = null, $memberN = null)
    {
        $memberNumber = func_num_args();
        $key = func_get_arg(0);
        $removedValues = 0;

        if (!isset($this->sets[$key])) {
            return $removedValues;
        }

        for ($i=1; $i < $memberNumber; $i++) {
            $value = func_get_arg($i);
            $idx = array_search($value, $this->sets[$key]);
            if ($idx !== null && $idx !== false) {
                unset($this->sets[$key][$idx]);
                $removedValues++;
            }
        }

        return $removedValues;
    }

    /**
     * Checks if value is a member of the set stored at the key key.
     *
     * @param   string  $key
     * @param   string  $value
     * @return  bool    TRUE if value is a member of the set at key key, FALSE otherwise.
     * @link    http://redis.io/commands/sismember
     * @example
     * <pre>
     * $redis->sAdd('key1' , 'set1');
     * $redis->sAdd('key1' , 'set2');
     * $redis->sAdd('key1' , 'set3'); // 'key1' => {'set1', 'set2', 'set3'}
     *
     * $redis->sIsMember('key1', 'set1'); // TRUE
     * $redis->sIsMember('key1', 'setX'); // FALSE
     * </pre>
     */
    public function sIsMember($key, $value)
    {
        if (!isset($this->sets[$key])) {
            return false;
        }

        if (in_array($value, $this->sets[$key])) {
            return true;
        }

        return false;
    }

    /**
     * @see sIsMember()
     * @link    http://redis.io/commands/sismember
     * @param   string  $key
     * @param   string  $value
     */
    public function sContains($key, $value)
    {
        return $this->sIsMember($key, $value);
    }

    /**
     * Returns the contents of a set.
     *
     * @param   string  $key
     * @return  array   An array of elements, the contents of the set.
     * @link    http://redis.io/commands/smembers
     * @example
     * <pre>
     * $redis->delete('s');
     * $redis->sAdd('s', 'a');
     * $redis->sAdd('s', 'b');
     * $redis->sAdd('s', 'a');
     * $redis->sAdd('s', 'c');
     * var_dump($redis->sMembers('s'));
     *
     * //array(3) {
     * //  [0]=>
     * //  string(1) "c"
     * //  [1]=>
     * //  string(1) "a"
     * //  [2]=>
     * //  string(1) "b"
     * //}
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public function sMembers($key)
    {
        if (!isset($this->sets[$key])) {
            return [];
        }

        return $this->sets[$key];

    }

    /**
     * @see sMembers()
     * @param   string  $key
     * @return array An array of elements, the contents of the set.
     * @link    http://redis.io/commands/smembers
     */
    public function sGetMembers($key)
    {
        return $this->sMembers($key);
    }
}