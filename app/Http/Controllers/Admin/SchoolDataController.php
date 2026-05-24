<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SchoolDataController extends Controller
{
    public function edit(): Response
    {
        $school = SchoolData::firstOrCreate(['id' => 1], ['name' => 'SMK Globin']);

        return Inertia::render('Admin/School', [
            'school' => $school,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'headmaster_name' => 'nullable|string|max:255',
            'treasurer_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'treasurer_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $school = SchoolData::firstOrCreate(['id' => 1]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('school', 'public');
        }

        if ($request->hasFile('signature')) {
            $validated['signature_path'] = $request->file('signature')->store('school', 'public');
        }

        if ($request->hasFile('treasurer_signature')) {
            $validated['treasurer_signature_path'] = $request->file('treasurer_signature')->store('school', 'public');
        }

        $school->update($validated);

        return back()->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function deleteImage(Request $request, string $type): RedirectResponse
    {
        $school = SchoolData::firstOrFail();

        $field = match ($type) {
            'logo' => 'logo_path',
            'signature' => 'signature_path',
            'treasurer_signature' => 'treasurer_signature_path',
            default => abort(404),
        };

        $oldPath = $school->$field;
        if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
            \Storage::disk('public')->delete($oldPath);
        }

        $school->update([$field => null]);

        return back()->with('success', 'Gambar berhasil dihapus.');
    }
}
