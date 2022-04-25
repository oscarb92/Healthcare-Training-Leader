<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

$allPrices = array();
foreach ($product -> get_children() as $variantID) {
	$variantPrice = get_post_meta( $variantID, '_price', true );
	if ($variantPrice) $allPrices[] = $variantPrice;
}

if (count($allPrices)) {
	$minPrice = min($allPrices);
	$maxPrice = max($allPrices);
} else {
	$minPrice = 0;
	$maxPrice = 0;
}

$userHasAccess = user_has_media_access(get_the_ID());
echo '<!-- 6hhuj '; var_dump($userHasAccess); echo ' -->';

$product -> userHasAccess = $userHasAccess;
$product -> min_price = $minPrice;

$prodType = $product -> get_type();

if ($prodType == "bundle") {
?>
<noscript>
<style>
select#pa_format {
	display: inline-block !important;
}
</style>
</noscript>
<?php
}

?>

<?php
	/**
	* woocommerce_before_single_product hook.
	*
	* @hooked wc_print_notices - 10
	*/
	do_action( 'woocommerce_before_single_product' );

	if ( post_password_required() ) {
		echo get_the_password_form();
		return;
	}

	$experts = get_field('expert');
	if ($experts !== false && !is_null($experts) && !is_array($experts)) $experts = array($experts);

	//$videoDuration = 0;
	$hasMedia = false;
	$mediaContainer = '';
	$liveMediaContainer = '';

	//$userHasAccess = false;

	//var_dump($product);
	//var_dump($userHasAccess);
	
	if (get_field('show_other_files') && have_rows('other_files')) {
		$hasMedia = true;
		$mediaContainer .= '<div id="media-files"><h2>Download Your Training Documents</h2><div id="media-files-inner" class="media-inner">';
		while (have_rows('other_files')) {
			the_row();

			$fileID = get_sub_field('file');
			//var_dump($userHasAccess);
			if ($userHasAccess && $userHasAccess !== 'unlimited') {
				$accessCounts = get_file_access_counts(get_the_ID(), $fileID, 1000);
				$remaining = $accessCounts['maxDownloads'] - $accessCounts['downloadCount'];
			}

			$file = pathinfo(get_attached_file($fileID));
			$mediaContainer .= '<a href="/download-purchase/?product='.get_the_ID().'&file='.urlencode($file['basename']);
			if ($_GET["accesskey"]) $mediaContainer .= '&accesskey='.urlencode($_GET["accesskey"]);
			$mediaContainer .= '" download="'.htmlspecialchars($file['basename']).'"><u>';
			if (get_sub_field('file_title')) $mediaContainer .= get_sub_field('file_title').'</u>'; else $mediaContainer .= $file['basename'].'</u>';
			if (isset($accessCounts) && is_int($remaining)) {
				$s = 's';
				if ($remaining == 1) $s = '';
				$mediaContainer .= '';
			}
			$mediaContainer .= '</a><br>';
		}
		$mediaContainer .= '</div></div>';
	}
	$liveMediaContainer = $mediaContainer;
	if (get_field('show_vimeo_videos') && have_rows('vimeo_videos')) {
		if (count(get_field('vimeo_videos'))) $s = 's'; else $s = '';
		$hasMedia = true;
		$mediaContainer .= '<div id="vimeo-videos"><h2>Watch Your Video'.$s.'</h2><div id="vimeo-videos-inner" class="media-inner">'.
		'<p class="instructions">To play your online training, click on the single arrow icon on the bottom left.<br>'.
		'To view the recording on a full screen, click on the multi-arrow icon on the bottom '.
		'right of the video screen.</p><div id="video-container">';
		while (have_rows('vimeo_videos')) {
			the_row();
			//$videoDuration += get_sub_field('duration');

			if ($userHasAccess) {
				$videoURL = explode('/', trim(get_sub_field('vimeo_url'), '/'));
				$videoID = $videoURL[count($videoURL) - 1];
				$mediaContainer .= '<div class="vimeo-video-wrapper"><h2 class="video-title">'.get_sub_field('video_title').'</h2>';
				$mediaContainer .= '<div class="vimeo-video-sizer"><iframe src="https://player.vimeo.com/video/'.$videoID.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div></div>';
			}
		}
		$mediaContainer .= '</div></div></div>';
	}
	if (!empty($mediaContainer)) $mediaContainer = '<div id="media-container">'.$mediaContainer.'</div>';

	$pClass = array();
	if ($userHasAccess === 'unlimited') $pClass[] = 'unlimited-subscriber';
	if ($userHasAccess) $pClass[] = 'user-has-access';
	if (get_field('live_datetime') > time() - 18000) $pClass[] = 'upcoming-webinar';
	if ($prodType == "variable" && count($product -> get_available_variations()) == 0) $pClass[] = "no-variations-available";
	else $pClass[] = "variations-available product_cat-variant-b-rev-2-allow-in-expert";
	//var_dump($product -> get_available_variations());

