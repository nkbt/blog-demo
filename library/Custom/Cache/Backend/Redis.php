<?php
class Custom_Cache_Backend_Redis extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{


    /**
     * Log message
     */
    const TAGS_UNSUPPORTED_BY_CLEAN_OF_REDIS_BACKEND = 'Zend_Cache_Backend_Xcache::clean() : tags are unsupported by the Redis backend';
    const TAGS_UNSUPPORTED_BY_SAVE_OF_REDIS_BACKEND = 'Zend_Cache_Backend_Xcache::save() : tags are unsupported by the Redis backend';
    const DEFAULT_SERVER = 'tcp://127.0.0.1';
    const DEFAULT_PORT = 6379;
    const PREFIX_KEY = 'Zend_Cache:';
    const MAX_LIFETIME = 2592000; /* Redis backend limit 2678400 */
    const COMPRESS_PREFIX = ":\x1f\x8b";
    const DEFAULT_CONNECT_TIMEOUT = 2.5;
    /** @var Redis */
    protected $_redis;
    /** @var int */
    protected $_lifetimelimit = self::MAX_LIFETIME; /* Redis backend limit */
    /** @var int */
    protected $_compressData = 0;
    /** @var int */
    protected $_compressThreshold = 20480;
    /** @var string */
    protected $_compressionLib;


    /**
     * Contruct Zend_Cache Redis backend
     *
     * @param array $options
     *
     * @return \Custom_Cache_Backend_Redis
     */
    public function __construct($options = array())
    {

        if (empty($options['server'])) {
            $options['server'] = self::DEFAULT_SERVER;
        }

        $isSocket = substr($options['server'], 0, 1) === '/';
        if (empty($options['port'])) {
            $options['port'] = self::DEFAULT_PORT;
        }

        $registry = isset($options['registry']) ? $options['registry'] : false;

        $timeout = isset($options['timeout']) ? $options['timeout'] : self::DEFAULT_CONNECT_TIMEOUT;

        if ($registry && Zend_Registry::isRegistered($registry)) {
            $this->_redis = Zend_Registry::get($registry);
        } else {
            $this->_redis = new Redis();

            $persistent = isset($options['persistent']) ? $options['persistent'] : false;

            if ($persistent) {
                if ($isSocket) {
                    $this->_redis->pconnect($options['server'], $timeout);
                } else {
                    $this->_redis->pconnect($options['server'], $options['port'], $timeout);
                }
            } else {
                if ($isSocket) {
                    $this->_redis->connect($options['server'], $timeout);
                } else {
                    $this->_redis->connect($options['server'], $options['port'], $timeout);
                }
            }

            if ($registry) {
                Zend_Registry::set($registry, $this->_redis);
            }
        }

        if (!empty($options['password'])) {
            $this->_redis->auth($options['password']) or Zend_Cache::throwException('Unable to authenticate with the redis server.');
        }

        // Always select database on startup in case persistent connection is re-used by other code
        if (empty($options['database'])) {
            $options['database'] = 0;
        }
        $this->_redis->select((int)$options['database']) or Zend_Cache::throwException('The redis database could not be selected.');

        if (isset($options['compress_data'])) {
            $this->_compressData = (int)$options['compress_data'];
        }

        if (isset($options['lifetimelimit'])) {
            $this->_lifetimelimit = (int)min($options['lifetimelimit'], self::MAX_LIFETIME);
        }

        if (isset($options['compress_threshold'])) {
            $this->_compressThreshold = (int)$options['compress_threshold'];
        }

        if (isset($options['automatic_cleaning_factor'])) {
            $this->_options['automatic_cleaning_factor'] = (int)$options['automatic_cleaning_factor'];
        } else {
            $this->_options['automatic_cleaning_factor'] = 0;
        }

        if (isset($options['compression_lib'])) {
            $this->_compressionLib = $options['compression_lib'];
        } else {
            if (function_exists('lzf_compress')) {
                $this->_compressionLib = 'lzf';
            } else {
                $this->_compressionLib = 'gzip';
            }
        }
        $this->_compressPrefix = substr($this->_compressionLib, 0, 2) . self::COMPRESS_PREFIX;
    }


    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id Cache id
     *
     * @return bool|int False if record is not available or "last modified" timestamp of the available cache record
     */
    public function test($id)
    {

        $ttl = $this->_redis->ttl($this->_encodeId($id));

        return $ttl > 0 ? (time() + $ttl) : false;
    }


    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string   $data             Datas to cache
     * @param  string   $id               Cache id
     * @param  array    $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  bool|int $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     *
     * @throws Custom_Cache_Backend_Redis_Exception
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {

        if (count($tags) > 0) {
            $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_REDIS_BACKEND);
        }

