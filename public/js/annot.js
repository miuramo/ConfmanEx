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

    const windowWidth = window.innerWidth;
    scale = windowWidth / image.naturalWidth;

    canvas.setWidth(windowWidth);
    canvas.setHeight(image.naturalHeight * scale);

    // オブジェクトがある場合は必要に応じて再スケール
    canvas.getObjects().forEach(obj => {
        let objWidth = obj.width * scale;
        const origSX = obj.scaleX;
        const origSY = obj.scaleY;
        obj.scaleToWidth(objWidth); // 例: オブジェクトをキャンバス幅の10%に調整
        // console.log(obj);
        obj.left = (obj.left * scale) / lastScale;
        obj.top = (obj.top * scale) / lastScale;
        obj.scaleX = origSX * scale / lastScale;
        obj.scaleY = origSY * scale / lastScale;
        if (obj.user_id !== user_id) {
            obj.excludeFromExport = true;
            obj.selectable = false;
            obj.hoverCursor = 'default';
            // obj.evented = false; // ここを使うとマウスホバーイベントが発生しなくなる
        }

    });
    canvas.renderAll();
    lastWidth = window.innerWidth;
    lastScale = scale;
}
// console.log('before w',obj.width, 'h',obj.height, 'left',obj.left,'top', obj.top, 'sX', obj.scaleX, 'sY', obj.scaleY);

function fix_scale_objects(lastS, curS) {
    canvas.getObjects().forEach(obj => {
        let objWidth = obj.width * curS;
        const origSX = obj.scaleX;
        const origSY = obj.scaleY;
        obj.scaleToWidth(objWidth); // 例: オブジェクトをキャンバス幅の10%に調整
        // console.log(obj);
        obj.left = (obj.left * curS) / lastS;
        obj.top = (obj.top * curS) / lastS;
        obj.scaleX = origSX * curS / lastS;
        obj.scaleY = origSY * curS / lastS;
        if (obj.user_id != user_id) {
            obj.excludeFromExport = true;
        }
    });
}

function import_notes() {
    if (notes === undefined || notes.length === 0) {
        return;
    }
    lastScale = 0.8058017727639001; //　横幅1000の場合のスケール
    canvas.loadFromJSON(notes, () => {
    }).then((canvas) => {
        resizeCanvas();
        canvas.renderAll(); // すべてのオブジェクトを再描画
    });
}

function inspect_selected() {
    const ao = canvas.getActiveObject();
    console.log('w', ao.width, 'h', ao.height, 'left', ao.left, 'top', ao.top, 'sX', ao.scaleX, 'sY', ao.scaleY, 'uID', ao.user_id);
}

function image_onload() {

    // ツールチップ要素
    const tooltip = document.getElementById('tooltip');

    // オブジェクトをクリックして選択できる
    canvas.on('object:selected', function (e) {
        console.log('選択されたオブジェクト:', e.target);
    });

    // オブジェクトが選択されているときにドラッグ可能
    canvas.on('object:moving', function (e) {
        const object = e.target;
        // console.log('move left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
        // console.log(object);
    });

    canvas.on('object:modified', function (e) {
        const object = e.target;
        // console.log('mod left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
        // console.log('オブジェクトが変更されました:', object);
    });
    canvas.on('object:resizing', function (e) {
        const object = e.target;
        // console.log('resize left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
    });
    canvas.on('object:scaling', function (e) {
        const object = e.target;
        // console.log('scaling left', object.left, 'top', object.top, 'width', object.width, 'height', object.height, 'sX', object.scaleX, 'sY', object.scaleY,);
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
            } else {
                if (canvas.getActiveObject() === undefined) {
                    save_objects();
                }
            }
        }
        lastClickTime = currentTime;
    });


    // マウスオーバーイベント
    canvas.on('mouse:over', (e) => {
        const target = e.target;
        if (target) {
            tooltip.style.background = (target.user_id == user_id) ? 'rgba(70, 250, 220, 0.8)' : 'rgba(230, 200, 40, 0.8)';
            tooltip.style.display = 'block';
            tooltip.textContent = 'uID:' + target.user_id || 'Tooltip';
        } else {
            tooltip.style.display = 'none';
        }
    });
    // マウス移動イベントでツールチップ位置を更新
    canvas.on('mouse:move', (e) => {
        if (tooltip.style.display === 'block') {
            const pointer = canvas.getPointer(e.e);
            const canvasRect = canvas.upperCanvasEl.getBoundingClientRect(); // canvas の位置を取得
            // console.log(canvasRect);
            tooltip.style.left = `${pointer.x + 30}px`;
            tooltip.style.top = `${pointer.y + canvasRect.top - 40}px`;
        }
    });
    // マウスアウトイベント
    canvas.on('mouse:out', () => {
        tooltip.style.display = 'none';
    });

    function dblclick(e) {
        if (canvas.getActiveObject() === undefined) {
            console.log('クリックされた座標:', e);
            const newTxt = new fabric.IText('ここをクリックして編集', {
                left: e.pointer.x,
                top: e.pointer.y,
                fontSize: 25,
                fill: 'blue',
                scaleX: scale,
                scaleY: scale,
                user_id: user_id,
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
            left: 200 * scale,
            top: 150 * scale,
            fontSize: 25,
            fill: 'blue',
            scaleX: scale,
            scaleY: scale,
            user_id: user_id,
        });
        canvas.add(newTxt);
    });
    document.getElementById('addRectButton').addEventListener('click', function () {
        // 半透明の四角形を描画
        const transparentRect = new fabric.Rect({
            left: 300,
            top: 50,
            fill: 'rgba(255, 255, 0, 0.3)', // 半透明の赤色
            width: 200,
            height: 100,
            hasBorders: true, // 境界線を表示
            hasControls: true, // リサイズコントロールを表示
            user_id: user_id,
        });
        canvas.add(transparentRect);
    });

    let previousData = null;
    function save_objects() {
        canvas.discardActiveObject(); // アクティブオブジェクトを解除
        canvas.renderAll(); // 再描画

        // 横幅を1000に一度リサイズ
        const tmpscale = 1000 / image.naturalWidth;
        fix_scale_objects(lastScale, tmpscale);

        // JSONを一旦オブジェクトに変換
        let json = canvas.toJSON();
        // windowWidthを JSON に追加
        json.windowWidth = 1000;
        json.lastScale = tmpscale;
        // JSON を文字列に変換
        const strjson = JSON.stringify(json);

        fix_scale_objects(tmpscale, lastScale);
        if (previousData === strjson) {
            console.log('変更なし');
            return;
        }

        // document.getElementById('output').textContent = strjson;
        document.getElementById('id_content').value = strjson;
        annot_changed('submit_annots');
        previousData = strjson;
    }
    // エクスポートボタンのクリックイベント
    document.getElementById('exportButton').addEventListener('click', function () {
        save_objects();
    });
    // document.getElementById('importButton').addEventListener('click', function () {
    //     import_notes();
    // });
    // document.getElementById('inspectButton').addEventListener('click', function () {
    //     inspect_selected();
    // });
}
// 画像の読み込み後に初期描画
image.onload = () => {
    image_onload();
    import_notes();
    // resizeCanvas();
};
// 画像がキャッシュ済みの場合の対応
if (image.complete) {
    image.onload();
}

window.onload = () => {
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