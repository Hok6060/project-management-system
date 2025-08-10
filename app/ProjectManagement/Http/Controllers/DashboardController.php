<?php

namespace App\ProjectManagement\Http\Controllers;

use App\ProjectManagement\Models\Project;
use App\ProjectManagement\Models\Task;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with user-specific data.
     */
    public function index()
    {
        $user = Auth::user();

        // Get total number of projects the user is associated with (as owner or client)
        $totalProjects = Project::where('owner_id', $user->id)
                                ->orWhere('client_id', $user->id)
                                ->count();

        // Get total number of tasks assigned to the user
        $totalTasks = Task::where('assignee_id', $user->id)->count();
        
        // Get tasks assigned to the user that are due in the next 7 days
        $tasksDueSoon = Task::where('assignee_id', $user->id)
                            ->where('status', '!=', 'done')
                            ->where('due_date', '>=', Carbon::now())
                            ->where('due_date', '<=', Carbon::now()->addDays(7))
                            ->orderBy('due_date', 'asc')
                            ->with('project') // Eager load the project relationship
                            ->get();

        return view('dashboard', compact('totalProjects', 'totalTasks', 'tasksDueSoon'));
    }
}
