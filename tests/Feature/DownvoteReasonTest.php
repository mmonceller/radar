<?php

use App\Leads\DownvoteReason;
use App\Livewire\Manage\MyPosts;
use App\Livewire\ShowRequest;
use App\Models\Category;
use App\Models\Lead;
use App\Models\LeadVote;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

function reasonQuestion(User $owner): Question
{
    $category = Category::create([
        'name' => 'Electronics '.uniqid(),
        'slug' => 'electronics-'.uniqid(),
    ]);

    return Question::create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'PS5 Pro',
        'description' => 'Disc edition',
    ]);
}

function reasonLead(Question $question, User $author): Lead
{
    return Lead::create([
        'question_id' => $question->id,
        'user_id' => $author->id,
        'store_name' => 'Local Shop',
        'price' => 1999.50,
        'is_online' => false,
        'latitude' => '14.5995',
        'longitude' => '120.9842',
        'description' => 'In stock near the entrance',
    ]);
}

it('requires a reason before a downvote is saved', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $voter = User::factory()->create();
    $question = reasonQuestion($owner);
    $lead = reasonLead($question, $author);

    Livewire::actingAs($voter)
        ->test(ShowRequest::class, ['request' => $question])
        ->call('startDownvote', $lead->id)
        ->assertSee('What was wrong with this lead?')
        ->assertSee('The store is permanently closed or does not exist')
        ->assertSee('Brief explanation (optional)')
        ->call('submitDownvote')
        ->assertHasErrors(['downvoteReason' => 'required']);

    expect(LeadVote::count())->toBe(0);

    Livewire::actingAs($voter)
        ->test(ShowRequest::class, ['request' => $question])
        ->call('vote', $lead->id, -1);

    expect(LeadVote::count())->toBe(0);
});

it('stores a downvote reason and shows it only to the lead author', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $voter = User::factory()->create();
    $stranger = User::factory()->create();
    $question = reasonQuestion($owner);
    $lead = reasonLead($question, $author);
    $explanation = 'The pin is in the wrong mall.';

    Livewire::actingAs($voter)
        ->test(ShowRequest::class, ['request' => $question])
        ->call('startDownvote', $lead->id)
        ->set('downvoteReason', DownvoteReason::LocationOrLink->value)
        ->set('downvoteExplanation', $explanation)
        ->call('submitDownvote')
        ->assertHasNoErrors();

    $vote = LeadVote::first();

    expect($vote->type)->toBe(-1)
        ->and($vote->reason)->toBe(DownvoteReason::LocationOrLink)
        ->and($vote->explanation)->toBe($explanation)
        ->and($lead->fresh()->downvotes_count)->toBe(1);

    $this->actingAs($author)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertSee('Downvote notes')
        ->assertSee('The location or link is wrong')
        ->assertSee($explanation);

    $this->actingAs($author)
        ->get(route('posts.manage'))
        ->assertOk()
        ->assertSee($explanation);

    $this->actingAs($owner)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertDontSee('Downvote notes')
        ->assertDontSee($explanation);

    $this->actingAs($voter)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertDontSee('Downvote notes')
        ->assertDontSee($explanation);

    $this->actingAs($stranger)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertDontSee($explanation);

    Livewire::actingAs($stranger)
        ->test(MyPosts::class)
        ->assertDontSee($explanation);
});

it('clears the downvote reason when the vote is changed to an upvote', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $voter = User::factory()->create();
    $question = reasonQuestion($owner);
    $lead = reasonLead($question, $author);

    $component = Livewire::actingAs($voter)
        ->test(ShowRequest::class, ['request' => $question])
        ->call('startDownvote', $lead->id)
        ->set('downvoteReason', DownvoteReason::PriceMismatch->value)
        ->set('downvoteExplanation', 'The shelf price was double.')
        ->call('submitDownvote');

    $component->call('vote', $lead->id, 1);

    $vote = LeadVote::first();

    expect((int) $vote->type)->toBe(1)
        ->and($vote->reason)->toBeNull()
        ->and($vote->explanation)->toBeNull();

    $this->actingAs($author)
        ->get(route('requests.show', $question))
        ->assertDontSee('The shelf price was double.');
});

it('does not let the lead author downvote their own lead', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = reasonQuestion($owner);
    $lead = reasonLead($question, $author);

    Livewire::actingAs($author)
        ->test(ShowRequest::class, ['request' => $question])
        ->call('startDownvote', $lead->id)
        ->assertSet('downvotingLeadId', null);

    expect(LeadVote::count())->toBe(0);
});

it('rejects a downvote reason that is not one of the listed choices', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $voter = User::factory()->create();
    $question = reasonQuestion($owner);
    $lead = reasonLead($question, $author);

    Livewire::actingAs($voter)
        ->test(ShowRequest::class, ['request' => $question])
        ->call('startDownvote', $lead->id)
        ->set('downvoteReason', 'not-a-real-reason')
        ->call('submitDownvote')
        ->assertHasErrors('downvoteReason');

    expect(LeadVote::count())->toBe(0);
});
