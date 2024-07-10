<?php

$REDIS_HOST='redis';

function ping_redis() {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
	if ($redis->ping()) return true;
	else return false;
}

function check_redis($group="webin") {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
	if ($redis->ping()) {
		$members = $redis->sMembers($group); // redis-cli -h safebox-redis smembers generated
		//print_r($members);

		foreach ($members as $member) {
			$value = $redis->get($member);
			$json_data = base64_decode($value);
			$data = json_decode($json_data);
			if ($data === null) {
				echo "JSON read error...";
				// TODO json error
			}
			else {
			}
		}
	}
}

function redis_get($key) {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
	if ($redis->ping()) {
		//$arList = $redis->keys("*"); // ? redis-cli -h safebox-redis keys "*" 
		//echo "Stored keys in redis:";
		//print_r($arList);
		if ($redis->exists($key)) {
			$value = $redis->get($key);
			//redis-cli -h safebox-redis get $key
			return base64_decode($value);
		} else {
			echo "Key does not exist: $key";
			// TODO
		}
	}
}

function redis_set($key, $value) {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
//	$redis->auth('password');
	if ($redis->ping()) {
		if (!$redis->exists($key)) {
			//redis-cli -h redis set $key "$value"
			//redis-cli -h redis sadd webout $key
			//redis-cli -h redis smembers webout
			$redis->set($key, base64_encode($value));
			$redis->sAdd('webout', $key);
		} else {
			echo "Key already exist: $key";
		}
	}
}

function redis_remove($key) {

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
//	$redis->auth('password');
	if ($redis->ping()) {
		//redis-cli -h redis srem webin $key
		//redis-cli -h redis del $key
		$redis->srem("webin", $key);
		$redis->del($key);
	}
}

?>
