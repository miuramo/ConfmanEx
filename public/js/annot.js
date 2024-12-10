// Fabric.js の Canvas を初期化
const canvas = new fabric.Canvas('canvas');
const image = document.getElementById('targetImage');
let isDrawingMode = false;
let imgInstance = null;

let scale = 1;
let lastScale = 1;
let isTextEditing = false;

// localStorage から色を読み込む
let textColor = localStorage.getItem('textColor') || '#0033ff';
let rectColor = localStorage.getItem('rectColor') || '#ffffaa';

const textCP = document.getElementById('ID_textColor');
const rectCP = document.getElementById('ID_rectColor');

// コピー用の変数
// let copiedObject = null;

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
        obj.scaleToWidth(objWidth); // オブジェクトをキャンバス幅に合わせて調整
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
        obj.setCoords(); // バウンディングボックスを再計算
    });
    canvas.renderAll();
    lastWidth = window.innerWidth;
    lastScale = scale;
}
// console.log('before w',obj.width, 'h',obj.height, 'left',obj.left,'top', obj.top, 'sX', obj.scaleX, 'sY', obj.scaleY);

function fix_scale_objects(lastS, curS) {
    canvas.getObjects().forEach(obj => {
        obj.excludeFromExport = (obj.user_id != user_id);
        let objWidth = obj.width * curS;
        const origSX = obj.scaleX;
        const origSY = obj.scaleY;
        obj.scaleToWidth(objWidth); // オブジェクトをキャンバス幅に合わせて調整
        obj.left = (obj.left * curS) / lastS;
        obj.top = (obj.top * curS) / lastS;
        obj.scaleX = origSX * curS / lastS;
        obj.scaleY = origSY * curS / lastS;
        obj.setCoords(); // バウンディングボックスを再計算
    });
}

function get_comment_json() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // データの取得が成功した場合
                var responseData = xhr.responseText;
                notes = JSON.parse(responseData);
                // console.log(notes);
            } else {
                // データの取得が失敗した場合
                console.error('Request failed:', xhr.status);
            }
        }
    };
    xhr.open('GET', '/annot/' + annotpaper_id + '/comment_json/' + page, false);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // これがないとAjax判定にならない
    xhr.send();
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

function convertToRgba(hex, alpha) {
    // 16進数カラーコードを正規表現で検証
    const isValidHex = /^#([0-9A-Fa-f]{6})$/.test(hex);
    if (!isValidHex) {
        throw new Error("Invalid HEX color format. Use #rrggbb.");
    }
    // rr, gg, bbを抽出
    const r = parseInt(hex.slice(1, 3), 16); // 赤成分
    const g = parseInt(hex.slice(3, 5), 16); // 緑成分
    const b = parseInt(hex.slice(5, 7), 16); // 青成分
    // アルファ値の範囲をチェック
    if (alpha < 0 || alpha > 1) {
        throw new Error("Alpha value must be between 0 and 1.");
    }
    // RGBA文字列を作成
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}


function new_text(px, py) {
    const newTxt = new fabric.IText('ここをクリックして編集', {
        left: px,
        top: py,
        fontSize: 25,
        fill: convertToRgba(textColor, 0.9),
        scaleX: scale,
        scaleY: scale,
        user_id: user_id,
        name: username,
        affil: useraffil,
    });
    canvas.add(newTxt);
}
function new_rect(px, py) {
    const newRect = new fabric.Rect({
        left: 200,
        top: 50,
        fill: convertToRgba(rectColor, 0.3),
        width: 200,
        height: 100,
        hasBorders: true, // 境界線を表示
        hasControls: true, // リサイズコントロールを表示
        user_id: user_id,
        name: username,
        affil: useraffil,
    });
    canvas.add(newRect);
}


function image_onload() {
    // color picker の初期化
    textCP.addEventListener('input', (event) => {
        textColor = event.target.value;
        localStorage.setItem('textColor', textColor);
        if (canvas.getActiveObject() && canvas.getActiveObject().type === 'i-text') {
            canvas.getActiveObject().set({ fill: convertToRgba(textColor, 0.9) });
            canvas.renderAll();
        }
    });
    rectCP.addEventListener('input', (event) => {
        rectColor = event.target.value;
        localStorage.setItem('rectColor', rectColor);
        if (canvas.getActiveObject() && canvas.getActiveObject().type === 'rect') {
            canvas.getActiveObject().set({ fill: convertToRgba(rectColor, 0.3) });
            canvas.renderAll();
        }
    });

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
            tooltip.textContent = target.name + " (" + target.affil + ")" || 'Tooltip';
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
            new_text(e.pointer.x, e.pointer.y);
        }
    }

    // キャンバス上でキーが押されたときのイベント
    document.onkeydown = function (e) {
        // もしDeleteキーが押されたら、選択中のオブジェクトを削除
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (!isTextEditing) {
                console.log('Deleteキーが押されました');
                const activeObjects = canvas.getActiveObjects();

                if (activeObjects.length) {
                    // 選択されたすべてのオブジェクトを削除
                    activeObjects.forEach(obj => canvas.remove(obj));

                    // 選択状態をクリア
                    canvas.discardActiveObject();
                    canvas.requestRenderAll();
                }
            }
        }
        // CTRL+C でコピー
        if ((e.ctrlKey || e.metaKey || e.altKey) && e.key === 'c') {
        }
        // CTRL+V で貼り付け
        if ((e.ctrlKey || e.metaKey || e.altKey) && e.key === 'v') {
        }
    };


    // フリーハンド描画モードの切り替え
    // document.getElementById('drawModeButton').addEventListener('click', function () {
    //     isDrawingMode = !isDrawingMode;
    //     canvas.isDrawingMode = isDrawingMode;
    //     this.textContent = isDrawingMode ? '描画モード終了' : 'フリーハンド描画モード';
    // });
    document.getElementById('addTextButton').addEventListener('click', function () {
        console.log(textColor);
        new_text(200, 50);
    });
    document.getElementById('addRectButton').addEventListener('click', function () {
        // 半透明の四角形を描画
        new_rect();
    });

    let previousData = null;
    function save_objects() {
        // 横幅を1000に一度リサイズ
        const tmpscale = 1000 / image.naturalWidth;
        fix_scale_objects(lastScale, tmpscale);

        // JSONを一旦オブジェクトに変換
        let json = canvas.toJSON();
        // windowWidthを JSON に追加
        json.windowWidth = 1000; //0.8058017727639001;
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
    document.getElementById('saveButton').addEventListener('click', function () {
        canvas.discardActiveObject();
        canvas.renderAll();

        save_objects();
    });
    document.getElementById('loadButton').addEventListener('click', function () {
        get_comment_json();
        import_notes();
    });
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
    textCP.value = textColor;
    rectCP.value = rectColor;
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
}

function flashSuccess() {
    document.querySelector('#saveButton').classList.add('flash-success');
    setTimeout(() => {
        document.querySelector('#saveButton').classList.remove('flash-success');
    }, 1300); // フラッシュの持続時間を1.3秒に設定
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
            console.log("success " + new Date());
            // main要素を緑色に1秒間フラッシュさせる
            flashSuccess();
        },
        error: function (xhr, textStatus, error) {
            console.log(textStatus);
            console.log(error);
        }
    });
}