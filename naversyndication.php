<?php
/*
Plugin Name: Naver Syndication
Plugin URI: http://qnibus.com/blog/naver-syndication-plugin/
Description: 워드프레스에서 네이버 신디케이션을 사용할 수 있게 도와주는 플러그인 입니다.
Version: 1.0
Author: qnibus (Jong-tae Ahn)
Author URI: http://qnibus.com
Author Email: andy@qnibus.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'QnibusNaverSyndication' ) ) {
	class QnibusNaverSyndication {
		public $name = 'qnibus_naversyndication';
		public $plugin_dir = '';
		public $plugin_url = '';
		public $plugin_file = '';
		public $table_name = '';
		public $options = array(); 
	
		function __construct() {
			$this->initialize();
			$this->initWithPings();

			if ( is_admin() ) {
				register_activation_hook( __FILE__ , array( get_class($this), 'activationSyndication' ) );
				register_uninstall_hook( __FILE__, array( get_class($this), 'uninstallSyndication' ) );
			}
		}
		
		
		/**************************************************************************
         * 플러그인 활성화
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		static function activationSyndication() {
			if ( version_compare( PHP_VERSION, '5.0.1', '<' ) ) { 
                deactivate_plugins( basename( __FILE__ ) ); // Deactivate ourself 
            	wp_die( "PHP 5.0.1 이상에서만 사용이 가능합니다. (현재 PHP 버전: ".PHP_VERSION.")" );
        	}

        	if(!get_option( 'permalink_structure' )) {
        		deactivate_plugins( basename( __FILE__ ) ); // Deactivate ourself 
          		wp_die( "네이버 정책상 퍼머링크를 활성화하지 않으면 사용할 수 없습니다." );
        	}

			global $wpdb;

			$table_name = $wpdb->prefix . 'qnibus_naversyndication';
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `$table_name` (
					`post_id` bigint(20) unsigned NOT NULL, 
					`term_id` bigint(20) unsigned NOT NULL, 
					`title` text NOT NULL,
					`link_alternative` varchar(250) NOT NULL, 
					`delete_date` varchar(14) NOT NULL, 
					PRIMARY KEY  (`post_id`,`term_id`)
				);"
			);
		}
		
		
		
		/**************************************************************************
         * 플러그인 삭제
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		static function uninstallSyndication() {
			if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
			    exit();
			
			if ( !is_multisite() ) {
			    delete_option( $this->name );
			} 
			else {
			    global $wpdb;
			    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			    $original_blog_id = get_current_blog_id();
			    foreach ( $blog_ids as $blog_id ) 
			    {
			        switch_to_blog( $blog_id );
			        delete_option( $this->name );  
			    }
			    switch_to_blog( $original_blog_id );
			    delete_site_option( $this->name );  
			}
		}
		
		
		/**************************************************************************
         * 초기화
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function initialize() {
			global $wpdb;
			
			$this->plugin_dir = trailingslashit( dirname( __FILE__ ) );
			$this->plugin_url = plugin_dir_url(__FILE__);
			$this->plugin_file = basename( __FILE__ );
			$this->table_name = $wpdb->prefix . $this->name;
			
			// static variable
			$this->options = wp_parse_args( get_option( $this->name ), array(
					'syndi_enable' => true,
					'syndi_homepage_type' => 'web',
					'syndi_tag_year' => date('Y'),
					'syndi_from_encoding' => 'UTF-8',
					'syndi_time_zone' => '+09:00',
				)
			);
			add_option( $this->name, $this->options );
			
			// dynamic variable
			$this->options = wp_parse_args( array(
					'syndi_homepage_url' => get_bloginfo( 'url' ),
					'syndi_homepage_title' => get_bloginfo( 'name' ),
					'syndi_permalink' => get_option( 'permalink_structure' ),
					'syndi_echo_url' => get_bloginfo( 'wpurl' ) . '/syndi/syndi_echo.php',
					'syndi_tag_domain' => $_SERVER['HTTP_HOST'],
				), get_option( $this->name )
			);
			update_option( $this->name, $this->options );
			add_action( 'init', array( &$this, 'addSyndicationFeed' ) );
			add_action( 'init', array( &$this, 'addSyndicationFeedUrl' ) );
			add_filter( 'init', array( &$this, 'addSyndicationFeedQueryVars' ) );
			add_action( 'admin_menu', array( &$this, 'registerSubmenuPage' ) );
		}
		
		
		/**************************************************************************
         * 관리메뉴 등록
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function registerSubmenuPage() {
			if ( ! current_user_can( 'manage_options' ) )  {
				return false;
			}
			add_submenu_page( 'options-general.php', 'NaverSyndication', 'Naver Syndication', 'manage_options', basename(__FILE__), array( &$this, 'displaySubmenuPage' ) );
			add_action( 'admin_init', array( &$this, 'registerOptions' ) );
		}
		
		
		/**************************************************************************
         * 관리페이지 출력
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function displaySubmenuPage() {
			$hidden_field_name = 'syndi_submit_hidden';
			if ( isset( $_POST[$hidden_field_name] ) && $_POST[$hidden_field_name] == 'Y' ) {
				$this->options['syndi_homepage_url'] = esc_url( $_POST['syndi_homepage_url'] );
				$this->options['syndi_homepage_type'] = esc_html( $_POST['syndi_homepage_type'] );
				//$this->options['syndi_homepage_title'] = esc_html( $_POST['syndi_homepage_title'] );
				//$this->options['syndi_enable'] = $_POST['syndi_enable'];
		        update_option( $this->name, $this->options );
?>
				<div class="updated"><p><strong><?php _e( 'settings saved.', 'syndication_notice_save' ); ?></strong></p></div>
<?php
			}
			require_once dirname( __FILE__ ) . '/naversyndication_admin_settings.php';
		}
		
		
		/**************************************************************************
         * 등록옵션
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function registerOptions() {
		    register_setting( $this->name, $this->name, array( &$this, 'optionValidate' ) );
		}
		
		
		/**************************************************************************
         * 피드 및 템플릿 연결
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function optionValidate($input) {
			return $input;
		}
		
		
		
		
		/**************************************************************************
         * 피드추가
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function addSyndicationFeed() {
			add_feed("syndi/syndi_echo.php", array( &$this, 'displaySyndicationFeed' ) );
		}
		
		
		/**************************************************************************
         * 피드주소 퍼머링크 생성
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function addSyndicationFeedUrl() {
			global $wp_rewrite;
			add_rewrite_rule(
				'^syndi/syndi_echo.php?id=(\S*)&type=(\S*)$',
				'index.php?feed=syndi&id=$matches[1]&type=$matches[2]',
				'top'
			);
			$wp_rewrite->flush_rules();
		}
		
		
		/**************************************************************************
         * 피드 쿼리 변수 설정
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function addSyndicationFeedQueryVars() {
			global $wp;
			
			$wp->add_query_var('id');
			$wp->add_query_var('type');
			$wp->add_query_var('start-time');
			$wp->add_query_var('end-time');
			$wp->add_query_var('max-entry');
			$wp->add_query_var('page');
		}
		
		
		
		/**************************************************************************
         * 피드 템플릿 출력
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function displaySyndicationFeed() {
			load_template( $this->plugin_dir . '/naversyndication_xml_template.php' );
		}

	    
	    /**************************************************************************
         * ping 초기화
         *
         * @since version 1.0
         * @return NULL
		 * @access public
		 * @reference http://codex.wordpress.org/Plugin_API/Action_Reference/
         **************************************************************************/
		function initWithPings() {
			if ( ! $this->options['syndi_enable'] )
				return;

			add_action( 'delete_post', array( &$this, 'pingTrashPost' ), 10, 3 );
			add_action( 'trash_post', array( &$this, 'pingTrashPost' ), 10, 3 );
			add_action( 'publish_post', array( &$this, 'pingPublishPost' ), 10, 3 );
			add_action( 'create_category', array( &$this, 'pingAllCategory' ), 10, 3 );
			add_action( 'edit_category', array( &$this, 'pingAllCategory' ), 10, 3 );
			add_action( 'delete_category', array( &$this, 'pingAllCategory' ), 10, 3 );
			//add_action( 'transition_post_status', 'intercept_all_status_changes', 10, 3 );
		}
		

		/**************************************************************************
         * 포스트의 상태 변화에 따른 액션 (추후 지원)
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function intercept_all_status_changes( $new_status, $old_status, $post ) {
		    if ( $new_status != $old_status ) {
		        // Post status changed
		    }
		}
		
		
		/**************************************************************************
         * 포스트 휴지통 및 영구삭제시 액션 처리
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function pingTrashPost($post_id){
			global $wpdb;
			
			$post = get_post($post_id);
			$category = get_the_category( $post_id );
			if (!$this->options['syndi_permalink']) {
				$link_alternative = home_url( '?p=' . $post_id );
			}
			else {
				$link_alternative = get_permalink( $post_id );
			}
		
			$wpdb->insert(
				$this->table_name,
				array(
					'post_id' => $post_id,
					'term_id' => $category[0]->term_id,
					'title' => $post->post_title,
					'link_alternative' => $link_alternative,
					'delete_date' => date("YmdHis"),
				)
			);			
			$this->request( $this->getTag( 'article', $category[0]->term_id ), 'deleted' );
		}
		
		
		/**************************************************************************
         * 포스트 생성 및 업데이트시 액션 처리
         *
         * @since version 1.0
         * @return NULL
		 * @access public
         **************************************************************************/
		function pingPublishPost($post_id){
			$category = get_the_category( $post_id );
			$this->request( $this->getTag( 'channel', $category[0]->term_id ), 'article' );
			
			/* 신규등록 or 수정에 따른 액션 처리
			if( ( $_POST['post_status'] == 'publish' ) && ( $_POST['original_post_status'] != 'publish' ) ) {
				$this->request( $this->getTag( 'article' ) );
		    } else {
		    	$this->request( $this->getTag( 'article', $category[0]->term_id, $post_id ), 'article' );
		    }
		    */
		}

		
		/**************************************************************************
         * 카테고리 생성/수정/삭제 액션 처리
         *
         * @since version 1.0
		 * @param string $term_id 카테고리 아이디
         * @return NULL
         * @access public
         **************************************************************************/
		function pingAllCategory($term_id) {
			$this->request( $this->getTag( 'site' ), 'channel' );
		}

		
		/**************************************************************************
         * 신디케이션 id(tag) 만들기
         *
         * @since version 1.0
		 * @param string $type 타입
		 * @param string $channel_id 채널 아이디
		 * @param string $article_id 글 아이디
         * @return string
         * @access private
         **************************************************************************/
		private function getTag($type, $channel_id=null, $article_id=null) {
			foreach(get_option('qnibus_naversyndication') as $k=>$v) $GLOBALS[$k] = $v;
			include_once dirname(__FILE__) . '/classes/SyndicationHandler.class.php';
			return SyndicationHandler::getTag($type, $channel_id, $article_id);
		}
		
		
		/**************************************************************************
         * 신디케이션 핑 요청
         *
         * @since version 1.0
		 * @param string $tag 태그
		 * @param string $type 타입 변수 값
         * @return NULL
         * @access private
         **************************************************************************/
		private function request($tag, $type) {
			if ( ! $this->options['syndi_enable'] )
				return;

			include_once dirname(__FILE__) . '/classes/SyndicationPing.class.php';
			$oPing = new SyndicationPing();
			$oPing->setId( $tag );
			$oPing->setType( $type );
			//$oPing->setStartTime();
			//$oPing->setEndTime();
			//$oPing->setMaxEntry();
			//$oPing->setPage();
			$oPing->request();
		}
		
		
		/**************************************************************************
         * 사이트 상태 조회
         *
         * @since version 1.0
         * @return array
         * @access private
         **************************************************************************/
		private function status() {
			include_once dirname(__FILE__) . '/classes/SyndicationStatus.class.php';
			$oStatus = new SyndicationStatus();
			$oStatus->setSite($this->options['syndi_tag_domain']);
			$data = $oStatus->request();

			if($data['error'] == -1) return $data['message'];
			else return $data['status'];
		}
	}
}
$qnibusNaverSyndication = new QnibusNaverSyndication();
