<?php

use App\Livewire\Leads\AwardLead;
use App\Livewire\Leads\EditLead;
use App\Livewire\Manage\MyPosts;
use App\Livewire\Questions\EditQuestion;
use App\Livewire\Questions\FollowUps;
use App\Livewire\ShowRequest;
use App\Models\Category;
use App\Models\Lead;
use App\Models\Question;
use App\Models\QuestionFollowUp;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function postCategory(): Category
{
    return Category::create([
        'name' => 'Electronics '.uniqid(),
        'slug' => 'electronics-'.uniqid(),
    ]);
}

function postQuestion(User $owner, array $overrides = []): Question
{
    return Question::create(array_merge([
        'user_id' => $owner->id,
        'category_id' => postCategory()->id,
        'title' => 'PS5 Pro',
        'description' => 'Disc edition',
    ], $overrides));
}

function postLead(Question $question, User $author, array $overrides = []): Lead
{
    return Lead::create(array_merge([
        'question_id' => $question->id,
        'user_id' => $author->id,
        'store_name' => 'Local Shop',
        'price' => 1999.50,
        'is_online' => false,
        'latitude' => '14.5995',
        'longitude' => '120.9842',
        'description' => 'In stock near the entrance',
    ], $overrides));
}

function uploadedImage(string $name = 'photo.jpg'): UploadedFile
{
    $jpeg = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=');

    return UploadedFile::fake()->createWithContent($name, $jpeg);
}

it('lets the owner edit the photo and description before any lead exists', function () {
    Storage::fake('public');
    Storage::disk('public')->put('requests/old.jpg', 'old-photo');

    $owner = User::factory()->create();
    $question = postQuestion($owner, [
        'image_path' => 'requests/old.jpg',
        'description' => 'Disc edition',
    ]);

    Livewire::actingAs($owner)
        ->test(EditQuestion::class, ['request' => $question])
        ->set('description', 'Digital edition, white')
        ->set('image', uploadedImage('new.jpg'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('requests.show', $question));

    $question->refresh();

    expect($question->title)->toBe('PS5 Pro')
        ->and($question->description)->toBe('Digital edition, white')
        ->and($question->image_path)->not->toBe('requests/old.jpg');

    Storage::disk('public')->assertMissing('requests/old.jpg');
    Storage::disk('public')->assertExists($question->image_path);
});

it('lets the owner remove the question photo before any lead exists', function () {
    Storage::fake('public');
    Storage::disk('public')->put('requests/old.jpg', 'old-photo');

    $owner = User::factory()->create();
    $question = postQuestion($owner, ['image_path' => 'requests/old.jpg']);

    Livewire::actingAs($owner)
        ->test(EditQuestion::class, ['request' => $question])
        ->set('removeImage', true)
        ->call('save')
        ->assertRedirect(route('requests.show', $question));

    expect($question->fresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing('requests/old.jpg');
});

it('locks the photo and description once a lead exists', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);
    postLead($question, $author);

    $this->actingAs($owner)
        ->get(route('requests.edit', $question))
        ->assertRedirect(route('requests.show', $question))
        ->assertSessionHas('follow_up_notice');

    expect($question->fresh()->description)->toBe('Disc edition');
});

it('rejects a photo or description save if a lead arrives during editing', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);

    $component = Livewire::actingAs($owner)
        ->test(EditQuestion::class, ['request' => $question]);

    postLead($question, $author);

    $component
        ->set('description', 'Changed after a lead')
        ->call('save')
        ->assertRedirect(route('requests.show', $question));

    expect($question->fresh()->description)->toBe('Disc edition');
});

it('forbids other users from editing a question', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = postQuestion($owner);

    $this->actingAs($stranger)
        ->get(route('requests.edit', $question))
        ->assertForbidden();
});

it('lets the owner post a specification update only after a lead exists', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);

    Livewire::actingAs($owner)
        ->test(FollowUps::class, ['question' => $question])
        ->set('body', 'Needs to be the 1TB model.')
        ->call('submit')
        ->assertForbidden();

    expect(QuestionFollowUp::count())->toBe(0);

    postLead($question, $author);

    Livewire::actingAs($owner)
        ->test(FollowUps::class, ['question' => $question])
        ->set('body', 'Needs to be the 1TB model.')
        ->call('submit')
        ->assertHasNoErrors();

    $followUp = QuestionFollowUp::first();

    expect($followUp->body)->toBe('Needs to be the 1TB model.')
        ->and($followUp->user_id)->toBe($owner->id);

    Livewire::actingAs($owner)
        ->test(FollowUps::class, ['question' => $question])
        ->call('edit', $followUp->id)
        ->set('editingBody', 'Needs to be the 1TB model, any color except red.')
        ->call('updateFollowUp');

    expect($followUp->fresh()->body)->toBe('Needs to be the 1TB model, any color except red.');
});

