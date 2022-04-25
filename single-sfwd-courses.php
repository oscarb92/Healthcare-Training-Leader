<?php
/**
 * LearnDash LD30 Displays a course
 *
 * Available Variables:
 * $course_id                   : (int) ID of the course
 * $course                      : (object) Post object of the course
 * $course_settings             : (array) Settings specific to current course
 *
 * $courses_options             : Options/Settings as configured on Course Options page
 * $lessons_options             : Options/Settings as configured on Lessons Options page
 * $quizzes_options             : Options/Settings as configured on Quiz Options page
 *
 * $user_id                     : Current User ID
 * $logged_in                   : User is logged in
 * $current_user                : (object) Currently logged in user object
 *
 * $course_status               : Course Status
 * $has_access                  : User has access to course or is enrolled.
 * $materials                   : Course Materials
 * $has_course_content          : Course has course content
 * $lessons                     : Lessons Array
 * $quizzes                     : Quizzes Array
 * $lesson_progression_enabled  : (true/false)
 * $has_topics                  : (true/false)
 * $lesson_topics               : (array) lessons topics
 *
 * @since 3.0.0
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/********************** IMPORTANT *********************
**** This code must be located at the top of the file: /wp-content/plugins/sfwd-lms/themes/ld30/templates/shortcodes/course_content_shortcode.php
****
// Start Lesson remove by product type
if ( function_exists( 'verify_lesson_access' ) ) {
	$new_lessons = array();
	$get_type_access = type_access_courses( $course_id ); //Get type access lessons
	echo '<div class="_type_access_course"><i>Type access: '.$get_type_access['type'].'</i></div>';
	foreach( $lessons as $lesson ) {
		if ( verify_lesson_access( $lesson['post']->ID, $get_type_access ) ) { //Verify access lessons
			$new_lessons[] = $lesson;
		}
	}
	$lessons = $new_lessons;
	//unset( $lesson );

}
// end Lesson remove by product type
****************** END IMPORTANT **********************/



$verify_access = true;
$get_type_access = type_access_courses( get_the_ID() );

if (wc_memberships_is_user_active_member(get_current_user_id(), 'unlimited')):

	ld_update_course_access( get_current_user_id(), get_the_ID(), $remove = false );
elseif( $get_type_access ):
	ld_update_course_access( get_current_user_id(), get_the_ID(), $remove = false );

else:
	ld_update_course_access( get_current_user_id(), get_the_ID(), $remove = true );
	//echo "<h1>TEST</h1>";
	$verify_access = false;

endif;

//var_dump($verify_access);

$lessons_course =  learndash_get_course_lessons_list();
$num_lessons = 0;
if( is_array($lessons_course) ){
	foreach ($lessons_course as $lesson_single ) {
		if ( verify_lesson_access( $lesson_single["id"], $get_type_access ) ) { //Verify access lessons
			var_dump($lesson_single["id"]);
			$num_lessons++;
		}
		
	}
}
//echo "<h1>Total: </h1>";
//var_dump($num_lessons);
//var_dump( verify_lesson_access( $lesson_single["id"], $get_type_access ) );

get_header();
$is_unlimited = (wc_memberships_is_user_active_member(get_current_user_id(), 'unlimited'))?true:false;
//var_dump($is_unlimited);
$has_lesson_quizzes = learndash_30_has_lesson_quizzes( $course_id, $lessons ); ?>
<div id="course-<?php the_ID(); ?>"  class="custom_learn <?php echo esc_attr( learndash_the_wrapper_class() ); ?>">


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
	$experts = get_field('expert');
	if ($experts !== false && !is_null($experts) && !is_array($experts)) $experts = array($experts);
		?>
		  
		  <div id="product-fields">
		    <div class="my_product_meta_area">
		      


	        </div>
		    </div>
			
		<div class="product-sidebar purchase-section course-sidebar"> 
		<?php $thumb = get_the_post_thumbnail_url(); ?>
		  <div class="my_product_background_image" style="background-image: url('<?php echo $thumb;?>')"></div>
		  		
		  		<div class="btn_pay_course"><?php echo do_shortcode('[learndash_payment_buttons]'); ?></div>

		  		  <div class="include">
		  		  	<h3>Training Session Includes</h3>
		  		  	<?php if ($get_type_access > 0):
		  		  			$num_lessons = get_field('number_of_lessons');
		  		  			$txt_lessons = 'Lesson';
		  		  			if($num_lessons>1) $txt_lessons = 'Lessons';
		  		  			 echo '<div class="lesson_count"><i class="fa fa-book" aria-hidden="true"></i> '.$num_lessons." ".$txt_lessons."</div>\n";
		  		  			
		  		  		endif; ?>
		  		  	 <?php if (get_field('number_of_quiz')) echo '<div class="lesson_count"><i class="fa fa-question-circle-o" aria-hidden="true"></i>'.get_field('number_of_quiz')." Quiz</div>\n" ?>
		  		  	 <?php if (get_field('list_ceu')) echo '<div class="lesson_count"><i class="fa fa-certificate" aria-hidden="true"></i>'.get_field('list_ceu')." </div>\n" ?>
		  		  </div>

		  		

	  </div><!-- .summary -->
			
	  </div>
	</div>

