<?php


namespace App\Http\Controllers\Backend;

use App\DataTables\BannerAdsDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\BannerAds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class BannerAdsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BannerAdsDataTable $dataTable)
    {
        abort_if(Gate::denies('banner_read'), 403);
        log_custom("Buka menu banner iklan");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#banner-ads').addClass('active');";

        // Statistics for dashboard
        $data['totalBanners'] = BannerAds::count();
        $data['activeBanners'] = BannerAds::where('is_active', true)->count();
        $data['leftBanners'] = BannerAds::where('position', 'left')->count();
        $data['rightBanners'] = BannerAds::where('position', 'right')->count();
        $data['aboveLogoBanners'] = BannerAds::where('position', 'above_logo')->count();

        return $dataTable->render("backend.banner-ads.index", $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('banner_write'), 403);
        $data = Template::get("datatable");
        return view('backend.banner-ads.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('banner_write'), 403);

        $request->validate([
            'title'    => 'required|string|max:255',
            'position' => 'required|in:top,left,right,above_logo',
            'image'    => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link'     => 'nullable|url',
            'target'   => 'required|in:_blank,_self',
            'is_active' => 'boolean',
            'order'    => 'nullable|integer',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banneriklan', 'public_files_2');
        }

        BannerAds::create($data);

        Alert::success('Sukses', 'Banner iklan berhasil ditambahkan');
        return redirect()->route('banner-ads.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_if(Gate::denies('banner_update'), 403);
        $data = Template::get("datatable");
        $data['bannerAds'] = BannerAds::findOrFail($id);
        return view('backend.banner-ads.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BannerAds $bannerAd)
    {
        abort_if(Gate::denies('banner_update'), 403);

        $request->validate([
            'title'    => 'required|string|max:255',
            'position' => 'required|in:top,left,right,above_logo',
            'image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link'     => 'nullable|url',
            'target'   => 'required|in:_blank,_self',
            'is_active' => 'boolean',
            'order'    => 'nullable|integer',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('image')) {

            // delete old image
            if ($bannerAd->image && !filter_var($bannerAd->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public_files_2')->delete($bannerAd->image);
            }

            // upload new image
            $data['image'] = $request->file('image')->store('banneriklan', 'public_files_2');
        }

        log_custom("Update banner iklan " . $bannerAd->id, $bannerAd->toArray());
        $bannerAd->update($data);

        Alert::success('Sukses', 'Banner iklan berhasil diperbarui');
        return redirect()->route('banner-ads.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BannerAds $bannerAd)
    {
        abort_if(Gate::denies('banner_delete'), 403);

        // Delete image
        if ($bannerAd->image && !filter_var($bannerAd->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public_files_2')->delete($bannerAd->image);
        }

        $bannerAd->delete();
        log_custom("Hapus banner iklan " . $bannerAd->id);
        return response()->json('ok');
    }

    /**
     * Search banner ads
     */
    public function search(Request $request)
    {
        abort_if(Gate::denies('banner_read'), 403);

        $query = BannerAds::query();

        // Search by title
        if ($request->has('q') && $request->q) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        // Filter by position
        if ($request->has('position') && $request->position) {
            $query->where('position', $request->position);
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $banners = $query->orderBy('position', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $banners,
            'count' => $banners->count()
        ]);
    }
}
