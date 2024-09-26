(function(){
    var isModifyUploadVideo = false;
    window.onload = function(){
        $focus($G("videoUrl"));
        initVideo();
    }
    function getParam(url, name) {
        return url.match(new RegExp('[?#&]' + name + '=([^?#&]+)', 'i')) ? RegExp.$1 : '';
    }
    function initVideo(){
        addUrlChangeListener($G("videoUrl"));
        addOkListener();
        var img = editor.selection.getRange().getClosedNode(),url;
        if(img && img.className){
            var hasFakedClass = (img.className == "edui-faked-video"),
                hasUploadClass = img.className.indexOf("edui-upload-video")!=-1;
            if(hasFakedClass || hasUploadClass) {
                $G("videoUrl").value = url = img.getAttribute("_url");
                $G("videoWidth").value = img.width;
                $G("videoHeight").value = img.height;
                var align = domUtils.getComputedStyle(img,"float"),
                    parentAlign = domUtils.getComputedStyle(img.parentNode,"text-align");
                updateAlignButton(parentAlign==="center"?"center":align);
            }
            if(hasUploadClass) {
                isModifyUploadVideo = true;
            }
        }else if(dialog.anchorEl){
            if($(dialog.anchorEl).hasClass('video_iframe')) {
                url = $(dialog.anchorEl).attr('src');
                url = decodeURIComponent(getParam(url, 'url'));
                $G("videoUrl").value = url;
                $G("videoWidth").value = $(dialog.anchorEl).attr('width');
                $G("videoHeight").value = $(dialog.anchorEl).attr('height');
            }
        }
        createPreviewVideo(url);

        $('#J_UpFile').click(function () {
            parent.window.FileStorage({
                type: 'video',
                callback: function (url, data) {
                    $('#videoUrl').val(url);
                    createPreviewVideo(url);
                }
            });
        });
    }
    function addUrlChangeListener(url){
        if (browser.ie) {
            url.onpropertychange = function () {
                createPreviewVideo(this.value);
            }
        } else {
            url.addEventListener( "input", function () {
                createPreviewVideo(this.value);
            }, false );
        }
    }
    function addOkListener(){
        dialog.onok = function(){
            $G("preview").innerHTML = "";
            return insertSingle();
        };
        dialog.oncancel = function(){
            $G("preview").innerHTML = "";
            $G("videoUrl").value = "";
        };
    }
    function insertSingle(){
        var width = $G("videoWidth"),
            height = $G("videoHeight"),
            url=$G('videoUrl').value;
        if(!url) return false;
        if ( !checkNum( [width, height] ) ) return false;
        if(dialog.anchorEl){
            var html = getVideoHtml(url,width.value,height.value);
            $(dialog.anchorEl).parent().after(html).remove();
        }else{
            var html = getVideoHtml(url,width.value,height.value);
            if(html) {
                editor.execCommand('insertHtml',html.replace('<div class="previewMsg"><span>'+lang.urlError+'</span></div>',''));
            }else{
                return false;
            }
        }
    }
    function checkNum( nodes ) {
        for ( var i = 0, ci; ci = nodes[i++]; ) {
            var value = ci.value.toLowerCase().replace('%','').replace('px','');
            if ( !isNumber( value ) && value) {
                alert( lang.numError );
                ci.value = "";
                ci.focus();
                return false;
            }
        }
        return true;
    }
    function isNumber( value ) {
        return /(0|^[1-9]\d*$)/.test( value );
    }
    function convert_url(url){
        if ( !url ) return '';
        //新的： http://v.qq.com/txp/iframe/player.html?vid=$1
        //旧的：v.qq.com/iframe/preview.html?vid=$1&amp;auto=0
        url = utils.trim(url)
            .replace(/v\.youku\.com\/v_show\/id_([\w\-=]+)\.html/i, 'player.youku.com/player.php/sid/$1/v.swf')
            .replace(/(www\.)?youtube\.com\/watch\?v=([\w\-]+)/i, "www.youtube.com/v/$2")
            .replace(/youtu.be\/(\w+)$/i, "www.youtube.com/v/$1")
            .replace(/v\.ku6\.com\/.+\/([\w\.]+)\.html.*$/i, "player.ku6.com/refer/$1/v.swf")
            .replace(/www\.56\.com\/u\d+\/v_([\w\-]+)\.html/i, "player.56.com/v_$1.swf")
            .replace(/www.56.com\/w\d+\/play_album\-aid\-\d+_vid\-([^.]+)\.html/i, "player.56.com/v_$1.swf")
            .replace(/v\.pps\.tv\/play_([\w]+)\.html.*$/i, "player.pps.tv/player/sid/$1/v.swf")
            .replace(/www\.letv\.com\/ptv\/vplay\/([\d]+)\.html.*$/i, "i7.imgs.letv.com/player/swfPlayer.swf?id=$1&autoplay=0")
            .replace(/www\.tudou\.com\/programs\/view\/([\w\-]+)\/?/i, "www.tudou.com/v/$1")
            .replace(/.+?\.qq\.com\/.+?\.html?.+?vid=([\w]+)(&.+)?/i, "http://v.qq.com/iframe/preview.html?vid=$1&amp;auto=0")
            .replace(/v\.qq\.com\/.+\/([\w]+)\.html$/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/v\.qq\.com\/cover\/[\w]+\/[\w]+\.html?vid=([\w]+)/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/v\.qq\.com\/[\w]+\/[\w]+\/[\w]+\/[\w]+\/[\w]+\/([\w]+)\.html/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/v\.qq\.com\/[\w]+\/[\w]+\/[\w]+\/[\w]+\/([\w]+)\.html/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/v\.qq\.com\/cover\/[\w]+\/[\w]+\/[\w]+\/([\w]+)\.html/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/v\.qq\.com\/cover\/[\w]+\/[\w]+\/([\w]+)\.html/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/v\.qq\.com\/cover\/[\w]+\/([\w]+)\.html/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/v\.qq\.com\/.+[\?\&]vid=([^&]+).*$/i, "v.qq.com/txp/iframe/player.html?vid=$1")
            .replace(/my\.tv\.sohu\.com\/[\w]+\/[\d]+\/([\d]+)\.shtml.*$/i, "share.vrs.sohu.com/my/v.swf&id=$1");
        return url;
    }
    function getVideoHtml(url,width,height){
        var reg = /^(?:http(?:s|):\/\/|)/ig;
        if (!reg.test(url)) {
            alert("请输入合法的网址");
            return '';
        }
        if (!width) width = '100%';
        if (!height) height = '294px';
        width = width.replace('px', '');
        width = width + (width.indexOf('%') > 0 ? '' : 'px');

        var URL = editor.options.UEDITOR_HOME_URL;
        var id = 'videoIframe_' + (new Date()).valueOf();
        var src = [
            URL + (/\/$/.test(URL) ? '' : '/') + "dialogs/video/show.html#",
            'url=' + encodeURIComponent(url),
            '&id=' + id,
        ].join('');


        var html = [
            '<iframe id="' + id + '" class="video_iframe" allowfullscreen="" frameborder="0" scrolling="no" ',
            ' src="' + src + '" ',
            ' width="' + width + '"',
            ' height="' + height + '"',
            ' style="position:relative;z-index:1;max-width:100%!important;margin:auto;width:' + width + '!important;" ',
            '></iframe>',
        ].join('');

        return '<section class="_wxEditor" style="text-align:center;">' + html + '</section>';

        // var conUrl = convert_url(url);
        // if(conUrl.indexOf('qq.com') !== -1 ) {
        //     return '<section class="_wxEditor" style="text-align:center;"><iframe class="video_iframe" style="position:relative;z-index:1" width="' + width + '" height="' + height + '" scrolling="no" src="'+conUrl+'" allowfullscreen="" frameborder="0"></iframe></section>'
        // }else{
        //     return '<div class="previewMsg"><span>'+lang.urlError+'</span></div>'+
        //         '<section class="_wxEditor" style="text-align:center;"><embed class="video_swf" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"' +
        //         ' src="' + conUrl + '"' +
        //         ' width="' + width + '"' +
        //         ' height="' + height + '"' +
        //         ' wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true" >' +
        //         '</embed></section>';
        // }
    }
    function createPreviewVideo(url){
        if ( !url )return;
        $G("preview").innerHTML = getVideoHtml(url);
    }

})();