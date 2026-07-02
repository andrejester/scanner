<?php

namespace App\Http\Controllers;

use App\DataTables\InboxDataTable;
use App\Library\Template;
use App\Models\Backend\Inbox;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    //
    public function index(InboxDataTable $dataTable)
    {
        $data = Template::get("datatable");

        $data['jsTambahan'] = "
        $('#inbox').addClass('active');
        ";
        return $dataTable->render("backend.inbox.inbox", $data);
        //return $dataTable->view("backend.inbox.inbox",$data);
    }

    public function show($Inbox)
    {
        $data = Template::get("datatable");
        $data['inbox'] = Inbox::findOrFail($Inbox);

        $data['inbox']->update(['read_at' => now()]);
        return view('backend.inbox.inbox_show', $data);
    }

    // Mark the message as read.
    public function markAsRead(Inbox $inbox)
    {
        $inbox->update(['read_at' => now()]);
    }

    public function create()
    {
        return view('contact'); // Menampilkan form kontak
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:15',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        dd($validated);
        // Simpan pesan ke dalam database
        $message = Inbox::create($validated);

        // Setelah pesan dikirim, kamu bisa redirect atau menampilkan pesan sukses
        return redirect()->route('contact')->with('success', 'Pesan berhasil dikirim!');
    }

    public function destroy(Inbox $inbox)
    {
        $inbox->delete();
        return redirect()->route('inboxes.index')->with('success', 'Message deleted successfully.');
    }
}
