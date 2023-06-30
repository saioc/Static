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

namespace CHV;
use G, Exception;

class Follow {
	
	static $table_fields = [
		'date',
		'date_gmt',
		'user_id',
		'followed_user_id',
		'ip',
	];
	
	public static function insert($args=[]) {
		self::validateInput($args);
		if(empty($args['ip'])) {
			$args['ip'] = G\get_client_ip();
		}
		$args = array_merge($args, [
			'date'		=> G\datetime(),
			'date_gmt'	=> G\datetimegmt(),
		]);
		// Disable autofollow
		if($args['user_id'] == $args['followed_user_id']) {
			throw new FollowException("Can't auto follow yourself", 403);
		}
		// Get user that will follow followed_user_id
		try {
			$user_db = User::getSingle($args['user_id'], 'id', FALSE);	
		} catch(Exception $e) {
			throw new FollowException($e);
		}
		if(!$user_db) {
			throw new FollowException('Invalid user_id in ' . __METHOD__, 401);
		}
		// Detect if this user already follow this guy
		if(self::doesFollow($args['user_id'], $args['followed_user_id'])) {
			throw new FollowException("User already being followed", 404);
		}
		$db_insert_handle = [];
		foreach(self::$table_fields as $k) {
			$db_insert_handle['fields'][] = DB::getFieldPrefix('follows') . '_' . $k;
			$db_insert_handle['values'][] = '"' . $args[$k] . '"';
		}
		try {
			$db = DB::getInstance();
			$insert_query = "INSERT INTO ".DB::getTable('follows')." (".implode(', ', $db_insert_handle['fields']).") VALUES (".implode(', ', $db_insert_handle['values']).");";
			$db->query($insert_query);
			$exec = $db->exec();
			$follow_id = $db->lastInsertId();
			if($exec) {
				// Trail user counters
				$sql_tpl = 
					'UPDATE `%table_users` SET user_following = user_following + 1 WHERE user_id = %user_id;' . "\n" .
					'UPDATE `%table_users` SET user_followers = user_followers + 1 WHERE user_id = %followed_user_id;';
				$sql = strtr($sql_tpl, [
					'%table_users'	=> DB::getTable('users'),
					'%user_id'	=> $args['user_id'],
					'%followed_user_id'	=> $args['followed_user_id'],
				]);
				try {
					DB::queryExec($sql);
				} catch(Exception $e) {
					throw new Exception($e->getMessage(), 400);
				}
				// Insert notification
				Notification::insert([
					'table'				=> 'follows',
					'user_id'			=> $args['followed_user_id'],
					'trigger_user_id'	=> $args['user_id'],
					'type_id'			=> $follow_id,
				]);
				return self::getFollowersCount($args);
			} else {
				return FALSE;
			}
		} catch(Exception $e) {
			throw new FollowException($e->getMessage(), 400);
		}
	}
	
	public static function delete($args=[]) {
		try {
			if(!is_array($args)) {
				$args = ['id' => $args['id']];
			}
			$follow = self::getSingle($args);
			$delete = DB::delete('follows', $args);
			if($delete) {
				// Trail user counters
				$sql_tpl = 
					'UPDATE `%table_users` SET user_following = user_following - 1 WHERE user_id = %user_id AND user_following > 0;' . "\n" . 
					'UPDATE `%table_users` SET user_followers = user_followers - 1 WHERE user_id = %followed_user_id AND user_followers > 0;';
				$sql = strtr($sql_tpl, [
					'%table_users'	=> DB::getTable('users'),
					'%user_id'	=> $args['user_id'],
					'%followed_user_id'	=> $args['followed_user_id'],
				]);
				try {
					DB::queryExec($sql);
				} catch(Exception $e) {
					throw new Exception($e->getMessage(), 400);
				}
				// Remove notifications related to this follow
				Notification::delete([
					'table'			=> 'follows',
					'user_id'		=> $follow['followed_user_id'],
					'type_id'		=> $follow['id'],
				]);
				return self::getFollowersCount($args);
			} else {
				return FALSE;
			}
		} catch(Exception $e) {
			throw new FollowException($e->getMessage(), 400);
		}
	}
	
	public static function doesFollow($user_id, $followed_id) {
		if(is_null($user_id)) return FALSE;
		$follow = DB::get('follows', ['user_id' => $user_id, 'followed_user_id' => $followed_id])[0];
		return $follow ? TRUE :  FALSE; // DB::formatRow($follow)
	}
	
	public static function getFollowersCount($args=[]) {
		self::validateInput($args);
		$user = User::getSingle($args['followed_user_id']);
		return G\array_filter_array($user, ['id', 'id_encoded', 'username', 'following', 'followers'], 'exclusion');
	}
	
	// Get a single content follow
	public static function getSingle($args=[]) {
		try {
			$follow = self::get($args, NULL, 1);
			return $follow ?: NULL;
		} catch(Exception $e) {
			throw new FollowException($e->getMessage(), 400);
		}
	}
	
	// Get all content follow 
	public static function getAll($args=[], $sort=[]) {
		try {
			$follow = self::get($args, $sort, NULL);
			return $follow ?: NULL;
		} catch(Exception $e) {
			throw new FollowException($e->getMessage(), 400);
		}
	}
	
	// Get core
	public static function get($args, $sort=[], $limit=NULL) {
		try {
			$get = DB::get('follows', $args, 'AND', $sort, $limit);
			return DB::formatRows($get);
		} catch(Exception $e) {
			throw new FollowException($e->getMessage(), 400);
		}
	}
	
	protected static function validateInput($args=[]) {
		if(!is_array($args)) {
			throw new FollowException('Expecting array, '.gettype($args).' given in ' . __METHOD__, 100);
		}
		if(empty($args['user_id'])) {
			throw new FollowException('Missing user_id in ' . __METHOD__, 101);
		}
		if(empty($args['followed_user_id'])) {
			throw new FollowException('Missing followed_user_id in ' . __METHOD__, 102);
		}
	}
}

class FollowException extends Exception {}