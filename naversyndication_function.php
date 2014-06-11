<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @brief 사이트 정보
 **/
function syndi_get_site_info( $args ) {
	$title = $GLOBALS['syndi_homepage_title'];
	$tag = SyndicationHandler::getTag( 'site' );

	$oSite = new SyndicationSite;
	$oSite->setId( $tag );
	$oSite->setTitle( $title );
	$oSite->setUpdated( date( 'YmdHis' ) );

	// 홈페이지 주소
	$oSite->setLinkAlternative( sprintf( 'http://%s', $GLOBALS['syndi_tag_domain'] ) );
	$oSite->setLinkSelf( sprintf( '%s?id=%s&type=%s', $GLOBALS['syndi_echo_url'], $tag, $args->type ) );

	return $oSite;
}

/**
 * @brief Channel(게시판) 목록 
 **/
function syndi_get_channel_list( $args ) {
	$categories = get_categories( array(
		'type' => 'post',
		'child_of' => 0,
		'orderby' => 'count',
	) );
	if( count($categories) > 0 ) {
		$channel_list = array();
		foreach( $categories as $category ) {
			if ( isset( $args->target_channel_id ) && $args->target_channel_id != $category->term_id )
				continue;

			$tag = SyndicationHandler::getTag( 'channel', $category->term_id );
			$oChannel = new SyndicationChannel;
			$oChannel->setId( $tag );
			$oChannel->setTitle( $category->cat_name );
			$oChannel->setType( $GLOBALS['syndi_homepage_type'] );
			$oChannel->setSummary( $category->description );
			$oChannel->setUpdated( date( 'YmdHis' ) );
			$oChannel->setLinkAlternative( get_category_link( $category->term_id ) );
			$oChannel->setLinkRss( get_category_feed_link( $category->term_id ) );
			$oChannel->setLinkSelf( sprintf( '%s?id=%s&type=%s', $GLOBALS['syndi_echo_url'], $tag, $args->type ) );
	
			$channel_list[] = $oChannel;
		}
		return $channel_list;
	}
}

/**
 * @brief 삭제 게시물 목록 
 * 삭제된 게시물에 대해 logging이 필요
 **/
function syndi_get_deleted_list($args) {
	global $wpdb, $qnibusNaverSyndication;

	$where = '';
	if($args->target_content_id) $where .= ' AND post_id='. $args->target_content_id;
	if($args->target_channel_id) $where .= ' AND term_id='. $args->target_channel_id;
	if($args->start_time) $where .= ' AND delete_date >= '. $args->start_time;
	if($args->end_time) $where .= ' AND delete_date <= '. $args->end_time;

	$sql = "SELECT post_id, term_id, title, delete_date, link_alternative FROM `".$qnibusNaverSyndication->table_name."` WHERE 1=1" . $where;
	$sql .= " order by delete_date desc ";	
	$sql .= sprintf(" limit %s,%s", ($args->page-1)*$args->max_entry, $args->max_entry);
	
	$deletedPosts = $wpdb->get_results( $sql );
	$deleted_list = array();
	foreach($deletedPosts as $post) {
		$oDeleted = new SyndicationDeleted;
		$tag = SyndicationHandler::getTag('article', $post->term_id, $post->post_id);
		$oDeleted->setId($tag);
		$oDeleted->setTitle($post->title);
		$oDeleted->setUpdated($post->delete_date);
		$oDeleted->setDeleted($post->delete_date);
		$oDeleted->setLinkAlternative($post->link_alternative);

		$deleted_list[] = $oDeleted;
	}

	return $deleted_list;
}

/**
 * @brief 게시물 목록 
 **/
function syndi_get_article_list($args) {
	if(isset($args->start_time)) {
		$sdate = getDate(strtotime($args->start_time));
		$start_time = array(
			'year' => $sdate['year'],
			'month' => $sdate['mon'],
			'day' => $sdate['mday'],
			'hour' => $sdate['hours'],
			'minute' => $sdate['minutes'],
			'second' => $sdate['seconds'],
		);
	} //else $start_time = array();
	
	if(isset($args->end_time)) {
		$edate = getDate(strtotime($args->end_time));
		$end_time = array(
			'year' => $edate['year'],
			'month' => $edate['mon'],
			'day' => $edate['mday'],
			'hour' => $edate['hours'],
			'minute' => $edate['minutes'],
			'second' => $edate['seconds'],
		);
	} //else $end_time = array();
	
	$the_query = new WP_Query(
		array(
			'p' => $args->target_content_id,
			'posts_per_page' => $args->max_entry,
			'cat' => $args->target_channel_id,
			'paged' => $args->page,
			'post_status' => 'publish',
			'cache_results' => false,
			'has_password' => false,
			'date_query' => array(
				array(
					'after' => $start_time,
					'before' => $end_time,
					'inclusive' => true
				)
			)
		)
	);
	$article_list = array();
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$category = get_the_category( get_the_ID() );

		$oArticle = new SyndicationArticle;
		$tag = SyndicationHandler::getTag('article', $category[0]->cat_ID, get_the_ID());
		$oArticle->setId( $tag );
		$oArticle->setTitle( get_the_title() );
		$oArticle->setContent( get_the_content() );
		$oArticle->setType( $GLOBALS['syndi_homepage_type'] );
		$oArticle->setCategory( $category[0]->name );
		$oArticle->setName( get_the_author_meta( 'display_name' ) );
		$oArticle->setEmail( get_the_author_meta( 'user_email' ) );
		$oArticle->setUrl( get_the_author_meta( 'user_url' ) );
		$oArticle->setPublished( get_the_date('YmdHis') );
		$oArticle->setUpdated( get_the_modified_date('YmdHis') );
		$oArticle->setLinkChannel($tag);
		$oArticle->setLinkChannelAlternative( get_category_link( $category[0]->term_id ) );
		//$oArticle->setLinkAlternative( home_url( '?p=' . get_the_ID() ) );
		$oArticle->setLinkAlternative( get_permalink( get_the_ID() ) );

		$article_list[] = $oArticle;
	}
	return $article_list;
}

