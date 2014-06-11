=== Naver Syndication ===
Contributors: qnibus
Donate link: http://qnibus.com
Plugin URI: http://qnibus.com/blog/naver-syndication-plugin/
Tags: 네이버, Naver, 신디케이션, NaverSyndication, Syndication
Requires at least: 3.8
Tested up to: 3.9
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

네이버 신디케이션을 사용할 수 있게 도와주는 플러그인 입니다.

== Description ==

본 플러그인은 standby 상태에서 <a href="https://help.naver.com/support/contents/contents.nhn?serviceNo=606&categoryNo=2017" target="_blank">네이버 고객센터</a>에 요청해 working 상태로 만드셔야 실 사용이 가능합니다. 

* PHP 5.5이상에서 사용 불가
* Permalink 활성화 사이트만 이용 가능
* 컨텐츠 타입(web, blog) 선택 가능
* 사이트 상태 체크
* 피드주소는 http://mydomain.com/syndi/syndi_echo.php?id=[tag]&type=[type] 형태로 만들어집니다.

== Installation ==

플러그인은 설치하시면 됩니다.

== Frequently Asked Questions ==

문의사항은 안반장의 개발 노트 블로그 http://qnibus.com/blog/naver-syndication-plugin/ 에
댓글남겨주세요. 신디케이션 자체가 신청한다고 다 되는 것도 아닌지라 문제도 많습니다.
플러그인 작동이 안되는 점을 제외하고는 네이버 고객센터로 문의 주세요!

1. 설치했는데 어떻게 사용해야 하나요? 방법을 모르겠습니다.
	* 설치 후 설정화면에서 설정 확인하시면 되며, 이후는 포스트 등록/수정, 카테고리 등록/수정에 의해서 자동으로 네이버에 전송되게 되어 있으니
	따로 건드리실 것이 없습니다.
2. 검색엔진과 연동이 되는 건가요?
	* 설정화면의 상태를 체크해주시기 바랍니다. 반드시 working 상태가 되어야 네이버 신디케이션 서버와 실 통신이 가능하오니 상태를 반드시 확인해주세요.
3. no_exist_site로만 나오는데 뭐가 문제입니까?
	* 네이버 신디케이션 핑 서버와 통신을 한번도 하지 못하는 경우 그렇습니다. 이럴 경우 포스트를 새로 등록해보세요.

== Screenshots ==
1. 관리자 모드로 최소한의 기능만 제작되었습니다.

== Changelog ==

= 1.0 =
* 포스트 등록/수정/삭제시 핑 전송
* 카테고리 등록/수정/삭제시 핑 전송
* 신디케이션 피드문서 자동 발행

== Upgrade Notice ==
* 채널 등록 일자 추가 방안 마련
* 컨텐츠 복구/삭제시 신디케이션 기능 지원