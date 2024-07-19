var stcolors = ['green', 'blue', 'purple', 'red', 'orange', '#acf']; //ラベルの枠の色の選択

$(document).ready(function () {
    $.ajaxSetup({ cache: false });
    //kjenter();

    // select options are initialized from "stcolors" array
    d3.select("#stcolor").selectAll("option").data(stcolors).enter().append("option").attr("value", function (d) { return d; }).text(function (d) { return d; });

    // try to load
    //load();
});

function init() {
    kjdata = [];
    kjupdate(0);
    kjdata = kjdata2;
    //        kjenter();
    //        parententer();
}


// Define your menu
var menu = [
    {
        title: '(cancel)',
        action: function (elm, d, i) { /* do nothing */ }
    },
    {
        title: 'Property',
        action: function (elm, d, i) {
            console.log('The data ' + i + ' label ' + d.txt + ' uid ' + d.uid);
            console.log(d);
        },
        disabled: false // optional, defaults to false
    },
    {
        title: 'Change Text',
        action: function (elm, d, i) {
            var str = prompt("edit label", d.txt);
            if (str == null) return;
            d.txt = str;
            kjupdate(0);
        },
        disabled: false // optional, defaults to false
    },
    {
        title: 'Copy',
        action: function (elm, d, i) {
            console.log('Copy ' + i + ' label ' + d.txt + ' is: ' + d);
            copy_label(JSON.stringify(elm));
        }
    },
    {
        title: 'Delete',
        action: function (elm, d, i) {
            console.log('Delete ' + i + ' label ' + d.txt + ' is: ' + d);
            delete_label(d);
        }
    }
];

var kjdata = [];
var kjdata2 = [];
////
class Oya {
    static parents = [];
    static parentObjs = {};
    static showGrp() {
        var idonly = {};
        Oya.parents.forEach(function (oo) {
            idonly[oo.gid] = oo.getUIDs();
        });
        return JSON.stringify(idonly);
    }
    // 親のY座標をみて、グルーピング
    static layoutParents(msec) {
        // console.log("layoutParents " + msec);
        var ylist = [];
        var maxlist = {};
        var ygroup = {};
        // maxをしらべる
        Oya.parents.forEach(function (oo) {
            // console.log(oo.y + " " + oo.getHeight() + " " + oo.name);
            if (!maxlist[oo.y]) {
                maxlist[oo.y] = oo.getHeight();
                ygroup[oo.y] = [];
                ylist.push(oo.y);
            }
            ygroup[oo.y].push(oo);
            if (maxlist[oo.y] < oo.getHeight()) {
                maxlist[oo.y] = oo.getHeight();
            }
        });

        var linenum = 0;
        var lasty = 20;
        ylist.forEach(function (yval) {
            ygroup[yval].forEach(function (ooo) {
                ooo.setY(lasty);
                // console.log(ooo.name+" "+lasty);
            });
            lasty = (lasty + maxlist[yval] + 20);
            linenum++;
        });
        //        Oya.layoutChildren();
        parentupdate(msec);
    }
    static layoutChildren() {
        Oya.parents.forEach(function (oo) {
            oo.layoutKodomo();
        });
    }
    // constructor(_x,_y,_gid){ this.x = _x ; this.y = _y; this.gid = _gid; this.kodomo = []; Oya.parents.push(this);}
    constructor(_x, _y, _gid, _nick, _name) {
        this.x = _x; this.y = _y; this.gid = _gid; this.kodomo = [];
        this.nick = _nick;
        this.name = _name;
        Oya.parents.push(this); Oya.parentObjs[this.gid] = this;
        // console.log("oya " + this.nick + " " + this.name);
    }
    getX() { return this.x; }
    getY() { return this.y; }
    getWidth() { return Number(this.getSvg("rect").attr("width")); }
    getHeight() { return Number(this.getSvg("rect").attr("height")); }
    addKodomo(ko) {
        //他のすべてのOya.kodomo から、 ko があれば取り除く
        Oya.parents.forEach(function (oo) {
            oo.delKodomo(ko);
        });
        this.kodomo.push(ko);
        this.kodomo = Array.from(new Set(this.kodomo));
        this.kodomo.sort(this.compareByY);
    }
    compareByY(a, b) {
        return (a.y < b.y) ? -1 : 1;
        //        return (a.y < b.y )? -1 : 1;
    }
    delKodomo(ko) {
        this.kodomo = this.kodomo.filter(itm => itm.uid != ko.uid);
        this.layoutKodomo();
    }
    showGrp() {
        if (this.kodomo.length > 0) {
            console.log(this.gid);
            this.kodomo.forEach(function (ko) {
                console.log("   " + ko.uid);
            });
        }
    }
    getUIDs() {
        var ret = [];
        this.kodomo.forEach(function (ko) {
            ret.push(ko.uid);
        });
        return ret;
    }
    layoutKodomo() {
        var yy = this.y + 16;
        for (var i = 0; i < this.kodomo.length; i++) {
            var ko = this.kodomo[i];
            ko.x = this.x + 5;
            ko.y = yy;
            var kosvg = d3.select("#chart").select("g.txtnode.uid" + ko.uid)._groups[0][0];
            d3.select(kosvg).attr("transform", function (d, i) {
                return "translate(" + d.x + " " + d.y + ")";
            });
            yy += 35;
        }
        var newheight = yy - this.y + 20;
        if (newheight < 30) newheight = 40;
        this.getSvg("rect").attr("height", newheight);
    }
    //drag parent move
    move(dragp) {
        const datum = this;
        //        d3.select(dragp).attr("transform", function(datum){ return "translate("+datum.x+" "+datum.y+")"; })
        d3.select(dragp).attr("transform", "translate(" + this.x + " " + this.y + ")");
    }
    getSvg(obj) {
        var svgobj = d3.select("#chart").select(obj + ".parent.gid" + this.gid)._groups[0][0];
        return d3.select(svgobj);
    }
    setY(yval) {
        this.y = yval;
        this.layoutKodomo();
    }
}
//// end of Oya