<div class="max1200">
<div class="course-main course-desc">
<div class="opt">
	<div class="w_50 instructor">

			<?php if (is_array($experts) && count($experts) == 1) {
				echo '<h3>Expert</h3>';
				echo '<span><a href="#product-experts">';
				//echo '<span class="expert2-image" style="background-image:url('.get_the_post_thumbnail_url($expert).');"></span>';
				echo '<b>'.get_post($experts[0])->post_title.'</b>';
				if (!empty(get_field('qualifications', $experts[0]))) echo ', '.get_field('qualifications', $experts[0]);
				echo "</a></span>\n";
			} elseif (is_array($experts) && count($experts) > 1) {
				echo '<h3>Experts</h3>';
				$names = "";
					  
					  echo "<span>";
					  foreach ($experts as $expert) {
					  	$names .= '<a class="exp" href="#product-experts">';
					  	//$names .= '<span class="expert2-image" style="background-image:url('.get_the_post_thumbnail_url($expert).');"></span>';
						  $names .= "<b>".get_post($expert)->post_title . "</b>, ";
						  if (!empty(get_field('qualifications', $expert))) $names .= get_field('qualifications', $expert) . ', '; 
						  $names .= '</a>';
					  }

					  echo trim($names, ', ');
					  echo "</span>";
			} ?>
	</div>
	<div class="w_50 training_info">
		<h3>Training Info</h3>

		<div class="meta hidden_evt">
	<?php if (get_field('live_datetime')) echo '<span><b>Date:</b> '.get_field('live_datetime')."</span>\n"; ?></div>
		<div class="meta hidden_evt">
	<?php if (get_field('time')) echo '<span><b>Time:</b> <span class="text_upper">'.get_field('time')."</span></span> <span>" .get_field('time_zone')." <span></span>\n"; ?></div>
		<div  class="meta">
		      <?php if (get_field('duration')) echo '<span><b>Length:</b> '.get_field('duration')."</span>\n"; ?></div>
		      <div class="meta hidden_evt">
		      		      <?php if (get_field('ceu')): 
		      		      			$ceu = get_field('ceu');
		      		      			$txt_ceu = "CEU";
		      		      			if($ceu) $txt_ceu = "CEUs";
		      		      			echo '<span><b>'.$txt_ceu.':</b> '.$ceu."</span>\n"; 
		      		      		endif; ?>
		      		      </div>
	</div>
</div>


<div class="course-desc">
	<?php echo the_content(); ?>
</div>
<div class="experter">
	<?php 
if (is_array($experts) && count($experts)) {
				echo '<div class="experts" id="product-experts">';

				if (count($experts) > 1) echo '<h3 class="content-section-header">Meet Your Experts</h3>';
				else echo '<h3 class="content-section-header">Meet Your Expert</h3>';

				foreach ($experts as $expert) {
					$expertPost = get_post($expert);
					setup_postdata($expert);
					echo '<div class="expert">';
					echo '<a href="'.get_the_permalink($expert).'">';
					echo '<div class="flex-info-expert">';
					echo '<span class="expert2-image" style="background-image:url('.get_the_post_thumbnail_url($expert).');"></span>';
					echo '<div class="info-expert-ct">';
					echo '<h3><span>'. $expertPost->post_title.'</span>';
					echo '<div> <small style="font-weight:normal;">'.get_field('qualifications', $expert).'</small></div>';
					echo '<div> <small>'.get_field('position', $expert).'</small></div>';
					echo '</h3>';
					echo '</div>';
					echo '</div>';
					echo '</a>';
					echo '<div class="expert-post-content">'; echo the_content(); echo '</div>';
					wp_reset_postdata();
					echo '</div>';
				}

				echo '</div>';
			}
	 ?>
