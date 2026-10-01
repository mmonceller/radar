<?php

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Support\StoredImage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditQuestion extends Component
{
    use WithFileUploads;

    public Question $question;

    public bool $fromPosts = false;

    public $description = '';

    public $image;

    public $removeImage = false;

    public function mount(Question $request): void
    {
        $this->fromPosts = request()->query('from') === 'posts';
        abort_unless($request->user_id === Auth::id(), 403);

        if ($request->leads()->exists()) {
            session()->flash('follow_up_notice', 'This request already has leads, so the photo and description stay locked. Post a specification update instead.');
            $this->redirect($this->questionUrl($request), navigate: true);

            return;
        }

        $this->question = $request;
        $this->description = $request->description ?? '';
    }

    public function updatedImage(): void
    {
        $this->removeImage = false;
    }

    public function save()
    {
        abort_unless($this->question->user_id === Auth::id(), 403);

        if ($this->question->leads()->exists()) {
            session()->flash('follow_up_notice', 'Someone posted a lead while you were editing, so the photo and description are now locked. Post a specification update instead.');
            $this->redirect($this->questionUrl($this->question), navigate: true);

            return;
        }

        $this->validate([
            'description' => 'nullable|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->question->image_path;

        if ($this->image) {
            $imagePath = StoredImage::replace($this->question->image_path, $this->image, 'requests');
        } elseif ($this->removeImage) {
            StoredImage::delete($this->question->image_path);
            $imagePath = null;
        }

        $description = trim((string) $this->description);

        $this->question->update([
            'description' => $description === '' ? null : $description,
            'image_path' => $imagePath,
        ]);

        session()->flash('question_updated', 'Photo and description updated.');

        return $this->redirect($this->questionUrl($this->question), navigate: true);
    }

    protected function questionUrl(Question $question): string
    {
        $parameters = ['request' => $question];

        if ($this->fromPosts) {
            $parameters['from'] = 'posts';
        }

        return route('requests.show', $parameters);
    }

    public function render()
    {
        return view('livewire.questions.edit-question')
            ->layout('layouts.app');
    }
}
