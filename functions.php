<?php
/**
 * codingleader functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package codingleader
 */




//Classic Widgets "fixes error regarding widgets/compatibility issues"

// Disables the block editor from managing widgets in the Gutenberg plugin.
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
// Disables the block editor from managing widgets.
add_filter( 'use_widgets_block_editor', '__return_false' );

//Error messages when an item is removed from the cart with conditional logic

function my_woocommerce_membership_notice($message, $product) {
    if (strpos($message,'Live Webinar has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.') !== false) {
    	$message = sprintf( __( '%s has been removed from your cart because the live session has already occurred and it can no longer be purchased. Please go the session product page and choose a different format. Contact us if you need assistance.', 'woocommerce' ), $product->get_name() ); 
    }
	elseif (strpos($message,'Live + On-Demand has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.') !== false) {
		$message = sprintf( __( '%s has been removed from your cart because the live session has already occurred and it can no longer be purchased. Please go the session product page and choose a different format. Contact us if you need assistance.', 'woocommerce' ), $product->get_name() ); 	
	}
	elseif (strpos($message,'CD-ROM has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.') !== false) {
		$message = sprintf( __( '%s has been removed from your cart because the live session has already occurred and it can no longer be purchased. Please go the session product page and choose a different format. Contact us if you need assistance.', 'woocommerce' ), $product->get_name() ); 	
	}
    return $message;
}
add_filter( 'woocommerce_cart_item_removed_message', 'my_woocommerce_membership_notice', 10, 2 );

//Button Shortcode to use it in Wordpress Classic Editor. 

function custom_button_shortcode( $atts, $content = null ) {
   
    // shortcode attributes
    extract( shortcode_atts( array(
        'url'    => '',
        'title'  => '',
        'target' => '',
        'text'   => '',
    ), $atts ) );
 
    $content = $text ? $text : $content;
 
    // Returns the button with a link
    if ( $url ) {
 
        $link_attr = array(
            'href'   => esc_url( $url ),
            'title'  => esc_attr( $title ),
            'target' => ( 'blank' == $target ) ? '_blank' : '',
            'class'  => 'custombutton'
        );
 
        $link_attrs_str = '';
 
        foreach ( $link_attr as $key => $val ) {
 
            if ( $val ) {
 
                $link_attrs_str .= ' ' . $key . '="' . $val . '"';
 
            }
 
        }
 
 
        return '<a' . $link_attrs_str . '><span>' . do_shortcode( $content ) . '</span></a>';
 
    }
 
    // Return as span when no link defined
    else {
 
        return '<span class="custombutton"><span>' . do_shortcode( $content ) . '</span></span>';
 
    }
 
}
add_shortcode( 'custombutton', 'custom_button_shortcode' );

function page_taxonomy() {
    register_taxonomy(
        'page_categories',  // The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
        'page',             // post type name
        array(
            'hierarchical' => true,
            'label' => 'Categories', // display name
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'page-categories',    // This controls the base slug that will display before each term
                'with_front' => false  // Don't display the category base before
            )
        )
    );
}
add_action( 'init', 'page_taxonomy');

/**
 * Control the number of search results
 */
function custom_posts_per_page($query) {

$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
if (strpos($url,'product') !== false) {

    if (is_search()) {
        $query->set('posts_per_page', 16);
    }
}
else{
	  if (is_search()) {
        $query->set('posts_per_page', -1);
    }

}
} //function


//this adds the function above to the 'pre_get_posts' action
add_action('pre_get_posts', 'custom_posts_per_page');



// Utility function to get the price of a variation from it's attribute value
function get_the_variation_price_html( $product, $name, $term_slug ){
    foreach ( $product->get_available_variations() as $variation ){
        if($variation['attributes'][$name] == $term_slug ){
            return strip_tags( $variation['price_html'] );
        }
    }
}

// Add the price  to the dropdown options items.
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'show_price_in_attribute_dropdown', 10, 2);
function show_price_in_attribute_dropdown( $html, $args ) {
    // Only if there is a unique variation attribute (one dropdown)
    if( sizeof($args['product']->get_variation_attributes()) == 1 ) :

    $options               = $args['options'];
    $product               = $args['product'];
    $attribute             = $args['attribute'];
    $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
    $id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
    $class                 = $args['class'];
    $show_option_none      = $args['show_option_none'] ? true : false;
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' );

    if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
        $attributes = $product->get_variation_attributes();
        $options    = $attributes[ $attribute ];
    }

    $html = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
    $html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

    if ( ! empty( $options ) ) {
        if ( $product && taxonomy_exists( $attribute ) ) {
            $terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );

            foreach ( $terms as $term ) {
                if ( in_array( $term->slug, $options ) ) {
                    // Get and inserting the price
                    $price_html = get_the_variation_price_html( $product, $name, $term->slug );
                    $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) . ' : ' . $price_html ) . '</option>';
                }
            }
        } else {
            foreach ( $options as $option ) {
                $selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
                // Get and inserting the price
                $price_html = get_the_variation_price_html( $product, $name, $term->slug );
                $html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) . ' : ' . $price_html ) . '</option>';
            }
        }
    }
    $html .= '</select>';

    endif;

    return $html;
}


//jquery file
function my_scripts_method() {
// register your script location, dependencies and version
   wp_register_script('custom_script',
   get_stylesheet_directory_uri() . '/js/functions.js',
   array('jquery'),
   '3.5.1' );
 // enqueue the script
  wp_enqueue_script('custom_script');
  }
add_action('wp_enqueue_scripts', 'my_scripts_method');

global $test_products_ids;
$test_products_ids = array(
	166704,
	169905,
	170840,
	173502
);

