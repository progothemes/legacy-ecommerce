<?php
/**
 * ProGo Themes' FBLikeBox Widget Class
 *
 * This widget is for controlling the "ProGo : FB Like Box" block
 * modelled after Hybrid theme's widget definitions
 *
 * @since 1.0
 *
 * @package ProGo
 * @subpackage Core
 */

class ProGo_Widget_FBLikeBox extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0
	 */
	function ProGo_Widget_FBLikeBox() {
		$this->prefix = 'progo';
		$this->textdomain = 'progo';

		$widget_ops = array( 'classname' => 'fblike', 'description' => __( 'FB Like Box in a Widget', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-fblike", __( 'ProGo : FB Like Box', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Twitter') : $instance['title'], $instance, $this->id_base);
		$url = strip_tags($instance['url']);
		$width = absint($instance['width']);
		$color = strip_tags($instance['color']);
		if ( !in_array( $color, array( 'light', 'dark' ) ) ) {
			$color = 'light';
		}
		
		$faces = $instance['faces'] == 'yes' ? 'true' : 'false';
		$stream = $instance['stream'] == 'yes' ? 'true' : 'false';
		$header = $instance['header'] == 'yes' ? 'true' : 'false';
		
		$height = 62;
		if($faces=='true') {
			$height += 196;
			if($stream=='true' || $header=='true') $height = 598;
		} else {
			if($stream=='true') {
				$height += 333;
				if($header=='true') $height = 427;
			}
		}
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		
		echo '<div class="fblikebox" style="display:block;overflow:hidden;width:'. ($width-12) .'px;height:'. ($height-31) .'px"><iframe src="http://www.facebook.com/plugins/likebox.php?href='. esc_url($url) .'&amp;width='. $width .'&amp;colorscheme='. $color .'&amp;show_faces='. $faces .'&amp;stream='. $stream .'&amp;header='. $header .'&amp;height='. $height .'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'. $width .'px; height:'. ($height-25) .'px;margin:-10px -1px 0" allowTransparency="true"></iframe></div>';
		
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 1.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => 'Facebook', 'url' => '', 'width' => 292, 'color' => 'light', 'faces' => 'yes', 'stream' => 'no', 'header' => 'no') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['url'] = strip_tags($new_instance['url']);
		$instance['width'] = (int) $new_instance['width'];
		
		$color = strip_tags($new_instance['color']);
		if ( !in_array( $color, array( 'light', 'dark' ) ) ) {
			$color = 'light';
		}
		$instance['color'] = $color;
		
		$instance['faces'] = $new_instance['faces'] == 'yes' ? 'yes' : 'no';
		$instance['stream'] = $new_instance['stream'] == 'yes' ? 'yes' : 'no';
		$instance['header'] = $new_instance['header'] == 'yes' ? 'yes' : 'no';
		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'url' => '', 'width' => 292, 'color' => 'light', 'faces' => 'yes', 'stream' => '', 'header' => '') );
		$title = strip_tags($instance['title']);
		$url = strip_tags($instance['url']);
		$width = absint($instance['width']);
		$color = strip_tags($instance['color']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('Page URL:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo esc_url($url); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>" /></p>
        <p><label for="<?php echo $this->get_field_id('color'); ?>"><?php _e('Color Scheme:'); ?></label>
		<select id="<?php echo $this->get_field_id('color'); ?>" name="<?php echo $this->get_field_name('color'); ?>"><?php
		$colors = array('light','dark');
        foreach ( $colors as $k ) {
			echo '<option value="'. $k .'"';
			if ( $k == $instance['color'] ) {
				echo ' selected="selected"';
			}
			echo '>'. $k .'</option>';
		}
        ?></select></p>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['faces'], 'yes') ?> id="<?php echo $this->get_field_id('faces'); ?>" name="<?php echo $this->get_field_name('faces'); ?>" value="yes" /> <label for="<?php echo $this->get_field_id('faces'); ?>"><?php _e('Show Faces'); ?></label></p>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['stream'], 'yes') ?> id="<?php echo $this->get_field_id('stream'); ?>" name="<?php echo $this->get_field_name('stream'); ?>" value="yes" /> <label for="<?php echo $this->get_field_id('stream'); ?>"><?php _e('Show stream'); ?></label></p>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['header'], 'yes') ?> id="<?php echo $this->get_field_id('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" value="yes" /> <label for="<?php echo $this->get_field_id('header'); ?>"><?php _e('Show header'); ?></label></p>
<?php
	}
}

?>