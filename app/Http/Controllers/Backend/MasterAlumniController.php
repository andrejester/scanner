<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterAlumniDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterAlumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;

class MasterAlumniController extends Controller
{
    public function index(MasterAlumniDataTable $dataTable)
    {
        abort_if(Gate::denies('masteralumni_read'), 403);
        log_custom("Buka menu alumni");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masteralumni').addClass('active');";
        return $dataTable->render("backend.masteralumni.masteralumni", $data);
    }

    /**
     * Download template CSV untuk upload kolektif
     */
    public function downloadTemplate()
    {
        abort_if(Gate::denies('masteralumni_read'), 403);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_alumni.csv"',
        ];

        $columns = [
            'nama',
            'nim',
            'jenjang',
            'program_studi',
            'tahun_lulus',
            'pekerjaan',
            'instansi',
            'testimoni',
            'is_active',
            'is_pinned'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Write BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header
            fputcsv($file, $columns);

            // Write example data
            fputcsv($file, [
                'John Doe',
                '123456789',
                's2',
                'Teknik Informatika',
                '2023',
                'Software Engineer',
                'PT. Tech Indonesia',
                'Pengalaman belajar yang sangat baik',
                '1',
                '0'
            ]);

            fputcsv($file, [
                'Jane Smith',
                '987654321',
                's3',
                'Sistem Informasi',
                '2022',
                'Data Analyst',
                'PT. Data Solutions',
                'Kampus yang luar biasa',
                '1',
                '1'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilkan form upload CSV
     */
    public function uploadForm()
    {
        abort_if(Gate::denies('masteralumni_write'), 403);

        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masteralumni').addClass('active');";

        return view('backend.masteralumni.masteralumni_upload', $data);
    }

    /**
     * Preview data dari CSV sebelum diproses
     */
    public function previewCsv(Request $request)
    {
        abort_if(Gate::denies('masteralumni_write'), 403);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            // Read CSV file
            $csvData = array_map(function ($line) {
                return str_getcsv($line, ',', '"', '\\');
            }, file($path));

            // Remove BOM if exists
            if (isset($csvData[0][0])) {
                $csvData[0][0] = preg_replace('/^\x{FEFF}/u', '', $csvData[0][0]);
            }

            // Get header
            $header = array_shift($csvData);

            // Validate header
            $expectedHeaders = ['nama', 'nim', 'jenjang', 'program_studi', 'tahun_lulus', 'pekerjaan', 'instansi', 'testimoni', 'is_active', 'is_pinned'];

            if (count(array_diff($expectedHeaders, $header)) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format CSV tidak sesuai. Silakan download template terlebih dahulu.'
                ], 422);
            }

            // Process data
            $processedData = [];
            $errors = [];

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2; // +2 karena header di row 1 dan index mulai dari 0

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map data
                $rowData = array_combine($header, $row);

                // Validate row
                $validator = Validator::make($rowData, [
                    'nama'          => 'required|string|max:255',
                    'nim'           => 'nullable|string|max:50',
                    'jenjang'       => 'nullable|in:s2,s3',
                    'program_studi' => 'nullable|string|max:255',
                    'tahun_lulus'   => 'nullable|integer|min:1900|max:' . date('Y'),
                    'pekerjaan'     => 'nullable|string|max:255',
                    'instansi'      => 'nullable|string|max:255',
                    'testimoni'     => 'nullable|string',
                    'is_active'     => 'required|in:0,1',
                    'is_pinned'     => 'nullable|in:0,1',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->all()
                    ];
                } else {
                    $processedData[] = [
                        'row' => $rowNumber,
                        'data' => $rowData,
                        'status' => 'valid'
                    ];
                }
            }

            // Store data in session for later processing
            session(['csv_preview_data' => $processedData]);

            return response()->json([
                'success' => true,
                'data' => $processedData,
                'errors' => $errors,
                'total' => count($processedData),
                'error_count' => count($errors)
            ]);
        } catch (\Exception $e) {
            Log::error('CSV Preview Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membaca file CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses import data dari CSV ke database
     */
    public function processCsv(Request $request)
    {
        abort_if(Gate::denies('masteralumni_write'), 403);

        try {
            $previewData = session('csv_preview_data');

            if (!$previewData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data preview tidak ditemukan. Silakan upload ulang file CSV.'
                ], 422);
            }

            DB::beginTransaction();

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($previewData as $item) {
                try {
                    $data = $item['data'];

                    // Convert string to integer for boolean fields
                    $data['is_active'] = (int) $data['is_active'];
                    $data['is_pinned'] = isset($data['is_pinned']) ? (int) $data['is_pinned'] : 0;

                    // Convert empty strings to null
                    foreach ($data as $key => $value) {
                        if ($value === '' || $value === null) {
                            $data[$key] = null;
                        }
                    }

                    MasterAlumni::create($data);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'row' => $item['row'],
                        'error' => $e->getMessage()
                    ];
                    Log::error('CSV Import Error Row ' . $item['row'] . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            // Clear session data
            session()->forget('csv_preview_data');

            log_custom("Import alumni dari CSV - Berhasil: {$successCount}, Gagal: {$failedCount}");

            return response()->json([
                'success' => true,
                'message' => "Import selesai! Berhasil: {$successCount}, Gagal: {$failedCount}",
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV Process Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        abort_if(Gate::denies('masteralumni_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masteralumni.masteralumni_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masteralumni_write'), 403);

        $request->validate([
            'nama'          => 'required|string|max:255',
            'nim'           => 'nullable|string|max:50',
            'jenjang'       => 'nullable|in:s2,s3',
            'program_studi' => 'nullable|string|max:255',
            'tahun_lulus'   => 'nullable|integer|min:1900|max:' . date('Y'),
            'pekerjaan'     => 'nullable|string|max:255',
            'instansi'      => 'nullable|string|max:255',
            'testimoni'     => 'nullable|string',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'     => 'required|in:0,1',
            'is_pinned'     => 'nullable|boolean',
        ]);

        $data = $request->except('foto');
        $data['is_pinned'] = $request->has('is_pinned') ? 1 : 0;

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->uploadAndResizeImage($request->file('foto'));
        }

        MasterAlumni::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('masteralumni.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        abort_if(Gate::denies('masteralumni_update'), 403);
        $data = Template::get("datatable");
        $data['masteralumni'] = MasterAlumni::findOrFail($id);
        return view('backend.masteralumni.masteralumni_edit', $data);
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('masteralumni_update'), 403);

        // Find alumni by ID
        $masteralumni = MasterAlumni::findOrFail($id);

        $request->validate([
            'nama'          => 'required|string|max:255',
            'nim'           => 'nullable|string|max:50',
            'jenjang'       => 'nullable|in:s2,s3',
            'program_studi' => 'nullable|string|max:255',
            'tahun_lulus'   => 'nullable|integer|min:1900|max:' . date('Y'),
            'pekerjaan'     => 'nullable|string|max:255',
            'instansi'      => 'nullable|string|max:255',
            'testimoni'     => 'nullable|string',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'     => 'required|in:0,1',
            'is_pinned'     => 'nullable|boolean',
        ]);

        // Ambil semua data kecuali foto, _token, dan _method
        $data = $request->except('foto', '_token', '_method');
        $data['is_pinned'] = $request->has('is_pinned') ? 1 : 0;

        Log::info('Update Alumni - ID: ' . $id);
        Log::info('Update Alumni - Alumni found: ' . $masteralumni->nama);

        if ($request->hasFile('foto')) {
            Log::info('Update Alumni - Processing new photo');

            // Hapus foto lama jika ada
            if ($masteralumni->foto) {
                $oldPhotoPath = storage_path('app/public/files/2/' . $masteralumni->foto);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                    Log::info('Update Alumni - Old photo deleted');
                }
            }

            // Upload foto baru
            $data['foto'] = $this->uploadAndResizeImage($request->file('foto'));
            Log::info('Update Alumni - New photo saved: ' . $data['foto']);
        }

        log_custom("Update alumni " . $masteralumni->id, $masteralumni->toArray());

        // Update data
        $updated = $masteralumni->update($data);

        Log::info('Update Alumni - Update result: ' . ($updated ? 'Success' : 'Failed'));

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('masteralumni.index');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('masteralumni_delete'), 403);

        $masteralumni = MasterAlumni::findOrFail($id);

        // Hapus foto jika ada
        if ($masteralumni->foto) {
            $photoPath = storage_path('app/public/files/2/' . $masteralumni->foto);
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $masteralumni->delete();
        log_custom("Hapus alumni " . $masteralumni->id);
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
        $path = 'Alumni/' . $filename;

        // Get image info
        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (!$image) {
            // Fallback to normal upload if image processing fails
            return $file->store('Alumni', 'public/files/2/');
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
        $storagePath = storage_path('app/public/files/2/' . $path);

        // Ensure directory exists
        $directory = dirname($storagePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Save based on file type
        switch ($file->getClientOriginalExtension()) {
            case 'png':
                imagepng($resizedImage, $storagePath, 9); // Max compression for PNG
                break;
            case 'webp':
                imagewebp($resizedImage, $storagePath, 85); // 85% quality for WebP
                break;
            default: // jpg, jpeg
                imagejpeg($resizedImage, $storagePath, 85); // 85% quality for JPEG
                break;
        }

        // Free memory
        imagedestroy($image);
        imagedestroy($resizedImage);

        return $path;
    }
}