if ( ! function_exists( 'codingleader_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function codingleader_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on codingleader, use a find and replace
		 * to change 'codingleader' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'codingleader', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		add_theme_support( 'woocommerce' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'codingleader' ),
			'menu-2' => esc_html__( 'Footer Column 1', 'codingleader' ),
			'menu-3' => esc_html__( 'Footer Column 2', 'codingleader' ),
			'menu-4' => esc_html__( 'Footer Column 3', 'codingleader' ),
			'menu-5' => esc_html__( 'Footer Column 4', 'codingleader' ),
			'menu-6' => esc_html__( 'Footer Social', 'codingleader' ),
			'menu-7' => esc_html__( 'Online Training', 'codingleader' ),
			'menu-8' => esc_html__( 'Resources', 'codingleader' ),
			'menu-9' => esc_html__( 'Support', 'codingleader' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'codingleader_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );

		add_image_size( 'home-hero-large',        1920, 600, true);
		add_image_size( 'home-hero-medlg',        1440, 575, true);
		add_image_size( 'home-hero-small',        320,  410, true);
		add_image_size( 'small-square',           200,  200, true);
		add_image_size( 'prod-image',             500,  320, array('center', 'bottom'));
		add_image_size( 'blog-image',             475,  320, true);
	}
endif;
add_action( 'after_setup_theme', 'codingleader_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function codingleader_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'codingleader_content_width', 640 );
}
add_action( 'after_setup_theme', 'codingleader_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function codingleader_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Post Sidebar', 'codingleader' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'codingleader' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Page Sidebar', 'codingleader' ),
		'id'            => 'sidebar-2',
		'description'   => esc_html__( 'Add widgets here.', 'codingleader' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Shop Sidebar', 'codingleader' ),
		'id'            => 'sidebar-3',
		'description'   => esc_html__( 'Add widgets here.', 'codingleader' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Product Page Sidebar', 'codingleader' ),
		'id'            => 'sidebar-4',
		'description'   => esc_html__( 'Widget shown on product page when the visitor has not purchased the product and is not unlimited.', 'codingleader' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Product Page Sidebar (buyer)', 'codingleader' ),
		'id'            => 'sidebar-6',
		'description'   => esc_html__( 'Widget shown on product page when the visitor is not unlimited but has purchased the product.', 'codingleader' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Product Page Sidebar (Unlimited)', 'codingleader' ),
		'id'            => 'sidebar-5',
		'description'   => esc_html__( 'Widget shown on product page when the visitor has an unlimited training subscription.', 'codingleader' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'codingleader_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function codingleader_scripts() {
	wp_enqueue_style( 'codingleader-style', get_stylesheet_uri(), array(), time() );

	wp_enqueue_style( 'codingleader-swiper', get_template_directory_uri() . '/swiper.min.css' );
	
	wp_enqueue_style('themecss-resources', get_template_directory_uri() . '/assets/css/resources.css');
	wp_enqueue_style('themecss-thankyou', get_template_directory_uri() . '/assets/css/thankyou.css');

	wp_enqueue_script( 'codingleader-navigation', get_template_directory_uri() . '/js/navigation.js', array(), time(), true );
	// wp_enqueue_script( 'custom-js', get_template_directory_uri() . '/js/custom.js', array(), time(), true );
	wp_localize_script( 'codingleader-navigation', 'codingleader_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	wp_enqueue_script( 'codingleader-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	wp_enqueue_script( 'codingleader-swiper', get_template_directory_uri() . '/js/swiper.min.js', array(), '20151215', false );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'codingleader_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

require get_template_directory() . '/inc/custom-post-type.php';

if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'CodingLeader Settings',
		'menu_title'	=> 'CodingLeader Settings',
		'menu_slug' 	=> 'codingleader-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));

}

add_action( 'pre_get_posts', 'handle_product_search' );

function handle_product_search($q) {
	if (is_admin() || is_shop()) return;

	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	if ($path == '/' && !empty($_GET['s'])) {
		$q -> tax_query = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_visibility',
				'field' => 'slug',
				'operator' => 'NOT IN',
				'terms' => array('exclude-from-search', 'hidden'),
			),
		);

		$q -> query_vars['tax_query'] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_visibility',
				'field' => 'slug',
				'operator' => 'NOT IN',
				'terms' => array('exclude-from-search', 'hidden'),
			),
		);
	}

	if($q->is_search()) {
    $post_type_to_remove = 'tribe_events';
    /* get all searchable post types */
    $searchable_post_types = get_post_types(array('exclude_from_search' => false));

    /* make sure you got the proper results, and that your post type is in the results */
    if(is_array($searchable_post_types) && in_array($post_type_to_remove, $searchable_post_types)){
      /* remove the post type from the array */
      unset( $searchable_post_types[ $post_type_to_remove ] );
      /* set the query to the remaining searchable post types */
      $q->set('post_type', $searchable_post_types);
    }

		$q -> set('category__not_in', [785]);
  }

	if ($q -> query["post_type"] == 'product' && $q -> is_archive && !empty($_GET['expert']) && is_numeric($_GET['expert']) && $q -> is_main_query() && !is_admin() ) {
		$q -> meta_query = array(
			'relation' => 'or',
			array(
				'key' => 'expert',
				'value' => '"'.$_GET['expert'].'"',
				'compare' => 'LIKE'
			),
			array(
				'key' => 'expert',
				'value' => $_GET['expert']
			)
		);

		$q -> query_vars["meta_query"] = array(
			'relation' => 'or',
			array(
				'key' => 'expert',
				'value' => '"'.$_GET['expert'].'"',
				'compare' => 'LIKE'
			),
			array(
				'key' => 'expert',
				'value' => $_GET['expert']
			)
		);
	} elseif ($q->is_search()) {
		$no_search_pages = get_posts(array(
			'post_type' => 'page',
			'meta_key' => 'exclude_from_search',
			'meta_value' => '1',
			'posts_per_page' => -1,
		));
		$no_search_pages_ids = [];
		foreach ($no_search_pages as $no_search_page) { $no_search_pages_ids[] = $no_search_page -> ID; }

		//wp_mail('dstrout@firsttracksmarketing.com', 'excsch', print_r($no_search_pages_ids, true));
		$q -> set('post__not_in', $no_search_pages_ids);
	}

	if ($q -> query["post_type"] == 'experts' && $q -> is_archive && !empty($_GET['topic']) && is_numeric($_GET['topic']) && $q -> is_main_query() && !is_admin() ) {
		$products = get_posts(array(
			'post_type' => 'product',
			'numberposts' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'product_tag',
					'field' => 'id',
					'terms' => $_GET['topic']
				)
			)
		));

		$topicExperts = array();
		foreach ($products as $product) {
			$prodExperts = get_field('expert', $product -> ID);
			if (!$prodExperts) continue;

			if (!is_array($prodExperts)) {
				if (!in_array($prodExperts, $topicExperts)) $topicExperts[] = $prodExperts;
			} else {
				foreach ($prodExperts as $prodExpert) {
					if (!in_array($prodExpert, $topicExperts)) $topicExperts[] = $prodExpert;
				}
			}
		}

		$q -> query_vars['post__in'] = $topicExperts;
		$q -> post__in = $topicExperts;
	}

	if ($q -> query["post_type"] == 'experts' && $q -> is_archive && $q -> is_main_query() && !is_admin() ) {
		$q -> orderby = 'title';
		$q -> order = 'ASC';
		$q -> query_vars['orderby'] = 'title';
		$q -> query_vars['order'] = 'ASC';
	}

	if (!is_admin() && $q -> is_main_query() && $q -> query['pagename'] == 'blog') {
		$q -> query['category__not_in'] = array(785);
		$q -> query_vars['category__not_in'] = array(785);
	}
}

remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_result_count', 20 );
//remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb', 20, 0);

function getExpertTopics($expertID) {
	$expertWebinars = get_posts( array (
		'posts_per_page' => 2,
		'post_type' => 'product',
		'meta_query' => array(
			'relation' => 'or',
			array(
				'key' => 'expert',
				'value' => '"'. $expertID .'"',
				'compare' => 'LIKE'
			),
			array(
				'key' => 'expert',
				'value' => $expertID
			)
		)
	) );

	$webinarIDs = array();
	foreach ($expertWebinars as $webinar) {
		$webinarIDs[] = $webinar -> ID;
	}

	$thisExpertTopics = wp_get_object_terms($webinarIDs, 'product_tag', array('fields' => 'all'));

	$realTopics = array_filter($thisExpertTopics, function($pTag) {
		$pTag = trim(strtolower($pTag -> name));
		if ($pTag == "webinar" || $pTag == "upcoming" || $pTag == "2016memorial" ||
		$pTag == "coding webinar" || $pTag == ""  || $pTag == "pm webinar" || $pTag == "july 4th 2015") return false;
		else return true;
	});

	return $realTopics;
}

remove_theme_support( 'wc-product-gallery-zoom' );
remove_theme_support( 'wc-product-gallery-lightbox' );
remove_theme_support( 'wc-product-gallery-slider' );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
//remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

add_action('woocommerce_before_single_product', 'hide_price_for_webinars');

function hide_price_for_webinars() {
	global $product;

	if ( $product->is_type( 'variable' ) ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	}
}

function get_current_user_orders() {
	if (!is_user_logged_in()) {
		$userEmail = base64_decode($_GET["accesskey"]);
		if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) return array();

		$orders = wc_get_orders( array(
			'customer' => $userEmail,
			'limit' => -1,
			'paginate' => false,
    ));
	} else {
		$orders = wc_get_orders( array(
			'customer' => get_current_user_id(),
			'limit' => -1,
			'paginate' => false,
    ));
	}

	return $orders;
}

function user_has_media_access($product_id) {
	return get_product_purchase_type($product_id);

	return false;
}

function get_product_purchase_type($product_id, $order_id = false) {
	if (!is_user_logged_in()) return false;
	elseif (wc_memberships_is_user_active_member(get_current_user_id(), 'unlimited')) return 'unlimited';

	if (!$order_id || !is_int($order_id)) $orders = get_current_user_orders();
	else $orders = array(wc_get_order($order_id));

	foreach ($orders as $order) {
		$wc_order = new WC_Order( $order -> ID );
		if ($wc_order -> get_status() != 'completed' && $wc_order -> get_status() != 'processing') continue;

		$order_items = $wc_order -> get_items();
		foreach ($order_items as $order_item) {
			$order_product_id = $order_item -> get_product_id();
			$order_variant_id = $order_item -> get_variation_id();
			if ($order_product_id && $order_product_id == $productID && !$order_variant_id) return true;
			if ($order_product_id == $product_id) {
				//$all_order_item_meta = $order_item->get_formatted_meta_data();
				$all_order_item_meta = $order_item->get_meta_data();
				$test_arr[] = $all_order_item_meta;
				foreach ($all_order_item_meta as $order_item_meta) {
					//if ($_SERVER['REMOTE_ADDR'] == '50.245.7.30') var_dump($order_item_meta);
					//$order_item_meta = $order_item_meta -> get_data();
					
					if ($order_item_meta->key == 'pa_format') {
						
						$itemFormat = $order_item_meta -> value;
						$test_arr[] = $itemFormat;
						if ( strpos($itemFormat, 'download') !== false || 
							 strpos($itemFormat, 'ondemand') !== false ||
							 strpos($itemFormat, 'downloadable-video-file') !== false ||
							 strpos($itemFormat, 'on-demand') !== false ) return array( 'is_course' => true, 'id_item' =>  $order_variant_id );

						if ( strpos($itemFormat, 'downloadable') !== false ) return 'unlimited';

						if ( strpos($itemFormat, 'live') !== false ) return 'live';
						
					}
				}
				return true;
			}
		}
	}
	return false;
}

function user_is_unlimited( $user = false ) {
	if (!$user) $user = get_current_user_id();


}

function retrieve_orders_ids_from_a_product_id( $product_id ) {
	global $wpdb;

	$table_posts = $wpdb->prefix . "posts";
	$table_items = $wpdb->prefix . "woocommerce_order_items";
	$table_itemmeta = $wpdb->prefix . "woocommerce_order_itemmeta";

	// Define HERE the orders status to include in  <==  <==  <==  <==  <==  <==  <==
	$orders_statuses = "'wc-completed', 'wc-processing', 'wc-on-hold'";

	# Requesting All defined statuses Orders IDs for a defined product ID
	$orders_ids = $wpdb->get_col( "
		SELECT DISTINCT $table_items.order_id
		FROM $table_itemmeta, $table_items, $table_posts
		WHERE  $table_items.order_item_id = $table_itemmeta.order_item_id
		AND $table_items.order_id = $table_posts.ID
		AND $table_posts.post_status IN ( $orders_statuses )
		AND $table_itemmeta.meta_key LIKE '_product_id'
		AND $table_itemmeta.meta_value LIKE '$product_id'
		ORDER BY $table_items.order_item_id DESC"
	);
	// return an array of Orders IDs for the given product ID
	return $orders_ids;
}

function get_file_access_counts($product_id, $fileID, $downloadsPerOrder) {
	if (is_user_logged_in() && wc_memberships_is_user_member(get_current_user_id(), 'unlimited')) return false;

	$orders = get_current_user_orders();

	$maxDownloads = 0;
	$downloadCount = 0;
	foreach ($orders as $order) {
		$access_type = get_product_purchase_type($product_id, $order -> id);
		//echo '<!-- 7688 ';var_dump($access_type);echo ' -->';
		if ($access_type && $access_type !== 'live') {
			$maxDownloads += $downloadsPerOrder;

			while (have_rows('field_5a207df5a29ad', $order -> ID)) {
				the_row();

				$downloadFileName = get_sub_field('filename');
				$downloadFileID = explode(':', $downloadFileName)[0];
				if ($downloadFileID == $fileID && get_sub_field('limiting')) $downloadCount++;
			}
		}
	}

	return array(
		'downloadCount' => $downloadCount,
		'maxDownloads' => $maxDownloads
	);
}

add_action( 'send_headers', 'send_download' );

function send_download() {
	$url = $_SERVER["REQUEST_URI"];
	if (substr($url, 0, 20) == '/download-purchase/?') {
		header("Content-type: text/plain");
		$fileID;
		$filePath;
		$accesses;
		$maxPerFile = 0;
		if (!have_rows('other_files', $_GET['product'])) return true;
		while (have_rows('other_files', $_GET['product'])) {
			the_row();

			$filePath = pathinfo(get_attached_file(get_sub_field('file')));
			if ($filePath['basename'] == $_GET['file']) {
				$fileID = get_sub_field('file');
				//$maxPerFile = get_sub_field('max_downloads');
				$accesses = get_file_access_counts($_GET['product'], $fileID, 9999);
				break;
			}
		}

		if (!$fileID) return true;

		if (wc_memberships_is_user_member(get_current_user_id(), 'unlimited')) {
			header('HTTP/1.1 200 OK');
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$filePath['basename'].'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Content-Length: ' . filesize($filePath['dirname'].'/'.$filePath['basename']));
			readfile($filePath['dirname'].'/'.$filePath['basename']);
			exit(0);
		} elseif (!empty($accesses)) {

			$orders = get_current_user_orders();

			foreach ($orders as $order) {
				$wcOrder = new WC_Order( $order -> ID );
				$orderItems = $wcOrder -> get_items();
				foreach ($orderItems as $item) {
					$orderItem = $item -> get_data();

					if ($orderItem['product_id'] == $_GET['product']) {
						$downloadsThisOrder = 0;

						$variation = str_replace('-', '', strtolower($orderItem["name"]));
						if (strpos($variation, 'download') !== false || strpos($variation, 'ondemand') !== false) {
							while (have_rows('field_5a207df5a29ad', $order -> ID)) {
								the_row();

								$downloadFileName = get_sub_field('filename');
								$downloadFileID = explode(':', $downloadFileName)[0];
								if ($downloadFileID == $fileID && get_sub_field('limiting')) $downloadsThisOrder++;
							}
						}

						//if ($downloadsThisOrder < $maxPerFile) {
							add_row('field_5a207df5a29ad', array(
								'field_5a207e03a29ae' => $fileID . ':' . $filePath['basename'], // file ID + name
								'field_5a207e22a29af' => time(), // download time (now)
								'field_5a207e57a29b0' => $_SERVER["REMOTE_ADDR"], // user IP
								'field_5a207e6fa29b1' => true // should this download count toward the limit? (default true)
							), $order -> ID);
						//}
					}
				}
			}
			error_log('sending file '.$_GET['file']);
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$filePath['basename'].'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Content-Length: ' . filesize($filePath['dirname'].'/'.$filePath['basename']));
			readfile($filePath['dirname'].'/'.$filePath['basename']);

			exit(0);
		} else echo 'Access denied';
	}

	if (substr($url, 0, 14) == '/live-register') {
		if (!is_user_logged_in() || !wc_memberships_is_user_member(get_current_user_id(), 'unlimited')) {
			exit('{"error": "this registration endpoint is for unlimited subscribers only"}');
		}
		$product = get_post($_POST['product']);
		if ( is_null($product) || $product -> post_type != 'product' ) exit('{"error": "invalid product id"}');
		if ( get_field('live_datetime', $product -> ID) < time() - 18000 ) exit('{"error": "product already passed"}');

		if (empty($_POST['attendees']) || !count($_POST['attendees'])) {
			exit('{"error": "no emails provided"}');
		}

		$currentAttendees = explode(',', get_field('attendee_emails', $product -> ID));
		foreach ($currentAttendees as $i => $currentAttendee) {
			$currentAttendees[$i] = trim($currentAttendee);
		}
		if (count($currentAttendees) == 1 && trim($currentAttendees[0]) == '') $currentAttendees = array();

		$attendees = array();
		$alreadyRegistered = array();
		foreach ($_POST['attendees'] as $attendeeEmail) {
			$attendeeEmail = trim($attendeeEmail);
			if (in_array($attendeeEmail, $currentAttendees)) $alreadyRegistered[] = $attendeeEmail;
			else $attendees[] = $attendeeEmail;
		}
		if (count($alreadyRegistered)) exit(json_encode(array('warning' => 'Some of the attendees you entered are already registered.', 'list' => $alreadyRegistered)));

		$newAttendees = array_merge($currentAttendees, $attendees);
		update_field('attendee_emails', implode(', ', $newAttendees), $product -> ID);
		echo json_encode(array(
			'success' => true,
			'list' => $attendees,
		));
		wp_update_post( $product );

		/*$wcProduct = new WC_Product_Variable($product -> ID);
		$wcVariations = $wcProduct -> get_available_variations();

		$liveVariationID = 0;
		$liveVariationAttrFormatName = '';
		$liveVariationAttrName = '';
		foreach ($wcVariations as $variation) {
			foreach ($variation['attributes'] as $attrName => $attrVal) {
				$simpleAttrVal = strtolower(str_replace('-', '', $attrVal));
				if (strpos($simpleAttrVal, 'live') !== false && strpos($simpleAttrVal, 'cdrom') === false) {
					$liveVariationID = $variation['variation_id'];
					$liveVariationAttrFormatName = $attrName;
					$liveVariationAttrName = $attrVal;
					break 2;
				}
			}
		}

		echo $liveVariationID . ': '.$liveVariationAttrFormatName.'='.$liveVariationAttrName;

		$args = array(
			'variation' => array( $liveVariationAttrFormatName => $liveVariationAttrName),
		);

		$order = wc_create_order();
		$order->add_product( $liveVariationID, 1, $args );
		$order->set_total( 0 );*/

		exit(0);
	}

	$explodedURL = explode('/', $url);
	if ($explodedURL[1] == 'apps' && $explodedURL[2] == 'downloads' && $explodedURL[3] == 'orders' && filter_var(urldecode(urldecode($explodedURL[4])), FILTER_VALIDATE_EMAIL)) {
		$email = urldecode(urldecode($explodedURL[4]));
		header("Location: /legacy-orders/?accesskey=".base64_encode($email));
		exit(0);
	}
}

