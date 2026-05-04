<?php

namespace App\Http\Controllers;

use App\Models\OccupantModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OccupantController extends Controller
{
    public function index()
    {
        $occupants = OccupantModel::all();
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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Exclude file field from request data to avoid UploadedFile object being passed to Eloquent
        $data = $request->except('occupant_ktp_photo');

        if ($request->hasFile('occupant_ktp_photo')) {
            $file = $request->file('occupant_ktp_photo');
            if (!$file->isValid()) {
                return response()->json(['message' => 'File upload tidak valid atau korup'], 400);
            }
            $path = Storage::disk('public')->putFile('ktp_photos', $file);
            $data['occupant_ktp_photo'] = $path;
        } else {
            $data['occupant_ktp_photo'] = null;
        }

        $occupant = OccupantModel::create($data);

        return response()->json([
            'message' => 'Occupant created successfully',
            'data' => $occupant
        ], 201);
    }

    public function show(OccupantModel $occupant)
    {
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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Exclude file field from request data to avoid UploadedFile object being passed to Eloquent
        $data = $request->except('occupant_ktp_photo');

        if ($request->hasFile('occupant_ktp_photo')) {
            $file = $request->file('occupant_ktp_photo');
            if (!$file->isValid()) {
                return response()->json(['message' => 'File upload tidak valid atau korup'], 400);
            }
            // Delete old photo if exists
            if (!empty($occupant->occupant_ktp_photo)) {
                Storage::disk('public')->delete($occupant->occupant_ktp_photo);
            }
            $path = Storage::disk('public')->putFile('ktp_photos', $file);
            $data['occupant_ktp_photo'] = $path;
        }

        $occupant->update($data);

        return response()->json([
            'message' => 'Occupant updated successfully',
            'data' => $occupant
        ]);
    }

    public function destroy(OccupantModel $occupant)
    {
        // Delete photo if exists
        if (!empty($occupant->occupant_ktp_photo)) {
            Storage::disk('public')->delete($occupant->occupant_ktp_photo);
        }

        OccupantModel::destroy($occupant->occupant_id);

        return response()->json(['message' => 'Occupant deleted successfully']);
    }
}
