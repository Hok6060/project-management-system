<?php

namespace App\ProjectManagement\Http\Controllers;

use App\Models\User;
use App\ProjectManagement\Models\Project;
use App\ProjectManagement\Notifications\UserRoleChanged;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function indexUsers()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function updateUser(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'project_manager', 'team_member', 'client', 'loan_officer'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Get the original role before the update
        $originalRole = $user->getOriginal('role');

        $user->update($validatedData);

        // Check if the role was actually changed
        if ($user->wasChanged('role')) {
            // Send the notification to the user whose role was changed
            $user->notify(new UserRoleChanged($originalRole));
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroyUser(User $user)
    {
        // Prevent an admin from deleting their own account
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if the user owns any projects
        if ($user->ownedProjects()->exists()) {
            return back()->with('error', 'This user cannot be deleted because they own active projects. Please reassign their projects first.');
        }

        // If the user doesn't own any projects, we can safely delete them.
        // The database will automatically set their assigned tasks to NULL.
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Display the admin dashboard with system-wide stats.
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Provide data for the project status chart.
     */
    public function projectStatusChartData()
    {
        $data = Project::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'labels' => $data->keys()->map(fn($status) => ucfirst(str_replace('_', ' ', $status))),
            'data' => $data->values(),
        ]);
    }
}