//add_action( 'woocommerce_after_order_notes', 'viewer_checkout_fields' );

function viewer_checkout_fields( $checkout ) {
	$ordinals = array( 'First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth',
		'Tenth', 'Eleventh', 'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth', 'Seventeenth',
		'Eighteenth', 'Nineteenth', 'Twentieth', 'Twenty-first', 'Twenty-second', 'Twenty-third', 'Twenty-fourth',
		'Twenty-fifth', 'Twenty-sixth', 'Twenty-seventh', 'Twenty-eighth', 'Twenty-ninth', 'Thirtieth'
	);

	$cartItems = WC()->cart->get_cart();

	foreach ( $cartItems as $cart_item ) {
		//var_dump($cart_item);

		if (!$cart_item['variation_id']) continue;
		$variationName = strtolower(str_replace('-', '', reset($cart_item['variation'])));
		echo "<!-- variation: $variationName -->";
		$format = "";
		/* if (strpos($variationName, 'live') !== false) $format = "Live";
		if (strpos($variationName, 'download') !== false) $format = "On-demand";
		if (strpos($variationName, 'ondemand') !== false) $format = "On-demand"; */
		if ($variationName == "od") $format = "On-demand";
		if ($variationName == "lv" || $variationName == "lc" || $variationName == "ld") $format = "Live";
		if (!$format) continue;

		$quantity = $cart_item['quantity'];
		//if (count($cartItems) > 1 || $quantity > 1) {
			echo '<div id="viewer_info_'.$cart_item['variation_id'].'" class="viewer-info-container"><h3>Viewer Details for '. $cart_item['data']->get_title() .' '.$format.' Webinar</h3>';

			for ($i=0; $i<$quantity; $i++) {
				$n = (string) $i + 1;
				$nth = $ordinals[$i]. ' ';
				if ($quantity == 1) $nth = '';
				woocommerce_form_field( 'cl_vid_'.$cart_item['variation_id'].'_viewer_'.$n.'_name', array(
					'type'          => 'text',
					'class'         => array('form-row-wide viewer-info viewer-top viewer-name viewer-top'),
					'label'         => __($nth . 'Viewer Name'),
				), $checkout->get_value( 'cl_vid_'.$cart_item['variation_id'].'_viewer_'.$n.'_name' ));

				woocommerce_form_field( 'cl_vid_'.$cart_item['variation_id'].'_viewer_'.$n.'_email', array(
					'type'          => 'email',
					'class'         => array('form-row-wide viewer-info viewer-email'),
					'label'         => __($nth . 'Viewer Email'),
				), $checkout->get_value( 'cl_vid_'.$cart_item['variation_id'].'_viewer_'.$n.'_email' ));

				woocommerce_form_field( 'cl_vid_'.$cart_item['variation_id'].'_viewer_'.$n.'_jobtitle', array(
					'type'          => 'text',
					'class'         => array('form-row-wide viewer-info viewer-job viewer-bot'),
					'label'         => __($nth . 'Viewer Job Title/Position'),
				), $checkout->get_value( 'cl_vid_'.$cart_item['variation_id'].'_viewer_'.$n.'_jobtitle' ));
			}

			echo '</div>';
		//}
	}

}