it('forbids other users from posting specification updates', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = postQuestion($owner);
    postLead($question, $stranger);

    Livewire::actingAs($stranger)
        ->test(FollowUps::class, ['question' => $question])
        ->set('body', 'I will change the request.')
        ->call('submit')
        ->assertForbidden();

    expect(QuestionFollowUp::count())->toBe(0);
});

it('shows the edit action before leads and the award action after', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);

    $this->actingAs($owner)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertSee('Edit photo or description')
        ->assertDontSee('I bought this')
        ->assertDontSee('Provide a Location Lead');

    postLead($question, $author);

    $this->actingAs($owner)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertSee('Update what you need')
        ->assertSee('I bought this')
        ->assertDontSee('Edit photo or description');

    $this->actingAsGuest()
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertDontSee('I bought this')
        ->assertDontSee('Update what you need');
});

it('awards one lead with a purchase photo and price', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);
    $first = postLead($question, $author, ['store_name' => 'First Shop']);
    $second = postLead($question, $author, ['store_name' => 'Second Shop']);

    Livewire::actingAs($owner)
        ->test(AwardLead::class, ['lead' => $first])
        ->set('verifiedPrice', 1500)
        ->call('submit')
        ->assertHasErrors(['verificationImage' => 'required']);

    expect($first->fresh()->awarded_at)->toBeNull();

    Livewire::actingAs($owner)
        ->test(AwardLead::class, ['lead' => $first])
        ->set('verifiedPrice', 1500)
        ->set('verificationImage', uploadedImage('receipt.jpg'))
        ->call('submit')
        ->assertHasNoErrors();

    $first->refresh();

    expect($first->awarded_at)->not->toBeNull()
        ->and($first->purchase_verified_at)->not->toBeNull()
        ->and((float) $first->verified_price)->toBe(1500.0)
        ->and($first->verification_image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($first->verification_image_path);

    Livewire::actingAs($owner)
        ->test(AwardLead::class, ['lead' => $second])
        ->set('verifiedPrice', 1600)
        ->set('verificationImage', uploadedImage('other.jpg'))
        ->call('submit')
        ->assertHasErrors('verifiedPrice');

    expect($second->fresh()->awarded_at)->toBeNull();

    $path = $first->verification_image_path;

    Livewire::actingAs($owner)
        ->test(AwardLead::class, ['lead' => $first])
        ->call('revoke');

    $first->refresh();

    expect($first->awarded_at)->toBeNull()
        ->and($first->verified_price)->toBeNull()
        ->and($first->verification_image_path)->toBeNull()
        ->and($first->purchase_verified_at)->toBeNull();

    Storage::disk('public')->assertMissing($path);
});

it('forbids other users from awarding a lead', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $question = postQuestion($owner);
    $lead = postLead($question, $stranger);

    Livewire::actingAs($stranger)
        ->test(AwardLead::class, ['lead' => $lead])
        ->set('verifiedPrice', 10)
        ->set('verificationImage', uploadedImage('receipt.jpg'))
        ->call('submit')
        ->assertForbidden();

    expect($lead->fresh()->awarded_at)->toBeNull();
});

it('lets the author edit their own lead without clearing a purchase award', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $question = postQuestion($owner);
    $lead = postLead($question, $author);

    $lead->forceFill([
        'awarded_at' => now(),
        'purchase_verified_at' => now(),
        'verified_price' => 1800,
        'verification_image_path' => 'verifications/receipt.jpg',
    ])->save();

    $this->actingAs($stranger)
        ->get(route('leads.edit', $lead))
        ->assertForbidden();

    Livewire::actingAs($author)
        ->test(EditLead::class, ['lead' => $lead])
        ->set('store_name', 'Updated Shop')
        ->set('price', 2100)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('requests.show', $question));

    $lead->refresh();

    expect($lead->store_name)->toBe('Updated Shop')
        ->and((float) $lead->price)->toBe(2100.0)
        ->and((float) $lead->verified_price)->toBe(1800.0)
        ->and($lead->verification_image_path)->toBe('verifications/receipt.jpg');
});

