$("input.whereBy").keydown(function (e) {
    var tdid = e.currentTarget.id;
    var m = tdid.match(/^(\w+)__([\w()]+)/); // tdidを分解する whereBy name
    if (e.key === "Enter") {
        var form = $('#admincrudwhere');
        form.submit();
    }
});

