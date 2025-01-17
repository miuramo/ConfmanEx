var previousJson;

function onNodeDragStart(json) {
    // console.log(json);
    previousJson = json;
}

function onNodeDragEnd(json) {
    // console.log(json);
    if (json != previousJson) {
        // console.log("diff!");
        // Oya.layoutChildren();
        Oya.layoutParents(10);
        boothpost(json);
        // $.ajax({
        //     url: "/admin/exelists/d3submit",
        //     type: "post",
        //     data: {
        //         id: 999,
        //         json: json,
        //         name: "exelist",
        //         memo: JSON.stringify(groups),
        //     },
        //     timeout: 3000,
        //     success: function (result, textStatus, xhr) {
        //         console.log("submit succeeded: " + result);
        //     }
        // });
    }
}

$(document).ready(function () {
    for (let i = 1; i < 13; i++) {
        new Oya(10, i * 150, i, i, "session " + i);
    }

    // console.log(subpapers);

    // var users = JSON.parse('$users'); // $users = json_encode($userary, JSON_THROW_ON_ERROR);
    // var u2g = JSON.parse('$u2g');

    var yy = 10;
    subpapers.forEach(function (sub) {
        // console.log(sub);
        var ud = {
            "uid": sub.paper.id,
            "x": 670, // 最初、未配属の発表のx
            "y": yy, // sub.paper_id*100,
            "txt": sub.paper.id,
            "txt2": sub.orderint+" "+sub.paper.title + " ["+sub.booth+"]",
            "stroke": "green"
        };
        //     var gid4u = u2g[usr.uid];
        //     if (gid4u) Oya.parentObjs[gid4u].kodomo.push(ud);
        // Oya.parentObjs[0].addKodomo(ud);
        if (sub.psession_id != null && Oya.parentObjs[sub.psession_id] != null){
            ud.y = sub.orderint*200;
            Oya.parentObjs[sub.psession_id].addKodomo(ud);
        } else {
            yy += 35;
        }
        kjdata.push(ud);
    });

    parententer();
    kjenter();
    Oya.layoutChildren();
    Oya.layoutParents(0);
    //load();

});


function boothpost(json) {
    var fd = new FormData();
    fd.append('_token', $('meta[name="csrf-token"]').attr("content"));
    fd.append('json', json);
    // fd.append('copy_orderint_to_booth', $('#copy_ordint_to_booth').prop('checked'));
    // fd.append('set_booth_with_session_id', $('#set_booth_with_session_id').prop('checked'));
    // fd.append('print_format', $('#print_format').val());
    var formDataObject = {};
    for (var pair of fd.entries()) {
        formDataObject[pair[0]] = pair[1];
    }
    var form = $("#boothpost");
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formDataObject,
        timeout: 10000,
        beforeSend: function (xhr, settings) { },
        complete: function (xhr, textStatus) { },
        success: function (result, textStatus, xhr) {
            if (/^OK/.test(result)){

            } else {
                alert(result);
            }
        },
        error: function (xhr, textStatus, error) {
            alert("error crudpost");
        }
    });
}
