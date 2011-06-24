<?php
/**
 * ProGo Themes' Twitter Widget Class
 *
 * This widget is for controlling the "ProGo : Twitter" block
 * modelled after Hybrid theme's widget definitions
 *
 * @since 1.0
 *
 * @package ProGo
 * @subpackage Core
 */

class ProGo_Widget_Tweets extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0
	 */
	function ProGo_Widget_Tweets() {
		$this->prefix = 'progo';
		$this->textdomain = 'progo';

		$widget_ops = array( 'classname' => 'twitter', 'description' => __( 'Pull in latest Tweets.', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-twitter", __( 'ProGo : Twitter', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Twitter') : $instance['title'], $instance, $this->id_base);
		$twitter = strip_tags($instance['twitter']);
		$num = absint($instance['number']);
		$text = strip_tags($instance['follow']);
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		
		if ( ( $twitter != '' ) && ( $num > 0 ) ) { ?>
        <div class="tweets">
		<p class="last"><a href="http://twitter.com/<?php esc_attr_e($twitter); ?>" target="_blank" class="tw"><?php echo wp_kses($text,array('br'=>array(),'em'=>array(),'strong'=>array())); ?></a></p>
        <script type="text/javascript" src="https://api.twitter.com/1/statuses/user_timeline.json?include_rts=true&amp;screen_name=<?php esc_attr_e($twitter); ?>&amp;count=<?php echo $num; ?>&amp;callback=proGoTwitterCallback"></script>
		</div><?php
		}
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 1.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'twitter' => '', 'number' => 1, 'follow' => 'Follow us on Twitter') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['twitter'] = strip_tags($new_instance['twitter']);
		$instance['number'] = absint($new_instance['number']);
		$instance['follow'] = strip_tags($new_instance['follow']);

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'twitter' => '', 'number' => 1, 'follow' => 'Follow us on Twitter') );
		$title = strip_tags($instance['title']);
		$tw = strip_tags($instance['twitter']);
		$num = absint($instance['number']);
		$ft = strip_tags($instance['follow']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('twitter'); ?>"><?php _e('Twitter @name:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" type="text" value="<?php echo esc_attr($tw); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Tweets:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr($num); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('follow'); ?>"><?php _e('"Follow us" text:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('follow'); ?>" name="<?php echo $this->get_field_name('follow'); ?>" type="text" value="<?php echo esc_attr($ft); ?>" /></p>
<?php
	}
}

?>