<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterDosenDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\MasterDosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class MasterDosenController extends Controller
{
    public function index(MasterDosenDataTable $dataTable)
    {
        abort_if(Gate::denies('masterdosen_read'), 403);
        log_custom("Buka menu dosen");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterdosen').addClass('active');";
        return $dataTable->render("backend.masterdosen.masterdosen", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterdosen_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masterdosen.masterdosen_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterdosen_write'), 403);

        $request->validate([
            'nama'                  => 'required|string|max:255',
            'nidn'                  => 'nullable|string|max:50',
            'nip'                   => 'nullable|string|max:50',
            'jabatan'               => 'nullable|string|max:255',
            'pendidikan_terakhir'   => 'nullable|string|max:100',
            'program_studi'         => 'nullable|string|max:255',
            'bidang_keahlian'       => 'nullable|string',
            'email'                 => 'nullable|email|max:255',
            'telepon'               => 'nullable|string|max:20',
            'foto'                  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'facebook'              => 'nullable|url|max:255',
            'twitter'               => 'nullable|url|max:255',
            'linkedin'              => 'nullable|url|max:255',
            'instagram'             => 'nullable|url|max:255',
            'biografi'              => 'nullable|string',
            'status'                => 'required|in:aktif,tidak_aktif',
            'urutan'                => 'nullable|integer|min:0',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->uploadAndResizeImage($request->file('foto'));
        }

        MasterDosen::create($data);

        Alert::success('Sukses', 'Data Dosen Berhasil Ditambahkan');
        return redirect()->route('masterdosen.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        abort_if(Gate::denies('masterdosen_update'), 403);
        $data = Template::get("datatable");
        $data['masterdosen'] = MasterDosen::findOrFail($id);
        return view('backend.masterdosen.masterdosen_edit', $data);
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('masterdosen_update'), 403);

        $masterdosen = MasterDosen::findOrFail($id);

        $request->validate([
            'nama'                  => 'required|string|max:255',
            'nidn'                  => 'nullable|string|max:50',
            'nip'                   => 'nullable|string|max:50',
            'jabatan'               => 'nullable|string|max:255',
            'pendidikan_terakhir'   => 'nullable|string|max:100',
            'program_studi'         => 'nullable|string|max:255',
            'bidang_keahlian'       => 'nullable|string',
            'email'                 => 'nullable|email|max:255',
            'telepon'               => 'nullable|string|max:20',
            'foto'                  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'facebook'              => 'nullable|url|max:255',
            'twitter'               => 'nullable|url|max:255',
            'linkedin'              => 'nullable|url|max:255',
            'instagram'             => 'nullable|url|max:255',
            'biografi'              => 'nullable|string',
            'status'                => 'required|in:aktif,tidak_aktif',
            'urutan'                => 'nullable|integer|min:0',
        ]);

        $data = $request->except('foto', '_token', '_method');

        Log::info('Update Dosen - ID: ' . $id);
        Log::info('Update Dosen - Dosen found: ' . $masterdosen->nama);

        if ($request->hasFile('foto')) {
            Log::info('Update Dosen - Processing new photo');

            // Hapus foto lama jika ada
            if ($masterdosen->foto) {
                $oldPhotoPath = storage_path('app/public/files/dosen/' . $masterdosen->foto);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                    Log::info('Update Dosen - Old photo deleted');
                }
            }

            // Upload foto baru
            $data['foto'] = $this->uploadAndResizeImage($request->file('foto'));
            Log::info('Update Dosen - New photo saved: ' . $data['foto']);
        }

        log_custom("Update dosen " . $masterdosen->id, $masterdosen->toArray());

        $updated = $masterdosen->update($data);

        Log::info('Update Dosen - Update result: ' . ($updated ? 'Success' : 'Failed'));

        Alert::success('Sukses', 'Data Dosen Berhasil Diperbarui');
        return redirect()->route('masterdosen.index');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('masterdosen_delete'), 403);

        $masterdosen = MasterDosen::findOrFail($id);

        // Hapus foto jika ada
        if ($masterdosen->foto) {
            $photoPath = storage_path('app/public/files/dosen/' . $masterdosen->foto);
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $masterdosen->delete();
        log_custom("Hapus dosen " . $masterdosen->id);
        return response()->json('ok');
    }

    /**
     * Upload and resize image to 390x400px
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function uploadAndResizeImage($file)
    {
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'Dosen/' . $filename;

        // Get image info
        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (!$image) {
            // Fallback to normal upload if image processing fails
            return $file->store('Dosen', 'public/files/dosen/');
        }

        // Get original dimensions
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // Target dimensions
        $targetWidth = 390;
        $targetHeight = 400;

        // Calculate crop dimensions to maintain aspect ratio
        $sourceAspect = $originalWidth / $originalHeight;
        $targetAspect = $targetWidth / $targetHeight;

        if ($sourceAspect > $targetAspect) {
            // Image is wider, crop width
            $newHeight = $originalHeight;
            $newWidth = $originalHeight * $targetAspect;
            $cropX = ($originalWidth - $newWidth) / 2;
            $cropY = 0;
        } else {
            // Image is taller, crop height
            $newWidth = $originalWidth;
            $newHeight = $originalWidth / $targetAspect;
            $cropX = 0;
            $cropY = ($originalHeight - $newHeight) / 2;
        }

        // Create new image with target dimensions
        $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Preserve transparency for PNG
        if ($file->getClientOriginalExtension() === 'png') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }

        // Crop and resize
        imagecopyresampled(
            $resizedImage,
            $image,
            0,
            0,
            $cropX,
            $cropY,
            $targetWidth,
            $targetHeight,
            $newWidth,
            $newHeight
        );

        // Save to storage
        $storagePath = storage_path('app/public/files/dosen/' . $path);

        // Ensure directory exists
        $directory = dirname($storagePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Save based on file type
        switch ($file->getClientOriginalExtension()) {
            case 'png':
                imagepng($resizedImage, $storagePath, 9);
                break;
            case 'webp':
                imagewebp($resizedImage, $storagePath, 85);
                break;
            default: // jpg, jpeg
                imagejpeg($resizedImage, $storagePath, 85);
                break;
        }

        // Free memory
        imagedestroy($image);
        imagedestroy($resizedImage);

        return $path;
    }
}
