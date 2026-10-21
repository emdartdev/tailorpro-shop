<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with(['profile', 'userRoles'])->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'email' => $u->email,
                'name' => $u->profile?->display_name ?? $u->name,
                'role' => $u->role,
                'created_at' => $u->created_at,
            ];
        });

        return response()->json([
            'data' => $users,
            'users' => $users,
        ]);
    }

    public function invite(Request $request): JsonResponse
    {
        $roleInput = $request->input('role', 'viewer');
        if ($roleInput === 'superadmin') $roleInput = 'admin';
        if ($roleInput === 'manager' || $roleInput === 'operator') $roleInput = 'editor';
        $request->merge(['role' => $roleInput]);

        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'string', 'in:admin,editor,viewer'],
            'name' => ['nullable', 'string'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'email' => $validated['email'],
                'name' => $validated['name'] ?? null,
                'password' => Hash::make(Str::random(32)),
            ]);

            UserRole::create([
                'user_id' => $user->id,
                'role' => $validated['role'],
            ]);

            Profile::create([
                'user_id' => $user->id,
                'display_name' => $validated['name'] ?? $validated['email'],
            ]);

            return $user;
        });

        return response()->json(['success' => true, 'user' => $user, 'data' => $user], 201);
    }

    public function updateRole(Request $request, string $id): JsonResponse
    {
        $roleInput = $request->input('role', 'viewer');
        if ($roleInput === 'superadmin') $roleInput = 'admin';
        if ($roleInput === 'manager' || $roleInput === 'operator') $roleInput = 'editor';
        $request->merge(['role' => $roleInput]);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,editor,viewer'],
        ]);

        $caller = $request->user();
        if ($caller && $caller->id === $id && $validated['role'] !== 'admin') {
            return response()->json(['error' => 'Cannot remove your own admin role'], 400);
        }

        DB::transaction(function () use ($id, $validated) {
            UserRole::where('user_id', $id)->delete();
            UserRole::create([
                'user_id' => $id,
                'role' => $validated['role'],
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $caller = $request->user();
        if ($caller->id === $id) {
            return response()->json(['error' => 'Cannot delete your own account'], 400);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }
}
