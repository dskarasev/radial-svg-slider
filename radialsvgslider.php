<?php
/*
    Plugin Name: Radial Svg Slider
    Description: WordPress plugin from Radial SVG Slider by CodyHouse.co
    Author: Antonio Trifirò
    Version: 1.0
*/

// rsvgs_init
function rsvgs_init() {
    add_shortcode('rsvgs-shortcode', 'rsvgs_function'); // add shortcode
    
    // Create custom post type
    $cpt_labels = array(
		'name'               => 'Slides',
		'singular_name'      => 'Slide',
		'menu_name'          => 'Radial SVG Slideshows',
		'name_admin_bar'     => 'Radial SVG Slideshow',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Slide',
		'new_item'           => 'New Slide',
		'edit_item'          => 'Edit Slide',
		'view_item'          => 'View Slide',
		'all_items'          => 'All Slide',
		'search_items'       => 'Search Slide',
		'parent_item_colon'  => 'Parent Slide:',
		'not_found'          => 'No slide found.',
		'not_found_in_trash' => 'No slide found in Trash.'
	);
    $args = array(
        'public' => true,
        'labels' => $cpt_labels,
        'capability_type' => 'post',
        'supports' => array(
            'title',
            'editor',
            'thumbnail'
        ),
        'taxonomies' => array('rsvgslideshow')
    );
    register_post_type('rsvgs_slides', $args); // End of Create custom post type 
    
    // Create custom taxonomy
    $labels = array(
        'name'          => 'Slideshows',
        'singular_name' => 'Slideshow',
        'all_items'     => 'All Slideshows',
        'edit_item'     => 'Edit Slideshow',
		'update_item'   => 'Update Slideshow',
		'add_new_item'  => 'Add New Slideshow',
		'new_item_name' => 'New Slideshow',
		'menu_name'     => 'Slideshows'
    );
    register_taxonomy( 'rsvgslideshow', 'rsvgs_slides', array(
		'hierarchical' => false,
		'labels' => $labels,
		'query_var' => true,
		'show_admin_column' => true
	) );
    
    add_image_size('rsvgs_function', 800, 457, true); // Add new image size
}
add_theme_support( 'post-thumbnails' ); // Add support for thumbnails
add_action('init', 'rsvgs_init');

// Including scripts and style
add_action('wp_print_scripts', 'rsvgs_register_scripts');
add_action('wp_print_styles', 'rsvgs_register_styles');

function rsvgs_register_scripts() {
    if (!is_admin()) {
        // register
        wp_register_script('rsvgs_slider-script', plugins_url('radial-svg-slider/js/main.js', __FILE__), array( 'jquery' ));
        wp_register_script('rsvgs_snap-script', plugins_url('radial-svg-slider/js/snap.svg-min.js', __FILE__), array( 'jquery' ));
 
        // enqueue
        wp_enqueue_script('rsvgs_slider-script');
        wp_enqueue_script('rsvgs_snap-script');
    }
}

function rsvgs_register_styles() {
    // register
    wp_register_style('rsvgs_reset-styles', plugins_url('radial-svg-slider/css/reset.css', __FILE__));
    wp_register_style('rsvgs_styles', plugins_url('radial-svg-slider/css/style.css', __FILE__));
 
    // enqueue
    wp_enqueue_style('rsvgs_reset-styles');
    wp_enqueue_style('rsvgs_styles');
}

// Custom fields



