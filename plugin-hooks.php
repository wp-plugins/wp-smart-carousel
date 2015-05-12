<?php
/*
Plugin Name: WP Smart Carousel
Plugin URI:  http://touchpointdev.com/wp-smart-carousel/
Description: In this smart carosuel you will get various style and feature with dynamic multiple shortcode. WP Smart Carousel it's supper easy to use.
Author: Rana Ahmed
Author URI: http://touchpointdev.com
Version: 2.1
*/

/* Adding Latest jQuery from Wordpress */
function tp_smooth_carousel_latest_jquery() {
	wp_enqueue_script('jquery');
}
add_action('init', 'tp_smooth_carousel_latest_jquery');

/*Some Set-up*/
define('TP_SMOOTH_CAROUSEL', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );

function tp_smooth_carousel_files(){
	/* Adding Plugin javascript file */
	wp_enqueue_script('tp-carousel-plugin-js', TP_SMOOTH_CAROUSEL.'js/owl.carousel.min.js', array('jquery'), '2.1', false);

	/* Adding Plugin owl carousel css file */
	wp_enqueue_style('tp-owl-carousel-css', TP_SMOOTH_CAROUSEL.'css/owl.carousel.css', array(), '2.1', false);
	
	/* Adding Plugin owl carousel Transition css file */
	wp_enqueue_style('tp-owl-transition-css', TP_SMOOTH_CAROUSEL.'css/owl.transitions.css', array(), '2.1', false);
	
	/* Adding Plugin tp carousel theme css file */
	wp_enqueue_style('tp-carousel-theme-css', TP_SMOOTH_CAROUSEL.'css/tp-carousel-style.css', array(), '2.1', false);
}
add_action( 'wp_enqueue_scripts', 'tp_smooth_carousel_files' );



/* Add Slider Shortcode Button on Post Visual Editor */
function tpcarousel_button_function() {
	add_filter ("mce_external_plugins", "tpcarosel_button_js");
	add_filter ("mce_buttons", "tpcarosel_button");
}

function tpcarosel_button_js($plugin_array) {
	$plugin_array['tpcarous'] = plugins_url('js/custom-button.js', __FILE__);
	return $plugin_array;
}

function tpcarosel_button($buttons) {
	array_push ($buttons, 'tpcarosel');
	return $buttons;
}
add_action ('init', 'tpcarousel_button_function'); 


// register custom post
add_action( 'init', 'tp_carousel_post_type' );
function tp_carousel_post_type() {
	register_post_type( 'tp-carousel-items',
		array(
			'labels' => array(
				'name' => __( 'Smart Carousels' ),
				'singular_name' => __( 'Smart Carousel' ),
				'add_new_item' => __( 'Add New Service' )
			),
			'public' => true,
			'supports' => array('thumbnail', 'title', 'custom-fields', 'editor'),
			'has_archive' => true,
			'rewrite' => array('slug' => 'carousel-items'),
		)
	);	
}

//Register Taxonomy for custom post.
function tp_carousel_taxonomy() {
	register_taxonomy(
		'tpcarousel_cat',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
		'tp-carousel-items',                  // ur custom post type name
		array(
			'hierarchical'          => true,
			'label'                 => 'Carousel Category',  //Display name
			'query_var'             => true,
			'hierarchical'     	 	=> true,
			'show_ui'          	 	=> true,
			'show_admin_column' 	=> true,
			'rewrite'               => array(
				'slug'              => 'carousel-category', // This controls the base slug that will display before each term
				'with_front'    => true // Don't display the category base before
				)
			)
	);
}
add_action( 'init', 'tp_carousel_taxonomy');  


add_theme_support( 'post-thumbnails', array( 'post', 'tp-carousel-items' ) );
add_image_size( 'thumb', 400, 300, true );

function tp_custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'tp_custom_excerpt_length', 999 );

/* Add Plugin Loop Code */
function tp_smooth_carousel_list_shortcode($atts){
	extract( shortcode_atts( array(
		'id' => 'demo',
		'count' => '10',
		'items' => '4',
		'navigation' => 'true',
		'autoplay' => 'true',
		'pagination' => 'false',
		'category' => '',
	), $atts, 'projects' ) );
	
    $q = new WP_Query(
        array('posts_per_page' => $count, 'post_type' => 'tp-carousel-items', 'tpcarousel_cat' => $category )
        );		

	$list = '
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#owl'.$id.'").owlCarousel({
			items : '.$items.',
			autoPlay: '.$autoplay.',
			lazyLoad : true,
			stopOnHover : true,
			pagination : '.$pagination.',
			navigation : '.$navigation.',
			navigationText: ["",""]
			});
		}); 
	</script>
<style type="text/css">
	div.owl-carousel .item{ margin: 4px;}
	div.owl-carousel  .item img{ display: block; width: 100%; height: auto; border-radius:none;	}
	.entry-content img, .comment-content img, .widget img, img.header-image, .author-avatar img, img.wp-post-image {
  border-radius: 0px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}
</style>

	<div id="owl'.$id.'" class="owl-carousel"> '; 
		while($q->have_posts()) : $q->the_post();
			$idd = get_the_ID();
			$img= get_the_post_thumbnail( $post->ID, 'thumb', array ('class' => 'lazyOwl' ) );	
			$list .= '
				<div class="item">'.$img.'
					<div class="tp_carousel_content">
						<h1>'.get_the_title().'</h1>
						<p>'.get_the_excerpt().'</p>			
						<div class="tp_read_more"><a href="'.get_the_permalink().'">Read More</a></div>						
					</div>
				</div>

				';        
		endwhile;
		$list.= '</div>';
		wp_reset_query();
		return $list;
}
add_shortcode('tp_carousel', 'tp_smooth_carousel_list_shortcode');	


?>