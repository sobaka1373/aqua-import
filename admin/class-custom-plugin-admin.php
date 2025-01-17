<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Custom_Plugin_Admin
{

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/main-admin.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/main-admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }

    public function register_admin_page() {
        add_menu_page(
            'custom plugin', 'Custom plugin title', 'manage_options',
            'custom-plugin-admin', 'admin_page_open', '', 6
        );
    }

    public function start_convert_data_to_posts(): void
    {
        $data = get_data_from_db();

	    $args = array(
		    'meta_key' => 'jos_table',
		    'meta_value' => '1',
		    'post_type' => 'post',
		    'post_status' => 'publish',
		    'posts_per_page' => -1
	    );
	    $posts = get_posts($args);
		$post_count = count($posts);

        for ($i = $post_count, $iMax = count( $data ); $i < $iMax; $i++) {
            $blogIds = ['10', '42', '43', '44', '45', '46', '48', '49', '50', '51', '52', '53', '54', '55'];
            $portfolioIds =  ['20', '21', '22', '23', '24', '25', '26', '27', '28', '29'];


            if (in_array((string)$data[$i]->catid, $portfolioIds)) {
                $post_data = array(
                    'post_title'    => sanitize_text_field( $data[$i]->title ),
                    'post_content'  => $data[$i]->introtext,
                    'post_name'     => $data[$i]->asset_id .'-'. $data[$i]->alias,
                    'post_type'     => 'portfolio',
                    'post_date'      => $data[$i]->publish_up,
                    'post_status'    => 'publish',
                    'meta_input'    => [ 'jos_table' => true, 'jos_repeat' => true ],
                );
                $post_id = wp_insert_post( $post_data );
                $cat_id = categoryTable($data[$i]->catid);
                setCategory($post_id, $cat_id);
                setAcfBannerData($post_id, $data[$i]->images, $post_data['post_title'], $data[$i]->introtext);
                setAcfPortfolioData($post_id, $data[$i]->id);

                if ($data[$i]->language === 'ru-RU') {
                    setRussianTranslation($post_id);
//                    setMetaDescription($post_id, 'ru', $post_data['post_type'], $post_data['post_title']);
                } else {
//                    setMetaDescription($post_id, 'uk', $post_data['post_type'], $post_data['post_title']);
                }
            } elseif (in_array((string)$data[$i]->catid, $blogIds)) {

                $post_data = array(
                    'post_title'    => sanitize_text_field( $data[$i]->title ),
                    'post_content'  => $data[$i]->introtext,
                    'post_name'     => $data[$i]->asset_id .'-'. $data[$i]->alias,
                    'post_type'     => 'post',
                    'post_date'      => $data[$i]->publish_up,
                    'post_status'    => 'publish',
                    'meta_input'    => [ 'jos_table' => true, 'jos_repeat' => true ],
                );
                $post_id = wp_insert_post( $post_data );

                if ($data[$i]->language === 'ru-RU') {
                    setRussianTranslation($post_id);
//                    setMetaDescription($post_id, 'ru', $post_data['post_type'], $post_data['post_title']);
                } else {
//                    setMetaDescription($post_id, 'uk', $post_data['post_type'], $post_data['post_title']);
                }
            }



        }
		wp_die();
    }

	public function start_parse_posts(): void
    {
//		$args = array(
//			'meta_key' => 'jos_table',
//			'meta_value' => '1',
//			'post_type' => 'post',
//			'post_status' => 'publish',
//			'posts_per_page' => 1000
//		);
//		$posts = get_posts($args);
//
//		foreach ($posts as $post) {
//
//			$old_post_content = $post->post_content;
//			$content = parse_article($old_post_content);
//            $content = aditional_parse($content);
//            $content = aditional_parse($content);
//            $content = aditional_parse($content);
//
//            $my_post = [
//				'ID' => $post->ID,
//				'post_content' => $content,
//			];
//			wp_update_post(wp_slash($my_post));
//			delete_post_meta( $post->ID, 'jos_table' );
//		}
//
//		check_posts_consist_meta();
		wp_die();
	}

    public function start_second_parse_posts(): void
    {
        $args = array(
            'meta_key' => 'jos_repeat',
            'meta_value' => '1',
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 1000
        );
        $posts = get_posts($args);

        foreach ($posts as $post) {

            $old_post_content = $post->post_content;
            $content = aditional_parse($old_post_content);
            $content = aditional_parse($content);

            $my_post = [
                'ID' => $post->ID,
                'post_content' => $content,
            ];
            wp_update_post(wp_slash($my_post));
            delete_post_meta( $post->ID, 'jos_repeat' );
        }

        check_posts_consist_meta();
        wp_die();
    }
}