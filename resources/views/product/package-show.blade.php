@extends('layouts.product')

@section('content')
<!-- MLM Package Selection -->

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">Select a Product Package to Join</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <div class="row">
                    <!-- Product Card Example -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-radius-xl position-relative">
                            <div class="card-body text-center">
                                <img src="../assets/img/products/vitamin-c.png" alt="Vitamin C" class="w-50 mb-3">
                                <h6 class="mb-2 text-dark">Vitamin C Package</h6>
                                <p class="text-xs text-muted">Boosts immunity and supports overall health.</p>
                                <h5 class="text-gradient text-success font-weight-bold">₱1,250</h5>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="mlm_package" id="package1" value="vitamin_c">
                                    <label class="form-check-label" for="package1">
                                        Select Package
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-radius-xl position-relative">
                            <div class="card-body text-center">
                                <img src="../assets/img/products/omega-3.png" alt="Omega 3" class="w-50 mb-3">
                                <h6 class="mb-2 text-dark">Omega 3 Package</h6>
                                <p class="text-xs text-muted">Supports heart and brain health.</p>
                                <h5 class="text-gradient text-success font-weight-bold">₱1,800</h5>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="mlm_package" id="package2" value="omega_3">
                                    <label class="form-check-label" for="package2">
                                        Select Package
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-radius-xl position-relative">
                            <div class="card-body text-center">
                                <img src="../assets/img/products/multivitamin.png" alt="Multivitamin" class="w-50 mb-3">
                                <h6 class="mb-2 text-dark">Multivitamin Package</h6>
                                <p class="text-xs text-muted">Daily essential vitamins and minerals.</p>
                                <h5 class="text-gradient text-success font-weight-bold">₱2,100</h5>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="mlm_package" id="package3" value="multivitamin">
                                    <label class="form-check-label" for="package3">
                                        Select Package
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-3">
                    <button class="btn btn-primary" id="joinMlmBtn">Join MLM with Selected Package</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('joinMlmBtn').addEventListener('click', () => {
    const selectedPackage = document.querySelector('input[name="mlm_package"]:checked');
    if (!selectedPackage) {
        alert('Please select a product package to join.');
        return;
    }
    // Here you can send the selectedPackage.value to your backend via AJAX or form submission
    console.log('Selected Package:', selectedPackage.value);
    alert(`You selected: ${selectedPackage.value} package`);
});
</script>
@endsection