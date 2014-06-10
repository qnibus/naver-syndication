<?php
/**
 * @file naversyndication_xml_template.php
 * @author sol (ngleader@gmail.com)
 * @brief Print Syndication Data XML
 */


/**
http://apps.qnibus.com/wp/syndi/tag:apps.qnibus.com,2010:site/article
http://apps.qnibus.com/wp/syndi/tag:apps.qnibus.com,2014:channel:1/channel
*/

header("Content-Type: text/xml; charset=UTF-8");
header("Pragma: no-cache");

if(version_compare(PHP_VERSION, '5.3.0') >= 0) {
	date_default_timezone_set(@date_default_timezone_get());		
}

// 플러그인 환경설정 값 전역변수로 설정
foreach(get_option('qnibus_naversyndication') as $k=>$v) {
	$GLOBALS[$k] = $v;
}

// 주소로 받은 query_var 값
$_GET['id'] = get_query_var('id');
$_GET['type'] = get_query_var('type');
$_GET['start-time'] = get_query_var('start-time');
$_GET['end-time'] = get_query_var('end-time');
$_GET['max-entry'] = get_query_var('max-entry');
$_GET['page'] = get_query_var('page');

// 클래스 불러오기
include dirname(__FILE__) . '/classes/SyndicationHandler.class.php';
include dirname(__FILE__) . '/classes/SyndicationObject.class.php';
include dirname(__FILE__) . '/classes/SyndicationSite.class.php';
include dirname(__FILE__) . '/classes/SyndicationChannel.class.php';
include dirname(__FILE__) . '/classes/SyndicationArticle.class.php';
include dirname(__FILE__) . '/classes/SyndicationDeleted.class.php';

// 파라미터에 따라 XML 불러오는 함수 실행
include dirname(__FILE__) . '/naversyndication_function.php';

$oSyndicationHandler = &SyndicationHandler::getInstance();
$oSyndicationHandler->setArgument();

echo $oSyndicationHandler->getXML();
?>