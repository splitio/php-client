<?php
namespace SplitIO\Component\Memory;

use SplitIO\Component\Memory\Exception\OpenSharedMemoryException;
use SplitIO\Component\Memory\Exception\ReadSharedMemoryException;
use SplitIO\Component\Memory\Exception\SupportSharedMemoryException;
use SplitIO\Component\Memory\Exception\WriteSharedMemoryException;

class SharedMemory
{

    private static function checkSHMOPSupport()
    {
        if (!function_exists('shmop_open')) {
            return false;
        }

        return true;
    }

    /**
     * @param $key
     * @param $value
     * @param int $expiration
     * @param int $mode
     * @param int $size
     * @return bool
     * @throws OpenSharedMemoryException
     * @throws SupportSharedMemoryException
     * @throws WriteSharedMemoryException
     */
    public static function write ($key, $value, $expiration=60, $mode=0644, $size=100)
    {
        if (!self::checkSHMOPSupport()) {
            return null;
        }

        $expiration = time() + $expiration;

        $data = json_encode(array('expiration' => $expiration, 'value' => serialize($value)));

        @$shm_id = shmop_open($key, "c", $mode, $size);

        if (!$shm_id) {
            throw new OpenSharedMemoryException("The shared memory block could not be opened");
        }

        @$shm_bytes_written = shmop_write($shm_id, $data, 0);

        if ($shm_bytes_written != strlen($data)) {
            shmop_delete($shm_id);
            shmop_close($shm_id);
            throw new WriteSharedMemoryException("Memory block could not write the entire length of data");
        }

        shmop_close($shm_id);

        return true;
    }

    /**
     * @param $key
     * @param int $mode
     * @param int $size
     * @return mixed|null
     * @throws OpenSharedMemoryException
     * @throws ReadSharedMemoryException
     * @throws SupportSharedMemoryException
     */
    public static function read($key, $mode=0644, $size=100)
    {
        if (!self::checkSHMOPSupport()) {
            return null;
        }

        @$shm_id = shmop_open($key, "a", $mode, $size); //read only

        if (!$shm_id) {
            throw new OpenSharedMemoryException("The shared memory block could not be opened");
        }

        @$cached_string = shmop_read($shm_id, 0, $size);

        if (!$cached_string) {
            shmop_delete($shm_id);
            shmop_close($shm_id);
            throw new ReadSharedMemoryException("The shared memory block could not be read");
        }

        $data = json_decode($cached_string, true);

        if ((isset($data['expiration']) && time() > $data['expiration']) || !isset($data['expiration'])) {
            shmop_delete($shm_id);
            shmop_close($shm_id);
            return null;
        }

        shmop_close($shm_id);
        return unserialize($data['value']);
    }
}
