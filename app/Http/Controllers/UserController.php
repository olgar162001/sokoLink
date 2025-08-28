<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users with filters
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        }

        $users = $query->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $users,
        ]);
    }

    /**
     * Update a user's role or status
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->has('role_id')) {
            $user->role_id = $request->role_id;
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $user->deleted_at = null;
            } elseif ($request->status === 'inactive') {
                $user->deleted_at = now();
            }
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }
}
