/* generated from resources/views/vendor/survloop/css/styles-2-navbar.blade.php */

#mainNav {
    position: fixed;
    z-index: 99;
    width: 100%;
    background: {!! $css["color-nav-bg"] !!};
    border-bottom: 1px {!! $css["color-main-grey"] !!} solid;
}
#mainNav, #mainNav .col-4, #mainNav .col-8, .navbar, 
#myNavBar, #myNavBar .navbar {
    height: 56px;
	min-height: 56px;
	max-height: 56px;
	padding-top: 1px;
	color: {!! $css["color-nav-text"] !!};
}
.navbar, #myNavBar, #myNavBar .navbar {
    text-align: right;
}
#mainNav2 {
    display: none;
    width: 100%;
    margin-top: -1px;
    background: {!! $css["color-nav-bg"] !!};
}
#headClear {
    clear: both;
	background: {!! $css["color-nav-bg"] !!};
	margin-left: -1px;
}

#topNavSearchBtn {
    display: block;
    margin-left: 30px;
}
#dashSearchFrmWrap {
    position: relative;
    width: 320px;
    height: 40px;
    margin-top: 7px;
}
#topNavSearchBtn {
    display: block;
    margin-left: 30px;
}
#topNavSearch {
    display: block;
    position: relative;
    margin-top: -1px;
    margin-left: 30px;
}
.topNavSearch #dashSearchFrmWrap #dashSearchBg,
.topNavSearchActive #dashSearchFrmWrap #dashSearchBg {
    position: absolute;
    z-index: 10;
    top: 0px;
    left: 0px;
    width: 320px;
    height: 40px;
    color: {!! $css["color-nav-text"] !!};
    background: {!! $css["color-nav-bg"] !!};
    box-shadow: 0px none;
    -moz-border-radius: 10px; border-radius: 10px;
}
.topNavSearchActive #dashSearchFrmWrap #dashSearchBg {
    box-shadow: 0px 0px 10px {!! $css["color-main-grey"] !!};
}
.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:link, 
.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:active, 
.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:visited, 
.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:hover,
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:link, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:active, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:visited, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:hover,
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:link, 
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:active, 
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:visited, 
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:hover,
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:link, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:active, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:visited, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:hover {
    position: absolute;
    z-index: 99;
    width: 20px;
    top: 6px;
    color: {!! $css["color-main-bg"] !!};
    padding: 5px;
}
.topNavSearch #dashSearchFrmWrap #admSrchFld {
    color: {!! $css["color-main-bg"] !!};
}

#admSrchFld {
    position: absolute;
    left: 40px;
    top: 0px;
    border: 0px none;
    background: none; 
    background-color: none;
    z-index: 80;
    width: 240px;
    height: 40px;
    padding-left: 5px;
}
#admSrchFld, #admSrchFld a:link, #admSrchFld a:visited, 
#admSrchFld a:active, #admSrchFld a:hover {
    color: {!! $css["color-main-bg"] !!};
}
#admSrchFld::placeholder, #admSrchFld:-ms-input-placeholder, 
#admSrchFld::-ms-input-placeholder {
    color: {!! $css["color-main-bg"] !!};
}
#hidivSearchOpts {
    display: none;
    position: absolute;
    z-index: 99;
    left: -1px;
    top: 41px;
    width: 322px;
    overflow: visible;
    color: {!! $css["color-main-text"] !!};
    background: {!! $css["color-main-bg"] !!};
    border: 1px {!! $css["color-main-grey"] !!} solid;
    -moz-border-radius: 3px; border-radius: 3px;
    box-shadow: 0px 0px 10px {!! $css["color-main-grey"] !!};
}
.srchOpt {
    width: 100%;
    height: 30px;
    padding: 10px 15px;

}

.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:link, 
.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:active, 
.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:visited, 
.topNavSearch #dashSearchFrmWrap a#dashSearchBtn:hover,
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:link, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:active, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:visited, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:hover {
    left: 10px;
}
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:link, 
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:active, 
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:visited, 
.topNavSearch #dashSearchFrmWrap a#hidivBtnSearchOpts:hover,
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:link, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:active, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:visited, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:hover {
    right: 10px;
}
.topNavSearchActive #dashSearchFrmWrap #admSrchFld,
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:link, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:active, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:visited, 
.topNavSearchActive #dashSearchFrmWrap a#dashSearchBtn:hover,
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:link, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:active, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:visited, 
.topNavSearchActive #dashSearchFrmWrap a#hidivBtnSearchOpts:hover {
    color: {!! $css["color-nav-text"] !!};
}

