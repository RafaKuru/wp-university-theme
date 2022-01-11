<?php

require get_theme_file_path('/includes/search-route.php');

function university_custom_rest() {
    register_rest_field('post', 'authorName', array(
        'get_callback' => function() {return get_the_author();}
    ));
}

add_action('rest_api_init', 'university_custom_rest');

function pageBanner($args = NULL) {
    if(!$args['background']) {
        if (get_field('page_banner_background_image') AND !is_archive() AND !is_home()) {
            $args['background'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
        } else {
            $args['background'] = get_theme_file_uri('/images/ocean.jpg');
        }
    }
    ?>
    <div class="page-banner">
        <?php $pageBannerImage = get_field('page_banner_background_image');?>
        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['background']?>)"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title'] ? $args['title'] : get_the_title() ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle'] ? $args['subtitle'] : get_field('page_banner_subtitle') ?></p>
            </div>
        </div>
    </div>
<?php }

function university_files() {
    wp_enqueue_script('main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));

    wp_localize_script('main-university-js', 'universityData', array(
        'root_url' => get_site_url()
    ));
}

add_action('wp_enqueue_scripts', 'university_files');

function university_features () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
}

add_action('after_setup_theme', 'university_features');

function university_adjust_queries ($query) {
    if(!is_admin() AND is_post_type_archive('event') AND is_main_query()){
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
            array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => date('Ymd'),
                'type' => 'numeric'
            )
        ));
    }

    if(!is_admin() AND is_post_type_archive('program') AND is_main_query()){
        $query->set('posts_per_page', -1);
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
    }
}

add_action('pre_get_posts', 'university_adjust_queries');

//redirect subscriber accounts out of admin and onto homepage
function redirectSubsToHome() {
    $currentUser = wp_get_current_user();

    if(count($currentUser->roles) == 1 AND $currentUser->roles[0] == 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
}

add_action('admin_init', 'redirectSubsToHome');

function noSubsAdminBar() {
    $currentUser = wp_get_current_user();

    if(count($currentUser->roles) == 1 AND $currentUser->roles[0] == 'subscriber') {
        show_admin_bar(false);
    }
}

add_action('wp_loaded', 'noSubsAdminBar');

//customize login screen
function headerUrl() {
    return esc_url(site_url('/'));
}

add_filter('login_headerurl', 'headerUrl');

function loadCssAdmin() {
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
}

add_action('login_enqueue_scripts', 'loadCssAdmin');

function loginTitle(){
    return get_bloginfo('name');
}
add_filter('login_headertitle', 'loginTitle');
