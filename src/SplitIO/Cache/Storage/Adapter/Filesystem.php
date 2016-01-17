<?php
namespace SplitIO\Cache\Storage\Adapter;

use SplitIO\Cache\Storage\Exception\AdapterException;
use SplitIO\Cache\Item;

/**
 * Class Filesystem
 * @package SplitIO\Cache\Storage\Adapter
 */
class Filesystem implements CacheStorageAdapterInterface
{

    /** Default filesystem path */
    const DEFAULT_PATH = '/tmp';

    /** Default file name prefix */
    const DEFAULT_FILENAME_PREFIX = 'splitio';

    /** Default file name extension */
    const DEFAULT_FILENAME_EXTENSION = 'cache';

    /** Default value time to live */
    const DEFAULT_VALUE_TTL = 60;

    /** @var resource|null  */
    private $resource = null;

    /** @var array */
    private $options = [
        'path'  => self::DEFAULT_PATH,
        'ttl'   => self::DEFAULT_VALUE_TTL
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {

        $this->options = array_merge($this->options, $options);

        $this->options['path'] = (!isset($options['path'])) ? sys_get_temp_dir() : $options['path'];

        if (!is_writable($this->options['path'])) {
            throw new AdapterException($this->options['path']." is not writable.");
        }

        $this->path = rtrim($this->options['path'], '/');

    }

    /**
     * @param string $key
     * @return \Psr\Cache\CacheItemInterface
     */
    public function getItem($key)
    {
        $item = new Item($key);

        $file = $this->getFilePath($key);

        if (file_exists($file)) {

            $data = unserialize(file_get_contents($file));
            $expiration = (int) $data['expiration'];

            if ($expiration !== 0 && $expiration < time()) {
                // expired
                return $item;
            }

            $item->set(unserialize($data['value']));
        }

        return $item;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $expiration
     * @return bool
     */
    public function addItem($key, $value, $expiration = null)
    {
        return $this->save($key, $value, $expiration);
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $files = glob($this->path.'/'.self::DEFAULT_FILENAME_PREFIX.'.*.'.self::DEFAULT_FILENAME_EXTENSION);
        $success = true;
        foreach ($files as $file) {
            $success &= @unlink($file);
        }
        return $success;
    }

    /**
     * @param $key
     * @return bool
     */
    public function deleteItem($key)
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            return @unlink($file);
        }

        return false;
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            if (! $this->deleteItem($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expiration
     * @return bool
     */
    public function save($key, $value, $expiration = null)
    {
        if ($expiration === 0 || $expiration === null) {
            $expirationToSet = time() + $this->options['ttl'];
        } else {
            $expirationToSet = $expiration;
        }

        $data = ['expiration' => $expirationToSet, 'value' => serialize($value)];
        $success = file_put_contents($this->getFilePath($key), serialize($data), \LOCK_EX);
        return $success !== false;


    }

    /**
     * @param $key
     * @return string
     */
    private function getFilePath($key)
    {
        return $this->options['path'].'/'.self::DEFAULT_FILENAME_PREFIX.'.'.urlencode($key).'.'.self::DEFAULT_FILENAME_EXTENSION;
    }

}