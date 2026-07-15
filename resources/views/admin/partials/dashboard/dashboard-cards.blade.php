<div>
    <div class="row">
        <div class="col-3">
            <div class="card">
                <div class="card-body row p-4">
                    <div class="col-4">
                        <img src="{{ asset('img/offices.svg') }}" alt="Offices">
                    </div>
                    <div class="col-8 text-end">
                        <h5 class="card-title fw-bold">Offices</h5>
                        <h5 class="mb-0 fw-bold">{{ $officesCount }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card">
                <div class="card-body row p-4">
                    <div class="col-4">
                        <img src="{{ asset('img/departments.svg') }}" alt="Programs">
                    </div>
                    <div class="col-8 text-end">
                        <h5 class="card-title fw-bold">Programs</h5>
                        <h5 class="mb-0 fw-bold">{{ $programsCount }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card">
                <div class="card-body row p-4">
                    <div class="col-4">
                        <img src="{{ asset('img/faculty.svg') }}" alt="Faculties">
                    </div>
                    <div class="col-8 text-end">
                        <h5 class="card-title fw-bold">Faculties</h5>
                        <h5 class="mb-0 fw-bold">{{ $facultyCount }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card">
                <div class="card-body row p-4">
                    <div class="col-4">
                        <img src="{{ asset('img/staff.svg') }}" alt="Staffs">
                    </div>
                    <div class="col-8 text-end">
                        <h5 class="card-title fw-bold">Staffs</h5>
                        <h5 class="mb-0 fw-bold">{{ $staffCount }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>