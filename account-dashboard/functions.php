<?php
/*
Author: Eddie Machado
URL: http://themble.com/bones/

This is where you can drop your custom functions or
just edit things like thumbnail sizes, header images,
sidebars, comments, etc.
*/

// LOAD BONES CORE (if you remove this, the theme will break)
require_once( 'library/bones.php' );

// CUSTOMIZE THE WORDPRESS ADMIN (off by default)
// require_once( 'library/admin.php' );

/*********************
LAUNCH BONES
Let's get everything up and running.
*********************/

function bones_ahoy() {

  //Allow editor style.
  add_editor_style( get_stylesheet_directory_uri() . '/library/css/editor-style.css' );

  // let's get language support going, if you need it
  load_theme_textdomain( 'bonestheme', get_template_directory() . '/library/translation' );



  // launching operation cleanup
  add_action( 'init', 'bones_head_cleanup' );
  // A better title
  add_filter( 'wp_title', 'rw_title', 10, 3 );
  // remove WP version from RSS
  add_filter( 'the_generator', 'bones_rss_version' );
  // remove pesky injected css for recent comments widget
  add_filter( 'wp_head', 'bones_remove_wp_widget_recent_comments_style', 1 );
  // clean up comment styles in the head
  add_action( 'wp_head', 'bones_remove_recent_comments_style', 1 );
  // clean up gallery output in wp
  add_filter( 'gallery_style', 'bones_gallery_style' );

  // enqueue base scripts and styles
  add_action( 'wp_enqueue_scripts', 'bones_scripts_and_styles', 999 );
  // ie conditional wrapper

  // launching this stuff after theme setup
  bones_theme_support();

  // adding sidebars to Wordpress (these are created in functions.php)
  add_action( 'widgets_init', 'bones_register_sidebars' );

  // cleaning up random code around images
  add_filter( 'the_content', 'bones_filter_ptags_on_images' );
  // cleaning up excerpt
  add_filter( 'excerpt_more', 'bones_excerpt_more' );

} /* end bones ahoy */

// let's get this party started
add_action( 'after_setup_theme', 'bones_ahoy' );

  // Story post type
  require_once( 'library/story-post-type.php' );
  require_once( 'library/logo-post-type.php' );
  require_once( 'library/board-post-type.php' );
  require_once( 'library/staff-post-type.php' );
   require_once( 'library/home-slides-post-type.php' );
/************* OEMBED SIZE OPTIONS *************/

if ( ! isset( $content_width ) ) {
	$content_width = 680;
}

/************* THUMBNAIL SIZE OPTIONS *************/

// Thumbnail sizes
/*add_image_size( 'bones-thumb-600', 600, 150, true );
add_image_size( 'bones-thumb-300', 300, 100, true );*/
add_image_size( 'circle-200', 200, 200, true );
add_image_size( 'circle-350', 350, 350, true );

/*
to add more sizes, simply copy a line from above
and change the dimensions & name. As long as you
upload a "featured image" as large as the biggest
set width or height, all the other sizes will be
auto-cropped.

To call a different size, simply change the text
inside the thumbnail function.

For example, to call the 300 x 100 sized image,
we would use the function:
<?php the_post_thumbnail( 'bones-thumb-300' ); ?>
for the 600 x 150 image:
<?php the_post_thumbnail( 'bones-thumb-600' ); ?>

You can change the names and dimensions to whatever
you like. Enjoy!
*/

add_filter( 'image_size_names_choose', 'bones_custom_image_sizes' );

function bones_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        /*'bones-thumb-600' => __('600px by 150px'),
        'bones-thumb-300' => __('300px by 100px'),*/
        'circle-200' => __('Circle: 200px by 200px'),
        'circle-350' => __('Circle: 350px by 350px')
    ) );
}

//Clean Up WordPress Shortcode Formatting - important for nested shortcodes
//adjusted from http://donalmacarthur.com/articles/cleaning-up-wordpress-shortcode-formatting/
function parse_shortcode_content( $content ) {

   /* Parse nested shortcodes and add formatting. */
    $content = trim( do_shortcode( shortcode_unautop( $content ) ) );

    /* Remove '' from the start of the string. */
    if ( substr( $content, 0, 4 ) == '' )
        $content = substr( $content, 4 );

    /* Remove '' from the end of the string. */
    if ( substr( $content, -3, 3 ) == '' )
        $content = substr( $content, 0, -3 );

    /* Remove any instances of ''. */
    $content = str_replace( array( '<p></p>' ), '', $content );
    $content = str_replace( array( '<p>  </p>' ), '', $content );

    return $content;
}

