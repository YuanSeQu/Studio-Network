(function () {
    var style = 'position:relative;z-index:1;max-width:100%!important;margin:auto;';
    function getParam(name) {
        return location.href.match(new RegExp('[?#&]' + name + '=([^?#&]+)', 'i')) ? RegExp.$1 : '';
    }

    var url = decodeURIComponent(getParam('url'));
    var iframeId = getParam('id'), $iframe = null, $wxEditor = null;
    if (iframeId) {
        $iframe = $(parent.document).contents().find('#' + iframeId);
        style = $iframe.attr('style');
        $wxEditor = $iframe.parent('._wxEditor');
        $wxEditor.height($iframe.height());
    }
    var videoObject = {
        container: '#video', //“#”代表容器的ID，“.”或“”代表容器的class
        // crossOrigin: 'Anonymous',//发送跨域字符
        video: url,//视频地址
        webFull:true,//是否启用页面全屏按钮，默认不启用
        theatre:true,//是否启用剧场模式按钮，默认不启用
        controls:false,//是否显示自带控制栏
        rightBar:true,//是否开启右边控制栏
        smallWindows:false,//是否启用小窗口模式
        screenshot:true,//截图功能是否开启
        menu: [
            {
                title:'关于视频',
                click:'aboutShow'
            }
        ],
    };
    var m = /.*(\.\w+)[?#].*$/.exec(url);
    if (m) {
        var type = m[1].toLowerCase();
        if (type == '.m3u8') {
            videoObject.plug = 'hls.js';
        } else if (type == '.flv') {
            videoObject.plug = 'flv.js';
        } else if (type == '.ts') {
            videoObject.plug = 'mpegts.js';
        }
    }
    var player = new ckplayer(videoObject);
    //页面全屏
    player.webFull(function (bool) {
        if (bool && $iframe) {
            var _style = $('#video >.ckplayer-ckplayer').attr('style');
            $iframe.attr('style', _style).css('z-index', '99999999999999');
        } else {
            $iframe.attr('style', style);
        }
    });
    //剧场模式
    player.theatre(function (bool) {
        if (bool && $iframe) {
            var _style = $('#video >.ckplayer-ckplayer').attr('style');
            $iframe.attr('style', _style).css('z-index', '99999999999999');
        } else {
            $iframe.attr('style', style);
        }
    });
    var small = player.smallWindows();
    //小窗口
    player.smallWindows(function (bool) {
        if (bool) {
            $(parent.window).off('scroll.player').on('scroll.player', function () {

                if ($('#video').get(0).webFull || $('#video').get(0).theatre) {
                    $iframe.removeAttr('smallwindow');
                    $('#video >.ckplayer-ckplayer').removeClass('ckplayer-ckplayer-smallwindow');
                    return;
                }

                var scrollTop = $(this).scrollTop();
                var offset = $wxEditor.offset();
                var height = $wxEditor.height();
                if (scrollTop > offset.top + height) {
                    if (!$iframe.attr('smallwindow')) {
                        $iframe.attr('smallwindow', 1);
                        $iframe.attr('style', 'position: fixed;z-index: 9999999;width: 420px;max-width: 100%;height: 266px;right: 10px;bottom: 10px;');
                        $('#video >.ckplayer-ckplayer').addClass('ckplayer-ckplayer-smallwindow')
                    }
                } else {
                    $iframe.removeAttr('smallwindow');
                    $iframe.attr('style', style);
                    $('#video >.ckplayer-ckplayer').removeClass('ckplayer-ckplayer-smallwindow');
                }
            });
        } else {
            $(parent.window).off('scroll.player');
            $iframe.removeAttr('smallwindow');
            $iframe.attr('style', style);
        }
    });
    small && player.smallWindows(true);
})();