add_action('woocommerce_checkout_process', 'viewer_checkout_fields_process');

function viewer_checkout_fields_process() {
	foreach ($_POST as $fieldName => $value) {
		if (strpos($fieldName, 'cl_vid_') !== 0) continue;

		$variantID = (int) substr($fieldName, 7);
		$productID = wp_get_post_parent_id($variantID);

		if (strpos($fieldName, 'email') !== false) {
			if (!filter_var($value, FILTER_VALIDATE_EMAIL))
				wc_add_notice( __( 'Please enter correct viewer emails for each product.' ), 'error' );

			$currentAttendees = explode(',', get_field('attendee_emails', $productID));
			foreach($currentAttendees as $currentAttendee) {
				$checkoutEmail = trim(strtolower($value));
				$attendeeEmail = trim(strtolower($value));

				if ($checkoutEmail == $attendeeEmail)
					wc_add_notice( __( 'The email address <b>'.$value.'</b> is already registered for this webinar.' ), 'error' );
			}

		} elseif (empty($value))
			wc_add_notice( __( 'Please enter correct viewer information for each product.' ), 'error' );
	}
}

add_action( 'woocommerce_checkout_update_order_meta', 'viewer_checkout_fields_update_order_meta' );

function viewer_checkout_fields_update_order_meta( $order_id ) {
	/* if ( ! empty( $_POST['my_field_name'] ) ) {
		update_post_meta( $order_id, 'My Field', sanitize_text_field( $_POST['my_field_name'] ) );
	} */

	foreach ($_POST as $fieldName => $value) {
		if (strpos($fieldName, 'cl_vid_') !== 0) continue;

		$variantID = (int) substr($fieldName, 7);
		$productID = wp_get_post_parent_id($variantID);

		update_post_meta($order_id, $fieldName, sanitize_text_field($value));
	}
}

function woocommerce_button_proceed_to_checkout() {
       $checkout_url = WC()->cart->get_checkout_url();
       ?>
       <a href="<?php echo $checkout_url; ?>" class="checkout-button button alt wc-forward"><?php _e( 'Checkout', 'woocommerce' ); ?></a>
       <?php
     }

add_action('woocommerce_account_dashboard', 'ftm_list_customer_products');
function ftm_list_customer_products() {
	$orders = get_current_user_orders();

	$ownedProducts = array();
	foreach ($orders as $order) {
		//echo '<!-- ';var_dump($order -> ID);echo ' -->';
		$wcOrder = new WC_Order( $order -> ID );
		if ($wcOrder -> get_status() != 'completed') continue;

		$orderDate = date('n/j/y', strtotime($wcOrder -> get_date_created()));

		$orderItems = $wcOrder -> get_items();
		foreach ($orderItems as $item) {
			$prodId = $item -> get_product_id();
			$variationId = $item -> get_variation_id();

			if ($prodId && !$variationId) {
				$ownedProducts[$prodId] = array('date' => $orderDate, 'attrs' => $attrs, 'orderid' => $order -> ID);
			} elseif ($prodId && $variationId) {
				$variant = new WC_Product_Variation($variationId);
				$attrs = array($item -> get_meta('pa_format', true));
				//foreach ($attrs as $attr => $val) {
					//$val = strtolower($val);
					//if (strpos($val, 'download') !== false || strpos($val, 'ondemand') !== false) {
						$ownedProducts[$prodId] = array('attrs' => $attrs, 'date' => $orderDate, 'orderid' => $order -> ID);
					//}
				//}
			}
		}
	}

	if (count($ownedProducts)) {
		//echo '<h2 id="my-account-owned-product-header">MY ONLINE TRAINING</h2>';
		echo '<div id="my-account-owned-product-list">';
		echo '<table><thead><tr><th colspan="3">My Online Training</th></tr><tr><th>Product</th><th>Order Date</th><th>Format</th></tr></thead><tbody>';

		foreach ($ownedProducts as $prodId => $prodDetails) {
			if (!count($prodDetails['attrs'])) $attrString = '&mdash;';
			else {
				foreach ($prodDetails['attrs'] as $i => $thisAttr) {
					$newThisAttr = array();
					$thisAttr = str_replace('-', '', strtolower($thisAttr));
					if (strpos($thisAttr, 'live') !== false) $newThisAttr[] = 'Live';
					if (strpos($thisAttr, 'ondemand') !== false || strpos($thisAttr, 'downl') !== false) $newThisAttr[] = 'On-demand';
					if (strpos($thisAttr, 'cd') !== false) $newThisAttr[] = 'CD-ROM';
					if (strpos($thisAttr, 'report') !== false) $newThisAttr[] = 'Report';
					$attrs[$i] = implode(" + ", $newThisAttr);
				}
				$attrString = implode(", ", $attrs);
			}
			//echo '<a href="'.get_the_permalink($prodId).'" class="product owned-product"> <span style="color:#E99137;">&gt;</span> '.get_the_title($prodId)."</a>\n";
			echo '<tr data-productid="'.$prodId.'" data-orderid="'.$prodDetails['orderid'].'">';
			echo '<td><a href="'.get_the_permalink($prodId).'">'.get_the_title($prodId).'</a></td>';
			echo '<td>'.$prodDetails['date'].'</td><td>'.$attrString.'</td></tr>';
		}

		echo '</tbody></table></div>';
	}
}

function save_utm_parameters() {
	global $wp_session;
	$captureURL = false;
	foreach ($_GET as $param => $val) {
		$param = strtolower($param);
		if (strpos($param, 'utm_') === 0 && !empty($val)) {
			$captureURL = true;
			wc_setcookie('ftm_'.$param, urldecode($val), time() + (86400 * 28));
		}
	}
	if ($captureURL && !isset($_COOKIE['ftm_landing_url'])) {
		wc_setcookie('ftm_landing_url', str_replace('=', '', base64_encode($_SERVER['REQUEST_URI'])));
	}
}
//add_action('send_headers', 'save_utm_parameters');

function save_utm_order_parameters($order_id) {
	//wp_mail('dstrout@firsttracksmarketing.com', 'new order '.$order_id.' cookies', print_r($_COOKIE, true));
	foreach ($_COOKIE as $cookie_name => $cookie_val) {
		$cookie_name = strtolower($cookie_name);
		if (strpos($cookie_name, 'ftm_utm_') === 0 && !empty($cookie_val)) {
			update_post_meta($order_id, substr($cookie_name, 4), $cookie_val);
		}
		if ($cookie_name == 'ftm_landing_url' && !empty($cookie_val)) {
			update_post_meta($order_id, 'landing_site', get_home_url(null, base64_decode($cookie_val)));
		}
	}
}
//add_action('woocommerce_new_order', 'save_utm_order_parameters');

function save_utm_order_parameters_ajax() {
	$utms = array();
	foreach ($_POST as $post_key => $post_value) {
		if (strpos($post_key, 'utm_') === 0 || $post_key == 'landing_site') $utms[$post_key] = $post_value;
	}
	if (!count($utms)) { exit('{"success": false, "error": "No UTMs"}'); }

	if (empty($_POST['order']) || !is_numeric($_POST['order']) || empty($_POST['order_key'])) { exit('{"success": false, "error": "No/invalid order number or key"}'); }

	$order_id = wc_sequential_order_numbers()->find_order_by_order_number( $_POST['order'] );

	$wc_order = wc_get_order($order_id);
	if (!$wc_order) { exit('{"success": false}'); }

	if ($wc_order -> get_order_key() != $_POST['order_key']) { exit('{"success": false, "error": "Order key not matched"}'); }

	if (get_post_meta($order_id, 'ajax_utms_sent', true) == 1) { exit('{"success": false, "error": "UTMs already submitted via AJAX"}'); }

	foreach ($utms as $utm_param => $utm_value) {
		update_post_meta($order_id, $utm_param, $utm_value);
	}
	update_post_meta($order_id, 'ajax_utms_sent', 1);
	exit('{"success": true}');
}
add_action('wp_ajax_save_utm_order_parameters', 'save_utm_order_parameters_ajax');
add_action('wp_ajax_nopriv_save_utm_order_parameters', 'save_utm_order_parameters_ajax');

function fix_cc_expiry_label($g) {
	//echo '<pre>'. htmlentities(print_r($g, true)) .'</pre>';
	$g['card-expiry-field'] = str_replace('Expiry ', 'Exp. ', $g['card-expiry-field']);
	return $g;
}
add_filter( 'woocommerce_credit_card_form_fields', 'fix_cc_expiry_label', 10, 1 );

