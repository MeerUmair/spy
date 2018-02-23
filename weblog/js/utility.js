jQuery(function(){

//マウスオーバー（ファイルの末尾に「_off」「_on」で切り替え）
jQuery("img.over,input.over")
.each( function(){
	jQuery("<img>,<input>").attr("src",jQuery(this).attr("src").replace(/^(.+)_off(\.[a-z]+)$/, "$1_on$2"));
})
.mouseover( function(){
	jQuery(this).attr("src",jQuery(this).attr("src").replace(/^(.+)_off(\.[a-z]+)$/, "$1_on$2"));
})
.mouseout( function(){
	jQuery(this).attr("src",jQuery(this).attr("src").replace(/^(.+)_on(\.[a-z]+)$/, "$1_off$2"));
});

//検索ボックス内のテキスト
jQuery(".search").val("検索ワードを入力");
jQuery(".search").focus( function(){
	var searchWord=jQuery(this);
		if(searchWord.val()=='検索ワードを入力'){
		jQuery(this).val(""),
		jQuery(this).addClass("onfocus");
	};
});
jQuery(".search").blur( function(){
	var searchWord=jQuery(this);
		if(searchWord.val()==''){
		jQuery(this).val("検索ワードを入力"),
		jQuery(this).removeClass("onfocus");
	};
});

//テーブルのセルとリストに偶数・奇数を付与
jQuery("li:odd,tr:odd").addClass("odd"),
jQuery("li:even,tr:even").addClass("even");

//スムーズスクロール
jQuery("a[href^=#]").click(function(){
	var Hash = jQuery(this.hash);
	var HashOffset = jQuery(Hash).offset().top;
	jQuery("html,body").animate({
		scrollTop: jQuery(jQuery(this).attr("href")).offset().top }, 'slow','swing');
	return false;
});

//グローバルメニューのプルダウン設定
jQuery("#menu li").hover(function() {
	jQuery("> ul:not(:animated)", this).fadeIn("normal");
}, function() {
	jQuery("> ul", this).fadeOut("normal");
});


//グローバルメニュー用のIE「z-index」バグ対策
var zNum = 1000;
jQuery('#global-nav li').each(function() {
	jQuery(this).css('zIndex', zNum);
	zNum = zNum-10;
});

//モバイル用のグローバルメニュー設定
jQuery(".btn-gnav").click(function(){
	jQuery(".menu-wrap").toggleClass("showMenu");
});

//モバイル用のサイドバーとサブコンテンツの設定
jQuery(".sub-contents-btn").click(function(){
	jQuery(".sub-column #sub-contents-in").toggleClass("showSubConts");
});

jQuery(".sidebar-btn").click(function(){
	jQuery(".sub-column #sidebar-in").toggleClass("showSidebar");
});

//クリックでテキストを選択
jQuery(".text-field")
	.focus(function(){
		jQuery(this).select();
	})
	.click(function(){
		jQuery(this).select();
		return false;
});


});


