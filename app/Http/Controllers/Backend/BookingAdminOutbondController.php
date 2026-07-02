<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BookingOutbondDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\BookingOutbond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class BookingAdminOutbondController extends Controller
{
    //
    public function index(BookingOutbondDataTable $dataTable)
    {
        abort_if(Gate::denies('bookingoutbondadmin_read'), 403);
        log_custom("Buka menu booking outbond");

        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#bookingoutbondadmin').addClass('active');";

        return $dataTable->render("backend.bookingoutbond.booking_outbond", $data);
    }

    public function show($id)
    {
        abort_if(Gate::denies('bookingoutbondadmin_read'), 403);

        $booking = BookingOutbond::with('paket')->findOrFail($id);

        $data = Template::get("datatable");
        $data['booking'] = $booking;

        return view('backend.bookingoutbond.booking_outbond_detail', $data);
    }

    public function updateStatus(Request $request, $id)
    {
        abort_if(Gate::denies('bookingoutbondadmin_update'), 403);

        $request->validate([
            'status' => 'required|in:pending,approved,cancelled,completed',
        ]);

        $booking = BookingOutbond::findOrFail($id);
        $oldStatus = $booking->status;
        $booking->status = $request->status;
        $booking->save();

        log_custom("Update status booking outbond " . $booking->kode_booking . ": $oldStatus -> $request->status");

        Alert::success('Berhasil', 'Status booking outbond berhasil diperbarui');
        return redirect()->route('bookingoutbondadmin.index');
    }

    public function destroy(BookingOutbond $booking)
    {
        abort_if(Gate::denies('bookingoutbondadmin_delete'), 403);

        $bookingArray = $booking->toArray();
        $kodeBooking = $booking->kode_booking;

        $booking->delete();

        log_custom("Hapus Booking Outbond: $kodeBooking", $bookingArray);

        return response()->json('ok');
    }
}