function tribe_set_link_website ( $link, $event_id ) {
	$event_end = strtotime(get_post_meta($event_id, '_EventEndDate', true));
	$event_past = time() > $event_end;
	$product_url = tribe_get_event_website_url( $event_id );
	$product_id = url_to_postid($product_url);

	/*if (strpos($product_url, '/product/')) {
		$product_slug = explode('/', explode('/product/', $product_url)[1])[0];
		if (!empty($product_slug)) {
			$product_lookup = get_posts(array(
				'post_type' => 'product',
				'name' => $product_slug,
			));
			if (is_array($product_lookup) && count($product_lookup) == 1) $product_id = $product_lookup[0] -> ID;
		}
	}*/

	if (!$product_id && $event_past) return '#';

	if (is_object_in_term($product_id, 'product_visibility', 'hidden')) return '#';

	if (!empty($product_url)) return $product_url;

	return $link;
}
add_filter( 'tribe_get_event_link', 'tribe_set_link_website', 100, 2 );

function add_price_to_label($origLabel, $y) {
	$prod = wc_get_product(get_the_id());
	if ($prod -> get_type() != 'variable') return $origLabel;

	$variations = $prod -> get_available_variations();
	foreach ($variations as $variation) {
		$variation = wc_get_product($variation['variation_id']);
		$variationName = trim(explode('(', str_replace(get_the_title().' - ', '', $variation -> get_formatted_name()))[0]);

		if ($variationName == $origLabel) $origLabel .= $variation -> get_price_html();
	}

	return $origLabel;
}
//add_filter('woocommerce_variation_option_name', 'add_price_to_label', 10, 2);

function bundle_variation_dropdown_to_radio($html, $args) {
    global $product, $test_products_ids;
	if (is_admin() || in_array($product->get_id(),$test_products_ids)) return $html;

	$prod = $args['product'];
	if ($prod -> get_type() != 'variable') return $html;

	$topProd = wc_get_product(get_the_id());

	$variations = $prod -> get_available_variations();
	$variationPricing = array();
	foreach ($variations as $variation) {
		//ftm_dump($variation);
		if (count($variation['attributes']) > 1) return $html;
		$attr = reset($variation['attributes']);
		$variationPrice = $variation['display_price'];
		$variationPrice = $variation['display_regular_price'];
		//ftm_dump($variation);
		if ($prod -> get_id() != $topProd -> get_id() && $topProd -> get_type() == 'bundle') {
			$bundledItems = $topProd -> get_bundled_items();
			foreach ($bundledItems as $bundledItem) {
				if ($bundledItem -> item_data['product_id'] == $prod -> get_id() && $bundledItem -> item_data['discount']) {
					$discount = (float) $bundledItem -> item_data['discount'];
					//$variationPrice = $variationPrice * ( (100 - $discount) / 100 );
				}
			}
		}

		$variationPricing[$attr] = $variationPrice;
	}

	$xml = simplexml_load_string($html);
	$name = explode('"', explode('name="', $html)[1])[0].'_radio';
	$newHTML = "<style>\nselect[data-origdropdown=\"1\"]{display:none !important;}\ndiv.product-radios{display:block !important;}\n</style>\n".
	"<noscript><style>\nselect[data-origdropdown=\"1\"]{display:block !important;}\ndiv.product-radios{display:none !important;}\n</style>".
	"</noscript>\n<div class=\"product-radios\">".str_replace('<select ', '<select data-origdropdown="1" ', $html);
	//var_dump($xml);
	foreach ($xml as $option) {
		//ftm_dump($option);
		$label = (string) $option;
		$attr = (string) $option['value'];
		$selected = (string) $option['selected'];
		if (!$attr || !isset($variationPricing[$attr])) continue;

		$newHTML .= '<label><input type="radio" value="'.$attr.'" name="'.$name.'"';
		if ($selected == "selected") $newHTML .= ' checked="checked"';
		$newHTML .= '>'.$label.' <span>$'.number_format($variationPricing[$attr], 2).'</span></label>';
	}
	$newHTML .= '</div>';

	if ($newHTML) return $newHTML;
	else return $html;
}
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'bundle_variation_dropdown_to_radio', 10, 2);

function ftm_dump($input) {
	if ($_SERVER['REMOTE_ADDR'] == '50.245.7.25' || $_SERVER['REMOTE_ADDR'] == '76.119.85.159') var_dump($input);
}

function ftm_dump_print($input) {
	if ($_SERVER['REMOTE_ADDR'] == '50.245.7.25') echo print_r($input, true);
}

function wrap_gateway_title($title) {
	if (is_admin()) return $title;
	if (defined( 'DOING_AJAX' ) && DOING_AJAX) return $title;

	return '<span class=\'gateway-title\'>'.$title.'</span>';
}
add_filter('woocommerce_gateway_title', 'wrap_gateway_title');

function add_dynamic_product_content($order, $a = false, $b = false, $email = false) {

	if ($order -> get_id() == 85240) var_dump($email);

	$items = $order -> get_items();

	$live = array('lv', 'ld', 'cl');
	$on_demand = array('od', 'ld');
	$cd = array('cd', 'cl');

	$product_types = array(
		'live' => false,
		'ondemand' => false,
		'cd' => false
	);
	foreach ($items as $item) {
		$prod_id = $item -> get_product_id();
		$varation_id = $item -> get_variation_id();
		$sku = "";

		if ($varation_id) {
			$varation = wc_get_product($varation_id);
			$sku = $varation -> get_sku();
		} elseif ($prod_id) {
			$product = wc_get_product($prod_id);
			$sku = $product -> get_sku();
		}

		if (!empty($sku)) {
			$sku_parts = explode('-', $sku);
			if (count($sku_parts) == 1) continue;
			$product_type = strtolower(end($sku_parts));

			if (in_array($product_type, $live)) $product_types['live'] = true;
			if (in_array($product_type, $on_demand)) $product_types['ondemand'] = true;
			if (in_array($product_type, $cd)) $product_types['cd'] = true;
		}

		if ($product_types['live'] === true && $product_types['ondemand'] === true && $product_types['cd'] === true) break;
	}

	foreach ($product_types as $type => $type_enabled) {
		if ($type_enabled) {
			the_field('email_'.$type.'_text', 'options');
		}
	}

}
add_action('woocommerce_email_customer_details', 'add_dynamic_product_content', 99, 4);

function link_product_name($name, $item) {
	if (is_admin()) return $name;

	$product_id = $item -> get_product_id();
	if (!$product_id) return $name;
	$product_post = get_post($product_id);
	if (is_null($product_post) || $product_post -> post_type != 'product') return $name;

	return '<a href="'.get_the_permalink($product_post).'">'.$name.'</a>';
}
add_filter('woocommerce_order_item_name', 'link_product_name', 20, 2);

function customize_account_links( $menu_links ){

	//unset( $menu_links['downloads'] );
	unset( $menu_links['memberships'] );

	return $menu_links;

}
add_filter ( 'woocommerce_account_menu_items', 'customize_account_links', 100, 1 );

function long_checkout() {
	sleep(25);
}
//add_action('woocommerce_after_checkout_validation', 'long_checkout');

function add_gform_utm_fields($value, $field, $name) {
	$field_label = $field -> label;
	if ($field_label == 'landing_site' && !$value) {
		if ($_COOKIE['ftm_init_url']) {
			return base64_decode($_COOKIE['ftm_init_url']);
		} else {
			foreach ($_GET as $get_var => $get_value) {
				if (strpos($get_var, 'utm') === 0) {
					return "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
				}
			}
			return $value;
		}
	} else return $value;
}
add_filter( 'gform_field_value', 'add_gform_utm_fields', 5, 3 );

function tl_subscriptions_url() {
	return '/plans-pricing/';
}
add_filter('woocommerce_subscriptions_message_store_url', 'tl_subscriptions_url');

function show_bundle_select_message() {
	echo '<div class="bundle-message"><strong>Important:</strong> '.
	'Please select a format for each part of this series below. Final discounted rate will be reflected once all selections are made.</div>';
}
add_action('woocommerce_before_bundled_items', 'show_bundle_select_message');

//add_action( 'pre_get_posts', 'sort_products');
function sort_products($query) {
    if ($query->is_main_query() && !is_admin() && $_SERVER['REMOTE_ADDR'] == '50.245.7.25') {
        //$query->set( 'post_type', 'some_post_type' );
				$query -> set('orderby', 'meta_value_num');
				$query -> set('order', 'DESC');
				$query -> set('meta_key', 'live_datetime');
				$query -> set('meta_compare', '>=');
				$query -> set('meta_value_name', '0');
				/*$query -> set('meta_query', array(array(
					'key' => 'live_datetime',
					'value' => 0,
					'compare' => '>='
				)));*/

				//echo '<pre>'; var_dump($query); echo '</pre>';
        return;
    }
}

add_action('send_headers', 'handle_legacy_redirects');
function handle_legacy_redirects() {
	$request_path = $_SERVER['REQUEST_URI'];
	if (strpos($request_path, '/pages/') === 0 || strpos($request_path, '/products/') === 0) {
		$slug = explode('/', $request_path)[2];
		header('Location: '.get_home_url().'/product/'.$slug);
		exit(0);
		//var_dump(get_home_url());

	}
}

add_action( 'wp_enqueue_scripts', 'dequeue_woocommerce_cart_fragments', 11);
function dequeue_woocommerce_cart_fragments() { if (is_front_page() || is_single()) wp_dequeue_script('wc-cart-fragments'); }

function customize_attribute_label($label, $name, $product) {
	$custom_label = get_field('add_to_cart_head', get_the_id());
	if ($custom_label) return $custom_label;
	else return $label;
}
add_filter('woocommerce_attribute_label', 'customize_attribute_label', 10, 3);

