(function ($) {
    $.fn.qrcode = function (options) {
        // if options is string, 
        if (typeof options === 'string') {
            options = { text: options };
        }

        // set default values
        // typeNumber < 1 for automatic calculation
        options = $.extend({}, {
            render: "canvas",
            width: 256,
            height: 256,
            typeNumber: -1,
            correctLevel: QRErrorCorrectLevel.H,
            margin: 10,
            strokeStyle: '#666', //边框线条颜色
            fillStyle: '#fff', //圆角矩形填充颜色
            background: "#ffffff",
            foreground: "#000000"
        }, options);

        var createCanvas = function () {
            // create the qrcode itself
            var qrcode = new QRCode(options.typeNumber, options.correctLevel);
            qrcode.addData(options.text);
            qrcode.make();

            // create canvas element
            var canvas = document.createElement('canvas');
            canvas.width = options.width;
            canvas.height = options.height;
            var ctx = canvas.getContext('2d');

            // compute tileW/tileH based on options.width/options.height
            var tileW = (options.width - (options.margin * 2)) / qrcode.getModuleCount();
            var tileH = (options.height - (options.margin * 2)) / qrcode.getModuleCount();

            ctx.fillStyle = options.background || '#fff';
            ctx.fillRect(0, 0, options.width, options.height); //先刷上背景

            // draw in the canvas
            for (var row = 0; row < qrcode.getModuleCount(); row++) {
                for (var col = 0; col < qrcode.getModuleCount(); col++) {
                    ctx.fillStyle = qrcode.isDark(row, col) ? options.foreground : options.background;
                    var w = (Math.ceil((col + 1) * tileW) - Math.floor(col * tileW));
                    var h = (Math.ceil((row + 1) * tileW) - Math.floor(row * tileW));
                    ctx.fillRect(Math.round(col * tileW + options.margin), Math.round(row * tileH + options.margin), w, h);
                }
            }

            //增加以下代码，把图片画出来
            if (options.imgsrc) {//传进来的图片地址

                var maxImgW = (options.width - (options.margin * 2)) * 0.45,
                    maxImgH = (options.height - (options.margin * 2)) * 0.33;

                //图片大小
                options.imgWidth = options.imgWidth || options.width / 4.7;
                options.imgHeight = options.imgHeight || options.height / 4.7;
                var img = new Image();
                img.src = options.imgsrc;
                //不放在onload里，图片出不来
                img.onload = function () {
                    var imgW = this.width,
                        imgH = this.height, zommC;
                    if (imgW > maxImgW) {
                        zommC = parseFloat(maxImgW / imgW);
                        imgW = maxImgW;
                        imgH = parseInt(imgH * zommC);
                    }
                    if (imgH > maxImgH) {
                        zommC = parseFloat(maxImgH / imgH);
                        imgH = maxImgH;
                        imgW = parseInt(imgW * zommC);
                    }
                    var x = (options.width - imgW) / 2,
                        y = (options.height - imgH) / 2;

                    drawRoundedRect.call(ctx, options.strokeStyle, options.fillStyle, x - 3, y - 3, imgW + 6, imgH + 6, 6);
                    ctx.drawImage(img, x, y, imgW, imgH);
                }
            }


            // return just built canvas
            return canvas;
        }

        function roundedRect(cornerX, cornerY, width, height, cornerRadius) {
            if (width > 0) this.moveTo(cornerX + cornerRadius, cornerY);
            else this.moveTo(cornerX - cornerRadius, cornerY);
            this.arcTo(cornerX + width, cornerY, cornerX + width, cornerY + height, cornerRadius);
            this.arcTo(cornerX + width, cornerY + height, cornerX, cornerY + height, cornerRadius);
            this.arcTo(cornerX, cornerY + height, cornerX, cornerY, cornerRadius);
            if (width > 0) {
                this.arcTo(cornerX, cornerY, cornerX + cornerRadius, cornerY, cornerRadius);
            }
            else {
                this.arcTo(cornerX, cornerY, cornerX - cornerRadius, cornerY, cornerRadius);
            }
        }
        //这个就是画圆角矩形的方法了，其中cornerX,cornerY是矩形左上角坐标。
        function drawRoundedRect(strokeStyle, fillStyle, cornerX, cornerY, width, height, cornerRadius) {
            this.beginPath();
            roundedRect.call(this, cornerX, cornerY, width, height, cornerRadius);
            this.strokeStyle = strokeStyle;
            this.fillStyle = fillStyle;
            this.stroke();
            this.fill();
        }

        // from Jon-Carlos Rivera (https://github.com/imbcmdth)
        var createTable = function () {
            // create the qrcode itself
            var qrcode = new QRCode(options.typeNumber, options.correctLevel);
            qrcode.addData(options.text);
            qrcode.make();

            // create table element
            var $table = $('<table></table>')
				.css("width", options.width + "px")
				.css("height", options.height + "px")
				.css("border", "0px")
				.css("border-collapse", "collapse")
				.css('background-color', options.background);

            // compute tileS percentage
            var tileW = options.width / qrcode.getModuleCount();
            var tileH = options.height / qrcode.getModuleCount();

            // draw in the table
            for (var row = 0; row < qrcode.getModuleCount(); row++) {
                var $row = $('<tr></tr>').css('height', tileH + "px").appendTo($table);

                for (var col = 0; col < qrcode.getModuleCount(); col++) {
                    $('<td></td>')
						.css('width', tileW + "px")
						.css('background-color', qrcode.isDark(row, col) ? options.foreground : options.background)
						.appendTo($row);
                }
            }
            //主要思想，把table，和img标签放在同一个div下，div relative定位，然后使得图片absolute定位在table中间
            if (options.imgsrc) {
                options.imgWidth = options.imgWidth || options.width / 4.7;
                options.imgHeight = options.imgHeight || options.height / 4.7;
                var $img = $('<img>').attr("src", options.imgsrc)
                  .css("width", options.imgWidth)
                  .css("height", options.imgHeight)
                  .css("position", "absolute")
                  .css("left", (options.width - options.imgWidth) / 2)
                  .css("top", (options.height - options.imgHeight) / 2);
                $table = $('<div style="position:relative;"></div>').append($table).append($img);
            }

            // return just built canvas
            return $table;
        }

        return this.each(function () {
            var element = options.render == "canvas" ? createCanvas() : createTable();
            $(element).appendTo(this);
        });
    };
})(window.jQuery || window.Zepto);
