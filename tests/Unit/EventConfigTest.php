<?php

namespace Tests\Unit;

use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\EnqueteItem;
use App\Models\EventConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_selection_numbers_only_for_the_requested_user(): void
    {
        $enquete = new Enquete(['name' => 'Registration']);
        $enquete->save();

        $item = new EnqueteItem([
            'enquete_id' => $enquete->id,
            'name' => 'role',
            'desc' => 'Role',
            'content' => "Role;selection;First;Second;Third",
        ]);
        $item->save();

        $config = new EventConfig([
            'event_id' => 42,
            'enquete_id' => $enquete->id,
        ]);
        $config->save();

        $answerForUserOne = new EnqueteAnswer([
            'enquete_id' => $enquete->id,
            'enquete_item_id' => $item->id,
            'user_id' => 1,
            'paper_id' => 0,
            'valuestr' => 'First',
        ]);
        $answerForUserOne->save();

        $answerForUserTwo = new EnqueteAnswer([
            'enquete_id' => $enquete->id,
            'enquete_item_id' => $item->id,
            'user_id' => 2,
            'paper_id' => 0,
            'valuestr' => 'Third',
        ]);
        $answerForUserTwo->save();

        $result = EventConfig::getEnqueteAnswersBySelectionNumber(42, 1);

        $this->assertSame(["role" => 1], $result);
    }
}
