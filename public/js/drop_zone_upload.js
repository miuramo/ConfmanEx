$(function () {
    if (window.File && window.FileReader && window.FileList && window.Blob) {
        // Great success! All the File APIs are supported.
    } else {
        alert('The File APIs are not fully supported in this browser.');
    }

    var reader;

    reader = new FileReader();
    // reader.onload = onLoad;

    $('input[type="file"]').on('change', onChange);

    var dropZone = document.getElementById('drop_zone');
    dropZone.addEventListener('dragover', handleDragOver, false);
    dropZone.addEventListener('drop', handleFileSelect, false);
    function handleDragOver(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
        evt.target.classList.add("bg-yellow-400");
    }
    // Drop
    function handleFileSelect(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        evt.target.classList.remove("bg-yellow-400");

        var files = evt.dataTransfer.files;
        sendFileToServer(files);
        //	filename = files[0].name;
        //	console.log(filename);

    }
    //ファイル選択で、fileが選ばれた時
    function onChange(event) {
        var files = event.target.files;
        sendFileToServer(files);
        //	console.log(filename+" onChange");
    }

    // ファイル送信処理の最初の入り口
    async function sendFileToServer(files) {
        for(const onefile of files){
            // console.log(onefile);
            console.log(onefile.name + " "+onefile.size);
            if (onefile.size > upload_max_filesize){
                alert("upload_max_filesize : "+onefile.name+" "+onefile.size+ " > "+upload_max_filesize);
                continue;
            }
            if (onefile.size >= post_max_size){
                alert("post_max_size : "+onefile.name+" "+onefile.size+" > "+post_max_size);
                continue;
            }
            await sendOneFile(onefile);
        }
    }

    async function sendOneFile(onefile){
        var fd = new FormData();
        fd.append('file', onefile);
        fd.append('_token', $('meta[name="csrf-token"]').attr("content"));
        fd.append('paper_id', paper_id);


        var url = "/file";

        var ajax = new XMLHttpRequest();
        ajax.upload.addEventListener("progress", progressHandler, false);
        ajax.addEventListener("load", completeHandler, false);
        ajax.addEventListener("error", errorHandler, false);
        ajax.addEventListener("abort", abortHandler, false);
        ajax.open("POST", url);
        ajax.send(fd);
        $("#drop_zone").text(onefile.name);
    }



    function progressHandler(event) {
        $("#loaded_n_total").text("Uploaded " + event.loaded + " bytes of " + event.total);
        var percent = (event.loaded / event.total) * 100;
        $("#progressbar").attr("value", Math.round(percent));
        $("#progressdiv").css("width", Math.round(percent) + "%");
        $("#status").text(Math.round(percent) + "% uploaded ... Please wait!");
    }

    var waitreload = 3;
    //  This is fired, if fail or done
    function completeHandler(event) {
        $("#status").html("アップロード完了。再読み込みしてください。<button class=\"inline-flex justify-center py-1 px-2 border border-transparent shadow-sm text-md font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500\" onclick=\"location.reload();\">再読み込み</button>");//event.target.responseText;
        $("#progressbar").attr("value", 100);
        $("#progressdiv").css("width", "100%");
        $("#drop_zone").text("Drop Files Here");//event.target.responseText;
        // $("#drop_zone").text("アップロード成功...リロードします ("+waitreload+")");//event.target.responseText;

        // AJAXでindexを呼び、#filelist にさしかえる
        reloadFileList();
    }
    function reloadFileList(){
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // データの取得が成功した場合
                    var responseData = xhr.responseText;
                    document.getElementById("filelist").innerHTML = responseData;
                } else {
                    // データの取得が失敗した場合
                    console.error('Request failed:', xhr.status);
                }
            }
        };
        xhr.open('GET', '/paper/'+paper_id+'/filelist', false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // これがないとAjax判定にならない
        xhr.send();
    }


    function errorHandler(event) {
        $("#status").text("Upload failed!");
    }

    function abortHandler(event) {
        $("#status").text("Upload aborted!");
    }


});