</div>

<?php if(!$verify_access): 
	$link_course = isset(learndash_get_setting(get_the_ID())['custom_button_url'])?learndash_get_setting(get_the_ID())['custom_button_url']:'';

	?>
	<script type="text/javascript">
		var link_course = "<?php echo $link_course; ?>";
		if( !jQuery('.btn_pay_course .btn-join').length && link_course != '' ){
			jQuery('.btn_pay_course').html('<a class="btn-join" href="'+link_course+'" id="btn-join">Start Your Training Now</a>')
		}
	</script>
<?php endif; ?>
<?php if (get_field('live_datetime')): 
	$date_course = get_field('live_datetime');
	$now_d = wp_date('Y-m-d');
	if( strtotime($date_course) > strtotime($now_d) ): ?>
		<script>
			jQuery('.btn_pay_course .btn-join').html('Sign Up Now');
		</script>
		<?php else: ?>
			<?php if($verify_access): ?>
				<div class="lesson">
					<h3 class="content-section-header">Access Your Content</h3>
					<?php if(!isset($_GET['test'])): ?>
						<style type="text/css">
							._type_access_course{
								display: none;
							}
						</style>
					<?php else: ?>
						<p><b>Has Access: </b><?php echo sfwd_lms_has_access(get_the_ID())?'Yes':'No'; ?></p>
					<?php endif; ?>
					<?php //echo do_shortcode('[learndash_course_progress]');
					echo do_shortcode('[course_content]');  

					?>


				</div>

			<?php endif; ?>


		<script>
			jQuery('.btn_pay_course .btn-join').html('Start Your Training Now');
			jQuery(document).ready(function($){
				$('.training_info .meta.hidden_evt').hide();
			});
				
		</script>

		<?php endif; ?>
	<?php endif; ?>
