<?php

namespace App\Http\Controllers;

use App\Models\HouseOccupantModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'end_in_date' => [
                'nullable',
                'required_if:is_current,0',
                'date',
                'after_or_equal:start_in_date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_current && $value && $value < date('Y-m-d')) {
                        $fail('Tanggal berakhir untuk penghuni aktif tidak boleh sebelum hari ini.');
                    }
                },
            ],
            'is_current' => 'required|boolean',
            'is_head_family' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->all();

        // Check if there are any current head of family in this house
        $hasHead = HouseOccupantModel::where('house_id', $request->house_id)
            ->where('is_current', true)
            ->where('is_head_family', true)
            ->exists();
            
        // If no head exists and this is an active occupant, automatically make them head
        if (!$hasHead && $request->is_current) {
            $data['is_head_family'] = true;
        }

        // If this occupant is head of family, unset any existing head of family for this house
        if (isset($data['is_head_family']) && $data['is_head_family']) {
            HouseOccupantModel::where('house_id', $request->house_id)
                ->where('is_head_family', true)
                ->update(['is_head_family' => false]);
        }

        $houseOccupant = HouseOccupantModel::create($data);

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
            'end_in_date' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () use ($request, $houseOccupant) {
                    $isCurrent = $request->has('is_current') ? $request->is_current : $houseOccupant->is_current;
                    return !$isCurrent;
                }),
                'date',
                'after_or_equal:start_in_date',
                function ($attribute, $value, $fail) use ($request, $houseOccupant) {
                    $isCurrent = $request->has('is_current') ? $request->is_current : $houseOccupant->is_current;
                    if ($isCurrent && $value && $value < date('Y-m-d')) {
                        $fail('Tanggal berakhir untuk penghuni aktif tidak boleh sebelum hari ini.');
                    }
                },
            ],
            'is_current' => 'sometimes|required|boolean',
            'is_head_family' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->all();
        $houseId = $request->house_id ?? $houseOccupant->house_id;
        $isCurrent = $request->has('is_current') ? $request->is_current : $houseOccupant->is_current;

        // Check if there are any current head of family in this house (excluding this record)
        $hasHead = HouseOccupantModel::where('house_id', $houseId)
            ->where('house_occupant_id', '!=', $houseOccupant->house_occupant_id)
            ->where('is_current', true)
            ->where('is_head_family', true)
            ->exists();
            
        // If no head exists and this is/remains an active occupant, automatically make them head
        if (!$hasHead && $isCurrent && !$request->has('is_head_family')) {
            $data['is_head_family'] = true;
        }

        // If updating to be head of family, unset others for this house
        if (isset($data['is_head_family']) && $data['is_head_family']) {
            HouseOccupantModel::where('house_id', $houseId)
                ->where('house_occupant_id', '!=', $houseOccupant->house_occupant_id)
                ->where('is_head_family', true)
                ->update(['is_head_family' => false]);
        } elseif ($request->has('is_head_family') && !$request->is_head_family && $isCurrent) {
            // If trying to UNSET head of family, check if they are the only active occupant
            $activeCount = HouseOccupantModel::where('house_id', $houseId)
                ->where('is_current', true)
                ->count();
            if ($activeCount <= 1) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['is_head_family' => ['Kepala keluarga tidak bisa dihapus jika hanya ada satu penghuni aktif.']]
                ], 400);
            }
        }

        $houseOccupant->update($data);

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
