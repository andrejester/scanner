<?php

namespace App\Http\Controllers;

use App\Models\Master\MasterPost;
use App\Models\Master\MasterInbox;
use App\Models\Event;
use App\Models\Master\MasterFaq;
use App\Models\Master\MasterPostCategory;
use App\Models\Master\MasterPostTag;
use App\Models\Master\MasterPostComment;
use App\Models\Master\MasterLayananKami;
use App\Models\Master\MasterPortofolio;
use App\Models\Master\MasterVideo;
use App\Models\Master\MasterSambutanDirektur;
use App\Models\System\Setting;
use App\Models\User;
use App\Notifications\InboxMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontendController extends Controller
{
    /**
     * Display home page with data from database
     */
    public function home()
    {
        $data = [];

        // Get About Us from Settings
        $data['about'] = Setting::first();

        // Get FAQs - limit to 6 for homepage
        $data['faqs'] = MasterFaq::where('status', 'active')
            ->orderBy('order')
            ->limit(6)
            ->get();

        // Get Recent Posts - limit to 3 for homepage carousel
        $data['blogs'] = MasterPost::where('status', 'active')
            ->select('id', 'title', 'slug', 'summary', 'photo', 'created_at')
            ->with('comments')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Get Services/Layanan Kami - limit to 5 for homepage
        $data['layanan'] = MasterLayananKami::select('id', 'title', 'deskripsi', 'icon')
            ->limit(5)
            ->get();

        // Get Portfolio/Proyek - limit to 6 for homepage
        $data['portofolio'] = MasterPortofolio::where('aktif', 'Y')
            ->select('id', 'title', 'slug', 'photo', 'description')
            ->limit(6)
            ->get();

        // Get Featured Video - limit to 1 for top banner
        $data['video'] = MasterVideo::where('status', 'active')
            ->select('id', 'title', 'slug', 'youtube', 'video', 'deskripsi', 'source_type')
            ->where('title', 'like', '%banner%')
            ->orderBy('tanggal', 'desc')
            ->first();

        // Get active TikTok videos for recent work section
        $data['tiktokVideos'] = MasterVideo::where('status', 'active')
            ->where('source_type', 'tiktok')
            ->select('id', 'title', 'slug', 'youtube', 'video', 'deskripsi')
            ->orderBy('tanggal', 'desc')
            ->limit(6)
            ->get();

        return view('frontend.pages.home', $data);
    }

    /**
     * Display services list page
     */
    public function services()
    {
        $data['about'] = Setting::first();
        $data['layanan'] = MasterLayananKami::select('id', 'title', 'deskripsi', 'icon')
            ->get();

        return view('frontend.pages.services', $data);
    }
    public function template()
    {

        return view('frontend.pages.services');
    }

    /**
     * Display portfolio list page
     */
    public function portfolio(Request $request)
    {
        $data['about']      = Setting::first();
        $data['categories'] = \App\Models\Master\MasterPortofolioCategory::where('status', 'active')
            ->orderBy('title')->get();

        $query = MasterPortofolio::where('aktif', 'Y')
            ->select('id', 'category_id', 'title', 'slug', 'photo', 'description')
            ->with('category');

        // Filter by kategori slug dari query string
        $activeCategory = null;
        if ($request->filled('kategori')) {
            $activeCategory = \App\Models\Master\MasterPortofolioCategory::where('slug', $request->kategori)
                ->where('status', 'active')->first();
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        $data['portofolio']     = $query->get();
        $data['activeCategory'] = $activeCategory;

        return view('frontend.pages.portfolio', $data);
    }

    /**
     * Display portfolio detail page
     */
    public function portfolioDetail(string $slug)
    {
        $data['about']     = Setting::first();
        $data['portfolio'] = MasterPortofolio::with('category')
            ->where('slug', $slug)
            ->where('aktif', 'Y')
            ->firstOrFail();

        // Proyek terkait — kategori sama, max 3
        $data['relatedPortfolios'] = MasterPortofolio::where('aktif', 'Y')
            ->where('slug', '!=', $slug)
            ->when($data['portfolio']->category_id, function ($q) use ($data) {
                $q->where('category_id', $data['portfolio']->category_id);
            })
            ->select('id', 'category_id', 'title', 'slug', 'photo', 'description')
            ->with('category')
            ->limit(3)
            ->get();

        // Navigasi prev / next
        $data['prevPortfolio'] = MasterPortofolio::where('aktif', 'Y')
            ->where('id', '<', $data['portfolio']->id)
            ->orderBy('id', 'desc')
            ->select('id', 'title', 'slug')
            ->first();

        $data['nextPortfolio'] = MasterPortofolio::where('aktif', 'Y')
            ->where('id', '>', $data['portfolio']->id)
            ->orderBy('id', 'asc')
            ->select('id', 'title', 'slug')
            ->first();

        return view('frontend.pages.portfolio-detail', $data);
    }

    /**
     * Display service detail page
     */
    public function serviceDetail(string $slug)
    {
        $data['about'] = Setting::first();

        $data['service'] = MasterLayananKami::findOrFail($slug);

        // Semua layanan untuk sidebar (termasuk yang aktif)
        $data['relatedServices'] = MasterLayananKami::select('id', 'title', 'icon')
            ->orderBy('id')
            ->get();

        return view('frontend.pages.service-detail', $data);
    }

    /**
     * Display blog detail page
     */
    public function BlogDetail(string $slug)
    {
        $blog = MasterPost::with(['category', 'comments.replies'])
            ->where('slug', $slug)
            ->firstOrFail();
        $blog->increment('dibaca');

        // Related blogs (same category)
        $relatedBlogs = MasterPost::where('post_cat_id', $blog->post_cat_id)
            ->where('status', 'active')
            ->where('slug', '!=', $slug)
            ->limit(3)
            ->get();

        // Sidebar data
        $tags        = MasterPostTag::all();
        $categories  = MasterPostCategory::where('status', 'active')->get();
        $recentBlogs = MasterPost::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(3)->get();

        // Prev / Next navigation
        $previousBlog = MasterPost::where('id', '<', $blog->id)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->first();

        $nextBlog = MasterPost::where('id', '>', $blog->id)
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->first();

        return view(
            'frontend.pages.blog-detail',
            compact('blog', 'relatedBlogs', 'tags', 'categories', 'recentBlogs', 'previousBlog', 'nextBlog')
        );
    }

    /**
     * Store comment on blog
     */
    public function storeComment(Request $request, string $slug)
    {
        $blog = MasterPost::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'comment' => 'required|string',
            'subject' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        MasterPostComment::create([
            'blog_id' => $blog->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'comment' => $request->comment,
            'subject' => $request->subject,
            'website' => $request->website,
            'status' => 'pending',
            'parent_id' => null,
        ]);

        return redirect()->back()->with('success', 'Your comment has been submitted and is awaiting approval.');
    }

    /**
     * Display blogs by tag
     */
    public function blogsByTag(string $slug)
    {
        $tag = MasterPostTag::where('slug', $slug)->firstOrFail();

        $blogs = MasterPost::where('status', 'active')
            ->where('tags', 'like', '%' . $tag->id . '%')
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        $popularTags = MasterPostTag::all();

        return view('frontend.pages.blog-by-tag', compact('tag', 'blogs', 'popularTags'));
    }

    /**
     * Display about page
     */
    public function about()
    {
        $data['about'] = Setting::first();
        return view('frontend.pages.about', $data);
    }

    /**
     * Display sambutan direktur page
     */
    public function sambutanDirektur()
    {
        $data['about'] = Setting::first();
        $data['sambutanData'] = MasterSambutanDirektur::where('is_active', true)->first();

        return view('frontend.pages.sambutan-direktur', $data);
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        $data['about'] = Setting::first();
        return view('frontend.pages.contact', $data);
    }

    /**
     * Search blogs
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return redirect()->route('home');
        }

        $blogs = MasterPost::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                    ->orWhere('summary', 'like', '%' . $query . '%')
                    ->orWhere('content', 'like', '%' . $query . '%');
            })
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        $popularTags = MasterPostTag::all();

        return view('frontend.pages.search', compact('query', 'blogs', 'popularTags'));
    }

    /**
     * Display FAQ page
     */
    public function faq()
    {
        $data['settings'] = Setting::query()->get();
        $data['faqs'] = MasterFaq::query()->where('status', 'active')->orderBy('order')->get();

        return view('frontend.pages.faq', $data);
    }

    /**
     * Display download page
     */
    public function download()
    {
        // Ambil semua kategori aktif beserta file downloadnya
        $data['categories'] = \App\Models\Master\MasterDownloadCategory::where('status', 'active')
            ->with(['downloads' => function ($q) {
                $q->orderBy('title');
            }])
            ->orderBy('title')
            ->get();

        $data['settings'] = \App\Models\System\Setting::query()->get();

        return view('frontend.pages.download', $data);
    }

    /**
     * Display privacy policy page
     */
    public function privacypolicy()
    {
        $data['settings'] = Setting::query()->get();
        return view('frontend.pages.privacypolicy', $data);
    }

    /**
     * Display blog list
     */
    public function listBlog(Request $request)
    {
        $data['about']      = Setting::first();
        $data['categories'] = MasterPostCategory::where('status', 'active')->get();
        $data['tags']       = MasterPostTag::all();

        $sort  = $request->get('sort', 'newest');
        $query = trim($request->get('q', ''));

        $blogs = MasterPost::where('status', 'active')->with('category', 'comments');

        if ($query) {
            $blogs->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                    ->orWhere('summary', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            });
        }

        switch ($sort) {
            case 'popular':
                $blogs->orderByDesc('dibaca');
                break;
            case 'oldest':
                $blogs->orderBy('created_at', 'asc');
                break;
            default:
                $blogs->orderBy('created_at', 'desc');
                break;
        }

        $data['blogs']       = $blogs->paginate(7)->withQueryString();
        $data['sort']        = $sort;
        $data['query']       = $query;
        $data['recentBlogs'] = MasterPost::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(3)->get();

        return view('frontend.pages.blog', $data);
    }

    /**
     * Display blogs by category
     */
    public function blogByCategory(Request $request, string $slug)
    {
        $data['about']      = Setting::first();
        $data['categories'] = MasterPostCategory::where('status', 'active')->get();
        $data['tags']       = MasterPostTag::all();
        $data['category']   = MasterPostCategory::where('slug', $slug)->firstOrFail();

        $sort = $request->get('sort', 'newest');

        $blogs = MasterPost::where('status', 'active')
            ->where('post_cat_id', $data['category']->id)
            ->with('category', 'comments');

        switch ($sort) {
            case 'popular':
                $blogs->orderByDesc('dibaca');
                break;
            case 'oldest':
                $blogs->orderBy('created_at', 'asc');
                break;
            default:
                $blogs->orderBy('created_at', 'desc');
                break;
        }

        $data['blogs']       = $blogs->paginate(9)->withQueryString();
        $data['sort']        = $sort;
        $data['query']       = '';
        $data['recentBlogs'] = MasterPost::where('status', 'active')
            ->orderBy('created_at', 'desc')->limit(3)->get();

        return view('frontend.pages.blog', $data);
    }

    /**
     * Store contact message
     */
    public function message(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $inbox = MasterInbox::create([
            'name'       => $request->name,
            'subject'    => $request->subject,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'message'    => $request->message,
            'ip_address' => $request->ip(),
        ]);

        // Kirim Web Push Notification ke semua admin
        try {
            $admins = User::role(['Super Admin', 'Admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new InboxMasuk($inbox));
            }
        } catch (\Exception $e) {
            // Silent fail — jangan sampai mengganggu flow user
            Log::error('Web Push gagal: ' . $e->getMessage());
        }

        return redirect()->route('contact')->with('success', 'Pesan berhasil dikirim!');
    }

    /**
     * Display TikTok video page
     */
    public function tiktok(Request $request)
    {
        $data['about'] = Setting::first();

        $query = trim($request->get('q', ''));

        $videos = MasterVideo::where('status', 'active')
            ->where('source_type', 'tiktok')
            ->select('id', 'title', 'slug', 'youtube', 'video', 'deskripsi', 'tanggal', 'dibaca');

        if ($query) {
            $videos->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                    ->orWhere('deskripsi', 'like', '%' . $query . '%');
            });
        }

        $data['videos'] = $videos->orderBy('tanggal', 'desc')->paginate(12)->withQueryString();
        $data['query']  = $query;

        return view('frontend.pages.tiktok', $data);
    }

    /**
     * Display event list
     */
    public function eventList()
    {
        $data['about'] = Setting::first();
        $data['events'] = Event::orderBy('tanggal_mulai', 'desc')->paginate(4);

        return view('frontend.pages.event', $data);
    }

    /**
     * Display event detail
     */
    public function eventDetail(string $slug)
    {
        $data['about'] = Setting::first();
        $data['event'] = Event::where('slug', $slug)->firstOrFail();

        $data['relatedEvents'] = Event::where('kategori', $data['event']->kategori)
            ->where('slug', '!=', $slug)
            ->where('status', 'aktif')
            ->orderBy('tanggal_mulai', 'desc')
            ->limit(3)
            ->get();

        return view('frontend.pages.event-detail', $data);
    }
}
