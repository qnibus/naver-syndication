<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>네이버 신디케이션</h2>
	<h3>기본 설정</h3>
	<hr />
	<form action="options-general.php?page=<?php echo $this->plugin_file; ?>" method="post">
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y" />
	<?php settings_fields($this->name); ?>
	<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="syndi_homepage_url">사이트 주소</label></th>
			<td>
				<input name="syndi_homepage_url" type="text" id="syndi_homepage_url" value="<?php echo $this->options['syndi_homepage_url']; ?>" class="regular-text">
				<!-- <button type="button" class="cpm_more_info_hndl cpm_blink_me">신디케이션 문서출력 동작확인</button> -->
				<div class="cpm_more_info" style="display: none;">
                    <p>To insert the map in a responsive design (in a responsive design, the map's width should be adjusted with the page width):</p>
                    <p>the value of map's width should be defined as a percentage of container's width, for example, type the value: <strong>100%</strong></p>
                    <a href="javascript:void(0)" onclick="cpm_hide_more_info( this );">[ + less information]</a>
                </div>
				<p class="description">신대케이션 정보를 제공할 때 사용되는 사이트의 주소를 입력해주세요 이 주소는 대표 주소를 이용해주시고 가능한 바꾸지 않는 것이 좋습니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="syndi_homepage_type">사이트 타입</label></th>
			<td>
				<select name="syndi_homepage_type" id="syndi_homepage_type">
					<option <?php echo $this->options['syndi_homepage_type'] == 'web' ? 'selected="selected"' : ''; ?> value="web">웹사이트</option>
					<option <?php echo $this->options['syndi_homepage_type'] == 'blog' ? 'selected="selected"' : ''; ?> value="blog">블로그</option>
				</select>
				<p class="description">사이트 타입은 기본적으로 웹사이트(web)로 지정됩니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">신디케이션 서비스</th>
			<td>
				<fieldset><legend class="hidden">신디케이션 서비스</legend>
				<h4><?php echo $this->status(); ?></h4>
				<p class="description">신디케이션이란 검색 서비스 업체와 syndication이라는 표준 규약을 통해서 보다 더 잘 검색되게 하는 기능입니다. 최소한의 요청만으로 효과적으로 컨텐츠를 검색 서비스 업체와 동기화합니다.</p>
				</fieldset>
			</td>
		</tr>
		<tr>
			<th scope="row">신디케이션 문서확인</th>
			<td>
				<fieldset><legend class="hidden">신디케이션 문서확인</legend>
				<ul>
					<li><a target="_blank" href="<?php echo $this->options['syndi_echo_url'] . '?id=' . $this->getTag( 'site' ) . '&type=site';?>" class="cpm_more_info_hndl cpm_blink_me">사이트 정보</a></li>
					<li><a target="_blank" href="<?php echo $this->options['syndi_echo_url'] . '?id=' . $this->getTag( 'site' ) . '&type=channel';?>" class="cpm_more_info_hndl cpm_blink_me">채널 목록</a></li>
					<li><a target="_blank" href="<?php echo $this->options['syndi_echo_url'] . '?id=' . $this->getTag( 'site' ) . '&type=article';?>" class="cpm_more_info_hndl cpm_blink_me">문서 목록</a></li>
					<li><a target="_blank" href="<?php echo $this->options['syndi_echo_url'] . '?id=' . $this->getTag( 'site' ) . '&type=deleted';?>" class="cpm_more_info_hndl cpm_blink_me">삭제된 문서 목록</a></li>
					<li><a target="_blank" href="<?php echo $this->options['syndi_echo_url'] . '?id=' . $this->getTag( 'channel', 1 ) . '&type=channel';?>" class="cpm_more_info_hndl cpm_blink_me">특정 채널 정보</a></li>
					<li><a target="_blank" href="<?php echo $this->options['syndi_echo_url'] . '?id=' . $this->getTag( 'channel', 1 ) . '&type=article';?>" class="cpm_more_info_hndl cpm_blink_me">특정 채널 문서 목록</a></li>
					<li><!-- <a href="" class="cpm_more_info_hndl cpm_blink_me disabled"> -->삭제 채널 목록<!-- </a> --></li>
					<li><a target="_blank" href="<?php echo $this->options['syndi_echo_url'] . '?id=' . $this->getTag( 'article', 1, 1 ) . '&type=article';?>" class="cpm_more_info_hndl cpm_blink_me">문서 정보</a></li>
				</ul>
				<p class="description">신디케이션 피드문서가 생성이 되어야 핑이 작동합니다. 버튼을 눌러 피드가 제대로 생성되었는지 확인하세요.</p>
				</fieldset>
			</td>
		</tr>
	</tbody>
	</table>
	<?php submit_button(); ?>
	</form>
	
	<h3>사용 방법 및 유의사항</h3>
	<hr />
	<ol>
		<li>포스트 또는 카테고리에 글 또는 카테고리를 등록하면 네이버 신디케이션과 자동으로 통신합니다.</li>
		<li>상태란에 <code>no_exist_site</code>가 나타나는 경우 1번을 하시면 잠시 후 <code>standby</code> 상태로 전환됩니다.</li>
		<li>실제 신디케이션이 검색엔진에 반영되려면 <code>working</code> 상태로 전환되어야 하며 전환하기 위해 네이버 고객센터에 별도 신청을 하셔야 합니다.</li>
		<li><a href="https://help.naver.com/support/contents/contents.nhn?serviceNo=606&categoryNo=2017" target="_blank">네이버 고객센터</a>로 가셔서 직접 신청해주시기 바라며, 신청한다고 모든 사이트가 신디케이션 서비스를 이용할 수 있는 것이 아니니 착오 없으시기 바랍니다.</li>
		<li>보다 자세한 내용은 <a href="http://developer.naver.com/wiki/pages/SyndicationAPI" target="_blank">네이버 신디케이션 API 사이트</a>를 참고하시기 바랍니다.</li>
		<li>저의 개인사이트 기준으로 기능 작동여부를 확인 했습니다. 사이트의 환경에 따라 작동이 안되는 경우가 발생할 수 있으니 참고하시기 바라오며, 플러그인 사이트에 <a href="http://qnibus.com/blog/naver-syndication-plugin" target="_blank">피드백</a> 주시기 바랍니다.</li>
	</ol>
</div>