?>

<div id="product-<?php the_ID(); ?>" <?php post_class($pClass); ?>>
	<div class="product-main<?php if ($hasMedia && $userHasAccess) echo ' has-media-container'; else echo ' no-media-container'; ?>">
		<?php if (has_term('all-webinars', 'product_cat')) {
			if (get_field('live_datetime') > time() - 18000) echo '<div class="product-type">Live Webinar</div>';
			else echo '<div class="product-type">On-Demand Webinar</div>';

			echo "\n";
		}
		?>
		
		<div class="my_single_product_headings_wrapper">
		  <?php 
		the_title('<h1 class="post-title product-title">', '</h1>');

		?>
		  
		  <div id="product-fields">
		    <div class="my_product_meta_area">
		      <?php if (get_field('live_datetime') > time() - 18000) echo '<span><b>Date:</b> '.date('l, F j, Y g:iA', get_field('live_datetime'))." ET</span>\n"; ?>
		      <?php if (get_field('duration')) echo '<span><b>Length:</b> '.get_field('duration')."</span>\n"; ?>
		      <?php if (is_array($experts) && count($experts) == 1) {
				echo '<span><a href="#product-experts"><b>Expert:</b> ';
				echo get_post($experts[0]) -> post_title;
				if (!empty(get_field('qualifications', $experts[0]))) echo ', '.get_field('qualifications', $experts[0]);
				echo "</a></span>\n";
			} elseif (is_array($experts) && count($experts) > 1) {
					  echo '<span><a href="#product-experts"><b>Experts:</b> ';
					  $names = "";
					  foreach ($experts as $expert) {
						  $names .= get_post($expert) -> post_title . ", ";
						  if (!empty(get_field('qualifications', $expert))) $names .= get_field('qualifications', $expert) . ', ';
					  }
					  echo trim($names, ', ');
					  echo '</a></span>';
				  } ?>
		      <?php if (get_field('ceu')) echo '<span><b>CEU:</b> '.get_field('ceu')."</span>\n"; ?>
	        </div>
		    </div>
		  <?php
			if ($userHasAccess && $userHasAccess !== 'live' && !isset( $userHasAccess["is_course"]) ) echo $mediaContainer;
			if ($userHasAccess && $userHasAccess === 'live') echo $liveMediaContainer;
		?>
			
		<div class="product-sidebar purchase-section">
		<?php $thumb = get_the_post_thumbnail_url(); ?>
		  <div class="my_product_background_image" style="background-image: url('<?php echo $thumb;?>')"></div>
		<?php if (get_field('add_to_cart_head') && $prodType != 'variable') { 
				echo '<span class="add-to-cart-head">'.get_field('add_to_cart_head').'</span>'; 
		}
		//var_dump($userHasAccess);
		if ( isset( $userHasAccess["is_course"]) ){ ?>
			<div class="unlimited-live-register">
				<h2 class="text-center"><?php echo get_the_title(); ?></h2>
				<div id="unlimited-live-register-instructions">
					<!--<p>As an <strong>Annual Training Subscriber</strong>, attendance to this LIVE training session is included in your subscriptions.</p>
						<p>Simply enter your email below, and you'll receive sign-in directions approximately 24 hours before the date of the training.</p>-->
					<?php if( isset( $userHasAccess["id_item"] ) && is_int( $userHasAccess["id_item"] ) ){ 
						$related_courses = get_post_meta($userHasAccess["id_item"], '_related_course');
						//var_dump( $related_courses );
						foreach ($related_courses[0] as $single_course ) {
							//var_dump($single_course);
							?>
							<script>
								window.location.replace("<?php echo get_the_permalink( $single_course ); ?>"); // Redirect user to course
							</script>
							<div class="text-center">	
								<a href="<?php echo get_the_permalink( $single_course ); ?>" class="view_couser_btn">View Content</a>
							</div>
					<?php }
					} ?>
				</div>
			</div>
		<?php }
		if ($userHasAccess === 'unlimited' && get_field('live_datetime') < time() - 18000) { 

			$args = array(
			    'post_type'     => 'product_variation',
			    'post_status'   => array( 'private', 'publish' ),
			    'numberposts'   => -1,
			    'orderby'       => 'menu_order',
			    'order'         => 'asc',
			    'post_parent'   => get_the_ID() // get parent post-ID
			);
			$variations = get_posts( $args );
			$related_courses_arr = [];
			if ( $variations ) {
				foreach ( $variations as $variation ) {

				    $variation_ID = $variation->ID;
				    $related_courses_arr[] = get_post_meta($variation->ID, '_related_course');

				}
				$filter_is_couses = array_filter( $related_courses_arr );

				if(isset($filter_is_couses[0][0])){
					foreach ($filter_is_couses[0][0] as $single_course ) {
						//var_dump($single_course); 
						ld_update_course_access( get_current_user_id(), $single_course, $remove = false );//Enrolled user to course

						?>
						<script>
							window.location.replace("<?php echo get_the_permalink( $single_course ); ?>"); // Redirect user to course
						</script>
					<?php }
				}
			}
			
			
			?>
			

		<?php }
		if ($userHasAccess === 'unlimited' && get_field('live_datetime') > time() - 18000) { ?>
				<div class="unlimited-live-register">
					<h2>Access This Live Training...</h2>
					<div id="unlimited-live-register-instructions">
						<p>As an <strong>Annual Training Subscriber</strong>, attendance to this LIVE training session is included in your subscriptions.</p>
						<p>Simply enter your email below, and you'll receive sign-in directions approximately 24 hours before the date of the training.</p>
						<noscript>Please enable JavaScript to complete your registration.</noscript>
						<form action="#" id="unlimited-live-register-form">
							<div class="email-fields">
								<input type="email" name="attendees[0]" value="<?php echo wp_get_current_user()->data->user_email; ?>" autocorrect="off" autocapitalize="off" spellcheck="false">
								<a href="#" title="Add another attendee" id="add-attendee">+</a>
							</div>
							<input type="hidden" name="product" value="<?php echo get_the_ID(); ?>">
							<button type="submit">Sign Up</button>
						</form>
					</div>
					<div id="unlimited-live-register-confirmation" style="display:none;">
						<p>Thank you for registering! We'll send sign-in directions approximately 24 hours before the date of the training.</p>
						<p>For confirmation, here <span class="plural">is the email address</span> you registered:</p>
						<p class="emails"></p>
					</div>
				</div>
		<?php
			}
			/**
			 * woocommerce_single_product_summary hook.
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 * @hooked WC_Structured_Data::generate_product_data() - 60
			 */
			do_action( 'woocommerce_single_product_summary' );
		?>
	  </div><!-- .summary -->
			
	  </div>
	</div>
