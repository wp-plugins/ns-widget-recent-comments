<?php
/*
* Plugin Name: NS Widget Recent Comments
* Plugin URI: http://netscripter.me/ns-widget-recent-comments
* Author: widget-recent-comments
* Author URI: http://netscripter.me
* Description: Add a recent comments widget that shows author's avatar.
* Version: 1.2
* Text Domain: widget-recent-comments
*/

function ns_init() {
	register_widget('NS_Recent_Comments_');
}
add_action( 'widgets_init', 'ns_init' );

function ns_recent_comments_init() {
  load_plugin_textdomain( 'widget-recent-comments', false, 'ns-widget-recent-comments/languages' );
}
add_action('init', 'ns_recent_comments_init');

function ns_scripts() {
wp_enqueue_style( 'recent-comment', plugins_url('/css/widget-recent-comments.css', __FILE__) );
wp_enqueue_style( 'recent-comments' );
}
add_action('wp_enqueue_scripts','ns_scripts');


/* Recent_Comments widget class */

class NS_Recent_Comments_ extends WP_Widget {

	function NS_Recent_Comments_() {
		$widget_ops = array('classname' => 'widget_ns', 'description' => __( 'Recent comments, with Avatars.' , 'widget-recent-comments') );
		parent::__construct('ns-recent-comments', __('NS Recent Comments', 'widget-recent-comments'), $widget_ops);
		$this->alt_option_name = 'widget_ns';

		if ( is_active_widget(false, false, $this->id_base) )
			add_action( 'wp_head', array(&$this, 'widget_style') );

		add_action( 'comment_post', array(&$this, 'flush_widget_cache') );
		add_action( 'transition_comment_status', array(&$this, 'flush_widget_cache') );
	}

	function widget_style() { ?>
	
<?php
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_ns', 'widget');
	}

	function widget( $args, $instance ) {
		global $comments, $comment;

		$cache = wp_cache_get('widget_ns', 'widget');

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

 		extract($args, EXTR_SKIP);
 		$output = '';
 		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Comments', 'widget-recent-comments') : $instance['title']);

		if ( ! $number = (int) $instance['number'] )
 			$number = 5;
 		else if ( $number < 1 )
 			$number = 1;

		$size = $instance['size'];

		$comments = get_comments( array( 'number' => $number, 'status' => 'approve' ) );
		$output .= $before_widget;
		if ( $title )
			$output .= $before_title . $title . $after_title;

		$output .= '<ul id="ns">';
		if ( $comments ) {
			foreach ( (array) $comments as $comment) {
				$output .= '<div class="avat">';
				$output .=  get_avatar(get_comment_author_email($comment->comment_ID), $size) . ' ';
				$output .=  sprintf(__('%1$s on </div><li class="ns-comment"> %2$s', 'widget-recent-comments'), get_comment_author(), '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>');
				$output .=  '<br style="clear:both;height:0;margin:0;padding:0;" /></li>';
			}
 		}
		$output .= '</ul>';
		$output .= $after_widget;

		echo $output;
		$cache[$args['widget_id']] = $output;
		wp_cache_set('widget_ns', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['size'] = ( $new_instance['size'] < 20 ) ? 20 : (int) $new_instance['size'];
		
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_ns']) )
			delete_option('widget_ns');

		return $instance;
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 3;
		$size = isset($instance['size']) ? absint($instance['size']) : 40;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'widget-recent-comments'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of comments to show:', 'widget-recent-comments'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		
		<p><label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Avatar size:', 'widget-recent-comments'); ?></label>
		<input id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" type="text" value="<?php echo $size; ?>" size="3" /></p>
<?php
	}
}
