<?php
/*
Plugin Name: Openmat
Plugin URI: //blog.openmat.training/2016/12/28/openmat-wordpress-plugin/
Description: Display your training calendar as a widget.
Version: 1.1
Author: B. Jordan
Author URI: https://openmat.training
License: GPL2
*/

class Openmat_Calendar extends WP_Widget {

	// constructor
	function __construct() {
		$widget_ops = array( 
			'description' => 'Display your training calendar on your blog.',
			'classname' => 'openmat_calendar'
		);
		parent::__construct( 'openmat_calendar', 'Openmat Calendar', $widget_ops );
	}

	// widget form creation
	function form($instance) {
		$title = ! empty( $instance['openmat_calendar_title'] ) ? $instance['openmat_calendar_title'] : esc_html__( 'My schedule', 'text_domain' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'openmat_calendar_title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'openmat_calendar_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'openmat_calendar_title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
    }

	// widget update
	function update($new_instance, $old_instance) {
        $instance = array();
		$instance['openmat_calendar_title'] = ( ! empty( $new_instance['openmat_calendar_title'] ) ) ? strip_tags( $new_instance['openmat_calendar_title'] ) : '';

		return $instance;
	}

	// widget display
	function widget($args, $instance) {
        
        $title = ! empty( $instance['openmat_calendar_title'] ) ? $instance['openmat_calendar_title'] : esc_html__( 'My schedule', 'text_domain' );
        
        $now = time();
        $year = date('Y',$now);
        $month = date('m',$now);
        $day = date('w',$now);
        
        $week_start = date("Y-m-d", strtotime('monday this week'));
        $week_end = date("Y-m-d", strtotime('sunday this week'));
		
        $APIKey = get_option('openmat_api_key');
        $sharedSecret = get_option('openmat_shared_secret');
        $hash =  hash_hmac('sha256', 'events', $sharedSecret);
        
        $response = wp_remote_get('https://openmat.training/api/1.0/events/', 
            array('headers' => 
                  array('Authorization' => "Basic key=\"$APIKey\" signature=\"$hash\"")
            )
        );
        
        $weekly_schedule = array();
        if( is_array($response) ) {
            $header = $response['headers'];
            $body = $response['body'];
            $json_response = json_decode($body);
        
            if (isset($json_response->errors)) {
                foreach ($json_response->errors as $error) {
                    print '<p><strong>' . $error->code. '</strong>: ' . $error->error . '</p>';
                }
            }
            
            if (isset($json_response->events)) {
                foreach ($json_response->events as $event) {
                    
					$event_ts = strtotime($event->year. '-' . $event->month . '-' . $event->day);
					$event_date = date("Y-m-d", $event_ts);
					
                    if ($event_date >= $week_start && $event_date <= $week_end) {
                        $day = (date('l',strtotime($event->year.'/'.$event->month.'/'.$event->day)));
                        $weekly_schedule[$day][] = $event;
                    }
                }
                
            }
        }
        
        $days = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
        
        print $args['before_widget'];
        
        register_plugin_styles();
        
        print '<h2 class="widget-title">' . $title . '</h2>';
        foreach ($days as $day) {
            if (isset($weekly_schedule[$day])) {
                print '<span class="openmat-day-name"><strong>' . $day . '</strong></span>';
                print '<span class="openmat-day">';
                foreach ($weekly_schedule[$day] as $event) {
                    echo '<span class="openmat-event-name">' . $event->name . '</span>';
                }
                print '</span> <!-- .openmat-day -->';
            }
        }
		
        print $args['after_widget'];
        
	} // end widget display
} // end widget class

function register_plugin_styles() {
	wp_register_style( 'openmat', plugins_url( 'css/openmat.css', __FILE__ ) );
	wp_enqueue_style( 'openmat' );
}

// register widget
add_action( 'widgets_init', function(){
	register_widget( 'Openmat_Calendar' );
});

// register menu item
add_action( 'admin_menu', 'openmat_menu' );

function openmat_menu() {
	add_submenu_page('options-general.php','Openmat API Settings', 'Openmat API', 'manage_options', 'openmat-api', 'openmat_plugin_options' );
	add_action( 'admin_init', 'register_openmat_settings' );
}

function register_openmat_settings() {
	register_setting( 'openmat-settings-group', 'openmat_calendar_title' );
	register_setting( 'openmat-settings-group', 'openmat_api_key' );
	register_setting( 'openmat-settings-group', 'openmat_shared_secret' );
}

function openmat_plugin_options() {
    global $wpdb;
    
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    ?>
    
    <div class="wrap">
        <h1>Openmat API settings</h1>
        <p>To generate an API key, visit the <a href="https://openmat.training/settings/developer/" target="_blank">Openmat Developer</a> page.</p>
        
        <form method="post" action="options.php"> 
        
        <?php settings_fields( 'openmat-settings-group' ); ?>
        <?php do_settings_sections( 'openmat-settings-group' ); ?>
            
        <table class="form-table">
            <tr valign="top">
                <th scope="row">API key</th>
                <td><input style="min-width: 320px;" type="text" name="openmat_api_key" value="<?php echo esc_attr( get_option('openmat_api_key') ); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Shared secret</th>
                <td><input style="min-width: 320px;" type="text" name="openmat_shared_secret" value="<?php echo esc_attr( get_option('openmat_shared_secret') ); ?>" /></td>
            </tr>
        </table>
            
        <?php submit_button(); ?>
            
        </form>
    </div>
<?php } ?>