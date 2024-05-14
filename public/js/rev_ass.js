var cursorreptag = null;
var ctu = null;
var ctp = null;
var save_background_color = null;

$(document).ready(function () {
    $("td.p").click(function (e) {
        var tdid = e.currentTarget.id;
        var m = tdid.match(/^u(\d+)_p(\d+)/);
        if (m == null) return;
        ctu = m[1];
        ctp = m[2];
        var tPosX = e.pageX - 30;
        var tPosY = e.pageY + 10;
        $("div.saihim").fadeIn(100);
        $("div.saihim").css({ "top": tPosY, "left": tPosX });

    });

    $("td.p").mousemove(function (e) {
        var tdid = e.currentTarget.id;
        var m = tdid.match(/^u(\d+)_p(\d+)/);
        if (m == null) return;
        cursorreptag = tdid;
        var tPosX = e.pageX - 50;
        var tPosY = e.pageY + 50;
        var mes = $("th#u" + m[1])[0].innerHTML;
        mes += "<br>→ ";
        mes += $("td#paper" + m[2])[0].innerHTML;
        $("div.tooltip").html(mes);
        $("div.tooltip").css({ "top": tPosY, "left": tPosX });
    });
    $("td.p").mouseover(function (e) {
        var tdid = e.currentTarget.id;
        save_background_color = $("#" + tdid).css("background-color");
        $("#" + tdid).css("background-color", "#ffa");
    });
    $("td.p").mouseout(function (e) {
        var tdid = e.currentTarget.id;
        $("#" + tdid).css("background-color", save_background_color);
        $("div.tooltip").css({ "top": -999, "left": 0 });
        //   $("#"+tdid).css("background-color","#fff");
    });
    $("td.n").mousemove(function (e) {
        var tdid = e.currentTarget.id;
        var tPosX = -100;
        var tPosY = -100;
        $("div.tooltip").css({ "top": tPosY, "left": tPosX });
    });

    $("div.saihi").mouseover(function (e) {
        var tdid = e.currentTarget.id;
        $("#" + tdid).css("background-color", "#fcc");
    });
    $("div.saihi").mouseout(function (e) {
        var tdid = e.currentTarget.id;
        $("#" + tdid).css("background-color", save_background_color);
    });
    $("div.saihi").click(function (e) {
        var tdid = e.currentTarget.id;
        var selection = tdid.match(/.+_(\d+)/);
        if (selection == null) {
            $("div.saihim").fadeOut();
            return; //cancel が選択された
        }
        var status = selection[1];
        //      console.log(status);
        if (status == 99) {
            $("div.saihim").fadeOut();
            return;
        }
        //   var win = window.open("/declinereviews/revupdate/'.$catid.'/"+ctu+"/"+ctp+"/"+status,"comment check", "width=600,height=500,left=800");
        // $("div.saihim").fadeOut();
        //   return;
        // }
        // $.post("/declinereviews/revupdate/'.$catid.'/" + ctu + "/" + ctp + "/" + status,
        //     null,
        //     function (data) {
        //         var m = this.url.match(/(\d+)\/(\d+)\/(\d+)/);
        //         $("#u" + m[2] + "_p" + m[3]).html(data);
        //     });
        changed(ctp, ctu, status);
        $("div.saihim").fadeOut();
    });

});

function changed(pid, uid, status) {
    $('#revass_paper_id').val(pid);
    $('#revass_user_id').val(uid);
    $('#revass_status').val(status);
    var form = $('#revass');
    console.log("p"+pid+"  u"+uid+"  status "+status);
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: form.serialize(),
        timeout: 10000,
        beforeSend: function (xhr, settings) { },
        complete: function (xhr, textStatus) { },
        success: function (result, textStatus, xhr) {
            // var ary = JSON.parse(result);
            // console.log(result);
            var bid = $("#u"+uid+"_p"+pid).data('bidding');
            if (bid == null) bid="";
            var elem = $("#u"+uid+"_p"+pid);
            elem.html(bid+" "+result);
            // elem.html('<span class="text-red-500">★</span>');
        },
        error: function (xhr, textStatus, error) {
            alert("error enq submit");
        }
    });

}