</div>
</div>



	<?php
	global $course_pager_results;

	/**
	 * Fires before the topic.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id   Post ID.
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-course-before', get_the_ID(), $course_id, $user_id );

	/**
	 * Fires before the course certificate link.
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-course-certificate-link-before', $course_id, $user_id );

	/**
	 * Certificate link
	 */

	if ( ! empty( $course_certficate_link ) ) :

		learndash_get_template_part(
			'modules/alert.php',
			array(
				'type'    => 'success ld-alert-certificate',
				'icon'    => 'certificate',
				'message' => __( 'You\'ve earned a certificate!', 'learndash' ),
				'button'  => array(
					'url'    => $course_certficate_link,
					'icon'   => 'download',
					'label'  => __( 'Download Certificate', 'learndash' ),
					'target' => '_new',
				),
			),
			true
		);

	endif;

	/**
	 * Fires after the course certificate link.
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-course-certificate-link-after', $course_id, $user_id );


	/**
	 * Course info bar
	 */
	learndash_get_template_part(
		'modules/infobar.php',
		array(
			'context'       => 'course',
			'course_id'     => $course_id,
			'user_id'       => $user_id,
			'has_access'    => $has_access,
			'course_status' => $course_status,
			'post'          => $post,
		),
		true
	);
	?>

	<?php
	/** This filter is documented in themes/legacy/templates/course.php */
	echo apply_filters( 'ld_after_course_status_template_container', '', learndash_course_status_idx( $course_status ), $course_id, $user_id );

	/**
	 * Content tabs
	 */
	learndash_get_template_part(
		'modules/tabs.php',
		array(
			'course_id' => $course_id,
			'post_id'   => get_the_ID(),
			'user_id'   => $user_id,
			'content'   => $content,
			'materials' => $materials,
			'context'   => 'course',
		),
		true
	);

	/**
	 * Identify if we should show the course content listing
	 */
	$show_course_content = ( ! $has_access && 'on' === $course_meta['sfwd-courses_course_disable_content_table'] ? false : true );

	if ( $has_course_content && $show_course_content ) :
		?>

		<div class="ld-item-list ld-lesson-list">
			<div class="ld-section-heading">

				<?php
				/**
				 * Fires before the course heading.
				 *
				 * @since 3.0.0
				 *
				 * @param int $course_id Course ID.
				 * @param int $user_id   User ID.
				 */
				do_action( 'learndash-course-heading-before', $course_id, $user_id );
				?>

				<h2>
				<?php
				printf(
					// translators: placeholder: Course.
					esc_html_x( '%s Content', 'placeholder: Course', 'learndash' ),
					LearnDash_Custom_Label::get_label( 'course' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Method escapes output
				);
				?>
				</h2>

				<?php
				/**
				 * Fires after the course heading.
				 *
				 * @since 3.0.0
				 *
				 * @param int $course_id Course ID.
				 * @param int $user_id   User ID.
				 */
				do_action( 'learndash-course-heading-after', $course_id, $user_id );
				?>

				<div class="ld-item-list-actions" data-ld-expand-list="true">

					<?php
					/**
					 * Fires before the course expand.
					 *
					 * @since 3.0.0
					 *
					 * @param int $course_id Course ID.
					 * @param int $user_id   User ID.
					 */
					do_action( 'learndash-course-expand-before', $course_id, $user_id );
					?>

					<?php
					// Only display if there is something to expand.
					if ( $has_topics || $has_lesson_quizzes ) :
						?>
						<div class="ld-expand-button ld-primary-background" id="<?php echo esc_attr( 'ld-expand-button-' . $course_id ); ?>" data-ld-expands="<?php echo esc_attr( 'ld-item-list-' . $course_id ); ?>" data-ld-expand-text="<?php echo esc_attr_e( 'Expand All', 'learndash' ); ?>" data-ld-collapse-text="<?php echo esc_attr_e( 'Collapse All', 'learndash' ); ?>">
							<span class="ld-icon-arrow-down ld-icon"></span>
							<span class="ld-text"><?php echo esc_html_e( 'Expand All', 'learndash' ); ?></span>
						</div> <!--/.ld-expand-button-->
						<?php
						/**
						 * Filters whether to expand all course steps by default. Default is false.
						 *
						 * @since 2.5.0
						 *
						 * @param boolean $expand_all Whether to expand all course steps.
						 * @param int     $course_id  Course ID.
						 * @param string  $context    The context where course is expanded.
						 */
						if ( apply_filters( 'learndash_course_steps_expand_all', false, $course_id, 'course_lessons_listing_main' ) ) {
							?>
							<script>
								jQuery( function(){
									setTimeout(function(){
										jQuery("<?php echo esc_attr( '#ld-expand-button-' . $course_id ); ?>").trigger('click');
									}, 1000);
								});
							</script>
							<?php
						}
					endif;

					/**
					 * Fires after the course content expand button.
					 *
					 * @since 3.0.0
					 *
					 * @param int $course_id Course ID.
					 * @param int $user_id   User ID.
					 */
					do_action( 'learndash-course-expand-after', $course_id, $user_id );
					?>

				</div> <!--/.ld-item-list-actions-->
			</div> <!--/.ld-section-heading-->

			<?php
			/**
			 * Fires before the course content listing
			 *
			 * @since 3.0.0
			 *
			 * @param int $course_id Course ID.
			 * @param int $user_id   User ID.
			 */
			do_action( 'learndash-course-content-list-before', $course_id, $user_id );

			/**
			 * Content content listing
			 *
			 * @since 3.0.0
			 *
			 * ('listing.php');
			 */
			learndash_get_template_part(
				'course/listing.php',
				array(
					'course_id'                  => $course_id,
					'user_id'                    => $user_id,
					'lessons'                    => $lessons,
					'lesson_topics'              => ! empty( $lesson_topics ) ? $lesson_topics : [],
					'quizzes'                    => $quizzes,
					'has_access'                 => $has_access,
					'course_pager_results'       => $course_pager_results,
					'lesson_progression_enabled' => $lesson_progression_enabled,
				),
				true
			);

			/**
			 * Fires before the course content listing.
			 *
			 * @since 3.0.0
			 *
			 * @param int $course_id Course ID.
			 * @param int $user_id   User ID.
			 */
			do_action( 'learndash-course-content-list-after', $course_id, $user_id );
			?>

		</div> <!--/.ld-item-list-->

		<?php
	endif;

	/**
	 * Fires before the topic.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id   Post ID.
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-course-after', get_the_ID(), $course_id, $user_id );
	learndash_load_login_modal_html();
	?>
</div>



<?php

get_footer(); ?>