<div class="max1200">

	<?php
		/**
		 * woocommerce_before_single_product_summary hook.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="product-main product-desc">
		<?php

			the_content();

			if (is_array($experts) && count($experts)) {
				echo '<div class="experts" id="product-experts">';

				if (count($experts) > 1) echo '<h2 class="content-section-header">Meet Your Experts</h2>';
				else echo '<h2 class="content-section-header">Meet Your Expert</h2>';

				foreach ($experts as $expert) {
					$expertPost = get_post($expert);
					echo '<div class="expert">';
					if (has_post_thumbnail($expert)) {
						echo '<a href="'.get_the_permalink($expert).'" class="expert-image" style="background-image:url('.get_the_post_thumbnail_url($expert).');"></a>';
					}
					echo '<h3><a href="'.get_the_permalink($expert).'">'. $expertPost -> post_title;
					if (!empty(get_field('qualifications', $expert))) echo '<br><span style="font-weight:normal;">'.get_field('qualifications', $expert).'</span>';
					if (!empty(get_field('position', $expert))) echo '<small>'.get_field('position', $expert).'</small>';
					echo '</a></h3>';
					setup_postdata($expert);
					//echo $expertPost -> post_content;
					echo '<div class="expert-post-content">'; the_content(); echo '</div>';
					wp_reset_postdata();
					echo '</div>';
				}

				echo '</div>';
			}

			$testimonials = get_posts(array(
				'post_type' => 'testimonials',
				'meta_key' => 'product',
				'meta_value' => get_the_ID()
			));

			if (count($testimonials)) {
				echo '<div id="our-reviews" class="reviews"><h2 class="content-section-header">Reviews</h2>';

				foreach ($testimonials as $testimonial) {
					$authorDetails = explode(', ', get_field('author', $testimonial -> ID), 2);
					$authorName = $authorDetails[0];
					$authorPosition = $authorDetails[1];

					echo '<div class="testimonial"><div class="testimonial-content">'. $testimonial -> post_content .'</div>';
					echo '<div class="testimonial-author"><b>'.$authorName.'</b><br>'.$authorPosition.'</div></div>';
				}

				echo '</div>';
			}
		?>
	</div>

	<div class="product-sidebar">
		<?php
		$prodFAQs = array();
		$prodTerms = get_the_terms(get_the_ID(), 'product_cat');
		//var_dump($prodTerms);

		foreach ($prodTerms as $term) {
			$faqs = get_posts(array(
				'post_type' => 'questions',
				'meta_query' => array(array(
					'key' => 'faq_cat',
					'value' => '"' . $term -> term_id . '"',
					'compare' => 'LIKE'
				))
			));

			foreach ($faqs as $faq) {
				if (!isset($prodFAQs[$faq -> ID])) $prodFAQs[$faq -> ID] = $faq;
			}
		}

		if (count($prodFAQs)) {
			echo '<div id="product-faqs"><h2>FAQ&#8217;s</h2>';
			foreach ($prodFAQs as $faq) {
				echo '<div class="product-faq faq"><h3>'. $faq -> post_title .'</h3><p>'. $faq -> post_content .'</p></div>';
			}
			echo '<a href="/faqs">See all FAQ&#8217s</a></div>';
		}
		echo '<div id="product-widget-sidebar">';
		if ($userHasAccess === 'unlimited') dynamic_sidebar('sidebar-5');
		elseif ($userHasAccess === true || $userHasAccess === 'live') dynamic_sidebar('sidebar-6');
		else dynamic_sidebar('sidebar-4');
		echo '</div>';
		?>
		<!--<div id="product-share" class="post-share">
			<span>Share:</span>
			<span class="icons">
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_the_permalink()); ?>" target="_blank">Share on Facebook</a>
				<a href="https://twitter.com/home?status=<?php echo urlencode(get_the_permalink()); ?>" target="_blank">Share on Twitter</a>
				<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_the_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>" target="_blank">Share on LinkedIn</a>
				<a href="https://plus.google.com/share?url=<?php echo urlencode(get_the_permalink()); ?>" target="_blank">Share on Google+</a>
			</span>
		</div> -->
	</div>
	<div style="clear:both;"></div>
</div>
	<?php
		/**
		 * woocommerce_after_single_product_summary hook.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'woocommerce_after_single_product_summary' );
	?>

</div><!-- #product-<?php the_ID(); ?> -->
<?php
function get_first_paragraph(){
    $str = wpautop( get_the_content() );
    $str = substr( $str, 0, strpos( $str, '</p>' ) + 4 );
    $str = strip_tags($str, '<a><strong><em>');
    return '' . $str . '';
}
global $wp;
$current_url = home_url(add_query_arg(array(), $wp->request));
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "<?php the_title(); ?>",
  "description": "<?php echo get_first_paragraph(); ?>",
  "image": "<?php the_post_thumbnail_url('prod-image'); ?>",
  "startDate": "<?php if (get_field('live_datetime') > time() - 18000) echo ''.date('l, F j, Y g:iA', get_field('live_datetime')) ?>",
  "endDate": "<?php if (get_field('live_datetime') > time() - 18000) echo ''.date('l, F j, Y g:iA', get_field('live_datetime')) ?>",
  "performer": {
    "@type": "Person",
    "name": "<?php echo get_post($experts[0]) -> post_title; if (!empty(get_field('qualifications', $experts[0]))) echo ', '.get_field('qualifications', $experts[0]); ?>"
  },
  "location": {
    "@type": "Place",
    "name": "<?php the_title(); ?>",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "",
      "addressLocality": "",
      "postalCode": "",
      "addressCountry": ""
    }
  },
  "offers": [{
    "@type": "Offer",
    "name": "Minimum Price",
    "price": "<?php echo $minPrice; ?>",
    "priceCurrency": "USD",
    "validFrom": "",
    "url": "<?php echo $current_url ?>",
    "availability": ""
  },{
    "@type": "Offer",
    "name": "Maximum Price",
    "price": "<?php echo $maxPrice; ?>",
    "priceCurrency": "USD",
    "validFrom": "",
    "url": "<?php echo $current_url ?>",
    "availability": ""
  }]
}
</script>

<?php do_action( 'woocommerce_after_single_product' ); ?>
