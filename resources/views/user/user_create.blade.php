<div class="modal-content">
    <form id='form1'>
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel1">Tambah User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col mb-3">
                    <label for="nameBasic" class="form-label">Nama</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Nama" />
                </div>
                <div class="col-6 mb-3">
                    <label for="nameBasic" class="form-label">Role Permission</label>
                    <select class="form-control select2" name="role" id="role" required>
                        <option value=""> Pilih Role</option>
                        @foreach (Spatie\Permission\Models\Role::where('name', '!=', 'Admin')->get() as $account)
                            <option value="{{ $account->id }}">{{ $account->id . '-' . $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label for="nameBasic" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Email"
                        required />
                </div>
            </div>

            <div class="row">
                <div class="col mb-3">
                    <label for="google2fa">Authenticator 2 Faktur</label>
                    {!! $qrCode !!}
                    <input type="show" name="google2fa_secret" value="{{ $secret }}">
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                Close
            </button>
            <button type="button" p class="btn btn-primary" onclick="save('{{ route('user.store') }}','post')"><i
                    class="bx bx-save"></i> Save
                changes</button>
        </div>
    </form>
</div>
<script>
    "use strict";
    $(function() {
        var o = document.querySelector(".numeral-mask");

        e && autosize(e);
        var s, i, e = $(".select2"),
            e = (e.length && e.each(function() {
                var e = $(this);
                e.wrap('<div class="position-relative"></div>').select2({
                    dropdownParent: e.parent(),
                    placeholder: e.data("placeholder")
                })
            }), $(".form-repeater"));
        e.length && (s = 2, i = 1, e.on("submit", function(e) {
            e.preventDefault()
        }), e.repeater({
            show: function() {
                var a = $(this).find(".form-control, .form-select"),
                    t = $(this).find(".form-label");
                a.each(function(e) {
                    var r = "form-repeater-" + s + "-" + i;
                    $(a[e]).attr("id", r), $(t[e]).attr("for", r), i++
                }), s++, $(this).slideDown(), $(".select2-container").remove(), $(
                    ".select2.form-select").select2({
                    placeholder: "Placeholder text"
                }), $(".select2-container").css("width", "100%"), $(
                    ".form-repeater:first .form-select").select2({
                    dropdownParent: $(this).parent(),
                    placeholder: "Placeholder text"
                }), $(".position-relative .select2").each(function() {
                    $(this).select2({
                        dropdownParent: $(this).closest(".position-relative")
                    })
                })
            }
        }))
    });
</script>
