<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BookingDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\Booking;
use App\Models\Backend\Pelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class BookingController extends Controller
{
    public function index(BookingDataTable $dataTable)
    {
        abort_if(Gate::denies('bookingadmin_read'), 403);
        log_custom("Buka menu booking");

        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#bookingadmin').addClass('active');";

        return $dataTable->render("backend.booking.booking", $data);
    }

    public function show($id)
    {
        abort_if(Gate::denies('bookingadmin_read'), 403);

        $booking = Booking::with('pelatihan')->findOrFail($id);

        $data = Template::get("datatable");
        $data['booking'] = $booking;

        return view('backend.booking.booking_detail', $data);
    }

    public function updateStatus(Request $request, $id)
    {
        abort_if(Gate::denies('bookingadmin_update'), 403);

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'payment_status' => 'required|in:unpaid,paid,refunded',
        ]);

        $booking = Booking::findOrFail($id);
        $oldStatus = $booking->status;
        $booking->status         = $request->status;
        $booking->payment_status = $request->payment_status;
        $booking->save();

        log_custom("Update status booking " . $booking->kode_booking . ": $oldStatus -> $request->status");

        Alert::success('Berhasil', 'Status booking berhasil diperbarui');
        return redirect()->route('bookingadmin.index');
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        abort_if(Gate::denies('bookingadmin_update'), 403);

        $request->validate([
            'payment_status' => 'required|in:unpaid,paid,refunded',
        ]);

        $booking = Booking::findOrFail($id);
        $oldStatus = $booking->payment_status;
        $booking->payment_status = $request->payment_status;
        $booking->save();

        log_custom("Update payment status booking " . $booking->kode_booking . ": $oldStatus -> $request->payment_status");

        return response()->json(['success' => true]);
    }

    public function destroy(Booking $booking)
    {
        abort_if(Gate::denies('bookingadmin_delete'), 403);

        $bookingArray = $booking->toArray();
        $kodeBooking = $booking->kode_booking;

        $booking->delete();

        log_custom("Hapus Booking: $kodeBooking", $bookingArray);

        return response()->json('ok');
    }
}
