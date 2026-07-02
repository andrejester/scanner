<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterGaleriDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Master\MasterPortofolio;
use App\Models\Master\MasterPortofolioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterGaleriController extends Controller
{
    public function index(MasterGaleriDataTable $dataTable)
    {
        abort_if(Gate::denies('mastergaleri_read'), 403);
        log_custom("Buka menu galeri ");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#mastergaleri').addClass('active');";
        return $dataTable->render("backend.mastergaleri.mastergaleri", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('mastergaleri_write'), 403);
        $data = Template::get("datatable");
        $data['categories'] = MasterPortofolioCategory::where('status', 'active')->orderBy('title')->get();
        return view('backend.mastergaleri.mastergaleri_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('mastergaleri_write'), 403);

        $request->validate([
            'category_id' => 'nullable|exists:master_portofolio_categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'aktif'       => 'required|in:Y,N',
        ]);

        $data = $request->except('photo');
        $data['slug'] = Str::slug($request->title);
        $data['photo'] = $this->uploadAndResizePortfolioImage($request->file('photo'));

        MasterPortofolio::create($data);

        Alert::info('Info', 'Data Portofolio Berhasil Ditambah');
        return redirect()->route('mastergaleri.index');
    }

    public function show(string $id) {}

    public function edit($id)
    {
        abort_if(Gate::denies('mastergaleri_update'), 403);
        $data = Template::get("datatable");
        $data['mastergaleri'] = MasterPortofolio::findOrFail($id);
        $data['categories'] = MasterPortofolioCategory::where('status', 'active')->orderBy('title')->get();
        return view('backend.mastergaleri.mastergaleri_edit', $data);
    }

    public function update(Request $request, MasterPortofolio $mastergaleri)
    {
        abort_if(Gate::denies('mastergaleri_update'), 403);

        $request->validate([
            'category_id' => 'nullable|exists:master_portofolio_categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'aktif'       => 'required|in:Y,N',
        ]);

        $data = $request->except('photo');
        $data['slug'] = Str::slug($request->title);

        if ($request->hasFile('photo')) {
            if ($mastergaleri->photo) {
                Storage::disk('public/files/2')->delete($mastergaleri->photo);

                if (Str::startsWith($mastergaleri->photo, 'portofolio/')) {
                    $oldOriginal = Str::replaceFirst('portofolio/', 'portofolio/original/', $mastergaleri->photo);
                    Storage::disk('public/files/2')->delete($oldOriginal);
                }
            }

            $data['photo'] = $this->uploadAndResizePortfolioImage($request->file('photo'));
        }

        log_custom("Update portofolio " . $mastergaleri->id, $mastergaleri->toArray());
        $mastergaleri->update($data);

        Alert::info('Info', 'Data Portofolio Berhasil Diperbarui');
        return redirect()->route('mastergaleri.index');
    }

    public function destroy(MasterPortofolio $mastergaleri)
    {
        abort_if(Gate::denies('mastergaleri_delete'), 403);

        if ($mastergaleri->photo) {
            Storage::disk('public/files/2')->delete($mastergaleri->photo);

            if (Str::startsWith($mastergaleri->photo, 'portofolio/')) {
                $oldOriginal = Str::replaceFirst('portofolio/', 'portofolio/original/', $mastergaleri->photo);
                Storage::disk('public/files/2')->delete($oldOriginal);
            }
        }

        $mastergaleri->delete();
        log_custom("Hapus portofolio  " . $mastergaleri->id);
        return response()->json('ok');
    }

    private function uploadAndResizePortfolioImage($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $resizedPath = 'portofolio/' . $filename;
        $originalPath = 'portofolio/original/' . $filename;

        // Save original file first
        $file->storeAs('portofolio/original', $filename, 'public/files/2');

        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            $file->storeAs('portofolio', $filename, 'public/files/2');
            return $resizedPath;
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        $targetWidth = 570;
        $targetHeight = 332;

        $sourceAspect = $originalWidth / $originalHeight;
        $targetAspect = $targetWidth / $targetHeight;

        if ($sourceAspect > $targetAspect) {
            $newHeight = $originalHeight;
            $newWidth = $originalHeight * $targetAspect;
            $cropX = ($originalWidth - $newWidth) / 2;
            $cropY = 0;
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalWidth / $targetAspect;
            $cropX = 0;
            $cropY = ($originalHeight - $newHeight) / 2;
        }

        $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($extension, ['png', 'webp'])) {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }

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

        $storagePath = storage_path('app/public/files/2/' . $resizedPath);
        $directory = dirname($storagePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        switch ($extension) {
            case 'png':
                imagepng($resizedImage, $storagePath, 9);
                break;
            case 'webp':
                imagewebp($resizedImage, $storagePath, 85);
                break;
            default:
                imagejpeg($resizedImage, $storagePath, 85);
                break;
        }

        imagedestroy($image);
        imagedestroy($resizedImage);

        return $resizedPath;
    }
}
