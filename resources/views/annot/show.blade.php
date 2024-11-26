<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ $apaper->paper->id_03d() }} {{ $apaper->paper->title }}
        </h2>
    </x-slot>
@section('title', 'AnnotPaper '.$apaper->paper->id_03d())
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    {{-- @for ($page = 1; $page <= $apaper->file->pagenum; $page++)
    <img src="{{ route('file.pdfimages', ['file' => $apaper->file->id, 'page'=>$page, 'hash' => substr($apaper->file->key, 0, 8)]) }}"
    title="page {{$page}}" loading="lazy" class="flex-shrink-0 border">
@endfor --}}

    <!-- 元の画像 -->
    <img src="{{ route('file.pdfimages', ['file' => $apaper->file->id, 'page' => 1, 'hash' => substr($apaper->file->key, 0, 8)]) }}"
        title="page" class="flex-shrink-0 border" id="targetImage" style="display:none;">

    <!-- 描画用のキャンバス -->
    <canvas id="canvas"></canvas>

    <script>
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
                    drawCanvas();
                // drawCanvas(); // 再描画
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

            // バネのアニメーション
            function animateSpring() {
                const stiffness = 0.1; // バネの強さ
                const damping = 0.8; // 減衰（速度を抑える係数）

                let isAnimating = false;

                annotations.forEach(annotation => {
                    if (annotation.x !== annotation.originalX || annotation.y !== annotation.originalY) {
                        isAnimating = true;
                        // バネの力を計算
                        const forceX = (annotation.originalX - annotation.x) * stiffness;
                        const forceY = (annotation.originalY - annotation.y) * stiffness;
                        // 速度にバネの力を加える
                        annotation.velocityX += forceX;
                        annotation.velocityY += forceY;
                        // 減衰
                        annotation.velocityX *= damping;
                        annotation.velocityY *= damping;
                        // 位置を更新
                        annotation.x += annotation.velocityX;
                        annotation.y += annotation.velocityY;
                    }
                });

                if (isAnimating) {
                    drawCanvas();
                    requestAnimationFrame(animateSpring); // 再度アニメーションを呼び出し
                }
            }

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
    </script>

</x-app-layout>
