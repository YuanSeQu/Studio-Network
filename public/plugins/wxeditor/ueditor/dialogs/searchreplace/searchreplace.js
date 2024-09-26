editor.firstForSR = 0;
editor.currentRangeForSR = null;
//执行
var frCommond = function (obj) {
    return editor.execCommand("searchreplace", obj);
};
//是否区分大小写
function getMatchCase(id) {
    return $G(id).checked ? true : false;
}
//查找
$G("nextFindBtn").onclick = function (txt, dir, mcase) {
    var findtxt = $G("findtxt").value, obj;
    if (!findtxt) {
        return false;
    }
    obj = {
        searchStr:findtxt,
        dir:1,
        casesensitive:getMatchCase("matchCase")
    };
    if (!frCommond(obj)) {
        var bk = editor.selection.getRange().createBookmark();
        layer.msg(lang.getEnd, {icon: 5,anim: 6})
        editor.selection.getRange().moveToBookmark(bk).select();
    }
};
$G("preFindBtn").onclick = function (txt, dir, mcase) {
    var findtxt = $G("findtxt").value, obj;
    if (!findtxt) {
        return false;
    }
    obj = {
        searchStr:findtxt,
        dir:-1,
        casesensitive:getMatchCase("matchCase")
    };
    if (!frCommond(obj)) {
        layer.msg(lang.getStart, {icon: 5,anim: 6});
    }
};
$G("nextReplaceBtn").onclick = function (txt, dir, mcase) {
    var findtxt = $G("findtxt1").value, obj;
    if (!findtxt) {
        return false;
    }
    obj = {
        searchStr:findtxt,
        dir:1,
        casesensitive:getMatchCase("matchCase1")
    };
    frCommond(obj);
};
$G("preReplaceBtn").onclick = function (txt, dir, mcase) {
    var findtxt = $G("findtxt1").value, obj;
    if (!findtxt) {
        return false;
    }
    obj = {
        searchStr:findtxt,
        dir:-1,
        casesensitive:getMatchCase("matchCase1")
    };
    frCommond(obj);
};
//替换
$G("repalceBtn").onclick = function () {
    var findtxt = $G("findtxt1").value.replace(/^\s|\s$/g, ""), obj,
        replacetxt = $G("replacetxt").value.replace(/^\s|\s$/g, "");
    if (!findtxt) {
        return false;
    }
    if (findtxt == replacetxt || (!getMatchCase("matchCase1") && findtxt.toLowerCase() == replacetxt.toLowerCase())) {
        return false;
    }
    obj = {
        searchStr:findtxt,
        dir:1,
        casesensitive:getMatchCase("matchCase1"),
        replaceStr:replacetxt
    };
    frCommond(obj);
};
//全部替换
$G("repalceAllBtn").onclick = function () {
    var findtxt = $G("findtxt1").value.replace(/^\s|\s$/g, ""), obj,
        replacetxt = $G("replacetxt").value.replace(/^\s|\s$/g, "");
    if (!findtxt) {
        return false;
    }
    if (findtxt == replacetxt || (!getMatchCase("matchCase1") && findtxt.toLowerCase() == replacetxt.toLowerCase())) {
        return false;
    }
    obj = {
        searchStr:findtxt,
        casesensitive:getMatchCase("matchCase1"),
        replaceStr:replacetxt,
        all:true
    };
    var num = frCommond(obj);
    if (num) {
        layer.msg(lang.countMsg.replace("{#count}", num), {icon: 1});
    }
};