function redirect_old_products() {
  $product_id = get_the_ID();
  $product_unavailable = get_field('product_outdated', $product_id);
  $product_redirect = get_field('redirect', $product_id);

  /*if ($_SERVER['REMOTE_ADDR'] == '76.24.192.251') {
    header('Content-type: text/plain');
    var_dump($product_redirect);
    exit(0);
  }*/

  $user_has_access = get_product_purchase_type($product_id);
  $no_access = $user_has_access === false && !current_user_can('administrator');

  if ($no_access && $product_unavailable && $product_redirect) {
    header('Location: '.$product_redirect);
    exit(0);
  } elseif ($no_access && $product_unavailable && !$product_redirect) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
    include( get_query_template( '404' ) );
    die();
  }

}
add_action('wp', 'redirect_old_products');

/*
add_filter( 'woocommerce_rest_prepare_shop_order_object', function( $response, $object, $request ) {
    if( isset( $request['modified_after'] ) && ! isset( $request['after'] ) ) {
		//error_log( print_r( $response, true ) );
		//error_log( print_r( $object, true ) );
		//error_log( print_r( $request, true ) );

		$order_data = $response->get_data();

		foreach ( $order_data as $key => $item ) {
			error_log( print_r( $key, true ) );
			error_log( print_r( $item, true ) );
			$orderDate = date('Y-m-dTH:i:s', strtotime($key -> get_date_modified()));
			if($orderDate > $request['modified_after']){
				unset($order_data[$key]);
			}

		}

		$response->data = $order_data;
		return $response;
		//if($orderDate > $request['modified_after']){
		//	return $response;
		//}
		//else {
		//	return null;
		//}
    }
    return $response;
}, 10, 3 );
*/


/*
add_filter( 'woocommerce_rest_shop_order_object_query', function( $args, $request ) {
	if( isset( $request['modified_after'] ) ) {
		error_log( print_r( $args, true ) );
		error_log( print_r( $request, true ) );
	}


	return $args;
}, 10, 2 );


add_filter( 'woocommerce_rest_query_vars', function( $valid_vars ) {
	//if( isset( $request['modified_after'] ) ) {
	//	error_log( print_r( $valid_vars, true ) );
	//	error_log( print_r( $request, true ) );
	//}


	$rest_valid = array(
		'modified_after'
	);
	$valid_vars = array_merge( $valid_vars, $rest_valid );


	return $valid_vars;
}, 10, 1 );




add_filter( 'woocommerce_rest_shop_order_query', function($args, $request ){

	//error_log( print_r( $args, true ) );
	//error_log( print_r( $request, true ) );

	if( isset( $request['modified_after'] ) && ! isset( $request['after'] ) ) {
        $args['date_query'][0]['after'] = $request['modified_after'];
		$args['date_query'][0]['column'] = 'post_modified';


	//error_log( print_r( $args, true ) );
	//error_log( print_r( $request, true ) );
    }
    return $args;

}, 10, 2 );
*/

add_action('wp_head', function() {
	if (current_user_can('administrator')) return;
?>
<!-- Anti-flicker snippet (recommended)  -->
<style>.async-hide { opacity: 0 !important} </style>
<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.start=1*new Date;
h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
(a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);h.timeout=c;
})(window,document.documentElement,'async-hide','dataLayer',4000,
{'GTM-NLX42W4':true});</script>
<?php
});

add_action('wp_enqueue_scripts', 'enqueue_scripts');

function enqueue_scripts(){

	global $post;
    //Css and JS only for this poroduct

    global $test_products_ids;
	if (in_array($post->ID, $test_products_ids))
	{
		//Themes js
		wp_register_script('theme_script', get_template_directory_uri() . '/assets/js/trainingleader.js', array(), wp_get_theme()->get('Version'), true);
		wp_enqueue_script('theme_script');

		//Themes css
		wp_register_style('themecss', get_template_directory_uri() . '/assets/css/trainingleader.css', array(), wp_get_theme()->get('Version'), false);
		wp_enqueue_style('themecss');
	}
}

//[cta-block]
function cta_block_shortcode($atts){

	global $product;

	$product_id = $product->get_id();
	$product_type = $product->get_type();

	if (!$product_type === "variable")
	    return;

	$variations = $product->get_available_variations();
	$display_price = number_format((float)$variations[0]['display_price'], 2, '.', '');

	extract(shortcode_atts(array(), $atts));

	ob_start();

	if(file_exists(locate_template('templates/product/cta-block.php'))){
		include (locate_template('templates/product/cta-block.php'));
	}

	return ob_get_clean();
}
add_shortcode('cta-block','cta_block_shortcode');


//[cta-bundle-block]
function cta_bundle_block_shortcode($atts){

	global $product;
	$product_type = $product->get_type();

	$title = (!empty($atts['title'])) ? $atts['title'] : "Get This Now";

	$product_cta_image = get_field('cta_image');

	if (!$product_type === "bundle")
		return;

    $product_id = $product->get_id();

    $display_price = number_format((float)get_post_meta($product_id, 'display_price')[0], 2, '.', '');
    $regular_price = number_format((float)get_post_meta($product_id, '_regular_price')[0], 2, '.', '');
    $sale_price = number_format((float)get_post_meta($product_id, '_sale_price')[0], 2, '.', '');

    extract(shortcode_atts(array(), $atts));

    ob_start();

    if(file_exists(locate_template('templates/product/cta-bundle-block.php'))){
        include (locate_template('templates/product/cta-bundle-block.php'));
    }

    return ob_get_clean();

}
add_shortcode('cta-bundle-block','cta_bundle_block_shortcode');


//[benefits]
function benefits_shortcode($atts){

	global $product;

	if(empty($product))
	    return;


	$product_id = (!empty($atts['product_id'])) ? $atts['product_id'] : $product->get_id();


	$benefits_label      = (!empty($atts['title'])) ? $atts['title'] : get_field('benefits_label', $product_id);
	$benefits_top_text   = get_field('benefits_top_text' , $product_id);
	$benefits_text_left  = get_field('benefits_text_left', $product_id);
	$benefits_text_right = get_field('benefits_text_right', $product_id);

	extract(shortcode_atts(array(), $atts));

	ob_start();

	if(file_exists(locate_template('templates/product/benefits-text.php'))){
		include (locate_template('templates/product/benefits-text.php'));
	}

	return ob_get_clean();

}
add_shortcode('benefits','benefits_shortcode');

add_filter( 'woocommerce_add_to_cart_fragments', 'iconic_cart_count_fragments', 10, 1 );

function iconic_cart_count_fragments( $fragments ) {

    $fragments['div.header-cart-count'] = '<div class="header-cart-count">' . WC()->cart->get_cart_contents_count() . '</div>';

    return $fragments;

}

add_filter( 'woocommerce_continue_shopping_redirect', 'bbloomer_change_continue_shopping' );

function bbloomer_change_continue_shopping() {
   return wc_get_page_permalink( 'shop' );
}

// Add a text input field inside the add to cart form
/*** add_action('woocommerce_single_product_summary','add_custom_text_field_single_product', 2 );
function add_custom_text_field_single_product(){
    global $product;

    if( $product->is_type('variable') ){
        add_action('woocommerce_before_single_variation','custom_product_text_input_field', 30 );
    } else {
        add_action('woocommerce_before_add_to_cart_button','custom_product_text_input_field', 30 );
    }
}

function custom_product_text_input_field(){
    echo '<div class="hidden-field">
    <p class="form-row product-coupon form-row-wide" id="product-coupon_field" data-priority="">
        <label for="product-coupon" class="">' . __("Do you have a coupon code?") . '</label>
        <span class="woocommerce-input-wrapper">
            <input type="text" class="input-text " name="product-coupon" id="product-coupon" placeholder="'.__("Coupon code").'" value="">
        </span>
    </p></div>';
}

// Apply the coupon code from product custom text imput field
add_filter('woocommerce_add_cart_item_data', 'coupon_code_product_add_to_cart', 20, 3);
function coupon_code_product_add_to_cart($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['product-coupon']) && ! empty($_POST['product-coupon'])) {
        WC()->cart->apply_coupon( sanitize_title( $_POST['product-coupon'] ) );
    }
    return $cart_item_data;
}  *****/

add_filter( 'body_class', function( $classes ) {
	global $woocommerce;

	$coupon = 'actnow10';

    if( in_array($coupon, $woocommerce->cart->get_applied_coupons() )){
        return array_merge( $classes, array( 'actnow10-applied' ) );
    } else {
		return $classes;
	}
} );

function cms_enqueue_couponjs() {
?>
	<script>
		jQuery( document.body ).on( 'updated_cart_totals', function(){ if( jQuery('.cart-discount').hasClass('coupon-actnow10') ){ jQuery( 'body' ).addClass('actnow10-applied');	} });
	</script>
<?php
}
add_action('wp_footer', 'cms_enqueue_couponjs' );


/* Remove footer embed stuff from iframe on covid19-action-center page */
add_filter('embed_site_title_html','__return_false');
remove_action( 'embed_content_meta', 'print_embed_comments_button' );
remove_action( 'embed_content_meta', 'print_embed_sharing_button' );






/* Variation B and C product quantity replace up and down arrows */

add_action( 'woocommerce_after_add_to_cart_quantity', 'ts_quantity_plus_sign' );

