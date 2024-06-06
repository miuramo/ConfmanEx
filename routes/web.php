<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BbController;
use App\Http\Controllers\BbMesController;
use App\Http\Controllers\EnqueteController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MailTemplateController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevConflictController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubmitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewpointController;
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
Route::get('awards/json_booth_title_author/{key?}', [SubmitController::class, 'json_bta'])->name('pub.json_booth_title_author');

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

    Route::get('/paper/{paper}/enq/{enq}/edit', [EnqueteController::class, 'edit'])->name('enquete.pageedit'); //インラインではなく個別のpageで表示
    Route::get('/paper/{paper}/enq/{enq}', [EnqueteController::class, 'show'])->name('enquete.pageview');
    Route::put('/paper/{paper}/enq/{enq}', [EnqueteController::class, 'update'])->name('enquete.update');
    //査読結果
    Route::get('/paper/{paper}/review', [PaperController::class, 'review'])->name('paper.review');
    //ドラッグ範囲選択
    Route::get('/paper/{paper}/dt', [PaperController::class, 'dragontext'])->name('paper.dragontext');
    Route::post('/paper/{paper}/dtpost', [PaperController::class, 'dragontextpost'])->name('paper.dragontextpost');


    // 利害表明
    Route::get('/review/conflict/{cat}', [ReviewController::class, 'conflict'])->name('review.conflict');
    Route::post('/paper/{paper}/conflictupdate', [RevConflictController::class, 'update'])->name('revconflict.update');
    // 査読フォーム
    Route::get('/review/{review}/edit', [ReviewController::class, 'edit'])->name('review.edit');
    Route::put('/review/{review}', [ReviewController::class, 'update'])->name('review.update');
    Route::get('/review/{review}', [ReviewController::class, 'show'])->name('review.show');
    Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
    Route::get('/review_downzip/{cat}', [ReviewController::class, 'zipdownload_for_rev'])->name('review.downzip');
    // Route::resource('review', ReviewController::class);
    // put /review/{review} -> review.update
    // get review.index で仮に作成

    // admin
    Route::get('/admin_dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin_disable_email', [AdminController::class, 'disable_email'])->name('admin.disable_email');
    Route::get('/admin_paperlist', [AdminController::class, 'paperlist'])->name('admin.paperlist');
    Route::post('/admin_paperlist', [AdminController::class, 'paperlist'])->name('admin.paperlist');
    // Route::get('/admin_zipf', [AdminController::class, 'zipdownload'])->name('admin.zipdownload');
    Route::post('/admin_zipf', [AdminController::class, 'zipdownload'])->name('admin.zipdownload');
    Route::get('/admin_paperlist_excel', [AdminController::class, 'paperlist_excel'])->name('admin.paperlist_excel');
    Route::get('/admin_hiroba_excel', [AdminController::class, 'hiroba_excel'])->name('admin.hiroba_excel');
    Route::get('/admin_filelist', [AdminController::class, 'filelist'])->name('admin.filelist');

    Route::get('/role/{role}/top', [RoleController::class, 'top'])->name('role.top');
    Route::get('/role/{role}/pc', [RoleController::class, 'top'])->name('role.pc'); //本当はrole.topがあればよいのだが、navigationをactiveにするため...
    Route::get('/role/{role}/pub', [RoleController::class, 'top'])->name('role.pub'); //本当はrole.topがあればよいのだが、navigationをactiveにするため...
    Route::get('/role/{role}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/role/{role}/edit', [RoleController::class, 'editpost'])->name('role.editpost');
    Route::delete('/role/{role}/leave/{user}', [RoleController::class, 'leave'])->name('role.leave');
    // 査読割り当て
    Route::get('/role/{role}/revassign/{cat}', [RoleController::class, 'revassign'])->name('role.revassign');
    Route::post('/role/{role}/revassign/{cat}', [RoleController::class, 'revassignpost'])->name('role.revassignpost');
    // 査読結果
    Route::get('/reviewresult/{cat}', [ReviewController::class, 'result'])->name('review.result');
    Route::post('/reviewresult/{cat}', [ReviewController::class, 'resultpost'])->name('review.resultpost');
    Route::get('/reviewcomment/{cat}', [ReviewController::class, 'comment'])->name('review.comment'); // ?excel=dl でExcel

    // 出版担当
    Route::get('pub/{cat}/booth', [SubmitController::class, 'booth'])->name('pub.booth');
    Route::post('pub/{cat}/booth', [SubmitController::class, 'booth'])->name('pub.booth');
    Route::get('pub/{cat}/boothtxt', [SubmitController::class, 'boothtxt'])->name('pub.boothtxt');
    Route::post('pub/{cat}/boothtxt', [SubmitController::class, 'boothtxt'])->name('pub.boothtxt');
    Route::post('pub_zipf', [SubmitController::class, 'zipdownload'])->name('pub.zipdownload');
    Route::get('pub/{cat}/bibinfochk', [SubmitController::class, 'bibinfochk'])->name('pub.bibinfochk'); //書誌情報の確認と修正
    Route::get('pub/{cat}/bibinfo/{abbr?}', [SubmitController::class, 'bibinfo'])->name('pub.bibinfo'); //書誌情報の表示 (abbrをtrueにすると同一所属を省略)

    // メール雛形
    Route::resource('mt', MailTemplateController::class);
    Route::post('mt/bundle', [MailTemplateController::class, 'bundle'])->name('mt.bundle');

    Route::get('/admin_crud', [AdminController::class, 'crud'])->name('admin.crud');
    Route::get('/admin_crudcopy', [AdminController::class, 'crudcopy'])->name('admin.crudcopy');
    Route::get('/admin_cruddelete', [AdminController::class, 'cruddelete'])->name('admin.cruddelete');
    Route::get('/admin_crudnew', [AdminController::class, 'crudnew'])->name('admin.crudnew');
    Route::post('/admin_crud', [AdminController::class, 'crud'])->name('admin.crudpost');
    Route::post('/admin_crudpost', [AdminController::class, 'crudpost'])->name('admin.crudpost');
    Route::get('/admin_catsetting', [AdminController::class, 'catsetting'])->name('admin.catsetting');
    Route::get('/admin_rebuildpdf', [AdminController::class, 'rebuildPDFThumb'])->name('admin.rebuildpdf');
    Route::get('/admin_mailtest', [AdminController::class, 'mailtest'])->name('admin.mailtest');
    Route::get('/admin_9wtest', [AdminController::class, 'test9w'])->name('admin.test9w');
    Route::get('/admin_resetpaper', [AdminController::class, 'resetpaper'])->name('admin.resetpaper');
    Route::get('/admin_chkexefiles', [AdminController::class, 'check_exefiles'])->name('admin.chkexefiles');

    // Export and Import
    Route::get('viewpoints/export', [ViewpointController::class, 'export'])->name('viewpoint.export');
    Route::post('viewpoints/import', [ViewpointController::class, 'import'])->name('viewpoint.import');

    // 掲示板
    Route::get('bb', [BbController::class, 'index'])->name('bb.index');
    Route::post('bb', [BbController::class, 'store'])->name('bb.createnew'); // まとめて作成
    Route::delete('bb', [BbController::class, 'destroy'])->name('bb.destroy'); // 全削除
    Route::get('bb/{bb}/{key}', [BbController::class, 'show'])->name('bb.show')->where('key', '([0-9A-Za-z]+)');
    Route::post('bb/{bb}/{key}', [BbMesController::class, 'store'])->name('bb.store')->where('key', '([0-9A-Za-z]+)');

    // 参加登録
    Route::resource('part', ParticipantController::class);
    // Route::get('part/create', [ParticipantController::class, 'create'])->name('part.create');

});

// Route::middleware('role.dyn')->group(function(){ });

Route::middleware('guest')->group(function () {
    Route::get('/entry', [UserController::class, 'entry0'])->name('entry0');
    Route::post('/entry', [UserController::class, 'entry'])->name('entry');
    Route::get('/validate/{key}', [UserController::class, 'validate_email'])->name('validate_email');
});

require __DIR__ . '/auth.php';