// php function
function rsvgs_function($slideshow_attr) {
    $slideshow_slug = $slideshow_attr['slideshow'];
    $args = array(
        'post_type' => 'rsvgs_slides',
        'tax_query' => array ( array(
            'taxonomy' => 'rsvgslideshow',
            'field' => 'slug',
            'terms' => $slideshow_slug
        ) )
    );
    $loop = new WP_Query($args);
    
    $result = '<div class="cd-radial-slider-wrapper">';
    
    //fallback for screen reader
    $result .= '<div class="carousel-fallback">';
    $result .= '<h2>Featured content</h2>';
    $result .= '<ul>';
    ////the loop
    while ($loop->have_posts()) {
        $loop->the_post();
        $result .= '<li><h3>' . get_the_title() . '</h3>';
        $alt = get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true);
        if (count($alt)) {
            $result .= '<p>Image description:' . $alt . '</p>';
        }
        $result .= '<p>' . get_the_content() . '</p>';
        $result .= '<a href="' . get_field('button_url') . '" tabindex="-1">' . get_field('button_text') . '</a>';
        $result .= '</li>';
    }
    $result .= '</ul></div>';
    
    //slideshow
    $result .= '<ul class="cd-radial-slider" data-radius1="60" data-radius2="1364" data-centerx1="110" data-centerx2="1290" aria-hidden="true" role="presentation">';
    ////the loop    
    $post_index = 0;
    while ($loop->have_posts()) {
        $post_index++;
        $loop->the_post();
 
        $total_posts = $loop->found_posts;
        if ($post_index==1) { $li_class = 'visible'; }
        elseif ($post_index==2) { $li_class = 'next-slide'; }
        elseif ($post_index==$total_posts) { $li_class = 'prev-slide'; }
        else { $li_class = ''; }
        
        $the_url = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), array(800,457));
        
        $result .='<li class="' . $li_class . '">';
        $result .='<div class="svg-wrapper">';
        $result .='<svg viewBox="0 0 1400 800"><title>Animated SVG</title><defs>';
        $result .='<clipPath id="cd-image-'.$post_index.'"><circle id="cd-circle-'.$post_index.'" cx="' . ($post_index==2 ? 1290 : 110) . '" cy="400" r="' . ($post_index==1 ? 1364 : 60) . '"/>';
        $result .='</clipPath></defs>';
        $result .='<image height="800px" width="1400px" clip-path="url(#cd-image-'.$post_index.')" xlink:href="' . $the_url[0] . '"></image>';
        $result .='</svg></div> <!-- .svg-wrapper -->';
        $trueshadow = get_field(enhance_elements_visibility);
        $result .='<div class="cd-radial-slider-content"><div class="wrapper"><div class="' . ($trueshadow?'rsvgs-shadow-text':'') . '">';
        $result .='<h2>' . get_the_title() . '</h2>';
        $result .='<p>' . get_the_content() . '</p>';
        $result .='<a href="' . get_field('button_url') . '" class="cd-btn ' . ($trueshadow?'rsvgs-shadow-button':'') . '">' . get_field('button_text') . '</a>';
        $result .='</div></div></div> <!-- .cd-radial-slider-content --></li>';
    }
    
    $result .= '</ul> <!-- .cd-radial-slider -->';
    $result .='<ul class="cd-radial-slider-navigation"><li><a href="#0" class="next">Next</a></li><li><a href="#0" class="prev">Prev</a></li></ul> <!-- .cd-radial-slider-navigation -->';
    $result .='<div class="cd-round-mask"><svg viewBox="0 0 1400 800"><defs><mask id="cd-left-mask" height="800px" width="1400px" x="0" y="0" maskUnits="userSpaceOnUse"><path fill="white" d="M0,0v800h1400V0H0z M110,460c-33.137,0-60-26.863-60-60s26.863-60,60-60s60,26.863,60,60S143.137,460,110,460z"/></mask><mask id="cd-right-mask" height="800px" width="1400px" x="0" y="0" maskUnits="userSpaceOnUse"><path fill="white" d="M0,0v800h1400V0H0z M1290,460c-33.137,0-60-26.863-60-60s26.863-60,60-60s60,26.863,60,60S1323.137,460,1290,460z"/></mask></defs></svg></div>';
    $result .='</div> <!-- .cd-radial-slider-wrapper -->';
    
    return $result;
}