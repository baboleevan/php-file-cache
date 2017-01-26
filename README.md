# php-file-cache
PHP File Cache

## How to use
1. require_once 'user/cache/path/cache.php';
2. $cache = new cache();
3. $cache->set('cache id','cache data','ttl');  // ttl second
4. $cache->get('cache id');
5. $cache->delete('cache id');
6. $cache->clean('cache sub folder',true);  // true (all delete) / false (time over delete)
