function changed(formName, name) {
    var form = $("#" + formName);
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: form.serialize(),
        timeout: 10000,
        beforeSend: function(xhr, settings) {},
        complete: function(xhr, textStatus) {},
        success: function(result, textStatus, xhr) {
            var ary = JSON.parse(result);
            var elem = $("#" + name);
            elem.html('<div class="bg-'+ary['bgcolor']+'-200 dark:bg-'+ary['bgcolor']+'-700 text-2xl mx-4 mb-1 p-4 rounded-md">'+ary['name']+'</div>');
        },
        error: function(xhr, textStatus, error) {
            alert("error enq submit");
        }
    });
}
