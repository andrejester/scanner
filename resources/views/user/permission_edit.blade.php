<div class="modal-content p-md-3 p-2">
    <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="mb-4 text-center">
            <h3 class="role-title">Edit New Role</h3>
            <p>Set role permissions</p>
        </div>
        <!-- Add role form -->
        <form id="form1" class="row g-3">
            <div class="col-12 mb-4">
                <label class="form-label" for="modalRoleName">Role Name</label>
                <input type="text" id="modalRoleName" name="name" class="form-control"
                    placeholder="Enter a role name" tabindex="-1" value="{{ $role->name }}" />
            </div>
            <div class="col-12">
                <h4>Role Permissions</h4>
                <!-- Permission table -->
                <div class="table-responsive">
                    <table class="table-flush-spacing table">
                        <tbody>
                            <tr>
                                <td class="fw-medium text-nowrap">Administrator Access <i
                                        class="bx bx-info-circle bx-xs" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Allows a full access to the system"></i></td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll" />
                                        <label class="form-check-label" for="selectAll">
                                            Select All
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            @foreach ($menu as $key => $value)
                                <tr>
                                    <td class="fw-medium text-nowrap">{{ $key }}</td>
                                    <td>
                                        <div class="d-flex">
                                            @foreach ($value as $key1 => $value1)
                                                @foreach ($value1 as $key2 => $value)
                                                    <div class="form-check me-lg-5 me-3">
                                                        <input class="form-check-input" name="permissions[]"
                                                            @if ($role->hasPermissionTo(strtolower($key1) . '_' . strtolower($value))) {{ 'checked' }} @endif
                                                            type="checkbox" id="{{ $key1 . '_' . $value }}"
                                                            value="{{ strtolower($key1) . '_' . strtolower($value) }}" />
                                                        <label class="form-check-label"
                                                            for="{{ $key1 . '_' . $value }}">
                                                            {{ $value }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <!-- Permission table -->
            </div>
            <div class="col-12 text-center">
                <button type="button" onclick="save('{{ route('permission.update', $role->id) }}','put')"
                    class="btn btn-primary me-sm-3 me-1">Submit</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                    aria-label="Close">Cancel</button>
            </div>
        </form>
        <!--/ Add role form -->
    </div>
</div>
<script>
    const t = document.querySelector("#selectAll"),
        o = document.querySelectorAll('[type="checkbox"]:not(#selectAll)');
    t.addEventListener("change", t => {
        o.forEach(e => {
            e.checked = t.target.checked
        })
    })
</script>
<div>
