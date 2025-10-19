<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Moderator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ModeratorController extends Controller
{
    public function index()
    {
        $moderators = Moderator::all();
        return response()->json(['data' => $moderators]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:moderators',
            'password' => 'required|string|min:6',
        ]);

        $moderator = Moderator::create([
            'full_name' => $validated['full_name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['data' => $moderator], 201);
    }

    public function show(Moderator $moderator)
    {
        return response()->json(['data' => $moderator]);
    }

    public function update(Request $request, Moderator $moderator)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('moderators')->ignore($moderator->id)],
            'password' => 'nullable|string|min:6',
        ]);

        $moderator->full_name = $validated['full_name'];
        $moderator->username = $validated['username'];

        if (isset($validated['password'])) {
            $moderator->password = Hash::make($validated['password']);
        }

        $moderator->save();

        return response()->json(['data' => $moderator]);
    }

    public function destroy(Moderator $moderator)
    {
        // Prevent self-deletion
        if ($moderator->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account'], 422);
        }

        $moderator->delete();
        return response()->json(null, 204);
    }

    public function profile()
    {
        return response()->json(['data' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $moderator = auth()->user();

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('moderators')->ignore($moderator->id)],
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        // Verify current password if changing password
        if (isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $moderator->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }
        }

        $moderator->full_name = $validated['full_name'];
        $moderator->username = $validated['username'];

        if (isset($validated['new_password'])) {
            $moderator->password = Hash::make($validated['new_password']);
        }

        $moderator->save();

        return response()->json(['data' => $moderator]);
    }
} 