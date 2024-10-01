<?php

namespace App\Http\Controllers;

use App\Exports\ReviewCommentExportFromView;
use App\Exports\ReviewResultExportFromView;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Bidding;
use App\Models\Category;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Score;
use App\Models\Submit;
use App\Models\User;
use App\Models\Viewpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class ReviewController extends Controller
{

    public function conflict(int $cat_id)
    {
        if (!auth()->user()->can('role', 'reviewer')) return abort(403);
        // 自著分、共著分については、さきにRevConflictを作成しておく
        $author_papers = Paper::where("owner", auth()->user()->id)->get();
        foreach ($author_papers as $p) {
            $revcon = RevConflict::firstOrCreate([
                'user_id' => auth()->user()->id,
                'paper_id' => $p->id,
                'bidding_id' => 1, // 1が共著者利害
            ]);
        }
        $user = User::find(auth()->user()->id);
        foreach ($user->coauthor_papers() as $p) {
            $revcon = RevConflict::firstOrCreate([
                'user_id' => auth()->user()->id,
                'paper_id' => $p->id,
                'bidding_id' => 1, // 1が共著者利害
            ]);
        }

        $papers = Paper::where('category_id', $cat_id)->whereNotNull('pdf_file_id')->orderBy('id')->get();
        $revconfs = RevConflict::with('bidding')->where('user_id', auth()->user()->id)->get();
        $revcon = [];
        $revconname = [];
        foreach ($revconfs as $rv) {
            $revcon[$rv->paper_id] = $rv->bidding_id;
            $revconname[$rv->paper_id] = $rv->bidding->name;
        }
        $revcondiv = Bidding::revcondiv();
        return view("review.conflict")->with(compact("papers", "cat_id", "revcon", "revconname", "revcondiv"));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('role', 'reviewer')) return abort(403);
        // さっそく、読み取る
        $reviews = Review::where("user_id", auth()->user()->id)->orderBy("category_id")->orderBy("paper_id")->get();
        $revbycat = [];
        $cats = Category::all();
        foreach ($cats as $cat) {
            $revbycat[$cat->id] = Review::where("user_id", auth()->user()->id)->where("category_id", $cat->id)->orderBy("paper_id")->get();
        }
        // 査読掲示板URLの生成は、index のなかで、必要があればrevからcomponentをよびだす

        return view("review.index")->with(compact("reviews", "revbycat", "cats"));
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function indexcat($cat_id)
    {
        if (!auth()->user()->can('role', 'reviewer')) return abort(403);
        if (!is_numeric($cat_id)) return abort(404);
        $reviews = Review::where("user_id", auth()->user()->id)->where("category_id", $cat_id)->orderBy("paper_id")->get();
        // $revbycat = [];
        $cat = Category::find($cat_id);
        // foreach ($cats as $cat) {
        //     $revbycat[$cat->id] = Review::where("user_id", auth()->user()->id)->where("category_id", $cat->id)->orderBy("paper_id")->get();
        // }
        // 査読掲示板URLの生成は、index のなかで、必要があればrevからcomponentをよびだす

        return view("review.indexcat")->with(compact("reviews", "cat", "cat_id"));
        //
    }

    /**
     * 査読結果 reviewresult
     */
    public function result(Category $cat)
    {
        $revlist = Category::select('id', 'status__revlist_on')
            ->get()
            ->pluck('status__revlist_on', 'id')
            ->toArray();

        if (!auth()->user()->can('role', 'pc')) {
            if (auth()->user()->can('role_any', 'reviewer|metareviewer') && $revlist[$cat->id]) {
                // OK , pass
            } else {
                return abort(403, 'review result');
            }
        }
        // Submitの一覧を返す
        $subs = Submit::with('paper')->where('category_id', $cat->id)->orderBy('score', 'desc')->get();
        $cat_id = $cat->id;
        return view("review.result")->with(compact("subs", "cat_id", "cat"));
    }
    public function resultpost(Request $req, Category $cat)
    {
        if (!auth()->user()->can('role', 'pc')) return abort(403);
        if ($req->has("action") && $req->input("action") == "excel") {
            return Excel::download(new ReviewResultExportFromView($cat), "reviewresult_{$cat->id}.xlsx");
        }
        $uprev = $req->input("uprev");
        $all = $req->all();
        foreach ($all as $k => $v) {
            if ($v == 'on') {
                DB::transaction(function () use ($k, $uprev) {
                    $subid = explode("_", $k)[1];
                    $sub = Submit::find($subid);
                    $sub->accept_id = $uprev;
                    $sub->save();
                });
            }
        }
        return redirect()->route("review.result", ["cat" => $cat]);
    }

    /**
     * 査読コメント for PC  name('review.comment')
     * scoreonly=0 だと、コメントも表示するが、横に長くなる。
     */
    public function comment(Request $req, Category $cat, $scoreonly = 0)
    {
        if (!Category::isShowReview($cat->id)) {
            return abort(403, 'review comment');
        }

        Score::updateAllScoreStat();
        if ($req->has("excel")) {
            return Excel::download(new ReviewCommentExportFromView($cat, $scoreonly), "reviewcomments_{$cat->id}.xlsx");
        }
        // Submitの一覧を返す
        $subs = Submit::with('paper')->where('category_id', $cat->id)->orderBy('score', 'desc')->get();
        $cat_id = $cat->id;
        return view("review.pccomment")->with(compact("subs", "cat_id", "cat", "scoreonly"));
    }
    public function comment_scoreonly(Request $req, Category $cat)
    {
        if (!Category::isShowReview($cat->id)) {
            return abort(403, 'review comment');
        }

        return $this->comment($req, $cat, 1);
    }
    // 査読会議でみる、詳細
    public function comment_paper(Category $cat, Paper $paper, string $token)
    {
        if (!Category::isShowReview($cat->id)) {
            return abort(403, 'review comment');
        }
        $rigais = RevConflict::arr_pu_rigai();
        if ($rigais[$paper->id][auth()->id()] < 3) {
            return abort(403, 'authors conflict');
        }
        $sub = Submit::where('paper_id', $paper->id)
            ->where('category_id', $cat->id)
            ->first();
        if ($sub->token() != $token) return abort(403, "TOKEN ERROR FOR SUBMIT");
        $cat_id = $cat->id;
        return view("review.commentpaper")->with(compact("sub", "cat_id", "cat", "paper"));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        //
    }

    /**
     * 査読者自身の参照用
     */
    public function show(Review $review)
    {
        if (!auth()->user()->can('role_any', 'pc|reviewer',)) return abort(403);
        if ($review->user_id != auth()->id()) return abort(403, "THIS IS NOT YOUR REVIEW");

        if ($review->ismeta) {
            $viewpoints = Viewpoint::where("category_id", $review->category_id)->where("formeta", 1)->orderBy("orderint")->get();
        } else {
            $viewpoints = Viewpoint::where("category_id", $review->category_id)->where("forrev", 1)->orderBy("orderint")->get();
        }
        // 既存回答
        $scoreobj = Score::where('review_id', $review->id)->get();
        $scores = [];
        foreach ($scoreobj as $ea) {
            $scores[$ea->viewpoint_id] = $ea;
        }
        return view("review.show")->with(compact("review", "viewpoints", "scores"));
        //
    }

    /**
     * 査読者同士の参照用
     */
    public function pubshow(Review $review, string $token)
    {
        if (!auth()->user()->can('role_any', 'pc|reviewer',)) return abort(403);
        if ($review->token() != $token) return abort(403, "Review Browse TOKEN ERROR");

        if ($review->ismeta) {
            $viewpoints = Viewpoint::where("category_id", $review->category_id)->where("formeta", 1)->orderBy("orderint")->get();
        } else {
            $viewpoints = Viewpoint::where("category_id", $review->category_id)->where("forrev", 1)->orderBy("orderint")->get();
        }
        // 既存回答
        $scoreobj = Score::where('review_id', $review->id)->get();
        $scores = [];
        foreach ($scoreobj as $ea) {
            $scores[$ea->viewpoint_id] = $ea;
        }
        return view("review.show")->with(compact("review", "viewpoints", "scores"));
    }

    /**
     * for test (dummy)
     */
    public function edit_dummy($cat_id, $ismeta = 0)
    {
        if (!auth()->user()->can('role', 'pc')) return abort(403);
        $rev = new Review();
        $rev->category_id = $cat_id;
        $rev->submit_id = 9999;
        $rev->user_id = auth()->id();
        $rev->ismeta = $ismeta;
        $rev->paper = new Paper();
        $rev->paper->id = 9999;
        $rev->paper->category_id = $cat_id;
        $rev->id = 0; // ダミーはかならずReviewID = 0 にする。
        return $this->edit($rev);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        if (!auth()->user()->can('role', 'reviewer')) return abort(403);
        if ($review->user_id != auth()->id()) return abort(403, "THIS IS NOT YOUR REVIEW");

        $query = Viewpoint::where("category_id", $review->category_id);
        if ($review->ismeta) {
            $query->where("formeta", 1);
        } else {
            $query->where("forrev", 1);
        }
        $viewpoints = $query->orderBy("orderint")->get();
        // 既存回答
        $scoreobj = Score::where('review_id', $review->id)->get();
        $scores = [];
        foreach ($scoreobj as $ea) {
            $scores[$ea->viewpoint_id] = $ea;
        }
        return view("review.edit")->with(compact("review", "viewpoints", "scores"));
        //
    }

    /**
     * Update the specified resource in storage.
     * じっさいにはScoreを作成する
     */
    public function update(UpdateReviewRequest $request, int $reviewid)
    {
        if ($reviewid == 0) return $request->shori_dummy(); // ダミーはかならずReviewID = 0 にする。
        if (!auth()->user()->can('role', 'reviewer')) return abort(403);
        $review = Review::find($reviewid);
        if ($review->user_id != auth()->id()) return abort(403, "THIS IS NOT YOUR REVIEW");

        if ($request->ajax()) return $request->shori();
        else {
            // input type=numberでEnterをおすと、submitしてしまうので、ここでリダイレクトしてあげる
            return redirect()->route('review.edit', ['review' => $request->input("review_id")]);
        }
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }

    /**
     * 査読者向けのZIPダウンロード
     */
    public function zipdownload_for_rev($catid)
    {
        if (!auth()->user()->can('role_any', 'reviewer|metareviewer')) return abort(403);

        // $reviews = Review::where("user_id", auth()->user()->id)->orderBy("category_id")->orderBy("paper_id")->get();
        $reviews = Review::where("user_id", auth()->user()->id)->where("category_id", $catid)->orderBy("paper_id")->get();
        if (count($reviews) == 0) {
            return redirect()->route('review.index')->with('feedback.success', '担当論文はまだありません。');
        }
        $zipFN = "cat{$catid}_forreview.zip";
        $zipFP = storage_path('app/' . $zipFN);
        $zip = new ZipArchive();
        if ($zip->open($zipFP, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $count = 0;
            foreach ($reviews as $rev) {
                $paper = $rev->paper;
                $count += $paper->addFilesToZip($zip, ["pdf", "video", "img", "altpdf"]);
            }
            $zip->close();
            if ($count == 0) {
                return redirect()->route('review.index')->with('feedback.success', 'ダウンロード可能なファイルはまだありません。');
            }
            // Zipアーカイブをダウンロード
            return response()->download($zipFP)->deleteFileAfterSend(true);
        } else {
            return response()->json(['message' => 'Zipファイルを作成できませんでした。'], 500);
        }
    }
}
