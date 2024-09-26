/**
 * 人人站CMS
 * ============================================================================
 * 版权所有 2015-2030 山东康程信息科技有限公司，并保留所有权利。
 * 网站地址: http://www.rrzcms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */
(function ($) {
    //处理节点颜色工具
    var tools = (function () {
        var r = ["color", "backgroundColor", "borderColor", "borderLeftColor", "borderTopColor", "borderRightColor", "borderBottomColor"]
            , o = ["inherit", "transparent", "initial"]
            , i = {
            getNodePosition: function (e) {
                for (var t = e.offsetTop, n = e.offsetLeft, r = e.offsetParent; null !== r;)
                    t += r.offsetTop,
                        n += r.offsetLeft,
                        r = r.offsetParent;
                var o = 0
                    , i = 0;
                return "BackCompat" == document.compatMode ? (o = document.body.scrollTop,
                    i = document.body.scrollLeft) : o = document.documentElement.scrollTop,
                    {
                        top: t - o,
                        left: n - i,
                        width: e.offsetWidth,
                        height: e.offsetHeight
                    }
            },
            convertNode: function (e) {
                if ("string" == typeof e) {
                    var t = document.createElement("div");
                    t.innerHTML = e,
                        e = t.childNodes[0]
                }
                return e
            },
            getNodeColor: function (e) {
                e = i.convertNode(e);
                for (var t = e.querySelectorAll("*"), n = [], a = {}, s = function (e) {
                    var i = t[e];
                    i.style && r.forEach(function (e) {
                        i.style[e] && -1 === i.style[e].indexOf("transparent") && !o.includes(i.style[e]) && (n.includes(i.style[e]) || n.push(i.style[e]),
                            a[i.style[e]] ? a[i.style[e]].push({
                                type: e,
                                node: i
                            }) : a[i.style[e]] = [{
                                type: e,
                                node: i
                            }])
                    })
                }, u = 0; u < t.length; u++)
                    s(u);
                return {
                    colors: n,
                    colorNodeList: a
                }
            },
            setNodeColor: function (e, t, n, r) {
                if (e = i.convertNode(e),
                !n || !r) {
                    var o = i.getNodeColor(e);
                    r || (r = o.colorNodeList),
                    n || (n = o.colors)
                }
                return "string" == typeof n && (n = [n]),
                    n.forEach(function (e) {
                        r[e].forEach(function (e) {
                            e.node.style[e.type] = t
                        })
                    }),
                    e ? e.outerHTML : null
            }
        };
        return i;
    })();
    var UE = window.baidu.editor,
        UI = window.baidu.editor.ui,
        popup = window.baidu.editor.ui.Popup,
        combox = window.baidu.editor.ui.Combox,
        utils = window.baidu.editor.utils,
        domUtils = window.baidu.editor.dom.domUtils,
        stateful = window.baidu.editor.ui.Stateful,
        uiUtils = window.baidu.editor.ui.uiUtils,
        uiBase = window.baidu.editor.ui.UIBase;

    var wxeditor = {
        init: function (me) {
            this.setModes(me);//新增工具栏点击按钮
            this.setOther(me);//新增工具栏下拉按钮
            me.addListener("click", function (t, evt) {

            });
        },
        setOther: function (me) {
            var that = this;
            var models = {
                'ex-indent': {
                    title: "缩进",
                    initValue: "缩进",
                    registerUI: function (e, t) {
                        var n = this, r = this;
                        r.commands.margin = {
                            execCommand: function (e, t) {
                                t = r.queryCommandState("margin") ? "0em" : r.options.indentValue || t,
                                    r.execCommand("Paragraph", "p", {
                                        style: "margin-left:" + t + ";margin-right:" + t
                                    });
                            },
                            queryCommandState: function () {
                                var e = domUtils.filterNodeList(n.selection.getStartElementPath(), "p h1 h2 h3 h4 h5 h6");
                                return e && e.style.margin && parseInt(e.style.margin, 10) ? 1 : 0;
                            }
                        };
                        e.registerCommand(t, {
                            execCommand: function (e, t) {
                                n.execCommand("margin", t + "em"), n.execCommand("justify", "justify");
                            },
                            queryCommandValue: function () {
                                return n.queryCommandValue("margin");
                            }
                        });
                    }
                },
                'letter-spacing': {
                    ciList: [.1, .5, 1, 1.5, 2, 3],
                    title: "字距",
                    initValue: "字距",
                    registerUI: function (e, t) {
                        var n = this, r = this;
                        r.commands.letter = {
                            execCommand: function (e, t) {
                                t = r.queryCommandState("letter-spacing") ? "0" : r.options.indentValue || t,
                                    r.execCommand("Paragraph", "p", {
                                        style: "letter-spacing:" + t
                                    });
                            },
                            queryCommandState: function () {
                                var e = domUtils.filterNodeList(n.selection.getStartElementPath(), "p h1 h2 h3 h4 h5 h6");
                                return e && e.style.margin && parseInt(e.style.margin, 10) ? 1 : 0;
                            }
                        };
                        e.registerCommand(t, {
                            execCommand: function (e, t) {
                                n.execCommand("letter", t + "px");
                            },
                            queryCommandValue: function () {
                                return n.queryCommandValue("letter");
                            }
                        });
                    }
                },
            };
            $.each(models, function (key, item) {
                var registerUI = item['registerUI'];
                registerUI , delete item['registerUI'];
                UI[key] = function (t) {
                    var btn = that.getCombox(key, t, item);
                    UI.buttons[key] = btn;
                    t.addListener('selectionchange', function (type, causeByUi, uiReady) {
                        if (!uiReady) {
                            var state = t.queryCommandState(key);
                            if (state == -1) {
                                btn.setDisabled(true);
                            } else {
                                btn.setDisabled(false);
                            }
                        }

                    });
                    return btn;
                };
                registerUI && UE.registerUI(key, registerUI);
            });
        },
        setModes: function (me) {
            var mdls = this.getModules(me);
            $.each(mdls, function (key, item) {
                UI[key] = function (r) {
                    var btn = new UI.Button(utils.extend({
                        className: "edui-for-" + key,
                        theme: r.options.theme,
                        editor: r,
                        onclick: function () {
                            this.editor.fireEvent(item.event);
                        }
                    }, item));
                    UI.buttons[key] = btn;
                    r.addListener('selectionchange', function (type, causeByUi, uiReady) {
                        if (!uiReady) {
                            var state = r.queryCommandState(key);
                            if (state == -1) {
                                btn.setDisabled(true);
                            } else {
                                btn.setDisabled(false);
                            }
                        }
                    });
                    return btn;
                };
                me.addListener(item.event, item.callback);
            });
        },
        getCombox: function (name, me, options) {
            for (var item, list = options.ciList || [.1, .5, 1, 2, 3, 4], items = [], i = 0; item = list[i++];) {
                "0.1" == item && (item = "0"),
                    items.push({
                        label: " " + item,
                        value: item,
                        renderLabelHtml: function () {
                            return '<div class="edui-label %%-label" style="text-align:center;\n                    line-height:2;font-size:12px;">' + (this.label || "") + "</div>"
                        }
                    });
            }
            return new combox({
                editor: me,
                className: "tool-" + name,
                theme: me.options.theme,
                items: items,
                onselect: function (n, r) {
                    me.execCommand(name, this.items[r].value);
                },
                title: options.title || "",
                initValue: options.initValue || ""
            });
        },
        getModules: function (me) {
            var that = this;
            var mdls = {
                "insert-image": {
                    title: "插入图片",
                    event: "extendImageSelect",
                    callback: function (e, evt) {
                        evt = evt || window.event;
                        var el = evt.target || evt.srcElement;
                        var me = this;
                        var simpleupload = $('#' + me.key).data('simpleupload');
                        if (simpleupload && $.isFunction(simpleupload)) {
                            simpleupload.call(me);
                            return
                        }
                        var format = evt ? evt.format || null : null;
                        top.window.ImgSpace && top.window.ImgSpace({
                            elem: el,
                            callback: function (url, list) {
                                //替换图片
                                if (format){
                                    format = format.replace(/([\s|_])src=\"([^\"]*)\"/ig, '$1src="' + url + '"');
                                    me.execCommand('inserthtml',format);
                                    return;
                                }
                                //新加图片
                                list = list ? list : [];
                                for (var i = 0, url = ''; url = list[i++];) {
                                    me.execCommand('inserthtml', '<p><img src="' + url + '" ></p>');
                                }
                            },
                            multiple: format ? false : true,
                        });
                    },
                },
                "attachment": {
                    title: "附件",
                    event: "extendFileSelect",
                    callback: function (e, evt) {
                        evt = evt || window.event;
                        var el = evt.target || evt.srcElement;
                        var me = this;
                        var format = evt ? evt.format || null : null;
                        top.window.FileStorage && top.window.FileStorage({
                            elem: el,
                            callback: function (url, list) {
                                var filelist = [];
                                for (var i = 0; i < list.length; i++) {
                                    var url = list[i];
                                    var title = url.substr(url.lastIndexOf('/') + 1);
                                    filelist.push({ title: title, url: url });
                                }
                                me.execCommand('insertfile', filelist);
                            },
                            isAll: true,
                            type: 'images',
                            multiple: true,
                        });
                    }
                }
            };
            return mdls;
        }
    };


    UE.plugins["wxeditor-extend"] = function () {
        wxeditor.init(this);
    };


    $.getEditor = function (id, config) {
        var initConfig = {
            toolbars: [
                [
                    "source","undo", "redo", "|","fontfamily", "fontsize","paragraph", "bold", "italic", "blockquote", "horizontal", "|", "removeformat", "formatmatch", "link",
                    "|", "show-image-select", "insertvideo", "music", "insert-image",

                    "forecolor", "backcolor", "|", "indent", "ex-indent", "letter-spacing", "justifyleft", "justifycenter", "justifyright", "justifyjustify", "|",
                    "rowspacingtop", "rowspacingbottom", "lineheight", "insertorderedlist", "insertunorderedlist",

                    "underline", "fontborder", "strikethrough", "emotion", "spechars", "inserttable", "|", "searchreplace", "autotypeset", "pasteplain",
                    // "map",
                    "attachment",
                    "fullscreen",
                ],
            ],
            zIndex: 1000,
            charset: "utf-8",
            fontfamily: [
                { label: "宋体", name: "songti", val: "宋体,SimSun" },
                { label: "新宋体", name: "xinsongti", val: '新宋体,NSimSun' },
                { label: "仿宋", name: "fangsong", val: '仿宋,仿宋_GB2312, SimFang' },
                { label: "楷体", name: "kaiti", val: '楷体,楷体_GB2312, SimKai' },
                { label: "微软雅黑", name: "yahei", val: '微软雅黑,Microsoft YaHei' },
                { label: "黑体", name: "heiti", val: "黑体, SimHei" },
                { label: "隶书", name: "lishu", val: "隶书, SimLi" },
                { label: "萝莉体", name: "luoliti", val: "萝莉体 第二版,Heiti SC" },
                { label: "andaleMono", name: "andaleMono", val: "andale mono" },
                { label: "arial", name: "arial", val: "arial, helvetica,sans-serif" },
                { label: "arialBlack", name: "arialBlack", val: "arial black,avant garde" },
                { label: "comicSansMs", name: "comicSansMs", val: "comic sans ms" },
                { label: "impact", name: "impact", val: "impact,chicago" },
                { label: "timesNewRoman", name: "timesNewRoman", val: "times new roman" }
            ],
            shortcutMenu: ["fontfamily", "fontsize", "bold", "italic", "underline", "fontborder", "strikethrough", "forecolor", "backcolor", "insertorderedlist", "insertunorderedlist", "superscript", "subscript", "justifyleft", "justifycenter", "justifyright", "justifyjustify", "rowspacingtop", "rowspacingbottom", "lineheight"],
            fontsize: [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 22, 24, 26, 28, 30, 32, 36, 48, 60, 72],
            enableAutoSave: false,//禁止自动保存
            autoClearinitialContent: false,
            autoFloatEnabled: false, // 是否保持 toolbar 滚动时不动
            autoHeightEnabled: false,//是否自动长高
            focus: false,
            wordCount: true,
            elementPathEnabled: false,
            initialFrameWidth: '100%', // 初始化编辑器宽度
            initialFrameHeight: 200,//初始化编辑器高度
            maximumWords: 1000000,//最大字数
            imageScaleEnabled: false,//图片调整大小
            disabledTableInTable: false,//禁止表格嵌套
            imagePopup: true,//图片悬浮操作显示
            pasteplain: false, // 是否默认为纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴
            allowDivTransToP: false,//禁止讲div 转换成p标签
            codeMirrorJsUrl: false,
            codeMirrorCssUrl: false,
        };
        var conf = $('#' + id).data('config') || {};
        $.isPlainObject(conf) || (conf = eval("(" + conf + ")"));
        var wxEditor = new UE.ui.Editor($.extend({}, initConfig, conf || {}, config || {}));
        wxEditor.render(id);
        $('#' + id).data('wxEditor', wxEditor);
        return wxEditor;
    }

})(window.jQuery);