        $lifetime = $this->getLifetime($specificLifetime);
        $lifetime = $lifetime ? $lifetime : $this->_lifetimelimit;

        if ($lifetime) {
            $this->_redis->multi();
        }

        if (!$this->_redis->set($this->_encodeId($id), $this->_encodeData($data, $this->_compressData))) {
            throw new Custom_Cache_Backend_Redis_Exception("Could not set cache key $id");
        }

        if ($lifetime) {
            $this->_redis->expire($this->_encodeId($id), $lifetime);
            $this->_redis->exec();
        }


        return true;
    }


    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     *
     * @return boolean True if no problem
     */
    public function remove($id)
    {

        return $this->_redis->del($this->_encodeId($id));
    }


    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => runs _collectGarbage()
     * 'matchingTag'    => not supported
     * 'notMatchingTag' => not supported
     * 'matchingAnyTag' => not supported
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     *
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {


        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_ALL:
                return $this->_redis->flushDb();

                break;
            case Zend_Cache::CLEANING_MODE_OLD:
                $this->_log("Custpm_Cache_Backend_Redis::clean() : CLEANING_MODE_OLD is unsupported by the Redis backend");
                break;

            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                $this->_log(self::TAGS_UNSUPPORTED_BY_CLEAN_OF_REDIS_BACKEND);
                break;

            default:
                Zend_Cache::throwException('Invalid mode for clean() method: ' . $mode);
                break;
        }

        return false;
    }


    /**
     * Return true if the automatic cleaning is available for the backend
     *
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {

        return false;
    }


    /**
     * Set the frontend directives
     *
     * @param  array $directives Assoc of directives
     *
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setDirectives($directives)
    {

        parent::setDirectives($directives);
        $lifetime = $this->getLifetime(false);
        if ($lifetime > self::MAX_LIFETIME) {
            Zend_Cache::throwException('Redis backend has a limit of 30 days (2592000 seconds) for the lifetime');
        }
    }


    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int    $extraLifetime
     *
     * @return boolean true if ok
     */
    public function touch($id, $extraLifetime)
    {

        return $this->_redis->expire($this->_encodeId($id), $extraLifetime);
    }


    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * The array must include these keys :
     * - automatic_cleaning (is automating cleaning necessary)
     * - tags (are tags supported)
     * - expired_read (is it possible to read expired cache records
     *                 (for doNotTestCacheValidity option for example))
     * - priority does the backend deal with priority when saving
     * - infinite_lifetime (is infinite lifetime can work with this backend)
     * - get_list (is it possible to get the list of cache ids and the complete list of tags)
     *
     * @return array associative of with capabilities
     */
    public function getCapabilities()
    {

        return array(
            'automatic_cleaning' => 0,
            'tags' => false,
            'expired_read' => false,
            'priority' => false,
            'infinite_lifetime' => true,
            'get_list' => true,
        );
    }


    /**
     * Load value with given id from cache
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     *
     * @return bool|string
     */
    public function load($id, $doNotTestCacheValidity = false)
    {

        $data = $this->_redis->get($this->_encodeId($id));
        if ($data === null) {
            return false;
        }

        return $this->_decodeData($data);
    }


    /**
     * @param string $data
     * @param int    $level
     *
     * @throws Custom_Cache_Backend_Redis_Exception
     * @return string
     */
    protected function _encodeData($data, $level)
    {

        if ($level && strlen($data) >= $this->_compressThreshold) {
            switch ($this->_compressionLib) {
                case 'lzf':
                    $data = lzf_compress($data);
                    break;
                case 'gzip':
                    $data = gzcompress($data, $level);
                    break;
            }
            if (!$data) {
                throw new Custom_Cache_Backend_Redis_Exception("Could not compress cache data.");
            }

            return $this->_compressPrefix . $data;
        }

        return $data;
    }


    /**
     * @param bool|string $data
     *
     * @return string
     */
    protected function _decodeData($data)
    {

        if (substr($data, 2, 3) == self::COMPRESS_PREFIX) {
            switch (substr($data, 0, 2)) {
                case 'lz':
                    return lzf_decompress(substr($data, 5));
                case 'gz':
                case 'zc':
                    return gzuncompress(substr($data, 5));
            }
        }

        return $data;
    }


    protected function _encodeId($id)
    {

        return self::PREFIX_KEY . str_replace('__', ':', $id);
    }

}