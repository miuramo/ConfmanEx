<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BbController;
use App\Http\Controllers\BbMesController;
use App\Http\Controllers\EnqueteAnswerController;
use App\Http\Controllers\EnqueteController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MailTemplateController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevConflictController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\SubmitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewpointController;
use App\Http\Controllers\VoteController;
use App\Models\RevConflict;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('guesttop');
})->name('guesttop');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/file/favicon', [FileController::class, 'favicon'])->name('file.favicon');

//表彰状作成用のJSON
Route::get('awards/json_booth_title_author/{key?}/{readable?}', [SubmitController::class, 'json_bta'])->name('pub.json_booth_title_author');
Route::get('json_review/{cat}/{key?}', [SubmitController::class, 'json_review'])->name('pub.json_review');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 順番をまちがえると、files/{file} の{file}に delall や test がマッチしちゃう。その結果、FileController.show(delall)になっちゃう。
    Route::delete('/file/delall', [FileController::class, 'delall'])->name('file.delall');
    Route::get('/file/adminlock', [FileController::class, 'adminlock'])->name('file.adminlock'); //ロック状態の変更(file)
    Route::post('/file/adminlock', [FileController::class, 'adminlock'])->name('file.adminlock'); //ロック状態の変更(file)
    Route::get('/file/altimgshow/{file}/{hash?}', [FileController::class, 'altimgshow'])->name('file.altimgshow');
    Route::get('/file/pdfimages/{file}/{page?}/{hash?}', [FileController::class, 'pdfimages'])->name('file.pdfimages')->where('page', '([0-9]+|)');
    Route::get('/file/pdftext/{file}', [FileController::class, 'pdftext'])->name('file.pdftext')->where('file', '([0-9]+|)');
    Route::resource('file', FileController::class);
    Route::get('/file/{file}/show/{hash?}', [FileController::class, 'show'])->name('file.showhash')->where('file', '([0-9]+|)');
    Route::delete('/file/{file}/abandon', [FileController::class, 'abandon'])->name('file.abandon')->where('file', '([0-9]+|)'); // 予稿集掲載対象から外す。

    Route::get('/paper/adminlock', [PaperController::class, 'adminlock'])->name('paper.adminlock'); //ロック状態の変更(paper) 順番が大事。
    Route::post('/paper/adminlock', [PaperController::class, 'adminlock'])->name('paper.adminlock'); //ロック状態の変更(paper)
    Route::resource('paper', PaperController::class);
    Route::get('/paper/{paper}/headimgshow/{file?}', [PaperController::class, 'headimgshow'])->name('paper.headimgshow');
    Route::get('/paper/{paper}/filelist', [PaperController::class, 'filelist'])->name('paper.filelist');
    Route::get('/paper/{paper}/sendsubmitted', [PaperController::class, 'sendSubmitted'])->name('paper.sendsubmitted');
    Route::put('/paper/{paper}/update_authorlist', [PaperController::class, 'update_authorlist'])->name('paper.update_authorlist');

    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile.edit');
    //アンケート回答
    Route::resource('enq', EnqueteController::class); // ここはenq.index, enq.store 等。
    Route::get('/enq/{enq}/answers', [EnqueteController::class, 'answers'])->name('enq.answers');
    Route::get('/enq_enqitmsetting', [EnqueteController::class, 'enqitmsetting'])->name('enq.enqitmsetting');
    Route::get('/enq_maptoroles', [EnqueteController::class, 'map_to_roles'])->name('enq.maptoroles');
    Route::post('/enq_maptoroles', [EnqueteController::class, 'map_to_roles'])->name('enq.maptoroles');
    Route::post('/enq_manualset', [EnqueteAnswerController::class, 'manualset'])->name('enq.manualset'); // マニュアル設定
    Route::get('/enq/{enq}/preview', [EnqueteController::class, 'edit_dummy'])->name('enq.preview');

    Route::get('/paper/{paper}/enq/{enq}/edit', [EnqueteController::class, 'edit'])->name('enquete.pageedit'); //インラインではなく個別のpageで表示
    Route::get('/paper/{paper}/enq/{enq}', [EnqueteController::class, 'show'])->name('enquete.pageview');
    Route::put('/paper/{paper}/enq/{enq}', [EnqueteController::class, 'update'])->name('enquete.update');
    //査読結果
    Route::get('/paper/{paper}/review/{token}', [PaperController::class, 'review'])->name('paper.review'); // 著者に返る査読結果
    //ドラッグ範囲選択
    Route::get('/paper/{paper}/dt', [PaperController::class, 'dragontext'])->name('paper.dragontext');
    Route::post('/paper/{paper}/dtpost', [PaperController::class, 'dragontextpost'])->name('paper.dragontextpost');
    Route::post('/paper/{paper}/fileadopt', [PaperController::class, 'fileadopt'])->name('paper.fileadopt'); // 出版が、投稿ファイルをマニュアルで採用する


    // 利害表明
    Route::get('/review/conflict/{cat}', [ReviewController::class, 'conflict'])->name('review.conflict');
    Route::post('/paper/{paper}/conflictupdate', [RevConflictController::class, 'update'])->name('revconflict.update');
    // 査読フォーム
    Route::get('/review/{review}/edit', [ReviewController::class, 'edit'])->name('review.edit');
    Route::put('/review/{review}', [ReviewController::class, 'update'])->name('review.update'); //査読フォームの変更を受けとる
    Route::get('/review/{review}', [ReviewController::class, 'show'])->name('review.show');
    Route::get('/review/pubkey/{review}/{token}', [ReviewController::class, 'pubshow'])->name('review.pubshow'); // 査読者同士の相互参照用

    Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
    Route::get('/review/indexcat/{cat}', [ReviewController::class, 'indexcat'])->name('review.indexcat');
    Route::get('/review_downzip/{cat}', [ReviewController::class, 'zipdownload_for_rev'])->name('review.downzip');
    // Route::resource('review', ReviewController::class);
    // put /review/{review} -> review.update
    // get review.index で仮に作成
    Route::get('/review/{cat}/edit_dummy/{ismeta}', [ReviewController::class, 'edit_dummy'])->name('review.edit_dummy');

    // admin
    Route::get('/admin_dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin_disable_email', [AdminController::class, 'disable_email'])->name('admin.disable_email');
    Route::get('/admin_paperlist', [AdminController::class, 'paperlist'])->name('admin.paperlist');
    Route::post('/admin_paperlist', [AdminController::class, 'paperlist'])->name('admin.paperlist');
    Route::get('/admin_deletepaper/{cat}', [AdminController::class, 'deletepaper'])->name('admin.deletepaper');          // PC Top
    Route::post('/admin_deletepaper/{cat}', [AdminController::class, 'deletepaper'])->name('admin.deletepaper');          // PC Top
    Route::get('/admin_timestamp/{cat}', [AdminController::class, 'timestamp'])->name('admin.timestamp');          // PC Top
    Route::post('/admin_zipf', [AdminController::class, 'zipdownload'])->name('admin.zipdownload');
    Route::post('/admin_zipds', [AdminController::class, 'zipdownloadstream'])->name('admin.zipstream');
    Route::get('/admin_paperlist_excel', [AdminController::class, 'paperlist_excel'])->name('admin.paperlist_excel');
    Route::get('/admin_hiroba_excel', [AdminController::class, 'hiroba_excel'])->name('admin.hiroba_excel');
    Route::get('/admin_filelist', [AdminController::class, 'filelist'])->name('admin.filelist');

    Route::get('/role/{role}/top', [RoleController::class, 'top'])->name('role.top');
    // Route::get('/role/{role}/pc', [RoleController::class, 'top'])->name('role.pc'); //本当はrole.topがあればよいのだが、navigationをactiveにするため...
    // Route::get('/role/{role}/pub', [RoleController::class, 'top'])->name('role.pub'); //本当はrole.topがあればよいのだが、navigationをactiveにするため...
    Route::get('/role/{role}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/role/{role}/edit', [RoleController::class, 'editpost'])->name('role.editpost');
    Route::delete('/role/{role}/leave/{user}', [RoleController::class, 'leave'])->name('role.leave');
    // 査読割り当て
    Route::get('/role/{role}/revassign/{cat}', [RoleController::class, 'revassign'])->name('role.revassign');
    Route::post('/role/{role}/revassign/{cat}', [RoleController::class, 'revassignpost'])->name('role.revassignpost');
    // Bidding結果のExcel
    Route::get('/role/{role}/revassign_excel/{cat}', [RoleController::class, 'revassign_excel'])->name('role.revassign_excel');
    // 査読結果
    Route::get('/reviewresult/{cat}', [ReviewController::class, 'result'])->name('review.result');
    Route::post('/reviewresult/{cat}', [ReviewController::class, 'resultpost'])->name('review.resultpost');
    Route::get('/reviewcomment/{cat}', [ReviewController::class, 'comment'])->name('review.comment'); // ?excel=dl でExcel
    Route::get('/reviewcomment_scoreonly/{cat}', [ReviewController::class, 'comment_scoreonly'])->name('review.comment_scoreonly'); // ?excel=dl でExcel
    Route::get('/reviewcomment/cat/{cat}/paper/{paper}/{token}', [ReviewController::class, 'comment_paper'])->name('review.commentpaper'); //判定会議で見る用

    // 別カテゴリでの採否を追加
    Route::get('addsubmit', [SubmitController::class, 'addsubmit'])->name('pub.addsubmit');
    Route::post('addsubmit', [SubmitController::class, 'addsubmit'])->name('pub.addsubmit');

    // 出版担当
    Route::get('pub_accstatus', [SubmitController::class, 'accstatus'])->name('pub.accstatus');
    Route::get('pub_accstatusgraph', [SubmitController::class, 'accstatusgraph'])->name('pub.accstatusgraph');
    Route::get('pub/{cat}/booth', [SubmitController::class, 'booth'])->name('pub.booth');
    Route::post('pub/{cat}/booth', [SubmitController::class, 'booth'])->name('pub.booth');
    Route::get('pub/{cat}/boothtxt', [SubmitController::class, 'boothtxt'])->name('pub.boothtxt');
    Route::post('pub/{cat}/boothtxt', [SubmitController::class, 'boothtxt'])->name('pub.boothtxt');
    Route::post('pub_zipf', [SubmitController::class, 'zipdownload'])->name('pub.zipdownload');
    Route::get('pub/{cat}/bibinfochk', [SubmitController::class, 'bibinfochk'])->name('pub.bibinfochk'); //書誌情報の確認と修正
    Route::post('pub/update_maydirty', [SubmitController::class, 'update_maydirty'])->name('pub.update_maydirty');// MayDirtyをリセット
    Route::get('pub/{cat}/bibinfo/{abbr?}', [SubmitController::class, 'bibinfo'])->name('pub.bibinfo'); //書誌情報の表示 (abbrをtrueにすると同一所属を省略)
    Route::get('pub/{cat}/fileinfochk', [SubmitController::class, 'fileinfochk'])->name('pub.fileinfochk'); // カメラレディのタイムスタンプ確認
    Route::get('pub/{paper}/paperfile', [SubmitController::class, 'paperfile'])->name('pub.paperfile'); // 論文ごとに、どのファイルを採用しているか？を表示する

    // メール雛形
    Route::resource('mt', MailTemplateController::class);
    Route::post('mt/bundle', [MailTemplateController::class, 'bundle'])->name('mt.bundle');
    Route::post('mt/import', [MailTemplateController::class, 'import'])->name('mt.import');

    Route::get('/admin_crud', [AdminController::class, 'crud'])->name('admin.crud');
    Route::get('/admin_crudcopy', [AdminController::class, 'crudcopy'])->name('admin.crudcopy');
    Route::get('/admin_cruddelete', [AdminController::class, 'cruddelete'])->name('admin.cruddelete');
    Route::get('/admin_crudnew', [AdminController::class, 'crudnew'])->name('admin.crudnew');
    Route::get('/admin_crudtruncate', [AdminController::class, 'crudtruncate'])->name('admin.crudtruncate');
    Route::post('/admin_crud', [AdminController::class, 'crud'])->name('admin.crudpost');
    Route::post('/admin_crudpost', [AdminController::class, 'crudpost'])->name('admin.crudpost');
    Route::get('/admin_catsetting', [AdminController::class, 'catsetting'])->name('admin.catsetting');
    Route::get('/admin_chkexefiles', [AdminController::class, 'check_exefiles'])->name('admin.chkexefiles');
    
    Route::get('/admin_resetpaper', [AdminController::class, 'resetpaper'])->name('admin.resetpaper');             // Danger Zone
    Route::get('/admin_resetaccesslog', [AdminController::class, 'resetaccesslog'])->name('admin.resetaccesslog'); // Danger Zone
    Route::get('/admin_resetbidding', [AdminController::class, 'resetbidding'])->name('admin.resetbidding');       // Danger Zone
    Route::get('/admin_forcedelete', [AdminController::class, 'forcedelete'])->name('admin.forcedelete');      // Danger Zone
    // 査読結果の選択的削除 (Score)
    Route::get('/resetscore', [ScoreController::class, 'resetscore'])->name('score.resetscore');       // Danger Zone
    Route::post('/resetscore', [ScoreController::class, 'resetscore'])->name('score.resetscore');      // Danger Zone
    // アンケートの選択的削除 (EnqueteAnswer)
    Route::get('/resetenqans', [EnqueteController::class, 'resetenqans'])->name('enq.resetenqans');    // Danger Zone
    Route::post('/resetenqans', [EnqueteController::class, 'resetenqans'])->name('enq.resetenqans');   // Danger Zone

    Route::get('/admin_passdumpsql', [AdminController::class, 'passdumpsql'])->name('admin.passdumpsql');

    Route::get('/man_rebuildpdf', [ManagerController::class, 'rebuildPDFThumb'])->name('admin.rebuildpdf');
    Route::get('/man_mailtest', [ManagerController::class, 'mailtest'])->name('admin.mailtest');
    Route::get('/man_9wtest', [ManagerController::class, 'test9w'])->name('admin.test9w');
    Route::get('/man_paperauthorhead', [ManagerController::class, 'paperauthorhead'])->name('admin.paperauthorhead');
    Route::post('/man_paperauthorhead', [ManagerController::class, 'paperauthorhead'])->name('admin.paperauthorhead');
    // 切り取った画像の一覧
    Route::get('/man_paperlist_headimg', [ManagerController::class, 'paperlist_headimg'])->name('admin.paperlist_headimg');
    Route::get('/man_paperlist_headimg_recrop', [ManagerController::class, 'paperlist_headimg_recrop'])->name('admin.paperlist_headimg_recrop');
    // Route::post('/admin_paperlist_headimg', [AdminController::class, 'paperlist_headimg'])->name('admin.paperlist_headimg');
    Route::get('/revcon', [RevConflictController::class, 'index'])->name('revcon.index');
    Route::get('/revcon/stat', [RevConflictController::class, 'stat'])->name('revcon.stat');
    Route::get('/revcon/revstat/{role?}', [RevConflictController::class, 'revstat'])->name('revcon.revstat'); // 査読割り当てStat
    Route::get('/revcon/revstatus', [RevConflictController::class, 'revstatus'])->name('revcon.revstatus');
    Route::get('/revcon/revname/{cat}', [RevConflictController::class, 'revname'])->name('revcon.revname'); // 査読者の名前
    Route::get('/revcon/notdownloaded', [RevConflictController::class, 'notdownloaded'])->name('revcon.notdownloaded');
    Route::get('/revcon/norev', [RevConflictController::class, 'norev'])->name('revcon.norev');
    
    // Export and Import
    Route::get('viewpoints/export', [ViewpointController::class, 'export'])->name('viewpoint.export');
    Route::post('viewpoints/import', [ViewpointController::class, 'import'])->name('viewpoint.import');
    Route::get('viewpoints/itmsetting', [ViewpointController::class, 'itmsetting'])->name('viewpoint.itmsetting');

    // 掲示板
    Route::get('bb', [BbController::class, 'index'])->name('bb.index');
    Route::get('bb_for_pub', [BbController::class, 'index_for_pub'])->name('bb.index_for_pub');
    Route::post('bb', [BbController::class, 'store'])->name('bb.createnew'); // まとめて作成
    Route::delete('bb', [BbController::class, 'destroy'])->name('bb.destroy'); // 全削除
    Route::delete('bb_destroy_bytype', [BbController::class, 'destroy_bytype'])->name('bb.destroy_bytype'); // 種別ごとに削除
    Route::get('bb/{bb}/{key}', [BbController::class, 'show'])->name('bb.show')->where('key', '([0-9A-Za-z]+)');
    Route::post('bb/{bb}/{key}', [BbMesController::class, 'store'])->name('bb.store')->where('key', '([0-9A-Za-z]+)');
    Route::post('bb/{bb}/{key}/adopt', [BbMesController::class, 'adopt'])->name('bb.adopt')->where('key', '([0-9A-Za-z]+)');

    // 参加登録
    Route::resource('part', ParticipantController::class);
    // Route::get('part/create', [ParticipantController::class, 'create'])->name('part.create');

    // 投票結果
    Route::get('down_voteanswers', [VoteController::class, 'download_answers'])->name('vote.download_answers');
    Route::get('resetall_voteanswers/{isclose}', [VoteController::class, 'resetall'])->name('vote.resetall'); // すべて削除

});

// 投票
Route::get('vote', [VoteController::class, 'index'])->name('vote.index');
Route::post('vote', [VoteController::class, 'index'])->name('vote.index');
Route::get('vote/{vote}/vote', [VoteController::class, 'vote'])->name('vote.vote');
Route::post('vote/{vote}/vote', [VoteController::class, 'vote'])->name('vote.vote');

Route::middleware('guest')->group(function () {
    Route::get('/entry', [UserController::class, 'entry0'])->name('entry0');
    Route::post('/entry', [UserController::class, 'entry'])->name('entry');
    Route::get('/validate/{key}', [UserController::class, 'validate_email'])->name('validate_email');
});

require __DIR__ . '/auth.php';

require __DIR__ . '/annotpaper.php';