it('requires a url when a lead is switched to an online store', function () {
    $author = User::factory()->create();
    $question = postQuestion(User::factory()->create());
    $lead = postLead($question, $author);

    Livewire::actingAs($author)
        ->test(EditLead::class, ['lead' => $lead])
        ->call('changeShopType', true)
        ->set('source_link', 'not-a-url')
        ->call('save')
        ->assertHasErrors('source_link');
});

it('shows only the signed-in user their questions and leads', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    postQuestion($owner, ['title' => 'My keyboard']);
    postQuestion($other, ['title' => 'Secret camera']);
    postLead(postQuestion($other), $owner, ['store_name' => 'My lead shop']);

    Livewire::actingAs($owner)
        ->test(MyPosts::class)
        ->assertSee('My keyboard')
        ->assertSee('My lead shop')
        ->assertDontSee('Secret camera');

    $this->actingAsGuest()
        ->get(route('posts.manage'))
        ->assertRedirect(route('login'));
});

it('lets an author delete their lead unless it has a verified purchase', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);
    $openLead = postLead($question, $author, ['store_name' => 'Open Shop']);
    $awardedLead = postLead($question, $author, ['store_name' => 'Awarded Shop']);
    $awardedLead->forceFill([
        'awarded_at' => now(),
        'purchase_verified_at' => now(),
        'verified_price' => 50,
        'verification_image_path' => 'verifications/receipt.jpg',
    ])->save();

    Livewire::actingAs($author)
        ->test(MyPosts::class)
        ->call('deleteLead', $awardedLead->id)
        ->assertHasErrors('delete');

    expect($awardedLead->fresh())->not->toBeNull();

    Livewire::actingAs($author)
        ->test(MyPosts::class)
        ->call('deleteLead', $openLead->id);

    expect(Lead::find($openLead->id))->toBeNull();

    $stranger = User::factory()->create();
    $remaining = postLead($question, $author, ['store_name' => 'Still here']);

    Livewire::actingAs($stranger)
        ->test(MyPosts::class)
        ->call('deleteLead', $remaining->id)
        ->assertForbidden();

    expect($remaining->fresh())->not->toBeNull();
});

it('still accepts a new lead from the request page', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);

    Livewire::actingAs($author)
        ->test(ShowRequest::class, ['request' => $question])
        ->set('store_name', 'Mall Kiosk')
        ->set('price', 75)
        ->set('latitude', '14.55')
        ->set('longitude', '121.02')
        ->call('submitLead')
        ->assertHasNoErrors();

    expect($question->leads()->where('store_name', 'Mall Kiosk')->exists())->toBeTrue();
});

it('returns to my posts when the request was opened from there', function () {
    $owner = User::factory()->create();
    $question = postQuestion($owner);

    $this->actingAs($owner)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertSee('Back to Search')
        ->assertDontSee('Back to My Posts');

    $this->actingAs($owner)
        ->get(route('requests.show', ['request' => $question, 'from' => 'posts']))
        ->assertOk()
        ->assertSee('Back to My Posts')
        ->assertSee(route('posts.manage'));

    $this->actingAs($owner)
        ->get(route('requests.edit', ['request' => $question, 'from' => 'posts']))
        ->assertOk()
        ->assertSee('Back to My Posts');

    Livewire::withQueryParams(['from' => 'posts'])
        ->actingAs($owner)
        ->test(EditQuestion::class, ['request' => $question])
        ->call('save')
        ->assertRedirect(route('requests.show', ['request' => $question, 'from' => 'posts']));
});

it('hides the lead form from the person who posted the question', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $question = postQuestion($owner);

    $this->actingAs($owner)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertDontSee('Provide a Location Lead');

    $this->actingAs($author)
        ->get(route('requests.show', $question))
        ->assertOk()
        ->assertSee('Provide a Location Lead');

    Livewire::actingAs($owner)
        ->test(ShowRequest::class, ['request' => $question])
        ->set('store_name', 'My own shop')
        ->set('price', 10)
        ->set('latitude', '14.55')
        ->set('longitude', '121.02')
        ->call('submitLead')
        ->assertForbidden();

    expect($question->leads()->count())->toBe(0);
});
