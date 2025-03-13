<?php

namespace App\Http\Controllers;

use App\Models\FoodRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoodRequestController extends Controller
{
    public function create()
    {
        return view('food-requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'student_count' => 'required|integer|min:1',
            'requested_date' => 'required|date|after_or_equal:today',
            'additional_notes' => 'nullable|string',
        ]);

        $foodRequest = new FoodRequest($validated);
        $foodRequest->user_id = Auth::id();
        $foodRequest->status = 'pending';
        $foodRequest->save();

        return redirect()->route('food-requests.index')
            ->with('success', 'Permintaan makanan berhasil diajukan. Tim kami akan segera meninjau permintaan Anda.');
    }

    public function index()
    {
        $foodRequests = FoodRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('food-requests.index', compact('foodRequests'));
    }

    public function show(FoodRequest $foodRequest)
    {
        // Check if the authenticated user owns this food request
        if (Auth::id() !== $foodRequest->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('food-requests.show', compact('foodRequest'));
    }

    public function seerequests()
    {
        $foodRequests = FoodRequest::orderBy('created_at', 'desc')->paginate(10);

        return view('admin-requests.see-requests', compact('foodRequests'));
    }


    public function approve($id)
    {
        $foodRequest = FoodRequest::findOrFail($id);
        $foodRequest->status = 'approved';
        $foodRequest->save();

        return redirect()->back()->with('success', 'Permintaan makanan disetujui.');
    }

    public function reject($id)
    {
        $foodRequest = FoodRequest::findOrFail($id);
        $foodRequest->status = 'rejected';
        $foodRequest->save();

        return redirect()->back()->with('success', 'Permintaan makanan ditolak.');
    }


}
