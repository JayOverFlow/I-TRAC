<div>
    <div class="row">
        <div class="col-3">
            <div class="card">
                <div class="card-body row p-4">
                    <div class="col-4">
                        <img src="{{ asset('img/department.png') }}" alt="Offices">
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
                        <img src="{{ asset('img/department.png') }}" alt="Departments">
                    </div>
                    <div class="col-8 text-end">
                        <h5 class="card-title fw-bold">Departments</h5>
                        <h5 class="mb-0 fw-bold">{{ $deptsCount }}</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="card">
                <div class="card-body row p-4">
                    <div class="col-4">
                        <img src="{{ asset('img/department.png') }}" alt="Faculty Members">
                    </div>
                    <div class="col-8 text-end">
                        <h5 class="card-title fw-bold">Faculty Members</h5>
                        <h5 class="mb-0 fw-bold">{{ $facultyCount }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card">
                <div class="card-body row p-4">
                    <div class="col-4">
                        <img src="{{ asset('img/department.png') }}" alt="Staffs">
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