//move wpautop filter to AFTER shortcode is processed
remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop' , 99);
add_filter( 'the_content', 'shortcode_unautop',100 );
/*
The function above adds the ability to use the dropdown menu to select
the new images sizes you have just created from within the media manager
when you add media to your content blocks. If you add more image sizes,
duplicate one of the lines in the array and name it according to your
new image size.
*/

/************* THEME CUSTOMIZE *********************/

/* 
  A good tutorial for creating your own Sections, Controls and Settings:
  http://code.tutsplus.com/series/a-guide-to-the-wordpress-theme-customizer--wp-33722
  
  Good articles on modifying the default options:
  http://natko.com/changing-default-wordpress-theme-customization-api-sections/
  http://code.tutsplus.com/tutorials/digging-into-the-theme-customizer-components--wp-27162
  
  To do:
  - Create a js for the postmessage transport method
  - Create some sanitize functions to sanitize inputs
  - Create some boilerplate Sections, Controls and Settings
*/

function bones_theme_customizer($wp_customize) {
  // $wp_customize calls go here.
  //
  // Uncomment the below lines to remove the default customize sections 

  // $wp_customize->remove_section('title_tagline');
  // $wp_customize->remove_section('colors');
  // $wp_customize->remove_section('background_image');
  // $wp_customize->remove_section('static_front_page');
  // $wp_customize->remove_section('nav');

  // Uncomment the below lines to remove the default controls
  // $wp_customize->remove_control('blogdescription');
  
  // Uncomment the following to change the default section titles
  // $wp_customize->get_section('colors')->title = __( 'Theme Colors' );
  // $wp_customize->get_section('background_image')->title = __( 'Images' );
}

add_action( 'customize_register', 'bones_theme_customizer' );

/************* ACTIVE SIDEBARS ********************/

