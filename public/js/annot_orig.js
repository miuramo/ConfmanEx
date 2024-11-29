



window.onload = () => {
    const image = document.getElementById('targetImage');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');   

    // アノテーションのオリジナルデータ（画像の元サイズに基づく）
    const annotations = [{
            x: 50,
            y: 250,
            width: 200,
            height: 100,
            text: 'サンプルテキスト',
            bgcolor: [255,0,0],
        },
        {
            x: 150,
            y: 450,
            width: 200,
            height: 100,
            text: 'サンプルテキスト',
            bgcolor: [0,255,0],
        },
        {
            x: 50,
            y: 650,
            width: 200,
            height: 100,
            text: 'サンプルテキスト',
            bgcolor: [0,0,255],
        },
    ];
    let hoveredAnnotation = null; // 現在ホバー中のアノテーション
    let scale = 1; // スケール（リサイズ対応用）
    let draggedAnnotation = null; // ドラッグ中のアノテーション
    let offsetX = 0; // マウスとアノテーションのオフセット
    let offsetY = 0;

    function drawCanvas() {
        // ウィンドウ幅に合わせて画像をリサイズ
        const windowWidth = window.innerWidth;
        const scale = windowWidth / image.naturalWidth; // スケール計算

        // キャンバスのサイズを設定
        canvas.width = windowWidth;
        canvas.height = image.naturalHeight * scale;

        // 画像を描画
        ctx.clearRect(0, 0, canvas.width, canvas.height); // キャンバスをクリア
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

        // アノテーションを描画
        annotations.forEach(annotation => {
            const scaledX = annotation.x * scale;
            const scaledY = annotation.y * scale;
            const scaledWidth = annotation.width * scale;
            const scaledHeight = annotation.height * scale;

            // ホバー判定
            const isHovered = hoveredAnnotation === annotation;

            // 色を設定（ホバー時に色を薄くする）
            ctx.fillStyle = isHovered ? 'rgba('+annotation.bgcolor+', 0.3)' : 'rgba('+annotation.bgcolor+', 0.2)';
            ctx.fillRect(scaledX, scaledY, scaledWidth, scaledHeight);

            // テキスト
            ctx.fillStyle = 'white';
            ctx.font = `${20 * scale}px Arial`; // フォントサイズもスケールに合わせる
            ctx.fillText(annotation.text, scaledX + 10, scaledY + 30 * scale);
        });
    }

    // マウスムーブイベントでホバー判定
    canvas.addEventListener('mousemove', (e) => {
        const mouseX = e.offsetX;
        const mouseY = e.offsetY;

        // アノテーションの範囲内かチェック
        hoveredAnnotation = null; // 初期化
        annotations.forEach(annotation => {
            const scaledX = annotation.x * (window.innerWidth / image.naturalWidth);
            const scaledY = annotation.y * (window.innerWidth / image.naturalWidth);
            const scaledWidth = annotation.width * (window.innerWidth / image.naturalWidth);
            const scaledHeight = annotation.height * (window.innerWidth / image.naturalWidth);

            if (
                mouseX >= scaledX && mouseX <= scaledX + scaledWidth &&
                mouseY >= scaledY && mouseY <= scaledY + scaledHeight
            ) {
                hoveredAnnotation = annotation;
            }
        });
        if (draggedAnnotation) {
            const mouseX = e.offsetX / scale;
            const mouseY = e.offsetY / scale;

            draggedAnnotation.x = mouseX - offsetX;
            draggedAnnotation.y = mouseY - offsetY;
        }
        drawCanvas(); // 再描画
    });

    // マウスイベント処理
    canvas.addEventListener('mousedown', (e) => {
        const mouseX = e.offsetX / scale;
        const mouseY = e.offsetY / scale;

        annotations.forEach(annotation => {
            if (
                mouseX >= annotation.x && mouseX <= annotation.x + annotation.width &&
                mouseY >= annotation.y && mouseY <= annotation.y + annotation.height
            ) {
                draggedAnnotation = annotation;
                offsetX = mouseX - annotation.x;
                offsetY = mouseY - annotation.y;
            }
        });
    });
    canvas.addEventListener('mouseup', () => {
        if (draggedAnnotation) {
            // animateSpring(); // バネアニメーションを開始
        }
        draggedAnnotation = null;
    });

    // 画像の読み込み後に初期描画
    image.onload = () => {
        const windowWidth = window.innerWidth;
        scale = windowWidth / image.naturalWidth;

        canvas.width = windowWidth;
        canvas.height = image.naturalHeight * scale;

        annotations.forEach(annotation => {
            annotation.originalX = annotation.x;
            annotation.originalY = annotation.y;
        });
        drawCanvas();
    };

    // ウィンドウのリサイズイベントで再描画
    // ウィンドウのリサイズイベントでスケール調整
    window.addEventListener('resize', () => {
        const windowWidth = window.innerWidth;
        scale = windowWidth / image.naturalWidth;

        canvas.width = windowWidth;
        canvas.height = image.naturalHeight * scale;

        drawCanvas();
    });

    // 画像がキャッシュ済みの場合の対応
    if (image.complete) {
        image.onload();
    }

    // // 画像が読み込まれた後に処理
    // image.onload = () => {
    //     // キャンバスのサイズを画像に合わせる
    //     canvas.width = image.width;
    //     canvas.height = image.height;

    //     // キャンバスに画像を描画
    //     ctx.drawImage(image, 0, 0);

    //     // 半透明の四角形を描画
    //     ctx.fillStyle = 'rgba(255, 0, 0, 0.5)'; // 赤色、50%の透明度
    //     ctx.fillRect(150, 150, 200, 100); // (x, y, 幅, 高さ)

    //     // テキストを描画
    //     ctx.fillStyle = 'white';
    //     ctx.font = '20px Arial';
    //     ctx.fillText('サンプルテキスト', 160, 200); // (x, y)
    // };

    // // 画像がキャッシュされている場合も処理が実行されるように明示的に呼び出す
    // if (image.complete) {
    //     image.onload();
    // }
};
