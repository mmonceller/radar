<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Question;
use App\Models\Category; // Import the Category model
use Illuminate\Support\Facades\Auth;

class CreateRequest extends Component
{
    use WithFileUploads;

    // Form inputs
    public $title = '';
    public $description = '';
    public $category_id = ''; // Changed from $category to $category_id
    public $image; 

    protected $rules = [
        'title' => 'required|min:3|max:255',
        'category_id' => 'required|exists:categories,id', // Validates that the ID actually exists in our table
        'description' => 'nullable|max:1000',
        'image' => 'nullable|image|max:2048', 
    ];

    public function save()
    {
        $this->validate();

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('requests', 'public');
        }

        // Save into the questions table with our new category_id column
        Question::create([
            'user_id' => Auth::id(),
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'image_path' => $imagePath,
        ]);

        session()->flash('message', 'Request created successfully!');

        return $this->redirect(route('home'), navigate: true);
    }

    public function render()
    {
        // Fetch all categories from the database dynamically
        return view('livewire.create-request', [
            'categories' => Category::orderBy('name', 'asc')->get()
        ])->layout('layouts.app');
    }
}