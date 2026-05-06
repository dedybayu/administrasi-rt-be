<?php

namespace App\Http\Controllers;

use App\Models\HouseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HouseController extends Controller
{
    public function index()
    {
        $houses = HouseModel::select('house_id', 'house_name', 'house_number')
            ->withCount(['houseOccupants as house_occupants_count' => function ($query) {
                $query->where('is_current', true);
            }])
            ->get();
            
        return response()->json([
            'message' => 'Success retrieve all houses',
            'data' => $houses
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'house_name' => 'required|string|max:255',
            'house_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $house = HouseModel::create($request->all());

        return response()->json([
            'message' => 'House created successfully',
            'data' => $house
        ], 201);
    }

    public function show(HouseModel $house)
    {
        $house->load([
            'houseOccupants.occupant',
            'houseOccupants.payments.duesType',
            'houseOccupants.payments.payerOccupant'
        ]);
        return response()->json([
            'message' => 'Success retrieve house detail',
            'data' => $house
        ]);
    }

    public function update(Request $request, HouseModel $house)
    {
        $validator = Validator::make($request->all(), [
            'house_name' => 'sometimes|required|string|max:255',
            'house_number' => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $house->update($request->all());

        return response()->json([
            'message' => 'House updated successfully',
            'data' => $house
        ]);
    }

    public function destroy(HouseModel $house)
    {
        HouseModel::destroy($house->house_id);

        return response()->json(['message' => 'House deleted successfully']);
    }

    public function occupants(HouseModel $house)
    {
        $occupants = $house->houseOccupants()->with('occupant')->get();
        return response()->json([
            'message' => 'Success retrieve house occupants',
            'data' => $occupants
        ]);
    }
}