function ts_quantity_plus_sign() {
	if ( has_term( 'variant-b', 'product_cat' ) ) {
   		echo '<button type="button" class="plus" >+</button>';
	}
	if ( has_term( 'variant-c', 'product_cat' ) ) {
   		echo '<button type="button" class="plus" >+</button>';
	}
	if ( has_term( 'variant-b-rev-1', 'product_cat' ) ) {
   		echo '<button type="button" class="plus" >+</button>';
	}
	if ( has_term( 'variant-b-rev-2', 'product_cat' ) ) {
   		echo '<button type="button" class="plus" >+</button>';
	}
	if ( has_term( 'variant-b-rev-2-allow-in-expert', 'product_cat' ) ) {
   		echo '<button type="button" class="plus" >+</button>';
	}
	if ( has_term( 'variant-c-rev-1', 'product_cat' ) ) {
   		echo '<button type="button" class="plus" >+</button>';
	}
   		echo '<button type="button" class="plus" >+</button>';
}

add_action( 'woocommerce_before_add_to_cart_quantity', 'ts_quantity_minus_sign' );

function ts_quantity_minus_sign() {
	if ( has_term( 'variant-b', 'product_cat' ) ) {
   		echo '<button type="button" class="minus" >-</button>';
	}
	if ( has_term( 'variant-c', 'product_cat' ) ) {
   		echo '<button type="button" class="minus" >-</button>';
	}
	if ( has_term( 'variant-b-rev-1', 'product_cat' ) ) {
   		echo '<button type="button" class="minus" >-</button>';
	}
	if ( has_term( 'variant-b-rev-2', 'product_cat' ) ) {
   		echo '<button type="button" class="minus" >-</button>';
	}
	if ( has_term( 'variant-b-rev-2-allow-in-expert', 'product_cat' ) ) {
   		echo '<button type="button" class="minus" >-</button>';
	}
	if ( has_term( 'variant-c-rev-1', 'product_cat' ) ) {
   		echo '<button type="button" class="minus" >-</button>';
	}
   		echo '<button type="button" class="minus" >-</button>';
}

add_action( 'wp_footer', 'ts_quantity_plus_minus' );

function ts_quantity_plus_minus() {

   // To run this on the single product page
   if ( ! is_product() ) return;
   ?>
   <script type="text/javascript">

      jQuery(document).ready(function($){

            $('form.cart').on( 'click', 'button.plus, button.minus', function() {

            // Get current quantity values
            var qty = $( this ).closest( 'form.cart' ).find( '.qty' );
            var val   = parseFloat(qty.val());
            var max = parseFloat(qty.attr( 'max' ));
            var min = parseFloat(qty.attr( 'min' ));
            var step = parseFloat(qty.attr( 'step' ));

            // Change the value if plus or minus
            if ( $( this ).is( '.plus' ) ) {
               if ( max && ( max <= val ) ) {
                  qty.val( max );
               }
            else {
               qty.val( val + step );
                 }
            }
            else {
               if ( min && ( min >= val ) ) {
                  qty.val( min );
               }
               else if ( val > 1 ) {
                  qty.val( val - step );
               }
            }

         });

      });

   </script>
   <?php
}

/* End - Variation B and C product quantity replace up and down arrows */

function add_acf_columns ( $columns ) {
	return array_merge ( $columns, array (
  	'conversion_channel' => __ ( 'Conversion Channel' )
	) );
}

add_filter ( 'manage_edit-shop_order_columns', 'add_acf_columns' );

	function populate_acf_column ( $column, $post_id ) {
		if ( $column == 'conversion_channel' ) {
			$data = get_post_meta ( $post_id, 'conversion_channel', true );
		 		if ( $data == "" ) {
			 		echo "Online/Web";
		 		} else {
			 echo $data;
		 }
	 }
 }
 add_action( 'manage_shop_order_posts_custom_column', 'populate_acf_column', 10, 2 );

/**
 * Change the default state and country on the checkout page
 */
add_filter( 'default_checkout_billing_country', 'change_default_checkout_country' );
add_filter( 'default_checkout_billing_state', 'change_default_checkout_state' );

function change_default_checkout_country() {
  return 'US'; // country code
}

function change_default_checkout_state() {
  return 'Select an option…'; // state code
}

add_filter( 'get_search_form', 'rlv_modify_search_form' );
function rlv_modify_search_form( $form ) {
	$form = str_replace( 'value="Search"', 'value="Submit"', $form );
	return $form;
}

function wpforo_search_form( $html ) {

	if(is_archive()){
		$html = str_replace( 'placeholder="Search ', 'placeholder="Search term', $html );

	}else{
        $html = str_replace( 'placeholder="Search ', 'placeholder="What would you like to learn?', $html );
    }

        return $html;
}
add_filter( 'get_search_form', 'wpforo_search_form' );

add_filter('relevanssi_excerpt_content', 'custom_fields_to_excerpts', 10, 3);

function custom_fields_to_excerpts($content, $post, $query) {
    $custom_field = get_post_meta($post->ID, 'custom_field_1', true);
    $content .= " " . $custom_field;
    $custom_field = get_post_meta($post->ID, 'custom_field_2', true);
    $content .= " " . $custom_field;
    return $content;
}

//Turn off Comments on Media
function filter_media_comment_status( $open, $post_id ) {
    $post = get_post( $post_id );
    if( $post->post_type == 'attachment' ) {
        return false;
    }
    return $open;
}
add_filter( 'comments_open', 'filter_media_comment_status', 10 , 2 );

add_filter( 'woocommerce_bundled_item_is_optional_checked', 'wc_pb_is_optional_item_checked', 10, 2 );

function wc_pb_is_optional_item_checked( $checked, $bundled_item ) {
	if ( ! isset( $_GET[ 'update-bundle' ] ) ) {
		$checked = fasle;
	}

	return $checked;
}




function filter_resource()
{
	$searchtext = $_POST["searchtext"];
	$category = $_POST["term_name"];
	$paged = $_POST["paged"];
	$postPerPage = $_POST["postperpage"];
	$currentPostCount = $_POST["currentPostCount"];
	$type = $_POST["type"];

	$args = array(
		'post_type' => 'free-resource',
		'posts_per_page' => $postPerPage,
		'post_status' => 'publish',
		'orderby' => array('date' => 'ASC'),
	);

	$args_total = array(
		'post_type' => 'free-resource',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);

	if (!empty($searchtext)) {
		$args['s'] = $searchtext;
		$args_total['s'] = $searchtext;
	}

	if ($category != null && !empty($category)) {
		$args_total['tax_query'] = array(
			array(
				'taxonomy' => 'free-resource_category',
				'field'    => 'term_id',
                'terms'    => $category,
			),
		);
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'free-resource_category',
				'field'    => 'term_id',
                'terms'    => $category,
			),
		);
	}

	if ($paged && $type == "loadmore") {
		$args['paged'] = $paged;
	} else {
		$args['paged'] = 1;
	}

	wp_reset_query();

	$loop_total = new WP_Query($args_total);
	$totalPost = $loop_total->found_posts;
	ob_start();

	wp_reset_query();
	$my_query = null;
	$my_query = new WP_Query($args);

	// echo "<pre>";
	// print_r($my_query);
	// echo "</pre>";

	if ($my_query->have_posts()) {
		while ($my_query->have_posts()) : $my_query->the_post();
		 echo get_template_part( 'template-parts/content', 'resources' );
		endwhile;

		echo	'<input type="hidden" value="' . $totalPost . '" id="total_post_count" />';
		echo	'<input type="hidden" value="' . $currentPostCount . '" id="current_post_count" />';
	} else {
		echo "false";
	}

	$output = ob_get_contents();
	ob_end_clean();
	wp_send_json_success($output);
}

add_action("wp_ajax_filter_resource", "filter_resource");
add_action("wp_ajax_nopriv_filter_resource", "filter_resource");



add_filter( 'woocommerce_bundled_item_discount_from_regular', 'wc_pb_bundled_item_discount_from_regular', 10, 2 );

function wc_pb_bundled_item_discount_from_regular( $from_regular, $bundled_item ) {
	return true;
}