// Sidebars & Widgetizes Areas
function bones_register_sidebars() {
	register_sidebar(array(
		'id' => 'sidebar1',
		'name' => __( 'Sidebar 1', 'bonestheme' ),
		'description' => __( 'The first (primary) sidebar.', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	/*
	to add more sidebars or widgetized areas, just copy
	and edit the above sidebar code. In order to call
	your new sidebar just use the following code:

	Just change the name to whatever your new
	sidebar's id is, for example:

	register_sidebar(array(
		'id' => 'sidebar2',
		'name' => __( 'Sidebar 2', 'bonestheme' ),
		'description' => __( 'The second (secondary) sidebar.', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	To call the sidebar in your template, you can just copy
	the sidebar.php file and rename it to your sidebar's name.
	So using the above example, it would be:
	sidebar-sidebar2.php

	*/
} // don't remove this bracket!


/************* COMMENT LAYOUT *********************/

// Comment Layout
function bones_comments( $comment, $args, $depth ) {
   $GLOBALS['comment'] = $comment; ?>
  <div id="comment-<?php comment_ID(); ?>" <?php comment_class('cf'); ?>>
    <article  class="cf">
      <header class="comment-author vcard">
        <?php
        /*
          this is the new responsive optimized comment image. It used the new HTML5 data-attribute to display comment gravatars on larger screens only. What this means is that on larger posts, mobile sites don't have a ton of requests for comment images. This makes load time incredibly fast! If you'd like to change it back, just replace it with the regular wordpress gravatar call:
          echo get_avatar($comment,$size='32',$default='<path_to_url>' );
        */
        ?>
        <?php // custom gravatar call ?>
        <?php
          // create variable
          $bgauthemail = get_comment_author_email();
        ?>
        <img data-gravatar="http://www.gravatar.com/avatar/<?php echo md5( $bgauthemail ); ?>?s=40" class="load-gravatar avatar avatar-48 photo" height="40" width="40" src="<?php echo get_template_directory_uri(); ?>/library/images/nothing.gif" />
        <?php // end custom gravatar call ?>
        <?php printf(__( '<cite class="fn">%1$s</cite> %2$s', 'bonestheme' ), get_comment_author_link(), edit_comment_link(__( '(Edit)', 'bonestheme' ),'  ','') ) ?>
        <time datetime="<?php echo comment_time('Y-m-j'); ?>"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php comment_time(__( 'F jS, Y', 'bonestheme' )); ?> </a></time>

      </header>
      <?php if ($comment->comment_approved == '0') : ?>
        <div class="alert alert-info">
          <p><?php _e( 'Your comment is awaiting moderation.', 'bonestheme' ) ?></p>
        </div>
      <?php endif; ?>
      <section class="comment_content cf">
        <?php comment_text() ?>
      </section>
      <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
    </article>
  <?php // </li> is added by WordPress automatically ?>
<?php
} // don't remove this bracket!


/*
This is a modification of a function found in the
twentythirteen theme where we can declare some
external fonts. If you're using Google Fonts, you
can replace these fonts, change it in your scss files
and be up and running in seconds.
*/
function bones_fonts() {
  wp_enqueue_style('googleFonts', 'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic');
}

//add_action('wp_enqueue_scripts', 'bones_fonts');

/* Add custom field for the page OR the custom Post Type for the Homepage Slides */

if ( function_exists( 'slt_cf_register_box') )
    add_action( 'init', 'register_my_custom_fields' );

function register_my_custom_fields() {
    $mainVolunteer = get_id_by_slug('main-volunteer');

    slt_cf_register_box( 
    array(
    'type' => 'post',
    'title' => 'Custom Page Options',
    'id' => 'page_title_cf',
    'context' => 'normal',
    'fields' => array(
       array(
        'name' => 'page_title',
        'label' => 'Custom Page Title',
        'description' => 'Use this custom field if you want to display custom title in the blue region',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
       array(
        'name' => 'page_link_cf',
        'label' => 'Custom Button Link',
        'description' => 'Use this field if you want to display the button underneath the content in the blue region. Example: www.google.com or /page-link',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
       array(
        'name' => 'page_label_cf',
        'label' => 'Custom Page Label',
        'description' => 'Use this field for custom button label',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),

       array(
        'name' => 'page_gallery',
        'label' => 'Custom Page Gallery',
        'description' => 'Use this field to add gallery',
        'type' => 'wysiwyg',
         'wysiwyg_settings' => array( 'teeny' => false, 'media_buttons' => true, 'tinymce' => true),
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),

       array(
        'name' => 'page_iframe',
        'label' => 'Custom Iframe For Embedding Forms/Maps',
        'description' => 'Use to embed external iframe codes',
        'type' => 'textarea',
        'scope' => array('page'),
        'capabilities' => array('edit_posts'),
        ),
    )
  ));

    slt_cf_register_box( 
    array(
    'type' => 'post',
    'title' => 'Additional Content',
    'id' => 'additional-cf',
    'context' => 'normal',
    'fields' => array(
       array(
        'name' => 'additional_content',
        'label' => 'Additional Content',
        'description' => 'Additional content that will be displayed',
        'type' => 'wysiwyg',
        'wysiwyg_settings' => array( 'teeny' => false, 'media_buttons' => true, 'tinymce' => true),
        'scope' => array( 'template' => array(
      'page-restore-donate.php', 'page-restore-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'additional_link_cf',
        'label' => 'Custom Button Link',
        'description' => 'Use this field if you want to display the button underneath the content.',
        'type' => 'text',
        'scope' => array( 'template' => array(
        'page-restore-donate.php','page-restore-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),

        /* Blue content box Additional Field Region 2 */
        array(
        'name' => 'additional_content_2',
        'label' => 'Additional Content Region 2',
        'description' => 'Additional content that will be displayed in 2nd Region',
        'type' => 'wysiwyg',
        'wysiwyg_settings' => array( 'teeny' => false, 'media_buttons' => true, 'tinymce' => true),
        'scope' => array( 'template' => array( 'page-restore-donate.php','page-restore-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'additional_link_cf_2',
        'label' => 'Custom Button Link for Region 2',
        'description' => 'Use this field if you want to display the button for 2nd Region',
        'type' => 'text',
        'scope' => array( 'template' => array('page-restore-donate.php','page-restore-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),

        /* White Content Additional Content  Region 3 */
        /* Blue content box Additional Field */
        array(
        'name' => 'additional_content_3',
        'label' => 'Additional Content Region 3',
        'description' => 'Additional content that will be displayed in 3rd Region',
        'type' => 'wysiwyg',
        'wysiwyg_settings' => array( 'teeny' => false, 'media_buttons' => true, 'tinymce' => true),
        'scope' => array( 'template' => array('page-restore-donate.php','page-restore-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'additional_link_cf_3',
        'label' => 'Custom Button Link for Region 3',
        'description' => 'Use this field if you want to display the button for 3rd Region',
        'type' => 'text',
        'scope' => array( 'template' => array('page-restore-donate.php','page-restore-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
      )
    ));

    slt_cf_register_box( 
    array(
    'type' => 'post',
    'title' => 'Additional Page Fields',
    'id' => 'page-fields',
    'context' => 'normal',
    'fields' => array(
      
        array(
        'name' => 'additional_link_cf_page',
        'label' => 'Custom Button Link',
        'description' => 'Use this field if you want to display the button underneath the content.',
        'type' => 'text',
        'scope' => array( 'page', 'except_posts' => array($mainVolunteer)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'additional_label_cf_page',
        'label' => 'Custom Button Label',
        'description' => 'Use this field if you want to display custom label for the button above.',
        'type' => 'text',
        'scope' => array( 'page', 'except_posts' => array($mainVolunteer)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'additional_target_cf_page',
        'label' => 'Open link in a new window? Type <em>_blank</em> to open in a new window or leave blank to open in current window.',
        'description' => 'Use this field if you want to open the link in a new window.',
        'type' => 'text',
        'scope' => array( 'page', 'except_posts' => array($mainVolunteer)),
        'capabilities' => array('edit_posts'),
        ),
       array(
        'name' => 'page_image',
        'label' => 'Custom Medium Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('page'),
        'capabilities' => array('edit_posts'),
        ),
       array(
        'name' => 'large_page_image',
        'label' => 'Custom Large Circle Image Thumbnail 350px by 350px',
        'description' => 'Use this image for displaying Large sized thumbnails',
        'type' => 'file',
        'scope' => array('page'),
        'capabilities' => array('edit_posts'),
        ),
      array(
        'name' => 'page_tag',
        'label' => 'Custom Page Tagline/Description',
        'description' => 'This can be used as an intro or tagline for this page. Important: it will <b>only</b> show up in the white box on top of your featured 
        image. ',
        'type' => 'textarea',
        'scope' => array('page'),
        'capabilities' => array('edit_posts'),
        ),
      array(
        'name' => 'additional_page_content',
        'label' => 'Additional Content',
        'description' => 'Additional content that will be displayed in the blue section',
        'type' => 'wysiwyg',
        'wysiwyg_settings' => array( 'teeny' => false, 'media_buttons' => true ),
        'scope' => array( 'page', 'except_posts' => array($mainVolunteer)),
        'capabilities' => array('edit_posts'),
        ),

      )
  ));

/* Custom Fields for the Posts */
  slt_cf_register_box( 
    array(
    'type' => 'post',
    'title' => 'Events Additional Fields',
    'id' => 'event-fields',
    'context' => 'normal',
    'fields' => array(
      array(
        'name' => 'event_date',
        'label' => 'Event Date',
        'description' => 'Use the field to display the Event\'s date',
        'type' => 'date',
        'scope' => array('category' => array('Events') ),
        'capabilities' => array('edit_posts'),
        ),
      array(
        'name' => 'event_link',
        'label' => 'Event URL',
        'description' => 'Use this field to link the Register button. Example: www.google.com or /events',
        'scope' => array('category' => array('Events') ),
        'capabilities' => array('edit_posts'),
        )

      )
  ));

  /* 3 Features Boxes */
  slt_cf_register_box(array(
    'type' => 'post',
    'title' => '3 Featured Boxes',
    'id' => 'volunteer-fields',
    'context' => 'normal',
    'fields' => array(

        /* 1st Box */
       array(
        'name' => 'vol_first_image',
        'label' => '1st Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_first_title',
        'label' => '1st Box Title',
        'description' => 'Use this image for displaying the title for the 1st Box',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_first_tag',
        'label' => '1st Box Description',
        'description' => 'This can be used as an intro or tagline for the 1st Box.',
        'type' => 'textarea',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_first_link',
        'label' => '1st Box Link',
        'description' => 'Use absolute or relative URL in this field for the button. Example: www.google.com or /donate',
        'type' => 'text',
        'scope' => array('template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),

        array(
        'name' => 'vol_first_link_label',
        'label' => '1st Box Link Label',
        'description' => 'Use this to label a button. Example: \'Volunteer\'',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),

        /* 2nd Box */
       array(
        'name' => 'vol_second_image',
        'label' => '2nd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_second_title',
        'label' => '2nd Box Title',
        'description' => 'Use this image for displaying the title for the 2nd Box',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_second_tag',
        'label' => '2nd Box Description',
        'description' => 'This can be used as an intro or tagline for the 2nd Box.',
        'type' => 'textarea',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_second_link',
        'label' => '2nd Box Link',
        'description' => 'Use absolute or relative URL in this field for the button. Example: www.google.com or /donate',
        'type' => 'text',
        'scope' => array('template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),

        array(
        'name' => 'vol_second_link_label',
        'label' => '2nd Box Link Label',
        'description' => 'Use this to label a button. Example: \'Volunteer\'',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
      
        /* 3rd Box */
        array(
        'name' => 'vol_third_image',
        'label' => '3rd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_third_title',
        'label' => '3rd Box Title',
        'description' => 'Use this image for displaying the title for the 3rd Box',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_third_tag',
        'label' => '3rd Box Description',
        'description' => 'This can be used as an intro or tagline for the 3rd Box.',
        'type' => 'textarea',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_third_link',
        'label' => '3rd Box Link',
        'description' => 'Use absolute or relative URL in this field for the button. Example: www.google.com or /donate',
        'type' => 'text',
        'scope' => array('template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),

        array(
        'name' => 'vol_third_link_label',
        'label' => '3rd Box Link Label',
        'description' => 'Use this to label a button. Example: \'Volunteer\'',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php', 'page-kindgift.php', 'page-youth.php', 'page-rocktheblock.php', 'page-homeowners.php')),
        'capabilities' => array('edit_posts'),
        ),

         /* 4th Box */
        array(
        'name' => 'vol_fourth_image',
        'label' => '4th Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_fourth_title',
        'label' => '4th Box Title',
        'description' => 'Use this image for displaying the title for the 4th Box',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_fourth_tag',
        'label' => '4th Box Description',
        'description' => 'This can be used as an intro or tagline for the 4th Box.',
        'type' => 'textarea',
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_fourth_link',
        'label' => '4th Box Link',
        'description' => 'Use absolute or relative URL in this field for the button. Example: www.google.com or /donate',
        'type' => 'text',
        'scope' => array('template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        ),

        array(
        'name' => 'vol_fourth_link_label',
        'label' => '4th Box Link Label',
        'description' => 'Use this to label a button. Example: \'Volunteer\'',
        'type' => 'text',
        'scope' => array( 'template' => array('page-main-volunteer.php')),
        'capabilities' => array('edit_posts'),
        )
      ))
    

  );

  /* 2 Features Boxes: Global etc */
  slt_cf_register_box(array(
    'type' => 'post',
    'title' => '2 Featured Boxes',
    'id' => 'two-boxes',
    'context' => 'normal',
    'fields' => array(

        /* 1st Box */
       array(
        'name' => 'vol_first_image',
        'label' => '1st Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_first_title',
        'label' => '1st Box Title',
        'description' => 'Use this image for displaying the title for the 1st Box',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_first_tag',
        'label' => '1st Box Description',
        'description' => 'This can be used as an intro or tagline for the 1st Box.',
        'type' => 'textarea',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_first_link',
        'label' => '1st Box Link',
        'description' => 'Use absolute or relative URL in this field for the button. Example: www.google.com or /donate',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),

        array(
        'name' => 'vol_first_link_label',
        'label' => '1st Box Link Label',
        'description' => 'Use this to label a button. Example: \'Volunteer\'',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),

        /* 2nd Box */
       array(
        'name' => 'vol_second_image',
        'label' => '2nd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_second_title',
        'label' => '2nd Box Title',
        'description' => 'Use this image for displaying the title for the 2nd Box',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_second_tag',
        'label' => '2nd Box Description',
        'description' => 'This can be used as an intro or tagline for the 2nd Box.',
        'type' => 'textarea',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_second_link',
        'label' => '2nd Box Link',
        'description' => 'Use absolute or relative URL in this field for the button. Example: www.google.com or /donate',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),

        array(
        'name' => 'vol_second_link_label',
        'label' => '2nd Box Link Label',
        'description' => 'Use this to label a button. Example: \'Volunteer\'',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),

        /* 3rd box */
       array(
        'name' => 'vol_third_image',
        'label' => '3rd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_third_title',
        'label' => '3rd Box Title',
        'description' => 'Use this image for displaying the title for the 3rd Box',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_third_tag',
        'label' => '3rd Box Description',
        'description' => 'This can be used as an intro or tagline for the 3rd Box.',
        'type' => 'textarea',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'vol_third_link',
        'label' => '3rd Box Link',
        'description' => 'Use absolute or relative URL in this field for the button. Example: www.google.com or /donate',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),

        array(
        'name' => 'vol_third_link_label',
        'label' => '3rd Box Link Label',
        'description' => 'Use this to label a button. Example: \'Volunteer\'',
        'type' => 'text',
        'scope' => array('template' => array('page-global.php')),
        'capabilities' => array('edit_posts'),
        ),
      
       
      ))
    

  );

  /* 4 Features Boxes For Restore Main Page and Homepage */
  slt_cf_register_box(array(
    'type' => 'post',
    'title' => '4 Boxes for Habitat\'s Impact or Restore\'s Impact',
    'id' => 'habitat-impact',
    'context' => 'normal',
    'fields' => array(

        /* 1st Box */
       array(
        'name' => 'habimpact_first_image',
        'label' => '1st Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_first_title',
        'label' => '1st Box Title',
        'description' => 'Use this image for displaying the title for the 1st Box',
        'type' => 'text',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_first_tag',
        'label' => '1st Box Description',
        'description' => 'This can be used as an intro or tagline for the 1st Box.',
        'type' => 'textarea',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
         array(
        'name' => 'habimpact_first_link',
        'label' => '1st Box Link',
        'description' => '1st Box URL',
        'type' => 'text',
        'scope' => array( 'posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),

        /* 2nd Box */
       array(
        'name' => 'habimpact_second_image',
        'label' => '2nd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_second_title',
        'label' => '2nd Box Title',
        'description' => 'Use this image for displaying the title for the 2nd Box',
        'type' => 'text',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_second_tag',
        'label' => '2nd Box Description',
        'description' => 'This can be used as an intro or tagline for the 2nd Box.',
        'type' => 'textarea',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
         array(
        'name' => 'habimpact_second_link',
        'label' => '2nd Box Link',
        'description' => '2nd Box URL',
        'type' => 'text',
        'scope' => array( 'posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
      
        /* 3rd Box */
       array(
        'name' => 'habimpact_third_image',
        'label' => '3rd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_third_title',
        'label' => '3rd Box Title',
        'description' => 'Use this image for displaying the title for the 3rd Box',
        'type' => 'text',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_third_tag',
        'label' => '3rd Box Description',
        'description' => 'This can be used as an intro or tagline for the 3rd Box.',
        'type' => 'textarea',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
         array(
        'name' => 'habimpact_third_link',
        'label' => '3rd Box Link',
        'description' => '3rd Box URL',
        'type' => 'text',
        'scope' => array( 'posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),

        /* 4th Box */
        array(
        'name' => 'habimpact_fourth_image',
        'label' => '4th Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_fourth_title',
        'label' => '4th Box Title',
        'description' => 'Use this image for displaying the title for the 4th Box',
        'type' => 'text',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'habimpact_fourth_tag',
        'label' => '4th Box Description',
        'description' => 'This can be used as an intro or tagline for the 4th Box.',
        'type' => 'textarea',
        'scope' => array('posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        ),
         array(
        'name' => 'habimpact_fourth_link',
        'label' => '4th Box Link',
        'description' => '4th Box URL',
        'type' => 'text',
        'scope' => array( 'posts' => array(5,17)),
        'capabilities' => array('edit_posts'),
        )
      ))
    

  );

  /* 3 Boxes for Opportunities */
  slt_cf_register_box(array(
    'type' => 'post',
    'title' => '3 Boxes for Opportunities',
    'id' => 'opportunities-fields',
    'context' => 'normal',
    'fields' => array(

        /* 1st Box */
       array(
        'name' => 'opportunity_first_image',
        'label' => '1st Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_first_title',
        'label' => '1st Box Title',
        'description' => 'Use this image for displaying the title for the 1st Box',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_first_tag',
        'label' => '1st Box Description',
        'description' => 'This can be used as an intro or tagline for the 1st Box.',
        'type' => 'textarea',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_first_label',
        'label' => '1st Box Button Label',
        'description' => 'This can be used to customize label for the button',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        
        array(
        'name' => 'opportunity_first_link',
        'label' => '1st Box Button URL',
        'description' => 'This can be used to add the link to the button',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        /* 2nd Box */
        array(
        'name' => 'opportunity_second_image',
        'label' => '2nd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_second_title',
        'label' => '2nd Box Title',
        'description' => 'Use this image for displaying the title for the 2nd Box',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_second_tag',
        'label' => '2nd Box Description',
        'description' => 'This can be used as an intro or tagline for the 2nd Box.',
        'type' => 'textarea',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_second_label',
        'label' => '2nd Box Button Label',
        'description' => 'This can be used to customize label for the button',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        
        array(
        'name' => 'opportunity_second_link',
        'label' => '2nd Box Button URL',
        'description' => 'This can be used to add the link to the button',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
      
        /* 3rd Box */
       array(
        'name' => 'opportunity_third_image',
        'label' => '3rd Box Circle Image Thumbnail 200px by 200px',
        'description' => 'Use this image for displaying Medium sized thumbnails',
        'type' => 'file',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_third_title',
        'label' => '3rd Box Title',
        'description' => 'Use this image for displaying the title for the 3rd Box',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_third_tag',
        'label' => '3rd Box Description',
        'description' => 'This can be used as an intro or tagline for the 3rd Box.',
        'type' => 'textarea',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        array(
        'name' => 'opportunity_third_label',
        'label' => '3rd Box Button Label',
        'description' => 'This can be used to customize label for the button',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
        
        array(
        'name' => 'opportunity_third_link',
        'label' => '3rd Box Button URL',
        'description' => 'This can be used to add the link to the button',
        'type' => 'text',
        'scope' => array('posts' => array(9)),
        'capabilities' => array('edit_posts'),
        ),
      ))
    

  );


}

/* ADD HTTP */

function addhttp($url) {
  if ( preg_match("%^[?/]%" , $url))
  {
    $url = get_bloginfo('url') . $url;
  }
  else if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
    $url = "http://" . $url;
  }
    return $url;
}

/* Gallery Images */

function get_images($size = 'large', $limit = '0', $offset = '0', $post_id) {
  //global $post;
 
  $images = get_children( 
    array(
      'post_parent' => $post_id, 
      'post_status' => 'inherit', 
      'post_type' => 'attachment', 
      'post_mime_type' => 'image', 
      'order' => 'DESC') );
 
  if ($images) {
    echo '<div class="swiper-container"> <div class="swiper-wrapper">';
 
    $num_of_images = count($images);
 
    if ($offset > 0) : $start = $offset--; else : $start = 0; endif;
    if ($limit > 0) : $stop = $limit+$start; else : $stop = $num_of_images; endif;
 
    $i = 0;
    foreach ($images as $attachment_id => $image) {
      if ($start <= $i and $i < $stop) {
      $img_title = $image->post_title;   // title.
      $img_description = $image->post_content; // description.
      $img_caption = $image->post_excerpt; // caption.
      $img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
 
        if ($img_alt == '') {
        $img_alt = $img_title;
        }
 
        if ($size == 'large') {
        $big_array = image_downsize( $image->ID, $size );
        $img_url = $big_array[0]; // large.
        } else {
        $img_url = wp_get_attachment_url($image->ID); // url of the full size image.
        }
      ?>
 
       <div class="swiper-slide"><img src="<?php echo $img_url; ?>" alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" /></div>
 
      <?php /* if ($img_caption != '') : ?>
      <div class="attachment-caption"><?php echo $img_caption; ?></div>
      <?php endif; ?>
      <?php if ($img_description != '') : ?>
      <div class="attachment-description"><?php echo $img_description; ?></div>
      <?php endif; */
      }
      $i++;
    }
  }
 
    echo '</div><div class="swiper-pagination"></div></div><!-- End gallery -->';
 
}

//USE: get_images('large','0','0',"$post->ID");

/* Create connection between Pages & Stories */
function my_connection_types() {
    p2p_register_connection_type( array(
        'name' => 'pages_to_pages',
        'from' => 'page',
        'to' => 'page'
    ));


    p2p_register_connection_type(array(
        'name' => 'story_to_pages',
        'from' => 'story',
        'to' => 'page'
    ));

    
}
add_action( 'p2p_init', 'my_connection_types' );


/* Add shortcode */
function item_shortcode( $atts, $content = null ) {
  return '<div class="quarter item">' . $content . '</div>';
}
add_shortcode( 'item', 'item_shortcode' );

/* third */
function item_third_shortcode( $atts, $content = null ) {
  return '<div class="third item">' . $content . '</div>';
}
add_shortcode( 'item-third', 'item_third_shortcode' );



/**
 * Add Custom URL field to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */
 
function be_attachment_field_credit( $form_fields, $post ) {
  

  $form_fields['custom-img-url'] = array(
    'label' => 'Image URL Link',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'custom-img-url', true ),
    'helps' => 'Add URL for this image to link to.',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'be_attachment_field_credit', 10, 2 );

/**
 * Save values of Photographer Name and URL in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function be_attachment_field_credit_save( $post, $attachment ) {
 

  if( isset( $attachment['custom-img-url'] ) )
update_post_meta( $post['ID'], 'custom-img-url', esc_url( $attachment['custom-img-url'] ) );

  return $post;
}

add_filter( 'attachment_fields_to_save', 'be_attachment_field_credit_save', 10, 2 );

//Custom Gallery Shortcode
remove_shortcode('gallery');
add_shortcode('gallery', 'shift_gallery_shortcode');

function shift_gallery_shortcode( $attr ) {
  $GLOBALS['photos'] = 1;

  $post = get_post();

  static $instance = 0;
  $instance++;

  if ( ! empty( $attr['ids'] ) ) {
    // 'ids' is explicitly ordered, unless you specify otherwise.
    if ( empty( $attr['orderby'] ) )
      $attr['orderby'] = 'post__in';
    $attr['include'] = $attr['ids'];
  }

  /**
   * Filter the default gallery shortcode output.
   *
   * If the filtered output isn't empty, it will be used instead of generating
   * the default gallery template.
   *
   * @since 2.5.0
   *
   * @see gallery_shortcode()
   *
   * @param string $output The gallery output. Default empty.
   * @param array  $attr   Attributes of the gallery shortcode.
   */
  $output = apply_filters( 'post_gallery', '', $attr );
  if ( $output != '' )
    return $output;

  // We're trusting author input, so let's at least make sure it looks like a valid orderby statement
  if ( isset( $attr['orderby'] ) ) {
    $attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
    if ( !$attr['orderby'] )
      unset( $attr['orderby'] );
  }

  $html5 = current_theme_supports( 'html5', 'gallery' );
  extract(shortcode_atts(array(
    'order'      => 'ASC',
    'orderby'    => 'menu_order ID',
    'id'         => $post ? $post->ID : 0,
    'itemtag'    => $html5 ? 'figure'     : 'div',
    'icontag'    => $html5 ? 'div'        : 'dt',
    'captiontag' => $html5 ? 'figcaption' : 'dd',
    'columns'    => 3,
    'size'       => 'photo-thumb',
    'include'    => '',
    'exclude'    => '',
    'link'       => ''
  ), $attr, 'gallery'));

  $id = intval($id);
  if ( 'RAND' == $order )
    $orderby = 'none';

  if ( !empty($include) ) {
    $_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

    $attachments = array();
    foreach ( $_attachments as $key => $val ) {
      $attachments[$val->ID] = $_attachments[$key];
    }
  } elseif ( !empty($exclude) ) {
    $attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
  } else {
    $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
  }

  if ( empty($attachments) )
    return '';

  if ( is_feed() ) {
    $output = "\n";
    foreach ( $attachments as $att_id => $attachment )
      $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
    return $output;
  }

  $itemtag = tag_escape($itemtag);
  $captiontag = tag_escape($captiontag);
  $icontag = tag_escape($icontag);
  $valid_tags = wp_kses_allowed_html( 'post' );
  if ( ! isset( $valid_tags[ $itemtag ] ) )
    $itemtag = 'div';
  if ( ! isset( $valid_tags[ $captiontag ] ) )
    $captiontag = 'dd';
  if ( ! isset( $valid_tags[ $icontag ] ) )
    $icontag = 'dt';

  $columns = intval($columns);
  $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
  $float = is_rtl() ? 'right' : 'left';

  $selector = "gallery-{$instance}";

  $gallery_style = $gallery_div = '';

  /**
   * Filter whether to print default gallery styles.
   *
   * @since 3.1.0
   *
   * @param bool $print Whether to print default gallery styles.
   *                    Defaults to false if the theme supports HTML5 galleries.
   *                    Otherwise, defaults to true.
   */
  if ( apply_filters( 'use_default_gallery_style', ! $html5 ) ) {
    $gallery_style = "";
  }

  $size_class = sanitize_html_class( $size );

  $main_image_id = reset($attachments)->ID;
  $main_image = wp_get_attachment_image( $main_image_id, 'gallery-l', false );

  $gallery_div = "<div class='swiper-container'> <div class='swiper-wrapper'>";

  /**
   * Filter the default gallery shortcode CSS styles.
   *
   * @since 2.5.0
   *
   * @param string $gallery_style Default gallery shortcode CSS styles.
   * @param string $gallery_div   Opening HTML div container for the gallery shortcode output.
   */
  $output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );

  $i = 0;
  foreach ( $attachments as $id => $attachment ) {

    // if ( ! empty( $link ) && 'file' === $link )
    //  $image_output = wp_get_attachment_link( $id, $size, false, false );
    // elseif ( ! empty( $link ) && 'none' === $link )
    //  $image_output = wp_get_attachment_image( $id, $size, false );
    // else
    //  $image_output = wp_get_attachment_link( $id, $size, true, false );
      $image_output = wp_get_attachment_image( $id, 'thumb-s', false );
      $new_image_href = wp_get_attachment_image_src( $id, 'gallery-l', false );
      //$new_image_headers = @get_headers($new_image_href);

    // if($new_image_headers[0] == 'HTTP/1.0 404 Not Found' ) {
      
    //  $new_image_href = $image_href;
    
    // }
    // else {
          
    // }

    $image_meta  = wp_get_attachment_metadata( $id );

    $orientation = '';
    if ( isset( $image_meta['height'], $image_meta['width'] ) )
      $orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';


    if($i == 0) {
      $output .= "<{$itemtag} class='swiper-slide active' data-src='" . $new_image_href[0] ."'>";
    } else {
      $output .= "<{$itemtag} class='swiper-slide' data-src='" . $new_image_href[0] ."'>";
    }

    //$link = $image_meta['image_meta']['custom_imgurl'];
    $link = get_post_meta($id,'custom-img-url',true);
    //echo $link; die;
    //print_r($image_meta);
    //echo 'Link' . $link;
    if($link) { $output .= '<a href="' .$link. '">'; $endtag = '</a>';} else { $endtag='';}
    $output .= "$image_output";
    if ( $captiontag && trim($attachment->post_excerpt) ) {
      $output .= "";
    }
    $output .= "</{$itemtag}>";
    $output .= $endtag;
    if ( ! $html5 && $columns > 0 && ++$i % $columns == 0 ) {
      $output .= '';
    }
    $i++;
  }

  if ( ! $html5 && $columns > 0 && $i % $columns !== 0 ) {
    $output .= "";
  }

  $output .= "
    </div><div class='swiper-pagination'></div></div><!-- End gallery -->\n";

  return $output;
}

/* Get ID by slug */
function get_id_by_slug($page_slug) {
    $page = get_page_by_path($page_slug);
    if ($page) {
        return $page->ID;
    } else {
        return null;
    }
} 

/* DON'T DELETE THIS CLOSING TAG */ ?>
