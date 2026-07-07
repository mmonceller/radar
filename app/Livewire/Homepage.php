<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Question;

class Homepage extends Component
{
    public $search = '';
    public $category = '';
    public $showFilters = false;
    public $showLatest = false;

    // Toggles the advanced filter section visibility
    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    // Toggles the feed section visibility
    public function toggleLatest()
    {
        $this->showLatest = !$this->showLatest;
    }

    public function render()
    {
        // Start a query builder instance
        $query = Question::with(['user'])->withCount('leads');

        // Apply real-time search filtering if something is typed
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Apply category filter if selected
        if (!empty($this->category)) {
            $query->where('category', $this->category);
        }

        // Fetch latest items matching the query constraints
        $questions = $query->latest()->take(6)->get();

        return view('livewire.homepage', [
            'questions' => $questions
        ]);
    }
}