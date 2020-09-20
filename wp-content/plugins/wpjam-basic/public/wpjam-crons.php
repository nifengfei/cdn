<?php
class WPJAM_Cron{
	private static $crons	= [];

	public static function register($callback, $args=[]){
		if(is_numeric($args)){
			$args	= ['weight'=>$args];
		}

		self::$crons[]	= wp_parse_args($args, [
			'callback'	=> $callback,
			'weight'	=> 1,
			'day'		=> -1
		]);
	}

	public static function get_all(){
		return self::$crons;
	}

	public static function get_callbacks(){
		$crons	= self::$crons;

		$crons	= array_filter($crons, function($cron){
			if($cron['day'] == -1){
				return true;
			}else{
				$day	= (current_time('H') > 2 && current_time('H') < 6) ? 0 : 1;
				return $cron['day']	== $day;
			}
		});
		
		return self::get_callbacks_by_crons($crons);
	}

	private static function get_callbacks_by_crons($crons){
		$callbacks	= [];

		foreach ($crons as $i=> &$cron) {
			if($cron['weight']){
				$callbacks[]	= $cron['callback'];

				if($cron['weight'] <= 1){
					unset($crons[$i]);
				}else{
					$cron['weight'] --;
				}
			}
		}

		if($crons){
			$callbacks	= array_merge($callbacks, self::get_callbacks_by_crons($crons)); 
		}

		return $callbacks;
	}
}

function wpjam_register_cron($callback, $args=[]){
	WPJAM_Cron::register($callback, $args);
}

function wpjam_get_crons(){
	return WPJAM_Cron::get_all();
}

function wpjam_scheduled(){
	if(get_site_transient('wpjam_crons_lock')){
		return;
	}

	set_site_transient('wpjam_crons_lock', 1, 5);

	$callbacks	= WPJAM_Cron::get_callbacks();
	$total		= count($callbacks);

	// trigger_error(var_export($callbacks, true));

	$index		= get_transient('wpjam_crons_index') ?: 0;
	$callback	= $callbacks[$index] ?? '';

	$index		= $index >= $total ? 0 : ($index + 1);
	set_transient('wpjam_crons_index', $index, DAY_IN_SECONDS);

	$today		= date('Y-m-d', current_time('timestamp'));
	$counter	= get_transient('wpjam_crons_counter:'.$today) ?: 0;	
	set_transient('wpjam_crons_counter:'.$today, ($counter+1), DAY_IN_SECONDS);

	if($callback){
		if(is_callable($callback)){
			// trigger_error(var_export($callback, true));
			return call_user_func($callback);
		}else{
			trigger_error('invalid_cron_callback'.var_export($callback, true));
		}
	}

	return true;
}

add_filter('cron_schedules', function($schedules){
	return array_merge($schedules, [
		'five_minutes'		=> ['interval'=>300,	'display'=>'每5分钟一次'],
		'fifteen_minutes'	=> ['interval'=>900,	'display'=>'每15分钟一次'],
	]);
});

add_action('init', function(){
	add_action('wpjam_scheduled',	'wpjam_scheduled');

	if(!wpjam_is_scheduled_event('wpjam_scheduled')){
		$recurrence	= wp_using_ext_object_cache() ? 'five_minutes' : 'fifteen_minutes';
		wp_schedule_event(time(), $recurrence, 'wpjam_scheduled');
	}
});



