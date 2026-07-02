<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\Note;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class NoteController extends Controller
{

    public function index()
    {
        $data = Template::get();
        array_push($data['pilihCss'],  "chart",  "apex-charts", "card-analytics");
        array_push($data['pilihJs'],   "chart");
        $data['jsTambahan'] = "
        $('#dashboards').addClass('open active');
        ";
        $data['notes'] = Note::where('user_id', auth()->user()->id)->get();

        return view('backend.note.note', $data);
    }
    //

    public function create()
    {
        // abort_if(Gate::denies('notes_write'), 403);
        log_custom("Buka menu tambah notes");
        $data = Template::get();

        return view('backend.note.note_create', $data);
    }


    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'message' => 'string|nullable'
        ]);

        // Retrieve all request data
        $data = $request->all();

        $data['user_id'] = auth()->user()->id;
        $data['date']    = date("Y-m-d");
        $data['top']     = 1;
        $data['left']    = 1;

        // Create a new Note using the validated data
        Note::create($data);

        // Show an info alert and redirect to the notes index
        Alert::info('Info Title', 'Data Berhasil Di tambah');

        // Redirect to the notes index page
        return redirect()->route('notes.index');
    }


    public function edit($Note)
    {
        $data = Template::get();
        $data['note'] = Note::findOrFail($Note);

        return view('backend.note.note_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        // Log request data to debug
        \Log::info($request->all());

        // Validate incoming data
        $request->validate([
            'message' => 'string|nullable'
        ]);

        // Ensure the note exists (optional)
        if (!$note) {
            return redirect()->route('notes.index')->withErrors('Note not found');
        }

        // Update the note
        $note->update($request->all());

        // Show success message
        Alert::info('Info Title', 'Data Berhasil Diperbarui');

        // Redirect to the index page
        return redirect()->route('notes.index');
    }



    public function savePosition(Request $request, $id)
    {
        $note = Note::findOrFail($id);
        $note->top = $request->top;
        $note->left = $request->left;
        $note->save();

        return response()->json(['status' => 'success']);
    }

    public function updatePosition(Request $request, $id)
    {
        $note = Note::findOrFail($id);
        $note->update([
            'top' => $request->top,
            'left' => $request->left,
        ]);
        return response()->json($note);
    }

    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        $note->delete();

        //Alert::info('Info Title', 'Data Berhasil Di Perbarui');
        return response()->json("reload", 200);
    }
}
