<?php
/**
 * ProGo Themes' Social Widget Class
 *
 * This widget is for positioning/removing the "Social" block
 * modelled after Hybrid theme's widget definitions
 *
 * Additional items can be added to the Social links output
 * via the "progo_widget_social_items" filter, like
 * 
 * function progo_sendfriend($items) {
 * $items[] = array(
 * 	'url' => '#me',
 * 	'class' => 'send',
 * 	'text' => 'send to a friend'
 * );
 * return $items;
 * }
 * add_filter('progo_widget_social_items','progo_sendfriend');
 *
 * @since 1.0
 *
 * @package ProGo
 * @subpackage Core
 */

class ProGo_Widget_Social extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0
	 */
	function ProGo_Widget_Social() {
		$this->prefix = 'progo';
		$this->textdomain = 'progo';

		$widget_ops = array( 'classname' => 'social', 'description' => __( 'Display links to your Social networks.', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-social", __( 'ProGo : Social', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Follow Us') : $instance['title'], $instance, $this->id_base);
		$facebook = strip_tags($instance['facebook']);
		$twitter = strip_tags($instance['twitter']);
		$youtube = strip_tags($instance['youtube']);
		$vimeo = strip_tags($instance['vimeo']);
		$linkedin = strip_tags($instance['linkedin']);
		$rss = $instance['rss'] == 'yes' ? 'yes' : 'no';
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		
		$items = array();
		if ( $facebook != '' ) {
			$items[] = array(
				'url' => $facebook,
				'class' => 'fb',
				'text' => 'Join us on Facebook'
			);
		}
		if ( $twitter != '' ) {
			$items[] = array(
				'url' => "http://twitter.com/$twitter",
				'class' => 'tw',
				'text' => 'Follow us on Twitter'
			);
		}
		if ( $youtube != '' ) {
			$items[] = array(
				'url' => $youtube,
				'class' => 'yt',
				'text' => 'Watch us on YouTube'
			);
		}
		if ( $vimeo != '' ) {
			$items[] = array(
				'url' => $vimeo,
				'class' => 'vm',
				'text' => 'Watch us on Vimeo'
			);
		}
		if ( $linkedin != '' ) {
			$items[] = array(
				'url' => $linkedin,
				'class' => 'in',
				'text' => 'Join us on LinkedIn'
			);
		}
		if ( $rss == 'yes' ) {
			$items[] = array(
				'url' => get_bloginfo('rss2_url'),
				'class' => 'rss',
				'text' => 'Subscribe'
			);
		}
		
		$items = apply_filters('progo_widget_social_items', $items);
		
		echo '<ul>';
		foreach ( $items as $i ) {
			echo '<li class="'.esc_attr($i['class']).'"><a href="'. esc_url($i['url']) .'" title="'. esc_attr($i['text']) .'" target="_blank">'. wp_kses($i['text'],array('br'=>array(),'em'=>array(),'strong'=>array())) .'</a></li>';
		}
		echo '</ul>';
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 1.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'facebook' => '', 'twitter' => '', 'youtube' => '', 'vimeo' => '', 'linkedin' => '', 'rss' => 'no') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['facebook'] = strip_tags($new_instance['facebook']);
		$instance['twitter'] = strip_tags($new_instance['twitter']);
		$instance['youtube'] = strip_tags($new_instance['youtube']);
		$instance['vimeo'] = strip_tags($new_instance['vimeo']);
		$instance['linkedin'] = strip_tags($new_instance['linkedin']);
		$instance['rss'] = $new_instance['rss'] == 'yes' ? 'yes' : 'no';

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'facebook' => '', 'twitter' => '', 'youtube' => '', 'vimeo' => '', 'linkedin' => '', 'rss' => 'yes') );
		$title = strip_tags($instance['title']);
		$fb = strip_tags($instance['facebook']);
		$tw = strip_tags($instance['twitter']);
		$yt = strip_tags($instance['youtube']);
		$vm = strip_tags($instance['vimeo']);
		$in = strip_tags($instance['linkedin']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('facebook'); ?>"><?php _e('Facebook URL:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('facebook'); ?>" name="<?php echo $this->get_field_name('facebook'); ?>" type="text" value="<?php echo esc_attr($fb); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('twitter'); ?>"><?php _e('Twitter @name:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" type="text" value="<?php echo esc_attr($tw); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('youtube'); ?>"><?php _e('YouTube Channel URL:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('youtube'); ?>" name="<?php echo $this->get_field_name('youtube'); ?>" type="text" value="<?php echo esc_attr($yt); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('vimeo'); ?>"><?php _e('Vimeo URL:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('vimeo'); ?>" name="<?php echo $this->get_field_name('vimeo'); ?>" type="text" value="<?php echo esc_attr($vm); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('linkedin'); ?>"><?php _e('LinkedIn URL:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('linkedin'); ?>" name="<?php echo $this->get_field_name('linkedin'); ?>" type="text" value="<?php echo esc_attr($in); ?>" /></p>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['rss'], 'yes') ?> id="<?php echo $this->get_field_id('rss'); ?>" name="<?php echo $this->get_field_name('rss'); ?>" value="yes" /> <label for="<?php echo $this->get_field_id('rss'); ?>"><?php _e('Include RSS Link'); ?></label></p>
<?php
	}
}

?>