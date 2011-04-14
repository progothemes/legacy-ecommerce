<?php
/**
 * ProGo Themes' Share Widget Class
 *
 * This widget is for positioning/removing the "Share" block on Direct Response pages.
 * modelled after Hybrid theme's widget definitions
 *
 * @since 1.0.57
 *
 * @package ProGo
 * @subpackage Direct
 */

class ProGo_Widget_Share extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0.57
	 */
	function ProGo_Widget_Share() {
		$this->prefix = 'progo';
		$this->textdomain = 'progo';

		$widget_ops = array( 'classname' => 'share', 'description' => __( 'Automatically includes the ShareThis chiclet if the plugin is installed.', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-share", __( 'ProGo : Share', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0.57
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Share') : $instance['title'], $instance, $this->id_base);
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			
		if (function_exists('sharethis_button')) {
			sharethis_button();
		} else { ?>
        <a name="fb_share" type="icon" href="http://www.facebook.com/sharer.php">Share</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
        <a href="http://twitter.com/share?url=<?php echo urlencode(get_permalink($post->ID)); ?>&amp;text=Check%20Out%20This%20Great%20Product!%20" class="twitter" target="_blank">Tweet</a>
		<?php
		}
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 1.0.57
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0.57
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
	}
}

?>