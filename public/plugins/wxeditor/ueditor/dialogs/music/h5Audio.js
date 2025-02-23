window.h5Audio = (function () {

    if (typeof Object.assign !== 'function') {
        // Must be writable: true, enumerable: false, configurable: true
        Object.defineProperty(Object, "assign", {
            value: function assign(target, varArgs) { // .length of function is 2
                'use strict';
                if (target === null || target === undefined) {
                    throw new TypeError('Cannot convert undefined or null to object');
                }

                var to = Object(target);

                for (var index = 1; index < arguments.length; index++) {
                    var nextSource = arguments[index];

                    if (nextSource !== null && nextSource !== undefined) {
                        for (var nextKey in nextSource) {
                            // Avoid bugs when hasOwnProperty is shadowed
                            if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                                to[nextKey] = nextSource[nextKey];
                            }
                        }
                    }
                }
                return to;
            },
            writable: true,
            configurable: true
        });
    }

    var e = "data:image/gif;base64,R0lGODlhVABUAPfJAButGiKwIe747m7Kbe/47/r8+vj7+J3bnB+vHqDcny20LByuG+j16Pz9/HvPeiOwIk/ATuT05FLBUTa3Np7bnTm4OCqzKdXv1ff79ySwI8Lowi+1Lj66Pb3mvdvx23nPeaTepMjqyLXktVzEW63hrTW2NEu+So7WjdLu0j66PrzmvKrgqn7QfeL04p/cnkm+SCiyJ7/nvmTHYyGwIPn8+fX69d7y3vb69iWxJE2/TPL58iuzKqzgrHjOeEW8RPT69PH58ZXYlNDtz4bThSyzK+337eb15mLGYdbv1mnJaW/Lb8bqxja3NeX15V3FXPD48GHGYfP689fw11HAUHzQe3fOd0q+STi4N8rrytPu01/FXz25PGzKa17FXez37CCvH6ngqaDcoOn26ODz3x6vHZLXkiaxJef150y/S+Hz4NDt0E/AT77nvrTjtJfZlnrPeje3N0K7QWvKaoXThLvmu8fqxmXHZIPSg5bZlavgq8/tz9zx3JzbnI/WjtHu0Ue9RkS8Q93y3ZTYk6LdovH58FfDV2DGYInUiX3QfIfThmfIZtnw2Dq4OZDXkLrlulDAUIzVi43VjG/LbsPpw/n7+XTNc1TBUx6uHcDnv3DLcDG1MN/y31/FXsjqx2jJaFPBUi60LS60LljDWHXNdGbIZTS2M6/hrnfOdke9R9Tv1FbCVWPHYkC6P1rEWbDir0a8RVXCVMnryYvVi4jUiMHowVnDWMvry+v36zO2Mqjfpx2uHGfIZyeyJsDowLHisZHXkZPYk7nluXbNddnw2fv8+4fUhzC1LxqtGf39/QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1Nzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo4NzEyYzBkMi03NGJlLTQ5MTEtYmQyMi1lNmI4ZTlhZmQ5ZGIiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkUzMTAyRkEyMjg0MTFFN0JDNzBCMEY5NjNCMDhDQjQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkUzMTAyRjkyMjg0MTFFN0JDNzBCMEY5NjNCMDhDQjQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4NzEyYzBkMi03NGJlLTQ5MTEtYmQyMi1lNmI4ZTlhZmQ5ZGIiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6ODcxMmMwZDItNzRiZS00OTExLWJkMjItZTZiOGU5YWZkOWRiIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+Af/+/fz7+vn49/b19PPy8fDv7u3s6+rp6Ofm5eTj4uHg397d3Nva2djX1tXU09LR0M/OzczLysnIx8bFxMPCwcC/vr28u7q5uLe2tbSzsrGwr66trKuqqainpqWko6KhoJ+enZybmpmYl5aVlJOSkZCPjo2Mi4qJ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",
        o = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFQAAABUCAMAAAArteDzAAAAaVBMVEUAAAAarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRkarRlIa6J1AAAAInRSTlMA9wYa38QR7ZJnMK1IIqBsO3fXDbSGQudZz5fKpV0rfbpRlHIjYQAAA35JREFUWMPFWduyqjAMDS0tgtwEFBGv/P9Hntmh3cWDTYsMs/Oio3SRy0qapuCU7PXIRdUGQxCFncgfrwzWCb/l4TCTML/xbxFlIQariEJ+AZnkwUBKkCdLIZvBQ5olsPw61Uhc4vTOa4Ca39P4IqYWXH2dyw5mWXUs2ez/8liZVx6YD2bW6wXRzmpesov0U70HxW5azTBmpD1xqJW9uUzfaS0Lp1ms0Nru6Nfv9WPSi8lahT2BKoWyvARPKZUPhLRiduq9ckHaKds6y5pa6XmARXJQutaEP4MzLJTzyJfmk193I2YKiyUdUXcf+OnCdKPO+JqNvxO2kx4YNcr+c2jvjpE7Wv27W4uRS/C1jFEu3mpdhJyX34PWISY3ByNj/SxhhZRjfZ0UMkUJt3Bxx08rJU2xbFB16YEZDiG3JSy6sHlXNPbCHIbOVpHiN1VzjBLzKOCkmxjGKld6B4oNbjkiqi3rkJeBNN8jBj7SUEaxyGgnjE1OkS0mHkUAgd5X/qWF80mWR7PaOY0410GrnHHXVHpSqlZII521RzeXqtpkTkgEEitIiwF1YeLDJgQnIldbgAx5wMBj5z4br+aWB5GdGbxUxGjUp6ESLmxhJsaMFzx+Pi5+VIpN6bTUlcvPfw/InXlvjO5MjsdE/ucg6DjxRlEJY4Wb0J1IlnR0ZoXGEHF/6l1I68d+vj3ho9xH0mO+cjumNiMxvg/tTOWYcIAkqCl+XjRbtH7CHv4aCQrIQIui3TCxNPyN1BMXfhQFFxCgJ/yzmYAaTpGgEZpPoOq60GJctfkRaX5IBApRVTNTm/TvnYHqCEoh6kMzUCuNxnUUpVzkB/2+/Pc5iTpT5PdNUx78FrMT6kymqbugmEpxNZU4JXaph7v0GbOGxJQ3SZU+ryINSWT8iAt6skg7txPD1wCJN/rrQG0nZuNzo54nHQOnNj6zRTtRj5Pe5klu0d7NBGTThvFENhNE20NQS5BtD9GgUdQqyQZtaSuZ4bIr1fUGcmHTCz1SRpJNL9GeE3xNHe35/CDhRj04DhLzI48b9eI48mxxONvyGLn+wGtsLTY5mm87RFg/7jhNxh3bD2aANWtHSFsOu7Yfy60fIG4/6lw/lN14fOwedJdWXxKD7m1H8u7LAwZMZsn88mCDa46/v5DZ6OoIhcf7dg7Y7mPalb7XcVEwDEFU+V3H/QOplcP+ctPpgwAAAABJRU5ErkJggg==";

    var a = function (i) {
        var t = {
            ele: null,
            width: "320px",
            title: "这是一个测试title",
            src: "",
            disc: "这是一个测试disc",
            autoplay: false,
            loop: true,
            ended: function () {
            }
        };
        this.opt = Object.assign({}, t, i),
        "string" === typeof this.opt.ele && (this.opt.ele = document.querySelector(this.opt.ele)),
        this.opt.ele && (this.loading = !1,
            this.isDrag = !1,
            this.isplaying = !1,
            this.durationT = 0,
            this.currentT = 0,
            this.currentP = 0,
            this.maxProgressWidth = 0,
            this.dragProgressTo = 0,
            this.reduceTBefore = 0,
            this.reduceTAfter = 0,
            this.initDom());
        return this;
    };

    a.prototype = {
        // constructor:function(i) {
        //
        // },

        initDom: function () {
            this.wxAudioC = document.createElement("div"),
                this.wxAudioC.className = "wx-audio-content",
                this.wxAudioC.style.width = this.opt.width,
                this.opt.ele.appendChild(this.wxAudioC),
                this.wxAudio = document.createElement("audio"),
                this.wxAudio.className = "wx-audio-content",
                this.wxAudio.src = this.opt.src,
            this.opt.loop && this.wxAudio.setAttribute("loop", this.opt.loop),
                this.wxAudioC.appendChild(this.wxAudio),
                this.wxAudioL = document.createElement("div"),
                this.wxAudioL.className = "wx-audio-left",
                this.wxAudioC.appendChild(this.wxAudioL),
                this.wxAudioStateImg = document.createElement("img"),
                this.wxAudioStateImg.className = "wx-audio-state",
                this.wxAudioStateImg.src = o,
                this.wxAudioL.appendChild(this.wxAudioStateImg),
                this.wxAudioR = document.createElement("div"),
                this.wxAudioR.className = "wx-audio-right",
                this.wxAudioC.appendChild(this.wxAudioR),
                this.wxAudioT = document.createElement("p"),
                this.wxAudioT.className = "wx-audio-title",
                this.wxAudioT.innerText = this.opt.title,
                this.wxAudioR.appendChild(this.wxAudioT),
                this.wxAudioD = document.createElement("p"),
                this.wxAudioD.className = "wx-audio-disc",
                this.wxAudioD.innerText = this.opt.disc,
                this.wxAudioR.appendChild(this.wxAudioD),
                this.wxAudioP = document.createElement("div"),
                this.wxAudioP.className = "wx-audio-progrees",
                this.wxAudioR.appendChild(this.wxAudioP),
                this.wxAudioDetail = document.createElement("div"),
                this.wxAudioDetail.className = "wx-progrees-detail",
                this.wxAudioP.appendChild(this.wxAudioDetail),
                this.wxVoiceP = document.createElement("span"),
                this.wxVoiceP.className = "wx-voice-p",
                this.wxAudioDetail.appendChild(this.wxVoiceP),
                this.wxBufferP = document.createElement("span"),
                this.wxBufferP.className = "wx-buffer-p",
                this.wxAudioDetail.appendChild(this.wxBufferP),
                this.wxLoading = document.createElement("span"),
                this.wxLoading.className = "wx-loading",
                this.wxAudioDetail.appendChild(this.wxLoading),
                this.wxLoadingWrapper = document.createElement("span"),
                this.wxLoadingWrapper.className = "wx-loading-wrapper",
                this.wxLoading.appendChild(this.wxLoadingWrapper),
                this.wxAudioOrigin = document.createElement("div"),
                this.wxAudioOrigin.className = "wx-audio-origin",
                this.wxAudioP.appendChild(this.wxAudioOrigin),
                this.wxAudioTime = document.createElement("div"),
                this.wxAudioTime.className = "wx-audio-time",
                this.wxAudioR.appendChild(this.wxAudioTime),
                this.wxAudioCurrent = document.createElement("span"),
                this.wxAudioCurrent.className = "current-t",
                this.wxAudioCurrent.innerText = "00:00",
                this.wxAudioTime.appendChild(this.wxAudioCurrent),
                this.wxAudioDuration = document.createElement("span"),
                this.wxAudioDuration.className = "duration-t",
                this.wxAudioDuration.innerText = "00:00",
                this.wxAudioTime.appendChild(this.wxAudioDuration),
                this.initAudioEvent();
            if (this.opt.autoplay) {
                var that = this;
                document.addEventListener("WeixinJSBridgeReady", function () {
                    alert(1);
                    that.audioPlay();
                });
                window.addEventListener("load", function () {
                    that.audioPlay();
                });
            }
        },

        audioPlay: function () {
            this.wxAudio.play(),
                this.isPlaying = !0
        },
        audioPause: function () {
            this.wxAudio.pause(),
                this.isPlaying = !1
        },
        audioPlayPause: function () {
            this.isPlaying ? this.audioPause() : this.audioPlay()
        },
        audioCut: function (i, t, A) {
            this.wxAudio.src = i,
                this.wxAudioT.innerText = t,
                this.wxAudioD.innerText = A,
                this.durationT = 0,
                this.currentT = 0,
                this.currentP = 0,
                this.dragProgressTo = 0,
                this.wxAudioCurrent.innerText = "00:00",
                this.wxAudioOrigin.style.left = "0px",
                this.wxVoiceP.style.width = "0px",
                this.audioPlay()
        },
        showLoading: function (i) {
            this.loading = i || !1,
                this.loading ? this.wxLoading.style.display = "block" : this.wxLoading.style.display = "none"
        },
        initAudioEvent: function () {
            var i = this;
            i.wxAudio.onplaying = function () {
                var t = new Date;
                i.isPlaying = !0,
                    i.reduceTBefore = Date.parse(t) - Math.floor(1e3 * i.wxAudio.currentTime),
                    i.wxAudioStateImg.src = e
            }
                ,
                i.wxAudio.onpause = function () {
                    i.isPlaying = !1,
                        i.showLoading(!1),
                        i.wxAudioStateImg.src = o
                }
                ,
                i.wxAudio.onloadedmetadata = function () {
                    i.durationT = i.wxAudio.duration,
                        i.wxAudioDuration.innerText = i.formartTime(i.wxAudio.duration)
                }
                ,
                i.wxAudio.onwaiting = function () {
                    i.wxAudio.paused || i.showLoading(!0)
                }
                ,
                i.wxAudio.onprogress = function () {
                    if (i.wxAudio.buffered.length > 0) {
                        for (var t = 0, A = 0; A < i.wxAudio.buffered.length; A++)
                            t += i.wxAudio.buffered.end(A) - i.wxAudio.buffered.start(A),
                            t > i.durationT && (t = i.durationT,
                                i.showLoading(!1),
                                console.log("缓冲完成"));
                        var e = Math.floor(t / i.durationT * 100);
                        i.wxBufferP.style.width = e + "%"
                    }
                    var o = new Date;
                    i.wxAudio.paused || (i.reduceTAfter = Date.parse(o) - Math.floor(1e3 * i.currentT),
                        i.reduceTAfter - i.reduceTBefore > 1e3 ? i.showLoading(!0) : i.showLoading(!1))
                }
                ,
                i.wxAudio.onended = function () {
                    i.opt.ended()
                }
                ,
                i.wxAudio.ontimeupdate = function () {
                    var t = new Date;
                    i.isDrag || (i.currentT = i.wxAudio.currentTime,
                        i.currentP = Number(i.wxAudio.currentTime / i.durationT * 100),
                        i.reduceTBefore = Date.parse(t) - Math.floor(1e3 * i.currentT),
                        i.currentP = i.currentP > 100 ? 100 : i.currentP,
                        i.wxVoiceP.style.width = i.currentP + "%",
                        i.wxAudioOrigin.style.left = i.currentP + "%",
                        i.wxAudioCurrent.innerText = i.formartTime(i.wxAudio.currentTime),
                        i.showLoading(!1))
                }
                ,
                i.wxAudioStateImg.onclick = function () {
                    i.audioPlayPause()
                }
                ,
                i.wxAudioOrigin.onmousedown = function (t) {
                    i.isDrag = !0;
                    var A = t || window.event
                        , e = A.clientX
                        , o = t.target.offsetLeft;
                    i.maxProgressWidth = i.wxAudioDetail.offsetWidth,
                        i.wxAudioC.onmousemove = function (t) {
                            if (i.isDrag) {
                                var A = t || window.event
                                    , a = A.clientX;
                                i.dragProgressTo = Math.min(i.maxProgressWidth, Math.max(0, o + (a - e))),
                                    i.updatePorgress()
                            }
                        }
                        ,
                        i.wxAudioC.onmouseup = function () {
                            i.isDrag && (i.isDrag = !1,
                                i.wxAudio.currentTime = Math.floor(i.dragProgressTo / i.maxProgressWidth * i.durationT))
                        }
                        ,
                        i.wxAudioC.onmouseleave = function () {
                            i.isDrag && (i.isDrag = !1,
                                i.wxAudio.currentTime = Math.floor(i.dragProgressTo / i.maxProgressWidth * i.durationT))
                        }
                }
                ,
                i.wxAudioOrigin.ontouchstart = function (t) {
                    i.isDrag = !0;
                    var A = t || window.event
                        , e = A.touches[0].clientX
                        , o = A.target.offsetLeft;
                    i.maxProgressWidth = i.wxAudioDetail.offsetWidth,
                        i.wxAudioC.ontouchmove = function (t) {
                            if (i.isDrag) {
                                var A = t || window.event
                                    , a = A.touches[0].clientX;
                                i.dragProgressTo = Math.min(i.maxProgressWidth, Math.max(0, o + (a - e))),
                                    i.updatePorgress()
                            }
                        }
                        ,
                        i.wxAudioC.ontouchend = function () {
                            i.isDrag && (i.isDrag = !1,
                                i.wxAudio.currentTime = Math.floor(i.dragProgressTo / i.maxProgressWidth * i.durationT))
                        }
                }
                ,
                i.wxAudioDetail.onclick = function (t) {
                    var A = t || window.event
                        , e = A.layerX
                        , o = i.wxAudioDetail.offsetWidth;
                    i.wxAudio.currentTime = Math.floor(e / o * i.durationT)
                }
        },
        isWeiXin: function () {
            var i = window.navigator.userAgent.toLowerCase();
            return "micromessenger" === String(i.match(/MicroMessenger/i))
        },
        updatePorgress: function () {
            this.wxAudioOrigin.style.left = this.dragProgressTo + "px",
                this.wxVoiceP.style.width = this.dragProgressTo + "px";
            var i = Math.floor(this.dragProgressTo / this.maxProgressWidth * this.durationT);
            this.wxAudioCurrent.innerText = this.formartTime(i)
        },
        formartTime: function (i) {
            var t = function (i) {
                return i = i.toString(),
                    i[1] ? i : "0" + i
            }
                , A = Math.floor(i / 60)
                , e = Math.floor(i % 60);
            return t(A) + ":" + t(e)
        }
    };

    return a;
})();