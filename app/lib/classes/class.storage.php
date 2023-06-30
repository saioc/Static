<?php

/* --------------------------------------------------------------------

  Chevereto
  http://chevereto.com/

  @author	Rodolfo Berrios A. <http://rodolfoberrios.com/>
            <inbox@rodolfoberrios.com>

  Copyright (C) Rodolfo Berrios A. All rights reserved.

  BY USING THIS SOFTWARE YOU DECLARE TO ACCEPT THE CHEVERETO EULA
  http://chevereto.com/license

  --------------------------------------------------------------------- */

/*
Local Storage
- URL (ok)
- path ?absolute/relative
- size limit
*/

namespace CHV;

use G;
use Exception;

class Storage
{
    protected static $apis = [
        10 => [
            'name' => 'Alibaba Cloud OSS',
            'type' => 'oss',
            'url' => 'https://<bucket>.<endpoint>/',
        ],
        1 => [
            'name' => 'Amazon S3',
            'type' => 's3',
            'url' => 'https://s3.amazonaws.com/<bucket>/',
        ],
        11 => [
            'name' => 'Backblaze B2',
            'type' => 'b2',
            'url' => 'https://f002.backblazeb2.com/file/<bucket>/',
        ],
        5 => [
            'name' => 'FTP',
            'type' => 'ftp',
            'url' => null,
        ],
        2 => [
            'name' => 'Google Cloud',
            'type' => 'gcloud',
            'url' => 'https://storage.googleapis.com/<bucket>/',
        ],
        8 => [
            'name' => 'Local',
            'type' => 'local',
            'url' => null,
        ],
        3 => [
            'name' => 'Microsoft Azure',
            'type' => 'azure',
            'url' => 'https://<accoount>.blob.core.windows.net/<container>/',
        ],
        7 => [
            'name' => 'OpenStack',
            'type' => 'openstack',
            'url' => null,
        ],
        9 => [
            'name' => 'S3 compatible',
            'type' => 's3compatible',
            'url' => null,
        ],
        6 => [
            'name' => 'SFTP',
            'type' => 'sftp',
            'url' => null,
        ],
    ];