.headGap {
    display: block;
    width: 100%;
    height: 56px;
	margin-bottom: 0px;
}
.headGap img {
    height: 56px;
    width: 1px;
}
#headBar {
    width: 100%;
    display: none;
	background: {!! $css["color-main-faint"] !!};
}

#mySidenav {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 99;
    top: 0;
    right: 0;
    border-left: 0px none;
    overflow-x: hidden;
    transition: 0.5s;
	color: {!! $css["color-nav-text"] !!};
    background: {!! $css["color-main-faint"] !!};
    border-left: 0px none;
    box-shadow: none;
}
#mySidenav a {
    padding: 10px 20px;
    text-decoration: none;
    font-size: 18px;
    color: {!! $css["color-main-link"] !!};
    display: block;
    transition: 0.3s
}
#mySidenav a:hover {
	color: {!! $css["color-nav-text"] !!};
	background: {!! $css["color-nav-bg"] !!};
}
@media screen and (max-height: 450px) {
    #mySidenav a {font-size: 18px;}
} 
#mySideUL {
    padding-top: 10px;
}

a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, 
a.slNavLnk:visited, a.slNavLnk:hover, 
.slNavRight a, 
.slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, 
.slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
    display: block;
    padding: 15px 15px;
    margin-right: 10px;
	color: {!! $css["color-nav-text"] !!};
}

#slNavMain {
    margin-top: -4px;
}
#slNavMain .card-body, #slNavMain div .card .card-body {
    padding: 5px 0px 0px 0px;
}
#slNavMain .card-body, #slNavMain div .card .card-body .list-group {
    margin: 0px;
}
.list-group-item.completed, .list-group-item.completed:hover, 
.list-group-item.completed:focus {
    z-index: 2;
    color: {!! $css["color-main-text"] !!};
    background-color: {!! $css["color-main-faint"] !!};
}


#slLogoWrap {
    display: block;
}
#slLogo {
    display: block;
    margin: 7px 0px 0px 30px;
}
#slLogoImg, #slLogoImgSm {
    display: inline;
    height: 40px;
    margin-top: 0px;
}
#slLogoImgSm {
    display: none;
}
#logoPrint #slLogoImg {
    height: 50px;
    margin-top: 10px;
}
#slLogo.w100 {
    width: 100%;
    margin-top: 10px;
}
#slLogoImg.w100 {
    height: auto;
    width: 100%;
}
.slPrint #slLogo, .slPrint #slLogo.w100, 
.slPrint #slLogoImg, .slPrint #slLogoImg.w100 {
    height: 100px;
    width: auto;
}

.navbar-brand, a.navbar-brand:link, a.navbar-brand:visited, a.navbar-brand:active, a.navbar-brand:hover {
	font-size: 32pt;
}
#logoTxt {
	padding-left: 10px;
	margin-top: -2px;
}
#headLogoLong img {
    height: 50px;
}

.search-bar {
    width: 100%;
    position: relative;
}
.search-bar input {
    width: 100%
}
.search-bar .search-btn-wrap {
    position: absolute;
    top: 0px;
    right: 0px;
    height: 54px;
    width: 52px;
    overflow: hidden;
}
.search-bar .search-btn-wrap a .fa-search {
    font-size: 15pt;
    margin: 5px 5px;
}
.btn.btn-info.searchBarBtn {
    height: 46px;
    width: 54px;
    margin-left: -2px;
    margin-right: -2px;
}

a.navbar-brand:link, a.navbar-brand:visited, 
a.navbar-brand:active, a.navbar-brand:hover {
	color: {!! $css["color-nav-text"] !!};
}

#userMenuBtnWrp {
    position: relative;
    padding: 0px 18px 0px 49px;
}
#userMenuArr, #userMenuBtnWrp #userMenuArr {
    position: absolute;
    top: 3px;
    right: 0px;
}
#userMenuBtnAvatar, #userMenuBtnWrp #userMenuBtnAvatar {
    position: absolute;
    top: -6px;
    left: 2px;
    border: 1px {!! $css["color-main-grey"] !!} solid;
    -moz-border-radius: 19px; border-radius: 19px;
    height: 36px;
    max-height: 36px;
    width: 36px;
    max-width: 36px;
    overflow: hidden;
}
#userMenuBtnAvatar img, 
#userMenuBtnWrp #userMenuBtnAvatar img {
    border: 0px none;
    width: 34px;
    min-width: 34px;
    max-width: 34px;
}
#userMenuBtnName {
    display: inline;
}
