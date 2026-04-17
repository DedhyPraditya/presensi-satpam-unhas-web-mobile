<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncidentReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|string|max:10485760', // base64
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = $request->user();
        $imagePath = null;

        if ($request->foto) {
            $imageName = $user->id . '_incident_' . time() . '.jpg';
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
            Storage::disk('public')->put('incidents/' . $imageName, $imageData);
            $imagePath = 'incidents/' . $imageName;
        }

        $report = IncidentReport::create([
            'user_id' => $user->id,
            'judul' => $request->judul,
            'tanggal' => now()->toDateString(),
            'jam' => now()->toTimeString(),
            'deskripsi' => $request->deskripsi,
            'foto' => $imagePath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan kejadian berhasil dikirim.',
            'data' => $report
        ]);
    }

    public function index(Request $request)
    {
        $reports = IncidentReport::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $reports
        ]);
    }
}