/**
 * @brief 게시물 목록 출력시 다음 페이지 번호 
 **/
function syndi_get_article_next_page($args) {
	if(isset($args->start_time)) {
		$sdate = getDate(strtotime($args->start_time));
		$start_time = array(
			'year' => $sdate['year'],
			'month' => $sdate['mon'],
			'day' => $sdate['mday'],
			'hour' => $sdate['hours'],
			'minute' => $sdate['minutes'],
			'second' => $sdate['seconds'],
		);
	} //else $start_time = array();
	
	if(isset($args->end_time)) {
		$edate = getDate(strtotime($args->end_time));
		$end_time = array(
			'year' => $edate['year'],
			'month' => $edate['mon'],
			'day' => $edate['mday'],
			'hour' => $edate['hours'],
			'minute' => $edate['minutes'],
			'second' => $edate['seconds'],
		);
	} //else $end_time = array();
	
	$the_query = new WP_Query(
		array(
			'p' => $args->target_content_id,
			'posts_per_page' => -1,
			'cat' => $args->target_channel_id,
			'post_status' => 'publish',
			'cache_results' => false,
			'has_password' => false,
			'date_query' => array(
				array(
					'after' => $start_time,
					'before' => $end_time,
					'inclusive' => true
				)
			)
		)
	);
	
	$total_count = $the_query->found_posts;
	$total_page = ceil($total_count / $args->max_entry);
	if($args->page < $total_page) {
		return array('page'=>$args->page+1);
	}
	else {
		return array(); 
	}
}

/**
 * @brief 게시물 삭제 목록 출력시 다음 페이지 번호 
 **/
function syndi_get_deleted_next_page($args) {
	global $wpdb, $qnibusNaverSyndication;
	
	$where = '';
	if($args->target_content_id) $where .= ' and post_id='. $args->target_content_id;
	if($args->target_channel_id) $where .= ' and term_id='. $args->target_channel_id;
	if($args->start_time) $where .= ' and delete_date >= '. $args->start_time;
	if($args->end_time) $where .= ' and delete_date <= '. $args->end_time;

	$wpdb->query( "SELECT post_id, term_id, title, delete_date, link_alternative FROM `" . $qnibusNaverSyndication->table_name . "` WHERE 1=1" . $where );	
	$total_count = $wpdb->num_rows;
	$total_page = ceil($total_count / $args->max_entry);

	if($args->page < $total_page) {
		return array('page'=>$args->page+1);
	}
	else {
		return array();
	}
}

/**
 * @brief Channel 목록 출력시 다음 페이지 번호 
 **/
function syndi_get_channel_next_page($args) {
	$categories = get_categories( array(
		'type' => 'post',
		'child_of' => 0,
		'orderby' => 'count',
	) );

	$total_count = 0;
	foreach( $categories as $category ) {
		if ( isset( $args->target_channel_id ) && $args->target_channel_id != $category->term_id )
			continue;
		$total_count++;
	}
	$total_page = ceil($total_count / $args->max_entry);

	if($args->page < $total_page) {
		return array('page'=>$args->page+1);
	}
	else {
		return array();
	}
}

$oSyndicationHandler = &SyndicationHandler::getInstance();
$oSyndicationHandler->registerFunction('site_info','syndi_get_site_info');
$oSyndicationHandler->registerFunction('channel_list','syndi_get_channel_list');
$oSyndicationHandler->registerFunction('channel_next_page','syndi_get_channel_next_page');
$oSyndicationHandler->registerFunction('article_list','syndi_get_article_list');
$oSyndicationHandler->registerFunction('article_next_page','syndi_get_article_next_page');
$oSyndicationHandler->registerFunction('deleted_list','syndi_get_deleted_list');
$oSyndicationHandler->registerFunction('deleted_next_page','syndi_get_deleted_next_page');
