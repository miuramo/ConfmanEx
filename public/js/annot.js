// Fabric.js の Canvas を初期化
const canvas = new fabric.Canvas('canvas');
const image = document.getElementById('targetImage');
let isDrawingMode = false;
let imgInstance = null;

let scale = 1;
let lastScale = 1;
let isTextEditing = false;

// キャンバスをウィンドウ幅に初期設定
function resizeCanvas() {
    lastScale = scale;
    const windowWidth = window.innerWidth - 15;
    scale = windowWidth / image.naturalWidth;

    canvas.setWidth(windowWidth);
    canvas.setHeight(image.naturalHeight * scale);

    // オブジェクトのリサイズと再配置
    // canvas.getObjects().forEach((obj, index) => {
    //     const original = originalState.objects[index];
    //     obj.scaleX = original.scaleX * scaleX;
    //     obj.scaleY = original.scaleY * scaleY;
    //     obj.left = original.left * scaleX;
    //     obj.top = original.top * scaleY;
    //     obj.setCoords(); // 座標を再計算
    //   });
    // オブジェクトがある場合は必要に応じて再スケール
    canvas.getObjects().forEach(obj => {
        let objWidth = obj.width * scale;
        obj.scaleToWidth(objWidth); // 例: オブジェクトをキャンバス幅の10%に調整
        // console.log(obj);
        obj.left = (obj.left * scale) / lastScale;
        obj.top = (obj.top * scale) / lastScale;
    });

    imgInstance.scaleToWidth(windowWidth);
    imgInstance.scaleToHeight(windowWidth * 1.41);

    canvas.renderAll();
}

function image_onload() {
    console.log('画像読み込み完了');
    imgInstance = new fabric.Image(image, {
        left: 0,
        top: 0,
        angle: 0,
        opacity: 1.0
    });
    imgInstance.selectable = false;
    imgInstance.evented = false;
    imgInstance.excludeFromExport = true;
    canvas.add(imgInstance);
    // canvas.sendToBack(imgInstance); 

    // 四角形を描画
    // const rect = new fabric.Rect({
    //     left: 50,
    //     top: 50,
    //     fill: 'rgba(0, 155, 0, 0.5)', // 半透明のgreen
    //     width: 100,
    //     height: 100,
    //     hasBorders: true, // 境界線を表示
    //     hasControls: true // リサイズコントロールを表示
    // });

    // テキストを描画
    const text = new fabric.IText('Hello, Fabric!', {
        left: 200,
        top: 150,
        fontSize: 30,
        fill: 'blue',
    });
    // 半透明の四角形を描画
    // const transparentRect = new fabric.Rect({
    //     left: 300,
    //     top: 50,
    //     fill: 'rgba(255, 0, 0, 0.5)', // 半透明の赤色
    //     width: 200,
    //     height: 100,
    //     hasBorders: true, // 境界線を表示
    //     hasControls: true // リサイズコントロールを表示
    // });
    // Canvas にオブジェクトを追加
    // canvas.add(rect);
    canvas.add(text);
    // canvas.add(transparentRect);

    // オブジェクトをクリックして選択できる
    canvas.on('object:selected', function (e) {
        console.log('選択されたオブジェクト:', e.target);
    });

    // オブジェクトが選択されているときにドラッグ可能
    canvas.on('object:moving', function (e) {
        const object = e.target;
        console.log('move left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
        console.log(object);
    });

    canvas.on('object:modified', function (e) {
        const object = e.target;
        console.log('mod left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
        console.log('オブジェクトが変更されました:', object);
    });
    canvas.on('object:resizing', function (e) {
        const object = e.target;
        console.log('resize left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
    });
    canvas.on('object:scaling', function (e) {
        const object = e.target;
        console.log('scaling left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
    });

    // テキスト編集完了後のイベントリスナー
    canvas.on('text:editing:exited', (event) => {
        const editedText = event.target;
        console.log('編集後のテキスト:', editedText.text);
        isTextEditing = false;
    });

    // // テキスト選択時にデバッグ用ログ
    canvas.on('text:editing:entered', (event) => {
        const editingText = event.target;
        console.log('テキスト編集開始:', editingText.text);
        isTextEditing = true;
    });

    let lastClickTime = 0; // 前回クリック時刻を保存

    // ダブルクリックで新規テキスト作成、
    canvas.on('mouse:down', (e) => {
        const currentTime = new Date().getTime();
        const timediff = currentTime - lastClickTime;
        if (timediff < 500) {
            dblclick(e);
        } else {
            if (isTextEditing) {
                canvas.discardActiveObject(); // アクティブオブジェクトを解除
                canvas.renderAll(); // 再描画
            }
        }
        lastClickTime = currentTime;
    });

    function dblclick(e) {
        if (canvas.getActiveObject() === undefined) {
            console.log('クリックされた座標:', e);
            const newTxt = new fabric.IText('ここをクリックして編集', {
                left: e.pointer.x,
                top: e.pointer.y,
                fontSize: 25,
                fill: 'blue',
            });
            canvas.add(newTxt);
        }
    }

    // キャンバス上でキーが押されたときのイベント
    document.onkeydown = function (e) {
        // もしDeleteキーが押されたら、選択中のオブジェクトを削除
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (!isTextEditing) {
                console.log('Deleteキーが押されました');
                canvas.remove(canvas.getActiveObject());
            }
        }
    };


    // フリーハンド描画モードの切り替え
    document.getElementById('drawModeButton').addEventListener('click', function () {
        isDrawingMode = !isDrawingMode;
        canvas.isDrawingMode = isDrawingMode;
        this.textContent = isDrawingMode ? '描画モード終了' : 'フリーハンド描画モード';
    });
    document.getElementById('addTextButton').addEventListener('click', function () {
        const newTxt = new fabric.IText('ここをクリックして編集', {
            left: 200,
            top: 150,
            fontSize: 25,
            fill: 'blue',
        });
        canvas.add(newTxt);
    });

    // エクスポートボタンのクリックイベント
    document.getElementById('exportButton').addEventListener('click', function () {
        const json = JSON.stringify(canvas.toJSON());

        document.getElementById('output').textContent = json;
        document.getElementById('id_content').value = json;
        annot_changed('submit_annots');
    });
}
// 画像の読み込み後に初期描画
image.onload = () => {
    image_onload();
    resizeCanvas();
};
// 画像がキャッシュ済みの場合の対応
if (image.complete) {
    image.onload();
}

window.onload = () => {

    // ウィンドウのリサイズイベントで再描画
    // ウィンドウのリサイズイベントでスケール調整
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

}

function annot_changed(formName) {
    var form = $("#" + formName);
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: form.serialize(),
        timeout: 10000,
        beforeSend: function (xhr, settings) { },
        complete: function (xhr, textStatus) { },
        success: function (result, textStatus, xhr) {
            console.log("success");
        },
        error: function (xhr, textStatus, error) {
            console.log(textStatus);
            console.log(error);
        }
    });
}