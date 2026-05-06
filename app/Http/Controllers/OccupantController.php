<?php

namespace App\Http\Controllers;

use App\Models\OccupantModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OccupantController extends Controller
{
    public function index()
    {
        $occupants = OccupantModel::with('users:user_id,username,occupant_id')
            ->select([
                'occupant_id',
                'occupant_name',
                'occupant_status',
                'occupant_phone_number',
                'is_married',
                'occupant_ktp_photo',
                'occupant_gender'
            ])->get();

        return response()->json([
            'message' => 'Success retrieve all occupants',
            'data' => $occupants
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'occupant_name' => 'required|string|max:255',
            'occupant_ktp_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'occupant_status' => 'required|string',
            'occupant_phone_number' => 'required|string|max:20',
            'is_married' => 'required|boolean',
            'occupant_gender' => 'nullable|in:L,P',
            'username' => 'required|string|unique:m_users,username',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Exclude file field from request data
        $data = $request->except(['occupant_ktp_photo', 'username', 'password']);

        if ($request->hasFile('occupant_ktp_photo')) {
            $file = $request->file('occupant_ktp_photo');
            if (!$file->isValid()) {
                return response()->json(['message' => 'File upload tidak valid atau korup'], 400);
            }
            $filename = $file->hashName();
            $file->storeAs('ktp_photos', $filename, 'public');
            $data['occupant_ktp_photo'] = $filename;
        } else {
            $data['occupant_ktp_photo'] = null;
        }

        $occupant = OccupantModel::create($data);

        // Create user
        UserModel::create([
            'username' => $request->username,
            'password' => $request->password, // Will be hashed by model cast
            'is_rt' => false,
            'occupant_id' => $occupant->occupant_id,
        ]);

        return response()->json([
            'message' => 'Occupant and User created successfully',
            'data' => $occupant->load('users')
        ], 201);
    }

    public function show(OccupantModel $occupant)
    {
        $occupant->load(['houseOccupants.house', 'payments.duesType']);
        return response()->json([
            'message' => 'Success retrieve occupant detail',
            'data' => $occupant
        ]);
    }

    public function update(Request $request, OccupantModel $occupant)
    {
        $validator = Validator::make($request->all(), [
            'occupant_name' => 'sometimes|required|string|max:255',
            'occupant_ktp_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'occupant_status' => 'sometimes|required|string',
            'occupant_phone_number' => 'sometimes|required|string|max:20',
            'is_married' => 'sometimes|required|boolean',
            'occupant_gender' => 'nullable|in:L,P',
            'username' => 'sometimes|required|string|unique:m_users,username,' . $occupant->users()->first()?->user_id . ',user_id',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Exclude file field from request data
        $data = $request->except(['occupant_ktp_photo', 'username', 'password']);

        if ($request->hasFile('occupant_ktp_photo')) {
            $file = $request->file('occupant_ktp_photo');
            if (!$file->isValid()) {
                return response()->json(['message' => 'File upload tidak valid atau korup'], 400);
            }
            // Delete old photo if exists
            if (!empty($occupant->occupant_ktp_photo)) {
                Storage::disk('public')->delete('ktp_photos/' . $occupant->occupant_ktp_photo);
            }
            $filename = $file->hashName();
            $file->storeAs('ktp_photos', $filename, 'public');
            $data['occupant_ktp_photo'] = $filename;
        }

        $occupant->update($data);

        // Update or create user
        $userData = [];
        if ($request->has('username')) $userData['username'] = $request->username;
        if ($request->has('password') && !empty($request->password)) $userData['password'] = $request->password;

        if (!empty($userData)) {
            $user = $occupant->users()->first();
            if ($user) {
                $user->update($userData);
            } else {
                UserModel::create(array_merge($userData, [
                    'is_rt' => false,
                    'occupant_id' => $occupant->occupant_id,
                    'password' => $userData['password'] ?? 'password123' // default if somehow missing
                ]));
            }
        }

        return response()->json([
            'message' => 'Occupant updated successfully',
            'data' => $occupant->load('users')
        ]);
    }

    public function destroy(OccupantModel $occupant)
    {
        // Delete photo if exists
        if (!empty($occupant->occupant_ktp_photo)) {
            Storage::disk('public')->delete('ktp_photos/' . $occupant->occupant_ktp_photo);
        }

        $user = UserModel::where('occupant_id', $occupant->occupant_id)->first();
        if ($user) {
            $user->delete();
        }

        OccupantModel::destroy($occupant->occupant_id);

        return response()->json(['message' => 'Occupant deleted successfully']);
    }

    public function showKtpPhoto($filename)
    {
        $path = 'ktp_photos/' . $filename;
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Photo not found'], 404);
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
