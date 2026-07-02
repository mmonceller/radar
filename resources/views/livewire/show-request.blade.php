<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Question;
use App\Models\Answer; // Ensure you have an Answer model setup
use Illuminate\Support\Facades\Auth;

class ShowRequest extends Component
{
    public Question $question;

    // Answer Form State Fields
    public $location = '';
    public $price = '';
    public $notes = '';

    protected $rules = [
        'location' => 'required|min:3|max:255',
        'price' => 'required|numeric|min:0',
        'notes' => 'nullable|max:500',
    ];

    public function mount(Question $request)
    {
        // Explicitly load the model instance using Route Model Binding
        $this->question = $request->load(['user', 'answers.user']);
    }

    public function saveAnswer()
    {
        // Ensure the user is authenticated before creating a lead
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $this->validate();

        // Save the lead linked to this request
        Answer::create([
            'question_id' => $this->question->id,
            'user_id' => Auth::id(),
            'location' => $this->location,
            'price' => $this->price,
            'notes' => $this->notes,
        ]);

        // Reset form variables safely
        $this->reset(['location', 'price', 'notes']);

        // Refresh model relations to instantly show the new answer
        $this->question->load('answers.user');

        session()->flash('message', 'Thank you! Your location lead has been submitted.');
    }

    public function render()
    {
        return view('livewire.show-request')->layout('layouts.app');
    }
}