function cptui_register_my_taxes_resource_topic() {
	$labels = [
		"name" => __( "Topics", "codingleader" ),
		"singular_name" => __( "Topic", "codingleader" ),
	];
	$args = [
		"label" => __( "Topics", "codingleader" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'resource_topic', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "resource_topic",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "resource_topic", [ "free-resource" ], $args );
}
add_action( 'init', 'cptui_register_my_taxes_resource_topic' );

/**
 * Links previous orders to a new customer upon registration.
 *
 * @param int $user_id the ID for the new user
 */
function sv_link_orders_at_registration( $user_id ) {
    wc_update_new_customer_past_orders( $user_id );
}
add_action( 'woocommerce_created_customer', 'sv_link_orders_at_registration' ); 

//assign user in guest order
add_action( 'woocommerce_new_order', 'action_woocommerce_new_order', 10, 1 );
function action_woocommerce_new_order( $order_id ) {
	$order = new WC_Order($order_id);
	$user = $order->get_user();
	
	if( !$user ){
		//guest order
		$userdata = get_user_by( 'email', $order->get_billing_email() );
		if(isset( $userdata->ID )){
			//registered
			update_post_meta($order_id, '_customer_user', $userdata->ID );
		}else{
			//Guest
		}
	}
}
 


function action_woocommerce_thankyou( $order_id ) {
    // Determines whether the current visitor is a logged in user.
    if ( is_user_logged_in() ) return;
    
    // Get $order object
    $order = wc_get_order( $order_id );
    
    // Get the user email from the order
    $order_email = $order->get_billing_email();

    // Check if there are any users with the billing email as user or email
    $email = email_exists( $order_email );  
    $user = username_exists( $order_email );

    // If the UID is null, then it's a guest checkout (new user)
    if ( $user == false && $email == false ) {
        // Random password with 12 chars
        $random_password = wp_generate_password();
        
        // Firstname
        $first_name = $order->get_billing_first_name();
        
        // Lastname
        $last_name = $order->get_billing_last_name();
        
        // Role
        $role = 'customer';

        // Create new user with email as username, newly created password and userrole          
        $user_id = wp_insert_user( 
            array(
                'user_email' => $order_email,
                'user_login' => $order_email,
                'user_pass'  => $random_password,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'role'       => $role,
            )
        );
        
        // Get all WooCommerce emails Objects from WC_Emails Object instance
        $emails = WC()->mailer()->get_emails();

        // Send WooCommerce "Customer New Account" email notification with the password
        $emails['WC_Email_Customer_New_Account']->trigger( $user_id, $random_password, true );

        // (Optional) WC guest customer identification
        //update_user_meta( $user_id, 'guest', 'yes' );

        // User's billing data
        update_user_meta( $user_id, 'billing_address_1', $order->get_billing_address_1() );
        update_user_meta( $user_id, 'billing_address_2', $order->get_billing_address_2() );
        update_user_meta( $user_id, 'billing_city', $order->get_billing_city() );
        update_user_meta( $user_id, 'billing_company', $order->get_billing_company() );
        update_user_meta( $user_id, 'billing_country', $order->get_billing_country() );
        update_user_meta( $user_id, 'billing_email', $order_email );
        update_user_meta( $user_id, 'billing_first_name', $order->get_billing_first_name() );
        update_user_meta( $user_id, 'billing_last_name',  $order->get_billing_last_name() );
        update_user_meta( $user_id, 'billing_phone', $order->get_billing_phone() );
        update_user_meta( $user_id, 'billing_postcode', $order->get_billing_postcode() );
        update_user_meta( $user_id, 'billing_state', $order->get_billing_state() );

        // User's shipping data
        update_user_meta( $user_id, 'shipping_address_1', $order->get_shipping_address_1() );
        update_user_meta( $user_id, 'shipping_address_2', $order->get_shipping_address_2() );
        update_user_meta( $user_id, 'shipping_city', $order->get_shipping_city() );
        update_user_meta( $user_id, 'shipping_company', $order->get_shipping_company() );
        update_user_meta( $user_id, 'shipping_country', $order->get_shipping_country() );
        update_user_meta( $user_id, 'shipping_first_name', $order->get_shipping_first_name() );
        update_user_meta( $user_id, 'shipping_last_name', $order->get_shipping_last_name() );
        update_user_meta( $user_id, 'shipping_method', $order->get_shipping_method() );
        update_user_meta( $user_id, 'shipping_postcode', $order->get_shipping_postcode() );
        update_user_meta( $user_id, 'shipping_state', $order->get_shipping_state() );

        // Link past orders to this newly created customer
        wc_update_new_customer_past_orders( $user_id );
        
        // Auto login
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
    }  
}
add_action( 'woocommerce_thankyou', 'action_woocommerce_thankyou', 10, 1 ); 

function filter_woocommerce_thankyou_order_received_text( $str, $order ) {
    // Determines whether the current visitor is a logged in user.
    if ( is_user_logged_in() ) return $str;
    
    // Get the user email from the order
    $order_email = $order->get_billing_email();
    
    // Check if there are any users with the billing email as user or email
    $email = email_exists( $order_email );  
    $user = username_exists( $order_email );

    // If the UID is null, then it's a guest checkout (new user)
    if ( $user == false && $email == false ) {
        // Link
        $link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );

        // Format
        $format_link = '<a href="' . $link . '">logged in</a>';

        // Append to orginal string
        $str .= sprintf( __( ' An account has been automatically created for you and you are now %s. You will receive an email about this.', 'woocommerce' ), $format_link ); 
    }       

    return $str;
}
add_filter( 'woocommerce_thankyou_order_received_text', 'filter_woocommerce_thankyou_order_received_text', 10, 2 );

function change_lost_your_password ($text) {

             if ($text == 'Lost your password?'){
                 $text = 'Forgot Password?';

             }
                    return $text;
             }
    add_filter( 'gettext', 'change_lost_your_password' );

/*
    function replacement_learndash_templates( $filepath, $name, $args, $echo, $return_file_path){
 if ( 'course' == $name ){
   $filepath = plugin_dir_path(__FILE__ ) . 'src/templates/course.php';
 }
 if ( 'lesson' == $name ){
   $filepath = plugin_dir_path(__FILE__) . 'src/templates/lesson.php';
 }
 return $filepath;
 
}
add_filter('learndash_template','replacement_learndash_templates', 90, 5);

add_filter( 'body_class', 'custom_class' );
function custom_class( $classes ) {
    if ( is_page_template( 'single-sfwd-courses' ) ) {
        $classes[] = 'single-product';
    }
    return $classes;
} 
*/


function verify_lesson_access($id_post_type, $get_type_access) {

	$has_access = get_field('disabled_lesson_by_type_buyer', $id_post_type);
	$access_lesson = true;

	if(isset($get_type_access['type'])){

		$type_access = $get_type_access['type'];
		
		if( $type_access == 'only-webinar' && $has_access['user_flow_for_live_webinar'] ){
			$access_lesson = false;
		}
		if( $type_access == 'on-demand-recording' && $has_access['user_flow_for_on-demand_recording'] ){
			$access_lesson = false;
		}
		if( $type_access == 'cd-rom' && $has_access['user_flow_for_cd-rom'] ){
			$access_lesson = false;
		}
		if( $type_access == 'live-on-demand-recording' && $has_access['user_flow_for_live_on-demand_recording'] ){
			$access_lesson = false;
		}
		if( $type_access == 'live-cd-rom' && $has_access['user_flow_for_live_cd-rom'] ){
			$access_lesson = false;
		}

		if($type_access == 'unlimited' && $has_access['annual_training_subscription_ats'] ){
			$access_lesson = false;
		} 
	}

	return $access_lesson;
	

}


function type_access_courses( $course_id ){

	if (!is_user_logged_in()) return false;
	elseif (wc_memberships_is_user_active_member(get_current_user_id(), 'unlimited')) return array('type'=>'unlimited');

	$orders = get_current_user_orders();

	foreach ($orders as $order) {

		$wc_order = new WC_Order( $order -> ID );

		if ($wc_order -> get_status() != 'completed' && $wc_order -> get_status() != 'processing') continue;

		$order_items = $wc_order -> get_items();
		foreach ($order_items as $order_item) {
			$order_product_id = $order_item -> get_product_id();
			$order_variant_id = $order_item -> get_variation_id();

				
			if ($order_product_id && $order_product_id == $productID && !$order_variant_id) return array('type'=>'simple-product');

			if ($order_product_id) {


				$related_courses = get_post_meta($order_variant_id, '_related_course')[0];

				

				if(in_array($course_id, $related_courses)){ //Course exist


					$all_order_item_meta = $order_item -> get_meta_data();
					foreach ($all_order_item_meta as $order_item_meta) {
						//if ($_SERVER['REMOTE_ADDR'] == '50.245.7.30') var_dump($order_item_meta);
						//$order_item_meta = $order_item_meta -> get_data();
						if ($order_item_meta -> key == 'pa_format') {
							
							$itemFormat = $order_item_meta -> value;
							
							// if ( strpos($itemFormat, 'download') !== false || 
							// 	 strpos($itemFormat, 'ondemand') !== false || 
							// 	 strpos($itemFormat, 'on-demand') !== false ) return array( 'is_course' => true, 'id_item' =>  $order_variant_id );

							// if ( strpos($itemFormat, 'downloadable') !== false ) return 'unlimited';

							// if ( strpos($itemFormat, 'live') !== false ) return 'live';

							// New
							//$itemFormat = "downloadable-video-file-mp4-6";
							if( in_array( $itemFormat, array('live', 'live-webinar') ) ){
								return array('type'=>'only-webinar', 'id_item' =>  $order_variant_id);
							}
							if ( in_array( $itemFormat, array("downloadable-video-file-mp4-6-on-demand", "downloadable-video-file-mp4-6") ) || strpos($itemFormat, 'downloadable-video-file-mp4-6') !== false ){
								return array('type'=>'on-demand-recording', 'id_item' =>  $order_variant_id);
							}
							elseif ( in_array( $itemFormat, array('cd-rom-2', 'cd-rom-transcript') ) ){
								return array('type'=>'cd-rom', 'id_item' =>  $order_variant_id);
							}
							elseif ( in_array( $itemFormat, array('live-on-demand', 'live-on-demand-2') ) ){
								return array('type'=>'live-on-demand-recording', 'id_item' =>  $order_variant_id);
							}
							elseif ( in_array( $itemFormat, array('live-cd-rom', 'live-cd-rom-2', 'cd-rom-live', 'cd-rom-live-bonus') ) ){
								return array('type'=>'live-cd-rom', 'id_item' =>  $order_variant_id);
							}

							
							
						}

					}
					
				}
			}

		}
	}
	return false;
}