    public static function getSingle($var)
    {
        try {
            $storage = self::get(['id' => $var], [], 1);

            return $storage ?: null;
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), 400);
        }
    }

    public static function getAll($args = [], $sort = [])
    {
        try {
            $storage = self::get($args, $sort, null);

            return $storage ?: null;
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), 400);
        }
    }

    public static function get($values = [], $sort = [], $limit = null)
    {
        try {
            $get = DB::get(['table' => 'storages', 'join' => 'LEFT JOIN '.DB::getTable('storage_apis').' ON '.DB::getTable('storages').'.storage_api_id = '.DB::getTable('storage_apis').'.storage_api_id'], $values, 'AND', $sort, $limit);
            if ($get[0]) {
                foreach ($get as $k => $v) {
                    self::formatRowValues($get[$k], $v);
                }
            } else {
                if ($get) {
                    self::formatRowValues($get);
                }
            }

            return $get;
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), 400);
        }
    }

    protected static function requiredByApi($api_id)
    {
        $required = ['api_id', 'bucket'];
        $type = self::getApiType($api_id);
        if ($type != 'local') {
            array_push($required, 'secret');
            if ($type != 'gcloud') {
                array_push($required, 'key');
            }
        }

        return $required;
    }

    public static function uploadFiles($targets, $storage, $options = [])
    {
        try {
            $keyprefix = $options['keyprefix'];
            if (!is_array($storage)) {
                $storage = self::getSingle($storage);
            } else {
                foreach (self::requiredByApi($storage['api_id']) as $k) {
                    if (!isset($storage[$k])) {
                        throw new Exception('Missing '.$k.' value in '.__METHOD__, 100);
                        break;
                    }
                }
            }

            if (!array_key_exists('api_type', $storage)) {
                $storage['api_type'] = self::getApiType($storage['api_id']);
            }

            $API = self::requireAPI($storage);

            $files = [];
            if ($targets['file']) {
                $files[] = $targets;
            } else {
                if (!is_array($targets)) {
                    $files = ['file' => $targets, 'filename' => $targets];
                } else {
                    $files = $targets;
                }
            }

            $disk_space_used = 0;
            $cache_control = 'public, max-age=31536000'; // Just like imgur

            // Upload the image chain
            foreach ($files as $k => $v) {
                $source_file = $v['file'];
                if (in_array($storage['api_type'], ['s3', 's3compatible', 'b2', 'azure', 'oss'])) {
                    switch ($storage['api_type']) {
                        case 'oss':
                            $source_file = file_get_contents($v['file']);
                        break;
                        default:
                            $source_file = @fopen($v['file'], 'r');
                        break;
                    }
                    if (!$source_file) {
                        throw new Exception('Failed to open file stream', 100);
                    }
                    $urn = $keyprefix.$v['filename'];
                }
                switch ($storage['api_type']) {
                    case 's3':
                    case 's3compatible':
                        $API->putObject([
                            'Bucket' => $storage['bucket'],
                            'Key' => $urn,
                            'Body' => $source_file,
                            'ACL' => 'public-read',
                            'CacheControl' => $cache_control,
                        ]);
                    break;

                    case 'azure':
                        $blobOptions = new \MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions();
                        $blobOptions->setContentType($v['mime']);
                        $API->createBlockBlob($storage['bucket'], $urn, $source_file, $blobOptions);
                    break;

                    case 'b2':
                        $API->upload([
                            'BucketName' => $storage['bucket'],
                            'FileName' => $urn,
                            'Body' => $source_file,
                        ]);
                    break;

                    case 'oss':
                        $API->putObject($storage['bucket'], $urn, $source_file);
                    break;

                    case 'gcloud':
                        // https://github.com/xown/gaufrette-gcloud/blob/master/src/Gaufrette/Adapter/GCloudStorage.php
                        $source_file = @file_get_contents($v['file']);
                        if (!$source_file) {
                            throw new Exception('Failed to open file stream', 100);
                        }

                        // Initiate Google object storage
                        $gc_obj = new \Google_Service_Storage_StorageObject();
                        $gc_obj->setName($keyprefix.$v['filename']);
                        $gc_obj->setAcl('public-read');
                        $gc_obj->setCacheControl($cache_control);

                        // Insert the object
                        $API->objects->insert($storage['bucket'], $gc_obj, [
                            'mimeType' => G\get_mimetype($v['file']),
                            'uploadType' => 'multipart',
                            'data' => $source_file,
                        ]);

                        // Set this as a public object
                        $gc_obj_acl = new \Google_Service_Storage_ObjectAccessControl();
                        $gc_obj_acl->setEntity('allUsers');
                        $gc_obj_acl->setRole('READER');
                        $API->objectAccessControls->insert($storage['bucket'], $gc_obj->name, $gc_obj_acl);
                    break;

                    case 'ftp':
                    case 'sftp':
                    case 'local':
                        // keyprefix = datefolders
                        $target_path = ($API instanceof LocalStorage ? $API->realPath : $storage['bucket']).$keyprefix;
                        if ($keyprefix != null) {
                            $API->mkdirRecursive($keyprefix);
                        }
                        $API->put([
                            'filename' => $v['filename'],
                            'source_file' => $source_file,
                            'path' => $target_path,
                        ]);
                        if (!$API instanceof LocalStorage) {
                            $API->chdir($storage['bucket']); // Reset pointer
                        }
                    break;

                    case 'openstack':
                        $source_file = @fopen($v['file'], 'r');
                        if (!$source_file) {
                            throw new Exception('Failed to open file stream', 100);
                        }
                        $container = $API->getContainer($storage['bucket']);
                        $container->uploadObject($keyprefix.$v['filename'], $source_file, ['Cache-Control' => $cache_control]);
                    break;
                }

                $filesize = @filesize($v['file']);
                if (!$filesize) {
                    error_log("Can't get filesize for ".$v['file'].' at Storage::upload');
                } else {
                    $disk_space_used += $filesize;
                }

                $files[$k]['stored_file'] = $storage['url'].$keyprefix.$v['filename'];
            }

            // Close the FTP/SFTP once is done
            if (in_array($storage['api_type'], ['ftp', 'sftp']) && is_object($API)) {
                $API->close();
            }

            // Update the storage usage
            DB::increment('storages', ['space_used' => '+'.$disk_space_used], ['id' => $storage['id']]);

            // Update the settings table (last storage used)
            DB::update('settings', ['value' => $storage['id']], ['name' => 'last_used_storage']);

            return $files;
        } catch (Exception $e) {
            error_log($e);
            throw new StorageException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete files from the external storage (using queues).
     *
     * @param $targets mixed (key, single array key, multiple array key)
     * @param $storage mixed (storage id, storage array)
     */
    public static function deleteFiles($targets, $storage)
    {
        try {
            if (!is_array($storage)) {
                $storage = Storage::getSingle($storage);
            } else {
                foreach (self::requiredByApi($storage['api_id']) as $k) {
                    if (!isset($storage[$k])) {
                        throw new Exception('Missing '.$k.' value in '.__METHOD__, 100);
                        break;
                    }
                }
            }

            $files = [];
            if ($targets['key']) {
                $files[] = $targets;
            } else {
                if (!is_array($targets)) {
                    $files = ['key' => $targets];
                } else {
                    $files = $targets;
                }
            }

            // Localize the array 'key'
            foreach ($files as $k => $v) {
                $files[$v['key']] = $v;
                $storage_keys[] = $v['key'];
                unset($files[$k]);
            }

            $deleted = [];
            $disk_space_freed = 0;

            if ($storage['id']) { // Storage already exist
                for ($i = 0; $i < count($storage_keys); ++$i) {
                    $queue_args = [
                        'key' => $storage_keys[$i],
                        'size' => $files[$storage_keys[$i]]['size'],
                    ];
                    Queue::insert(['type' => 'storage-delete', 'args' => json_encode($queue_args), 'join' => $storage['id']]);
                    $deleted[] = $v; // Just for CHV::DB, the real thing will be deleted in the queue
                }
            } else { // We are just testing the thing with a non-existent storage (DB)
                self::deleteObject($storage_keys[0], $storage);
                $deleted[] = $storage_keys[0];
            }

            // Return the array of queued delete files (keys)
            return count($deleted) > 0 ? $deleted : false;
        } catch (Exception $e) {
            error_log($e);
            throw new StorageException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete a single file from the external storage.
     *
     * @param $key a representation of the object (file) to delete relative to the bucket
     * @param $storage array with storage connection info
     */
    public static function deleteObject($key, $storage)
    {
        $storage_type = self::getApiType($storage['api_id']);
        $API = self::requireAPI($storage);
        switch (self::getApiType($storage['api_id'])) {
            case 's3':
            case 's3compatible':
                $API->deleteObject([
                    'Bucket' => $storage['bucket'],
                    'Key' => $key,
                ]);
            break;
            case 'b2':
                $API->deleteFile([
                    'BucketName' => $storage['bucket'],
                    'FileName' => $key,
                ]);
            break;
            case 'azure':
                $API->deleteBlob($storage['bucket'], $key);
            break;
            case 'oss':
                $API->deleteObject($storage['bucket'], $key);
            break;
            case 'gcloud':
                $API->objects->delete($storage['bucket'], $key);
            break;
            case 'ftp':
            case 'sftp':
            case 'local':
                $API->delete($key);
            break;
            case 'openstack':
                $container = $API->getContainer($storage['bucket']);
                try {
                    $object = $container->getObject($key);
                } catch (Exception $e) {
                } // Silence
                if ($object) {
                    $object->delete();
                }
            break;
        }
    }

    // Test the target storage with a test file upload
    public static function test($storage)
    {
        try {
            $datetime = preg_replace('/(.*)_(\d{2}):(\d{2}):(\d{2})/', '$1_$2h$3m$4s', G\datetimegmt('Y-m-d_h:i:s'));
            $filename = 'Chevereto_test_'.$datetime.'.png';
            $file = CHV_APP_PATH_CONTENT_SYSTEM.'favicon.png';
            self::uploadFiles([
                'file' => $file,
                'filename' => $filename,
                'mime' => 'image/png',
            ], $storage);
            self::deleteFiles(['key' => $filename, 'size' => @filesize($file)], $storage);
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), 400);
        }
    }

    // Insert new storage
    public static function insert($values)
    {
        try {
            if (!is_array($values)) {
                throw new Exception('Expecting array values, '.gettype($values).' given in '.__METHOD__, 100);
            }
            $required = ['name', 'api_id', 'key', 'secret', 'bucket', 'url']; // Global
            $required_by_api = [
                's3' => ['region'],
                's3compatible' => ['region', 'server'],
                'oss' => ['server'],
                'ftp' => ['server'],
                'sftp' => ['server'],
            ];
            $storage_api = self::getApiType($values['api_id']);
            if ($storage_api == 'local') {
                unset($required[2], $required[3]); //  key, secret
            }
            if ($storage_api == 'gcloud') {
                // 2.2.2
                $values['secret'] = trim($values['secret']);
                unset($required[2]); // key //  TODO: WEA
            }
            // Meet the requirements by each storage API
            if (array_key_exists('api_id', $values) && array_key_exists(self::getApiType($values['api_id']), $required_by_api)) {
                foreach ($required_by_api[$storage_api] as $k => $v) {
                    $required[] = $v;
                }
            }
            foreach ($required as $v) {
                if (!G\check_value($values[$v])) {
                    throw new Exception("Missing $v value in ".__METHOD__, 101);
                }
            }
            // Validate each value (global thing)
            $validations = [
                'api_id' => [
                    'validate' => is_numeric($values['api_id']),
                    'message' => 'Expecting integer value for api_id, '.gettype($values['api_id']).' given in '.__METHOD__,
                    'code' => 102,
                ],
                'url' => [
                    'validate' => G\is_url($values['url']),
                    'message' => 'Invalid storage URL given in '.__METHOD__,
                    'code' => 103,
                ],
            ];
            // nota: add n => regions
            foreach ($validations as $k => $v) {
                if (!$v['validate']) {
                    throw new Exception($v['message'], $v['code']);
                }
            }

            // Pretty URL
            $values['url'] = G\add_ending_slash($values['url']);

            self::formatValues($values);

            // Test the thing
            try {
                self::test($values);
            } catch (Exception $e) {
                throw new Exception(_s("Can't insert storage.").' Error: '.$e->getMessage().' - check your input parameters', 500);
            }

            // OK
            return DB::insert('storages', $values);
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), $e->getCode());
        }
    }

    public static function update($id, $values)
    {
        try {
            $storage = self::getSingle($id);
            if (!$storage) {
                throw new Exception("Storage ID:$id doesn't exists", 100);
            }
            // Workaround the URL
            if (isset($values['url'])) {
                if (!G\is_url($values['url'])) {
                    if (!$storage['url']) {
                        throw new Exception('Missing storage URL in '.__METHOD__, 100);
                    } else {
                        unset($values['url']);
                    }
                } else {
                    $values['url'] = G\add_ending_slash($values['url']);
                }
            } else {
                //$values['url'] = $storage['url'];
            }

            self::formatValues($values, 'null');

            // Valid capacity?
            if (array_key_exists('capacity', $values) && !empty($values['capacity']) && $values['capacity'] < $storage['space_used']) {
                throw new Exception(_s("Storage capacity can't be lower than its current usage (%s).", G\format_bytes($storage['space_used'])), 101);
            }

            // All the values
            $new_values = array_merge($storage, $values);

            // Test the credendials if needed
            $test_credentials = false;

            foreach (['key', 'secret', 'bucket', 'region', 'server', 'account_id', 'account_name'] as $v) {
                if (isset($values[$v]) and $values[$v] !== $storage[$v]) {
                    $test_credentials = true;
                    break;
                }
            }
            if ($test_credentials or $values['is_active'] == 1) {
                try {
                    self::test($new_values);
                } catch (Exception $e) {
                    throw new Exception(_s("Can't update storage details.").' Error: '.$e->getMessage(), 500);
                }
            }

            /// De-activate anything else // deprecated
            /*
            if(isset($values['is_active'])) {
                $activate_this = $values['is_active'] == 1;
                if($activate_this) {
                    DB::update('storages', ['is_active' => 0], ['is_active' => 1]);
                }
                DB::update('settings', ['value' => $activate_this ? $id : NULL], ['name' => 'active_storage']);
            }
            */

            return DB::update('storages', $values, ['id' => $id]);
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), $e->getCode());
        }
    }

    // What about delete the storage and all its contents?
    /*
    public static function delete($values, $clause='AND') {
        try {
            return DB::delete('storages', $values, $clause);
        } catch(Exception $e) {
            throw new StorageException($e->getMessage(), 400);
        }
    }
    */

    public static function requireAPI($storage)
    {
        $api_type = self::getApiType($storage['api_id']);

        switch ($api_type) {
            case 's3':
            case 's3compatible':
                $factoria = [
                    'version' => '2006-03-01',
                    'region' => $storage['region'],
                    'command.params' => ['PathStyle' => true],
                    'credentials' => [
                        'key' => $storage['key'],
                        'secret' => $storage['secret'],
                    ],
                    'http' => [
                        'verify' => CHV_APP_PATH_LIB_VENDOR.'ca-bundle.crt',
                    ],
                ];
                if ($api_type == 's3compatible') {
                    $factoria['endpoint'] = $storage['server'];
                }

                return \Aws\S3\S3Client::factory($factoria);
            break;

            case 'azure':
                $connectionString = 'DefaultEndpointsProtocol=https;AccountName='.$storage['key'].';AccountKey='.$storage['secret'];
                if ($storage['server']) {
                    $connectionString .= ';BlobEndpoint='.$storage['server'];
                }
                try {
                    return \MicrosoftAzure\Storage\Blob\BlobRestProxy::createBlobService($connectionString);
                } catch (\WindowsAzure\Common\ServiceException $e) {
                    throw new StorageException('Azure storage client connect error: '.$e->getMessage());
                }
            break;

            case 'b2':
                try {
                    // key: account id
                    // secret: master application key
                    return new \esac\B2\Client($storage['key'], ['applicationKey' => $storage['secret']]);
                } catch (\esac\B2\B2Exception $e) {
                    throw new StorageException('Backblaze B2 storage client connect error: '.$e->getMessage());
                }
            break;

            case 'oss':
                try {
                    return new \OSS\OssClient($storage['key'], $storage['secret'], $storage['server']);
                } catch (\OSS\Core\OssException $e) {
                    throw new StorageException('Alibaba storage client connect error: '.$e->getMessage());
                }
            break;

            case 'gcloud':
                try {
                    $client = new \Google_Client();
                    $client->setApplicationName('Chevereto Google Cloud Storage');
                    $client->addScope('https://www.googleapis.com/auth/devstorage.full_control');
                    $credentials = json_decode(trim($storage['secret']), true);
                    $client->setAuthConfig($credentials);
                    $client->fetchAccessTokenWithAssertion();
                    if (!$client->getAccessToken()) {
                        throw new Exception('No access token');
                    }

                    return new \Google_Service_Storage($client);
                } catch (Exception $e) {
                    throw new StorageException('Google cloud storage client connect error: '.$e->getMessage());
                }

                return $client;
            break;

            case 'ftp':
            case 'sftp':
                $class = 'CHV\\'.ucfirst($api_type);

                return new $class([
                    'server' => $storage['server'],
                    'user' => $storage['key'],
                    'password' => $storage['secret'],
                    'path' => $storage['bucket'],
                ]);
            break;

            case 'openstack':
                $credentials = [
                    'username' => $storage['key'],
                    'password' => $storage['secret'],
                ];
                foreach (['id', 'name'] as $k) {
                    if (isset($storage['account_'.$k])) {
                        $credentials['tenant'.ucfirst($k)] = $storage['account_'.$k];
                    }
                }
                $client = new \OpenCloud\OpenStack($storage['server'], $credentials);

                return $client->objectStoreService($storage['service'] ?: 'swift', $storage['region'] ?: null, 'publicURL'); // Service
            break;

            case 'local':
                return new LocalStorage($storage);
            break;
        }
    }

    // Get storage service regions
    public static function getAPIRegions($api)
    {
        $regions = [
            's3' => [
                'us-east-1' => 'US East (N. Virginia)',
                'us-east-2' => 'US East (Ohio)',
                'us-west-1' => 'US West (N. California)',
                'us-west-2' => 'US West (Oregon)',

                'ca-central-1' => 'Canada (Central)',

                'ap-south-1' => 'Asia Pacific (Mumbai)',
                'ap-northeast-2' => 'Asia Pacific (Seoul)',
                'ap-southeast-1' => 'Asia Pacific (Singapore)',
                'ap-southeast-2' => 'Asia Pacific (Sydney)',
                'ap-northeast-1' => 'Asia Pacific (Tokyo)',

                'eu-central-1' => 'EU (Frankfurt)',
                'eu-west-1' => 'EU (Ireland)',
                'eu-west-2' => 'EU (London)',
                'eu-west-3' => 'EU (Paris)',

                'sa-east-1' => 'South America (Sao Paulo)',
            ],
        ];
        foreach ($regions['s3'] as $k => &$v) {
            $s3_subdomain = 's3'.($k !== 'us-east-1' ? ('-'.$k) : null);
            $v = [
                'name' => $v,
                'url' => 'https://'.$s3_subdomain.'.amazonaws.com/',
            ];
        }

        return $regions[$api];
    }

    // Get the API type by providing the API_ID
    public static function getApiType($api_id)
    {
        return self::$apis[$api_id]['type'];
    }

    // Get a valid name to be used in the target storage
    public static function getStorageValidFilename($filename, $storage_id, $filenaming, $destination)
    {
        if ($filenaming == 'id') {
            return $filename;
        }

        $extension = G\get_file_extension($filename);

        for ($i = 0; $i < 25; ++$i) {
            if ($i == 0) {
                $filenaming = $filenaming;
            } elseif ($i < 5 && $i < 15) {
                $filenaming = $filenaming == 'random' ?: 'mixed';
            } elseif ($i > 15) {
                $filenaming = 'random';
            }
            $filename_by_method = G\get_filename_by_method($filenaming, $filename);
            $wanted_names[] = G\get_basename_without_extension($filename_by_method);
        }

        $stock_qry = 'SELECT DISTINCT image_name, image_id FROM '.DB::getTable('images').' WHERE image_storage_id=:image_storage_id AND image_extension=:image_extension AND image_name IN('.'"'.implode('","', $wanted_names).'"'.') ';
        $stock_binds = [
            'storage_id' => $storage_id,
            'extension' => $extension,
        ];

        // Datefolder storage?
        $datefolder = rtrim(preg_replace('#'.CHV_PATH_IMAGES.'#', null, $destination, 1), '/'); // Destination datefolder?
        if (preg_match('#\d{4}\/\d{2}\/\d{2}#', $datefolder)) {
            $datefolder = str_replace('/', '-', $datefolder);
            $stock_qry .= 'AND DATE(image_date)=:image_date ';
            $stock_binds['date'] = $datefolder;
        }
        $stock_qry .= 'ORDER BY image_id DESC;';

        try {
            $taken_names = [];
            $db = DB::getInstance();
            $db->query($stock_qry);
            foreach ($stock_binds as $k => $v) {
                $db->bind(':image_'.$k, $v);
            }
            $images_stock = $db->fetchAll();
            foreach ($images_stock as $k => $v) {
                $taken_names[] = $v['image_name'];
            }
        } catch (Exception $e) {
        }

        // Name taken
        if (count($taken_names) > 0) {
            foreach ($wanted_names as $candidate) {
                if (in_array($candidate, $taken_names)) {
                    continue;
                }
                $return = $candidate;
                break;
            }
        } else {
            $return = $wanted_names[0];
        }

        return $return ? ($return.'.'.$extension) : self::getStorageValidFilename($filename, $storage_id, $filenaming);
    }

    public static function getApis()
    {
        // Amazon SDK needs PHP >= 5.5.0
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            self::$apis[1] = array_merge(self::$apis[1], [
                'disabled' => true,
                'disabled_msg' => _s('requires %s', 'PHP >= 5.5.0'),
            ]);
        }

        return self::$apis;
    }

    // Always match the right thing
    protected static function formatValues(&$values, $junk = 'keep')
    {
        // Capacity as bytes
        if (array_key_exists('capacity', $values)) {
            G\nullify_string($values['capacity']);
            if (!is_null($values['capacity'])) {
                $values['capacity'] = G\get_bytes($values['capacity']);
                if (!is_numeric($values['capacity'])) { // G\get_bytes returns FLOAT
                    throw new StorageException('Invalid storage capacity value. Make sure to use a valid format.', 100);
                }
            }
        }

        // Workaround the https thing
        if (array_key_exists('is_https', $values)) {
            if (!$values['url']) {
                $values['url'] = $storage['url'];
            }
            $protocol_stock = ['http', 'https'];
            if ($values['is_https'] != 1) {
                $protocol_stock = array_reverse($protocol_stock);
            }
            $values['url'] = preg_replace('#^https?://#', '', $values['url'], 1); // Remove protocol
            $values['url'] = $protocol_stock[1].'://'.$values['url'];
        } elseif (array_key_exists('url', $values)) {
            $values['is_https'] = (int) G\is_https($values['url']);
        }

        // Always use a neat path for S?FTP
        if (in_array(self::getApiType($values['api_id']), ['ftp', 'sftp']) and isset($values['bucket'])) {
            $values['bucket'] = G\add_ending_slash($values['bucket']);
        }

        // Get rid of some junk
        if (in_array($junk, ['null', 'remove']) and array_key_exists('api_id', $values)) {
            $junk_values_by_api = [
                1 => ['server'],
                5 => ['region'],
            ];
            if (array_key_exists('api_id', $junk_values_by_api)) {
                switch ($junk) {
                    case 'null':
                        foreach ($junk_values_by_api[$values['api_id']] as $k => $v) {
                            $values[$v] = null;
                        }
                    break;
                    case 'remove':
                        $values = G\array_filter_array($values, $junk_values_by_api[$values['api_id']], 'rest');
                    break;
                }
            }
        }
    }

    // Format get row return
    protected static function formatRowValues(&$values, $row = [])
    {
        $values = DB::formatRow(count($row) > 0 ? $row : $values);
        $values['url'] = G\is_url($values['url']) ? G\add_ending_slash($values['url']) : null;
        $values['usage_label'] = ($values['capacity'] == 0 ? _s('Unlimited') : G\format_bytes($values['capacity'], 2)).' / '.G\format_bytes($values['space_used'], 2).' '._s('used');
    }

    public static function regenStorageStats($storageId)
    {
        $storage = Storage::getSingle($storageId);
        if ($storage == false) {
            throw new Exception(sprintf("Error: Storage id %s doesn't exists", $storageId));
        }
        $query = 'UPDATE '.DB::getTable('storages').' SET storage_space_used = (SELECT IFNULL(SUM(image_size) + SUM(image_thumb_size) + SUM(image_medium_size),0) FROM '.DB::getTable('images').' WHERE image_storage_id = :storageId) WHERE storage_id = :storageId';
        $db = DB::getInstance();
        $db->query($query);
        if ($storageId != 0) {
            $db->bind(':storageId', $storageId);
        }
        $db->exec();

        return sprintf('Storage %s stats re-generated', $storageId != 0 ? ('"'.$storage['name'].'" ('.$storage['id'].')') : 'local');
    }

    public static function migrateStorage($sourceStorageId, $targetStorageId)
    {
        if ($sourceStorageId == $targetStorageId) {
            throw new Exception(sprintf('You have to provide two different storage ids (same id %s provided)', $sourceStorageId));
        }
        $sourceStorage = $sourceStorageId == 0 ? 'local' : Storage::getSingle($sourceStorageId);
        $targetStorage = $targetStorageId == 0 ? 'local' : Storage::getSingle($targetStorageId);
        $error_message = ["Storage id %s doesn't exists", "Storage ids %s doesn't exists"];
        $error = [];
        foreach (['source', 'target'] as $v) {
            $object = $v;
            $prop = $v.'Storage';
            $id = $prop.'Id';
            if ($$prop == false) {
                array_push($error, $$id);
            } else {
                if (is_array($$prop) == false) {
                    $$prop = ['name' => $$prop, 'type' => $$prop, 'api_type' => $$prop];
                }
            }
        }
        if ($error) {
            throw new Exception(str_replace('%s', implode(', ', $error), $error_message[count($error) - 1]));
        }
        $db = DB::getInstance();
        $query = 'UPDATE '.DB::getTable('images').' SET image_storage_id = :targetStorageId WHERE ';
        // local (null) -> external
        if ($sourceStorageId == 0) {
            $query .= 'ISNULL(image_storage_id)';
        // external -> external
        } else {
            $query .= 'image_storage_id = :sourceStorageId';
        }
        $db->query($query);
        if ($sourceStorageId != 0) {
            $db->bind(':sourceStorageId', $sourceStorageId);
        }
        $db->bind(':targetStorageId', $targetStorageId == 0 ? null : $targetStorageId);
        $db->exec();
        $rowCount = $db->rowCount();
        if ($rowCount > 0) {
            $return = [];
            if ($sourceStorageId != 0) {
                array_push($return, static::regenStorageStats($sourceStorageId));
            }
            if ($targetStorageId != 0) {
                array_push($return, static::regenStorageStats($targetStorageId));
            }
            array_unshift($return, strtr('OK: %s image(s) migrated from "%source" to "%target"', [
                '%s' => $rowCount,
                '%source' => $sourceStorage['name'],
                '%target' => $targetStorage['name'],
            ]));

            return implode(' - ', $return);
        } else {
            throw new Exception('No images to migrate');
        }
    }
}

class StorageException extends Exception
{
}
