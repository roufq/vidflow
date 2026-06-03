<?php

namespace App\Http\Controllers;

use App\Models\UploadJob;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index()
    {
        $jobs = UploadJob::where('user_id', auth()->id())
            ->with('platformUploads')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return inertia('History', ['jobs' => $jobs]);
    }
}
