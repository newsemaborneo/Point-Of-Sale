<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\Branch; // Import Branch model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::latest()->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = DB::table('roles')->get();
        $branches = Branch::all(); // Fetch all branches
        return view('users.form', [
            'user' => new User(),
            'roles' => $roles,
            'branches' => $branches, // Pass branches to the view
        ]);
    }

    public function store(Request $request)
    {
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|string|exists:roles,id', // Memastikan peran yang dipilih ada di tabel 'roles'
            'phone' => 'nullable|string|max:20', // Add phone validation
            'branch_id' => 'nullable|exists:branches,id', // Add branch_id validation
            'password' => 'nullable|string|min:6',
        ]);
        // dd($data) ;

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
    
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = DB::table('roles')->get();
        $branches = Branch::all(); // Fetch all branches
        return view('users.form', [
            'user' => $user,
            'roles' => $roles,
            'branches' => $branches, // Pass branches to the view
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|string|exists:roles,id', // Memastikan peran yang dipilih ada di tabel 'roles'
            'phone' => 'nullable|string|max:20', // Add phone validation
            'branch_id' => 'nullable|exists:branches,id', // Add branch_id validation
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function activityLog(User $user)
    {
        $logs = ActivityLog::where('user_id', $user->id)->latest()->paginate(20);
        return view('users.activity-log', compact('user', 'logs'));
    }
}
