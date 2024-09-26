var tab = 3,audio_eidt = 0;
layui.element.on('tab(music)', function(data){
    tab = data.index;
});
function Music() {
    this.init();
}
function Kugou() {
    this.init();  // LGD 08-05 屏蔽 暂不需要使用酷狗
}
function createPreviewAudio(url,play){
    var reg = /^(?:http(?:s|):\/\/|)/ig;
    if ( !url ) {
        $G("A_preview").innerHTML = '';
        return;
    }else if( !reg.test(url) ) {
        $G("A_preview").innerHTML = '';
        return;
    }
    $G("A_preview").innerHTML = '<audio style="width:100%;margin-bottom:15px"' + (play ? ' autoplay=""' : '') + ' controls="" loop="" src="' + url + '"></audio>';
}

(function () {
    addUrlChangeListener($G("J_AudioUrl"));
    function addUrlChangeListener(url){
        if (browser.ie) {
            url.onpropertychange = function () {
                createPreviewAudio( this.value , true);
            }
        } else {
            url.addEventListener( "input", function () {
                createPreviewAudio( this.value , true);
            }, false );
        }
    }
    var pages = [],
        panels = [],
        page_s = [],
        panel_s = [],
        selectedItem = null;
    Music.prototype = {
        total:70,
        pageSize:7,
        page: 1,
        total: 0,
        dataUrl: 'https://c.y.qq.com/soso/fcgi-bin/client_search_cp?aggr=1&cr=1&flag_qc=0',
        playerUrl:"http://ws.stream.qqmusic.qq.com/",
        init:function () {
            var me = this;
            domUtils.on($G("J_searchName"), "keyup", function (event) {
                var e = window.event || event;
                if (e.keyCode == 13) {
                    me.page = 1;
                    me.dosearch();
                }
            });
            domUtils.on($G("J_searchBtn"), "click", function () {
                me.page = 1;
                me.dosearch();
            });

        },
        callback:function (data) {
            var me = this;
            me.data = data.data.song.list;
            me.total = data.data.song.totalnum;
            setTimeout(function () {
                $G('J_resultBar').innerHTML = me._renderTemplate(data.data.song.list);
            }, 300);
        },
        dosearch:function () {
            $('.m-trying').trigger('click');
            var me = this;
            selectedItem = null;
            var key = $G('J_searchName').value;
            if (utils.trim(key) == "")return false;
            key = encodeURIComponent(key);
            me._sent(key);
        },
        doselect:function (i) {

            var me = this;
            if (typeof i == 'object') {
                selectedItem = i;
            } else if (typeof i == 'number') {
                selectedItem = me.data[i];
            }
        },
        onpageclick:function (id) {
            var me = this;
            me.page  = id;
            $('.m-trying').trigger('click');
            me.dosearch();
        },
        /*onpageclick:function (id) {
            var me = this;
            for (var i = 0; i < pages.length; i++) {
                $G(pages[i]).className = 'pageoff';
                $G(panels[i]).className = 'paneloff';
            }
            $G('page' + id).className = 'pageon';
            $G('panel' + id).className = 'panelon';
        },*/
        listenTest:function (elem) {
            var me = this,
                view = $G('J_preview'),
                is_play_action = (elem.className == 'm-try' ? 1 : 0),
                old_trying = me._getTryingElem();
            if (old_trying) {
                old_trying.className = 'm-try';
                view.innerHTML = '';
            }
            if (is_play_action) {
                elem.className = 'm-trying';
                var html = '';
                $.ajax({
                    url:  'https://c.y.qq.com/base/fcgi-bin/fcg_music_express_mobile3.fcg?' +
                        'format=json205361747&platform=yqq&cid=205361747&' +
                        'callback=callback&songmid='+selectedItem.songmid+'&filename=C400'+selectedItem.songmid+'.m4a&guid=126548448',
                    dataType: "jsonp",
                    type: "get",
                    async: false,
                    success: function(a) {
                        //console.log(a);
                        if (a.data.items[0].subcode == 104003) {
                            layer.msg('收费歌曲,请复制或同步到公众号播放');
                        } else {
                            var url = 'http://ws.stream.qqmusic.qq.com/C400'+selectedItem.songmid+'.m4a?fromtag=0&guid=126548448&vkey='+ a.data.items[0].vkey;
                            html = '<embed class="BDE_try_Music" allowfullscreen="false"';
                            html += ' src="' + url + '"';
                            html += ' width="1" height="1" style="position:absolute;left:-2000px;"';
                            html += ' wmode="transparent" play="true" loop="false"';
                            html += ' menu="false" allowscriptaccess="never" scale="noborder">';
                            view.innerHTML = html;
                        }
                    },
                    error: function() {

                    }
                })
            }
        },
        _sent:function (param) {
            var me = this;
            $G('J_resultBar').innerHTML = '<div class="loading"></div>';
            $.ajax({
                url: 'https://c.y.qq.com/soso/fcgi-bin/client_search_cp?aggr=1&cr=1&flag_qc=0&w='+ param +'&p='+ me.page +'&n='+me.pageSize+'&jsonpCallback=success_jsonpCallback',
                dataType: "jsonp",
                jsonp: "callback",
                jsonpCallback: "success_jsonpCallback",
                type: "get",
                success: function(a) {
                    me.callback(a)
                }
            })
        },
        _getTryingElem:function () {
            var s = $G('J_listPanel').getElementsByTagName('span');
            for (var i = 0; i < s.length; i++) {
                if (s[i].className == 'm-trying')
                    return s[i];
            }
            return null;
        },
        // LGD 08-05 暂没用到
        _buildMusicHtml:function () {
            var html = '';
            var guid = Math.round(2147483647 * Math.random()) * time % 1e10;
            $.ajax({
                url:  'https://c.y.qq.com/base/fcgi-bin/fcg_music_express_mobile3.fcg?' +
                    'format='+selectedItem.format+'&platform=yqq&cid=205361747&' +
                    'callback=callback&songmid='+selectedItem.songmid+'&filename=C400'+selectedItem.songmid+'.m4a&guid='+selectedItem.songmid,
                dataType: "jsonp",
                type: "get",
                async: false,
                success: function(a) {
                    var url = 'http://dl.stream.qqmusic.qq.com/C400'+selectedItem.songmid+'.m4a?fromtag=50&guid='+selectedItem.songmid+'&&uin=0&vkey='+ a.data.items[0].vkey;
                    html = '<embed class="BDE_try_Music" allowfullscreen="false"';
                    html += ' src="' + url + '"';
                    html += ' width="1" height="1" style="position:absolute;left:-2000px;"';
                    html += ' wmode="transparent" play="true" loop="false"';
                    html += ' menu="false" allowscriptaccess="never" scale="noborder">';
                    view.innerHTML = html;
                }
            })
        },
        _byteLength:function (str) {
            return str.replace(/[^\u0000-\u007f]/g, "\u0061\u0061").length;
        },
        _getMaxText:function (s) {
            var me = this;
            if (me._byteLength(s) > 100)
                return s.substring(0, 100) + '…';
            if (!s) s = "&nbsp;";
            return s;
        },
        _rebuildData:function (data) {
            var me = this,
                newData = [],
                d = me.pageSize,
                itembox;
            for (var i = 0; i < data.length; i++) {
                if ((i + d) % d == 0) {
                    itembox = [];
                    newData.push(itembox)
                }
                itembox.push(data[i]);
            }
            return newData;
        },
        _renderTemplate:function (data) {
            var me = this;
            if (data.length == 0)return '<div class="empty">' + lang.emptyTxt + '</div>';
            // LGD 截取一下数组吧，不知道为什么 pagesize 有时不行
            data = data.slice(0,7);
            data = me._rebuildData(data);
            var s = [], p = [], t = [];
            s.push('<div id="J_listPanel" class="listPanel">');
            p.push('<div class="page">');

            // LGD 重新分頁
            var total_page =  Math.ceil( me.total/me.pageSize);
            if (total_page >10 ) total_page = 10;
            for(var i = 1;i<= total_page;i++) {
                panels.push('panel' + i);
                pages.push('page' + i);
                if (i == me.page) {
                    t.push('<div id="page' + i + '" onclick="music.onpageclick(' + i + ')" class="pageon">' + (i ) + '</div>');
                } else {
                    t.push('<div id="page' + i + '" onclick="music.onpageclick(' + i + ')" class="pageoff">' + (i ) + '</div>');
                }
            }

            for (var i = 0, tmpList; tmpList = data[i++];) {
                s.push('<div class="m-box">');
                s.push('<div class="m-h"><span class="m-t">' + lang.chapter + '</span><span class="m-s">' + lang.singer
                    + '</span><span class="m-z">' + lang.special + '</span><span class="m-try-t">' + lang.listenTest + '</span></div>');

                for (var j = 0, tmpObj; tmpObj = tmpList[j++];) {
                    s.push('<label for="radio-' + i + '-' + j + '" class="m-m">');
                    s.push('<input type="radio" id="radio-' + i + '-' + j + '" name="musicId" class="m-l" onclick="music.doselect(' + (me.pageSize * (i-1) + (j-1)) + ')"/>');  // 这个需要处理
                    s.push('<span class="m-t">' + me._getMaxText(tmpObj.songname) + '</span>');
                    s.push('<span class="m-s">' + me._getMaxText(tmpObj.singer[0]['name']) + '</span>'); // todo
                    s.push('<span class="m-z">' + me._getMaxText(tmpObj.albumname) + '&nbsp;</span>');
                    s.push('<span class="m-try" onclick="music.doselect(' + (me.pageSize * (i-1) + (j-1)) + ');music.listenTest(this)"></span>');
                    s.push('</label>');
                }
                s.push('</div>');
                s.push('</div>');
            }
            t.reverse();
            p.push(t.join(''));
            s.push('</div>');
            p.push('</div>');
            return s.join('') + p.join('');
        },
        exec:function () {
            var me = this,qqmusic = {error:1};
            $G('J_preview').innerHTML = "";
            if (selectedItem == null){
                layui.layer.msg('请选择音乐',{icon:5,anim:6});
            }else{
                var imgroot2 = "https://y.gtimg.cn/music/photo_new/T002R68x68M000#mid#.jpg";
                var album_url = imgroot2.replace("#mid#",selectedItem.albummid);
                qqmusic = {
                    error:0,
                    musicid: selectedItem.songid,
                    mid:selectedItem.songmid,
                    albumurl: selectedItem.albumurl,
                    audiourl: selectedItem.m4a,
                    music_name: selectedItem.songname,
                    singer: selectedItem.singer[0].name,
                    //play_length: selectedItem.f.split("|")[7],
                    src: '/cgi-bin/readtemplate?t=tmpl/qqmusic_tmpl&singer=' + encodeURIComponent(selectedItem.singer[0].name) + '&music_name='+encodeURIComponent(selectedItem.songname)+(album_url?'&albumurl='+encodeURIComponent(album_url):'')+'&musictype=1',
                    musictype: 1,
                    //	otherid: selectedItem.f.split("|")[22],
                    albumid: selectedItem.strMediaMid,
                    otherid: selectedItem.albummid,
                    jumpurlkey: '',
                }
            }
            return qqmusic;
        }
    };
    Kugou.prototype = {
        total:70,
        pageSize:7,
        dataUrl:"http://songsearch.kugou.com/song_search_v2?",
        playerUrl:"http://www.kugou.com/yy/index.php?",
        init:function () {
            var me = this;
            domUtils.on($G("K_searchName"), "keyup", function (event) {
                var e = window.event || event;
                if (e.keyCode == 13) {
                    me.dosearch();
                }
            });
            domUtils.on($G("K_searchBtn"), "click", function () {
                me.dosearch();
            });

        },
        callback:function (data) {
            var me = this;
            me.data = data.data.lists;
            setTimeout(function () {
                $G('K_resultBar').innerHTML = me._renderTemplate(data.data.lists);
            }, 300);
        },
        dosearch:function () {
            var me = this;
            selectedItem = null;
            var key = $G('K_searchName').value;
            if (utils.trim(key) == "")return false;
            key = encodeURIComponent(key);
            me._sent(key);
        },
        doselect:function (i,play) {
            var me = this;
            if (typeof i == 'object') {
                selectedItem = i;
            } else if (typeof i == 'number') {
                selectedItem = me.data[i];
                $.ajax({
                    url: "http://m.kugou.com/app/i/getSongInfo.php?cmd=playInfo&hash=" + selectedItem.FileHash,
                    dataType: "jsonp",
                    jsonp: "callback",
                    jsonpCallback: "success_jsonpCallback",
                    type: "get",
                    success: function(a) {
                        selectedItem.albumurl = a.data.imgUrl;
                        selectedItem.play_url = a.data.url;
                    },
                    error: function() {
                        layui.layer.msg('地址获取失败',{icon:5,anim:6});
                        selectedItem = null;
                    }
                })
            }
        },
        onpageclick:function (id) {
            var me = this;
            for (var i = 0; i < page_s.length; i++) {
                $G(page_s[i]).className = 'pageoff';
                $G(panel_s[i]).className = 'paneloff';
            }
            $G('page_' + id).className = 'pageon';
            $G('panel_' + id).className = 'panelon';
        },
        listenTest:function (elem,FileHash) {
            var me = this,
                view = $G('K_preview'),
                is_play_action = (elem.className == 'm-try' ? 1 : 0),
                old_trying = me._getTryingElem();
                console.log(old_trying);
            if (old_trying) {
                old_trying.className = 'm-try';
                view.innerHTML = '';
            }
            if (is_play_action) {
                elem.className = 'm-trying';
                me._buildMusicHtml(view,FileHash);
            }
        },
        _sent:function (param) {
            var me = this;
            $G('K_resultBar').innerHTML = '<div class="loading"></div>';
            $.ajax({
                url: me.dataUrl + "keyword=" + param + "&platform=WebFilter&pagesize=" + me.total + "&page=1",
                dataType: "jsonp",
                jsonp: "callback",
                jsonpCallback: "success_jsonpCallback",
                type: "get",
                success: function(a) {
                    me.callback(a)
                }
            })
        },
        _getTryingElem:function () {
            var s = $G('K_listPanel').getElementsByTagName('span');
            for (var i = 0; i < s.length; i++) {
                if (s[i].className == 'm-trying')
                    return s[i];
            }
            return null;
        },
        _buildMusicHtml:function (view,FileHash) {
            var me = this;
            $.ajax({
                url: "http://m.kugou.com/app/i/getSongInfo.php?cmd=playInfo&hash=" + FileHash,
                dataType: "jsonp",
                jsonp: "callback",
                jsonpCallback: "success_jsonp_Callback",
                type: "get",
                success: function(a) {
                    var html = '<embed class="BDE_try_Music" allowfullscreen="false"';
                    html += ' src="' + a.data.url + '"';
                    html += ' width="1" height="1" style="position:absolute;left:-2000px;"';
                    html += ' wmode="transparent" play="true" loop="false"';
                    html += ' menu="false" allowscriptaccess="never" scale="noborder">';
                    view.innerHTML = html;
                }
            })
        },
        _byteLength:function (str) {
            return str.replace(/[^\u0000-\u007f]/g, "\u0061\u0061").length;
        },
        _getMaxText:function (s) {
            var me = this;
            if (me._byteLength(s) > 100)
                return s.substring(0, 100) + '…';
            if (!s) s = "&nbsp;";
            return s;
        },
        _rebuildData:function (data) {
            var me = this,
                newData = [],
                d = me.pageSize,
                itembox;
            for (var i = 0; i < data.length; i++) {
                if ((i + d) % d == 0) {
                    itembox = [];
                    newData.push(itembox)
                }
                itembox.push(data[i]);
            }
            return newData;
        },
        _renderTemplate:function (data) {
            var me = this;
            if (data.length == 0)return '<div class="empty">' + lang.emptyTxt + '</div>';
            data = me._rebuildData(data);
            var s = [], p = [], t = [];
            s.push('<div id="K_listPanel" class="listPanel">');
            p.push('<div class="page">');
            for (var i = 0, tmpList; tmpList = data[i++];) {
                panel_s.push('panel_' + i);
                page_s.push('page_' + i);
                if (i == 1) {
                    s.push('<div id="panel_' + i + '" class="panelon">');
                    if (data.length != 1) {
                        t.push('<div id="page_' + i + '" onclick="kugou.onpageclick(' + i + ')" class="pageon">' + (i ) + '</div>');
                    }
                } else {
                    s.push('<div id="panel_' + i + '" class="paneloff">');
                    t.push('<div id="page_' + i + '" onclick="kugou.onpageclick(' + i + ')" class="pageoff">' + (i ) + '</div>');
                }
                s.push('<div class="m-box">');
                s.push('<div class="m-h"><span class="m-t">' + lang.chapter + '</span><span class="m-s">' + lang.singer
                    + '</span><span class="m-z">' + lang.special + '</span><span class="m-try-t">' + lang.listenTest + '</span></div>');
                for (var j = 0, tmpObj; tmpObj = tmpList[j++];) {
                    s.push('<label for="radio_' + i + '-' + j + '" class="m-m">');
                    s.push('<input type="radio" id="radio_' + i + '-' + j + '" name="musicId" class="m-l" onclick="kugou.doselect(' + (me.pageSize * (i-1) + (j-1)) + ')"/>');
                    s.push('<span class="m-t">' + me._getMaxText(tmpObj.SongName) + '</span>');
                    s.push('<span class="m-s">' + me._getMaxText(tmpObj.SingerName) + '</span>');
                    s.push('<span class="m-z">' + me._getMaxText(tmpObj.AlbumName) + '</span>');
                    s.push('<span class="m-try" onclick="kugou.listenTest(this,\'' + tmpObj.FileHash + '\')"></span>');
                    s.push('</label>');
                }
                s.push('</div>');
                s.push('</div>');
            }
            t.reverse();
            p.push(t.join(''));
            s.push('</div>');
            p.push('</div>');
            return s.join('') + p.join('');
        },
        exec:function () {
            var me = this,kugoumusic = {error:1}
            $G('K_preview').innerHTML = "";
            if (selectedItem == null){
                layui.layer.msg('请选择音乐',{icon:5,anim:6});
            }else{
                kugoumusic = {
                    error:0,
                    albumurl: selectedItem.albumurl,
                    album_url: encodeURIComponent(selectedItem.albumurl),
                    audiourl: selectedItem.play_url,
                    music_name: selectedItem.SongName,
                    singer: selectedItem.SingerName,
                }
            }
            return kugoumusic;
        }
    };

    //编辑音乐
    if (dialog.anchorEl && $(dialog.anchorEl).is('iframe.iframe_audio')){
        audio_eidt = 1;
        layui.element.tabChange('music', 'audio');

        function getParam(url, name) {
            return url.match(new RegExp('[?#&]' + name + '=([^?#&]+)', 'i')) ? RegExp.$1 : '';
        }

        var $ele = $(dialog.anchorEl);
        var url = $ele.attr('src'),
            data = {
                width: $ele.attr('width'),
                url: decodeURIComponent(getParam(url, 'src')),
                title: decodeURIComponent(getParam(url, 'title')),
                disc: decodeURIComponent(getParam(url, 'disc')),
                auto: getParam(url, 'auto'),
                loop: getParam(url, 'loop'),
            };
        $('#J_AudioUrl').val(data.url);
        $("#J_AudioTitle").val(data.title);
        $("#J_AudioDesc").val(data.disc);
        $("#J_AudioW").val(data.width);
        //创建试听
        createPreviewAudio(data.url, false);

        if (data.auto > 0) {
            $('#autoplay').attr('checked', true);
        } else {
            $('#autoplay').removeAttr('checked');
        }

        if (data.loop > 0) {
            $('#loop').attr('checked', true);
        } else {
            $('#loop').removeAttr('checked');
        }

        layui.form.render('checkbox');
    }
})();
var music = new Music;
var kugou = new Kugou;
dialog.onok = function () {
    if(tab == 0){
        var qqmusic = music.exec();
        if(qqmusic.error == 1) return false;
        editor.focus();
        var html = '<iframe class="res_iframe qqmusic_iframe js_editor_qqmusic" style="width:100%;height:66px;" scrolling="no" frameborder="0" musicid="'+qqmusic['musicid']+'" mid="'+qqmusic['mid']+'" albumurl="'+qqmusic['albumurl']+'" audiourl="'+qqmusic['audiourl']+'" music_name="'+qqmusic['music_name']+'" commentid="" singer="'+qqmusic['singer']+'" play_length="'+qqmusic['play_length']+'" src="'+qqmusic['src']+'" musictype="'+qqmusic['musictype']+'" otherid="'+qqmusic['otherid']+'" albumid="'+qqmusic['albumid']+'" jumpurlkey="'+qqmusic['jumpurlkey']+'"></iframe>';
        if(audio_eidt == 0){
            editor.execCommand('inserthtml','<section class="_wxEditor" style="text-align:center;">' + html + '</section>');
        }else{
            $(dialog.anchorEl).parent().html(html);
        }
    }else if(tab == 1){
        var kugoumusic = kugou.exec();
        if(kugoumusic.error == 1) return false;
        editor.focus();
        var html = '<iframe class="res_iframe qqmusic_iframe js_editor_qqmusic" style="width:100%;height:66px;" scrolling="no" frameborder="0" musicid="" mid="" albumurl="'+kugoumusic['albumurl']+'" audiourl="'+kugoumusic['audiourl']+'" music_name="'+kugoumusic['music_name']+'" singer="'+kugoumusic['singer']+'" play_length="" src="https://mp.weixin.qq.com/cgi-bin/readtemplate?t=tmpl/qqmusic_tmpl&singer='+kugoumusic['singer']+'&music_name='+kugoumusic['music_name']+'&albumurl='+kugoumusic['album_url']+'&musictype=2" musictype="2" otherid="" albumid="" jumpurlkey=""></iframe>';
        if(audio_eidt == 0){
            editor.execCommand('inserthtml','<section class="_wxEditor" style="text-align:center;">' + html + '</section>');
        }else{
            $(dialog.anchorEl).parent().html(html);
        }
    }else{
        var url = $G("J_AudioUrl").value,reg = /^(?:http(?:s|):\/\/|)/ig;
        if ( !url ) {
            layui.layer.tips('请输入音乐地址',"#J_AudioUrl",{tips:[1,'#FF5722']});
            return false;
        }else if( !reg.test(url) ) {
            layui.layer.tips('音乐地址格式错误',"#J_AudioUrl",{tips:[1,'#FF5722']});
            return;
        }

        var data = {
            url: url,
            width: $('#J_AudioW').val().replace('px', '') || 400,
            title: $("#J_AudioTitle").val().trim(),
            desc: $("#J_AudioDesc").val().trim(),
            auto: $('#autoplay:checked').length,
            loop: $('#loop:checked').length,
        };
        var URL = editor.options.UEDITOR_HOME_URL;
        var url = [
            URL + (/\/$/.test(URL) ? '' : '/') + "dialogs/music/show.html",
            '#title=' + encodeURIComponent(data.title),
            '&src=' + encodeURIComponent(data.url),
            '&disc=' + encodeURIComponent(data.desc),
            '&auto=' + data.auto,
            '&loop=' + data.loop,
        ].join('');

        //var html = '<audio ' + ($('#autoplay:checked').length > 0 ? 'autoplay ' : '') + ($('#controls:checked').length > 0 ? 'controls ' : '') + ($('#loop:checked').length > 0 ? ' loop ' : '') + 'src="' + $G("J_AudioUrl").value + '"></audio>';

        var html = [
            '<iframe class="qqmusic_iframe iframe_audio" frameborder="0" ',
            ' src="' + url + '"',
            ' width="' + (data.width + (data.width.indexOf('%') > 0 ? '' : 'px')) + '"',
            ' style="width:' + (data.width + (data.width.indexOf('%') > 0 ? '' : 'px')) + '!important;height: 105px!important;margin:auto;display:block;max-width:100%!important;;"',
            '></iframe>',
        ].join('');

        audio_eidt = $.contains(editor.body, dialog.anchorEl);

        if(audio_eidt == 0){
            editor.execCommand('inserthtml','<section class="_wxEditor" style="text-align:center;">' + html + '</section>');
        }else{
            $(dialog.anchorEl).parent().html(html);
        }
    }
};
dialog.oncancel = function () {
    $G('J_preview').innerHTML = "";
    $G('K_preview').innerHTML = "";
    $G('A_preview').innerHTML = "";
};