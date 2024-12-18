<?php

namespace Tests;

use App\Models\Category;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Submit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Setting::seeder();
    }

    /**
     * 投稿完了状態をつくる
     */
    public function paper_submit(int $cat_id = 1, int $num_author = 1, int $num_paper_per_author = 1)
    {
        User::factory($num_author)->withPapers($num_paper_per_author, $cat_id)->create();
    }
    public function add_reviewer(int $num_reviewer = 1)
    {
        User::factory($num_reviewer)->withRoles('reviewer')->create();
    }
    public function add_metareviewer(int $num_metareviewer = 1)
    {
        User::factory($num_metareviewer)->withRoles('metareviewer')->create();
    }
    public function add_pc(int $num_pc = 1)
    {
        User::factory($num_pc)->withRoles('pc')->create();
    }
    public function give_reviewer_priv_to_authors(int $cat_id, string $rolename = 'reviewer')
    {
        Paper::where('category_id', $cat_id)->get()->each(function ($paper) use ($rolename) {
            $paper->paperowner->each(function ($author) use ($rolename) {
                $author->roles()->syncWithoutDetaching(Role::where('name', $rolename)->first());
            });
        });
    }
    public function start_bidding(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__bidding_on', $on);
    }
    public function end_bidding(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__bidding_off', $on);
    }
    public function start_review(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__revedit_on', $on);
    }
    public function end_review(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__revedit_off', $on);
    }
    public function start_bb(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__revbb_on', $on);
    }
    public function show_revlist_to_reviewer(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__revlist_on', $on);
        $this->sadoku_sinko_kanri($cat_id, 'status__revlist_for', 'reviewer');
    }
    public function show_revlist_to_metareviewer(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__revlist_on', $on);
        $this->sadoku_sinko_kanri($cat_id, 'status__revlist_for', 'metareviewer');
    }
    public function show_revresult_to_author(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'status__revreturn_on', $on);
    }
    public function show_bibinfo_button(int $cat_id, bool $on)
    {
        $this->sadoku_sinko_kanri($cat_id, 'show_bibinfo_btn', $on);
    }
    protected function sadoku_sinko_kanri(int $cat_id, string $field, bool|string $on)
    {
        $cat = Category::find($cat_id);
        $cat->$field = $on;
        $cat->save();
    }

    protected function dump_papers()
    {
        $papers = Paper::where('category_id', 1)->get();
        foreach ($papers as $paper) {
            dump($paper->id . " " . $paper->title . " " . $paper->owner);
        }
    }
    protected function dump_role($rolename)
    {
        $users = Role::findByIdOrName($rolename)->users->sortBy('id');
        foreach ($users as $user) {
            dump($user->id . " " . $user->name);
        }
    }

    protected function dump_conflict(int $cat_id = 1, string $rolename = 'reviewer')
    {
        $revcon = RevConflict::arr_pu_bid($cat_id);
        $users = Role::findByIdOrName($rolename)->users->sortBy('id');
        $uidstr = "    ";
        foreach ($users as $user) {
            $uidstr .= sprintf("%2d ", $user->id);
        }
        dump($uidstr);
        $papers = Paper::where('category_id', $cat_id)->get();
        foreach ($papers as $paper) {
            $line = sprintf("%03d:", $paper->id);
            foreach ($users as $user) {
                $line .= sprintf("%2d ", $revcon[$paper->id][$user->id]);
            }
            dump($line);
        }
    }
    protected function dump_assign(int $cat_id = 1, string $rolename = 'reviewer')
    {
        $users = Role::findByIdOrName($rolename)->users->sortBy('id');
        $uidstr = "    ";
        foreach ($users as $user) {
            $uidstr .= sprintf("%2d ", $user->id);
        }
        dump($uidstr);
        // get reviews
        $arr_pu_rev = Review::arr_pu_rev($cat_id);
        $papers = Paper::where('category_id', $cat_id)->get();
        foreach ($papers as $paper) {
            $line = sprintf("%03d:", $paper->id);
            foreach ($users as $user) {
                $rev = @$arr_pu_rev[$paper->id][$user->id];
                if ($rev != null) {
                    $line .= sprintf("%2d ", $rev->ismeta);
                } else {
                    $line .= sprintf(" - ",);
                }
            }
            dump($line);
        }
    }
    protected function dump_decision(int $cat_id = 1)
    {
        $submits = Submit::where('category_id', $cat_id)->get();
        foreach ($submits as $submit) {
            dump($submit->id . " " . $submit->accept->name. " " . $submit->paper->title);
        }
    }
    protected function bidding(string $rolename)
    {
        $users = Role::findByIdOrName($rolename)->users;
        foreach ($users as $user) {
            $user->test_revconflict();
        }
    }
    protected function revassign(int $cat_id = 1, string $rolename = 'reviewer', int $num_assign_reviewer = 1)
    {
        // Bidding状況のチェック
        $revcon = RevConflict::arr_pu_bid($cat_id);
        $num_paper = Paper::where('category_id', $cat_id)->count();
        $num_reviewer = Role::findByIdOrName($rolename)->users->count();
        if (count($revcon) != $num_paper * $num_reviewer) {
            RevConflict::fillBidding($cat_id, $rolename, 5);
        }
        // Biddingがそろったら、Reviewを割り当てる
        $revcon = RevConflict::arr_pu_bid($cat_id);
        $papers = Paper::where('category_id', $cat_id)->get();
        foreach ($papers as $paper) {
            $candidate = $revcon[$paper->id]; // 候補配列
            $count = 0;
            while (true) {
                $randKey = array_rand($candidate);
                $randVal = $candidate[$randKey];
                if ($randVal > 2) {
                    $arr_pu_rev = Review::arr_pu_rev($cat_id);
                    if (@$arr_pu_rev[$paper->id][$randKey] == null) {
                        Review::firstOrCreate([
                            'submit_id' => $paper->submits->first()->id,
                            'paper_id' => $paper->id,
                            'user_id' => $randKey,
                            'category_id' => $cat_id,
                            'ismeta' => ($rolename == 'metareviewer'),
                        ]);
                        $count++;
                    }
                }
                if ($count >= $num_assign_reviewer) break;
            }
        }
    }

    /** 判定までを行う */
    protected function revdecision(int $cat_id = 1)
    {
        $submits = Submit::where('category_id', $cat_id)->get();
        // $accs = Accept::select("name", "id")->pluck("name", "id")->toArray();
        $accids = [1,2,4,21];
        foreach($submits as $submit) {
            $randkey = array_rand($accids);
            $submit->accept_id = $accids[$randkey];
            $submit->save();
        }
    }

    protected function prepare_pdf_file()
    {
        
    }

    protected function proceed_to_submit(int $cat_id)
    {
        $num_paper_per_author = 2;
        $num_author = 5;
        self::paper_submit($cat_id, $num_author, $num_paper_per_author);

        // 査読者を追加
        $num_reviewer = 5;
        self::add_reviewer($num_reviewer);
    }
    protected function proceed_to_assign(int $cat_id)
    {
        // parent::dump_papers();
        self::dump_role('reviewer');
        self::give_reviewer_priv_to_authors(1, 'reviewer');

        Review::extractAllCoAuthorRigais();
        self::bidding('reviewer');
        dump(RevConflict::count());
        RevConflict::fillBidding($cat_id, "reviewer", 5);
        self::dump_conflict();
        // $this->assertCount(4, Role::findByIdOrName('reviewer')->users);

        self::revassign($cat_id, 'reviewer', 2);
        self::revassign($cat_id, 'metareviewer', 1);
        self::dump_assign();
    }

    protected function proceed_to_decision(int $cat_id)
    {
        self::revdecision($cat_id);
        self::dump_decision($cat_id);
    }
}
