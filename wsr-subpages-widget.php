<?php 
/*
Plugin Name: WSR Subpages Widget
Plugin URI: http://websector.com
Description: lists subpages for the current page based on the Wordpress menu
Version: 1.0.0
Author: WSR
Author URI: http://websector.com
License: A short license name. Example: GPL2
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class wsr_Subpages_Widget extends WP_Widget {
	public function __construct() {
    	$widget_options = array( 
    		'classname' => 'wsr_subpage_widget',
     		'description' => 'Displays parent sub pages',
    	);
    	parent::__construct( 'wsr_subpage_widget', 'Sub Page Widget', $widget_options );
  	}

  	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance[ 'title' ] );
		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title']; ?>

		<?php wp_nav_menu( array(  
	        'menu'              => 'primary',
	        'submenu'			=> get_the_id(),
	        'theme_location'    => 'primary',
	        'depth'             => 2,
	        'container'         => 'div'
		)); ?>


		<?php echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : ''; ?>
	  	<p>
	    	<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
	    	<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
	  	</p><?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
	  	$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
	  	return $instance;
	}

}



add_action( 'widgets_init', 'wsr_register_subpages_widget' );
function wsr_register_subpages_widget() { 
	register_widget( 'wsr_Subpages_Widget' );
}



add_filter( 'wp_nav_menu_objects', 'submenu_limit', 10, 2 );
function submenu_limit( $items, $args ) {
    if ( empty( $args->submenu ) ) {
        return $items;
    }
    $menuParentids = wp_filter_object_list( $items, array( 'object_id' => $args->submenu ), 'and', 'menu_item_parent' );
    $parent_id = array_pop( $menuParentids );
    if ($parent_id == 0){
    	$menuParentids = wp_filter_object_list( $items, array( 'object_id' => $args->submenu ), 'and', 'ID' );
    	$parent_id = array_pop( $menuParentids );
    }
    $children  = submenu_get_children_ids( $parent_id, $items );
    foreach ( $items as $key => $item ) {
        if ( ! in_array( $item->ID, $children ) ) {
            unset( $items[$key] );
        }
    }
    return $items;
}



function submenu_get_children_ids( $id, $items ) {
    $ids = wp_filter_object_list( $items, array( 'menu_item_parent' => $id ), 'and', 'ID' );
    foreach ( $ids as $id ) {
        $ids = array_merge( $ids, submenu_get_children_ids( $id, $items ) );
    }
    return $ids;
}