// コンストラクタのなかで、Oya.parentsに追加している

var svg = d3.select("#chart");

function parentupdate(msec) {
    var grp = svg.selectAll(".grp").data(Oya.parents);
    grp.transition().duration(msec).attr("transform", function (d) { return "translate(" + d.x + " " + d.y + ")"; })
        // .style("opacity", 0.0)
        // .transition().duration(msec)
        .style("opacity", 0.9);
}

function parententer() {
    var parentNodes = svg.selectAll(".grp").data(Oya.parents).enter().append("g")
        .attr("class", "grp")
        .attr("transform", function (d) { return "translate(" + d.x + " " + d.y + ")"; })
        .attr("width", 0)
        .attr("height", 0)
        .call(d3.drag()
            //.on("start", startParent)
            .on("drag", dragParent)
            //.on("end", endParent)
        );
    parentNodes.style("opacity", 0.0)
        .transition().duration(1000)
        .style("opacity", 0.9);

    parentNodes.append("rect")
        .attr("class", function (d) { return "parent gid" + d.gid; })
        .attr("x", function (d) { return 0; })
        .attr("y", function (d) { return 0; })
        .attr("width", 620)
        .attr("height", 100);

    parentNodes.append("text").text(function (d) { return d.name; }).attr("font-family", "sans-serif").attr("font-size", "12px").attr("x", 5).attr("y", 13).attr("fill", "black");
    // parentNodes.append("text").text(function (d) { return d.gid + "  " + d.nick; }).attr("font-family", "sans-serif").attr("font-size", "9px").attr("x", 5).attr("y", 24).attr("fill", "black");
}
// function startParent(pd){      }
function dragParent(pd) {
    pd.x += d3.event.dx;
    pd.y += d3.event.dy;
    pd.move(this);
    pd.layoutKodomo();
}
// function endParent(pd){      }

var dragtrans = d3.drag()
    .on("start", dragstart)
    .on("drag", draggedtrans)
    .on("end", dragend);
function dragstart(d) {
    if (onNodeDragStart !== null) {
        var json = Oya.showGrp();
        onNodeDragStart(json);
    }
}
function draggedtrans(d) {
    d.x += d3.event.dx;
    d.y += d3.event.dy;
    d3.select(this).attr("transform", function (d, i) {
        return "translate(" + d.x + " " + d.y + ")";
    });
}
function dragend(d) {
    // get nearest parent
    var closestParentIdx = getClosestParent(d);
    if (closestParentIdx > -1) {
        var closestParent = Oya.parents[closestParentIdx];
        closestParent.addKodomo(d);
        closestParent.layoutKodomo();
        if (onNodeDragEnd !== null) {
            var json = Oya.showGrp();
            onNodeDragEnd(json);
        }
    }
}
function getClosestParent(child) {
    var closestParent = -1;
    var shortestDistance = Infinity;
    for (var i = 0; i < Oya.parents.length; i++) {
        var distance = getDistance(Oya.parents[i], child);
        if (distance < shortestDistance) {
            closestParent = i;
            shortestDistance = distance;
        }
    }
    return closestParent;
}
function getDistance(parent, child) {
    var dx = parent.x - child.x;
    var dy = parent.y + 15 + parent.kodomo.length * 20 - child.y;
    return Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2));
}

svg.on("contextmenu", function () {
    d3.event.preventDefault();
});

