<?php

namespace App\Http\Controllers;

use App\Models\HouseOccupantModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HouseOccupantController extends Controller
{
    public function index()
    {
        $houseOccupants = HouseOccupantModel::with(['house', 'occupant'])->get();
        return response()->json([
            'message' => 'Success retrieve all house occupants',
            'data' => $houseOccupants
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'house_id' => 'required|exists:m_houses,house_id',
            'occupant_id' => 'required|exists:m_occupants,occupant_id',
            'start_in_date' => 'required|date',
            'end_in_date' => 'nullable|date|after_or_equal:start_in_date',
            'is_current' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $houseOccupant = HouseOccupantModel::create($request->all());

        return response()->json([
            'message' => 'House occupant created successfully',
            'data' => $houseOccupant->load(['house', 'occupant'])
        ], 201);
    }

    public function show(HouseOccupantModel $houseOccupant)
    {
        return response()->json([
            'message' => 'Success retrieve house occupant detail',
            'data' => $houseOccupant->load(['house', 'occupant'])
        ]);
    }

    public function update(Request $request, HouseOccupantModel $houseOccupant)
    {
        $validator = Validator::make($request->all(), [
            'house_id' => 'sometimes|required|exists:m_houses,house_id',
            'occupant_id' => 'sometimes|required|exists:m_occupants,occupant_id',
            'start_in_date' => 'sometimes|required|date',
            'end_in_date' => 'sometimes|nullable|date|after_or_equal:start_in_date',
            'is_current' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $houseOccupant->update($request->all());

        return response()->json([
            'message' => 'House occupant updated successfully',
            'data' => $houseOccupant->load(['house', 'occupant'])
        ]);
    }

    public function destroy(HouseOccupantModel $houseOccupant)
    {
        HouseOccupantModel::destroy($houseOccupant->house_occupant_id);
        
        return response()->json(['message' => 'House occupant deleted successfully']);
    }
}