function kjenter() {
    var grp = d3.select("#chart").selectAll(".txtnode").data(kjdata).enter().append("g")
        .attr("class", function (d) { return "txtnode uid" + d.uid; })
        //        .attr("class","txtnode")
        .attr("transform", function (d) { return "translate(" + d.x + " " + d.y + ")"; })
        .attr("width", 0).attr("height", 0).call(dragtrans)
        .on('contextmenu', d3.contextMenu(menu));
    grp.style("opacity", 0.0)
        .transition().duration(1000)
        .style("opacity", 1.0);

    grp.append("rect")
        .attr("x", 5).attr("y", 5).attr("rx", 7).attr("ry", 7).attr("width", 600).attr("height", 30)
        .style("fill", "white").style("stroke", function (d) { return d.stroke; }).style("stroke-width", 3).style("opacity", 0.0)
        .transition().duration(1000)
        .style("fill", "white").style("stroke", function (d) { return d.stroke; }).style("stroke-width", 3).style("opacity", 0.8);

    //1行目
    grp.append("text").text(function (d) { return d.txt; }).attr("font-family", "sans-serif")
        .attr("x", 10).attr("y", 25).attr("fill", "black");
    //2行目
    grp.append("text").text(function (d) { return d.txt2; }).attr("font-family", "sans-serif").attr("font-size", "12px")
        .attr("x", 35).attr("y", 25).attr("fill", "black");
}
function kjupdate(msec) {
    var grp = d3.select("#chart").selectAll("g").data(kjdata);
    grp.transition().duration(msec).attr("transform", function (d) { return "translate(" + d.x + " " + d.y + ")"; });
    grp.select("text").text(function (d) { return d.txt; });
    grp.select("rect").style("stroke", function (d) { return d.stroke; });

    d3.select("#chart").selectAll("g").data(kjdata).exit().remove();
}
function kjremove() {
    d3.select("#chart").selectAll("g").data(kjdata).exit().remove();
}

function add_label() {
    var txt = prompt("input text", "ここに入力して(" + kjdata.length + ")");
    if (txt == null) return;
    //        console.log(txt);
    var stcolor = document.getElementById("stcolor");
    kjdata.push({ "uid": getuniqueid(), "x": Math.random() * 200 + 30, "y": Math.random() * 200 + 30, "r": 1, "scale": 1, "txt": txt, "stroke": stcolor.value });
    kjenter();
}
function getuniqueid() {
    return Date.now();
}
function layout_label() {
    var sx = 20; var sy = 20;
    var grp = d3.select("#chart").selectAll("g").data(kjdata)[0];
    for (var i = 0; i < grp.length; i++) {
        var target = grp[i].__data__;
        target.x = sx;
        target.y = sy;
        //          target.txt += "x";
        sx += 170;
        if (sx > 700) {
            sx = 20; sy += 80;
        }
    }
    kjupdate(1000);
}
function shuffle_label() {
    var grp = d3.select("#chart").selectAll("g").data(kjdata)[0];
    for (var i = 0; i < grp.length; i++) {
        var target = grp[i].__data__;
        target.x = Math.random() * 400 + 30;
        target.y = Math.random() * 400 + 30;
    }
    kjupdate(1000);
}
function kjdelete_elm(kj_d) {
    for (var i = kjdata.length - 1; i > -1; i--) {
        var target = kjdata[i];
        if (target.uid == kj_d.uid) {
            console.log(target.uid + " == " + kj_d.uid + " index " + i);
            kjdata.splice(i, 1);
            break;
        }
    }
}
function delete_label(kj_d) {
    kjdelete_elm(kj_d);
    kjupdate(0);
}
function copy_label(json) {
    var data = JSON.parse(json);
    data = data.__data__;
    data.x += 50; data.y += 30;
    data.uid = getuniqueid();
    kjdata.push(data);
    kjenter();
}

var filename = "gkjdata.json";

function save() {
    $.ajax({
        url: "../_edit/__save.php",
        type: "post",
        data: { file: "webgkj/" + filename, code: "" },
        timeout: 3000,
        success: function (result, textStatus, xhr) {
            reallysave();
            //console.log("save success");
        }
    });
}
function reallysave() {
    $.ajax({
        url: "../_edit/__save.php",
        type: "post",
        data: { file: "webgkj/" + filename, code: JSON.stringify({ "nodes": kjdata, "parents": Oya.parents }) },
        timeout: 3000,
        success: function (result, textStatus, xhr) {
            console.log("save success : " + kjdata.length + " nodes " + Oya.parents.length + " parents.");
            // alert("File "+filename+" Deleted.");
        }
    });
}
function saveLocal() {
    localStorage.webgkj = JSON.stringify({ "nodes": kjdata, "parents": Oya.parents });
    console.log("save success : " + kjdata.length + " items.");
}
function loadLocal() {
    var txt = localStorage.webgkj;
    if (txt == undefined || txt == null) return;
    var obj = eval("(" + txt + ")");
    dataclear();
    for (var i = 0; i < obj.node.length; i++) {
        kjdata.push(obj.node[i]);
    }
    kjenter();
}
function load() {
    var p = true; // confirm("Load "+filename+" ?");
    // console.log(p);
    if (!p) return;
    dataclear();
    parententer();
    $.getJSON(filename, function (json) {
        //          json.nodes.forEach( function(ary){
        //         kjdata.push(ary);
        //    });
        json.parents.forEach(function (ary) {
            //var kdm = [];
            ary.kodomo.forEach(function (kk) {
                kjdata.push(kk);
            });
            new Oya(ary.x, ary.y, ary.gid, ary.kodomo);
            //            Oya.parents.push(ary);
        });
        parententer();
        kjenter();
        Oya.layoutChildren();
    });
}
function dataclear() {
    kjdata = [];
    kjupdate(0);
}
