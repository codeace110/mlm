@extends('layouts.dashboard')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">Select a Product Package to Join</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <div class="row">
                    @php
                        $products = [
                            ['name'=>'Vitamin C Package','desc'=>'Boosts immunity and supports overall health.','price'=>'₱1,250','value'=>'vitamin_c','img'=>'https://cdn.pixabay.com/photo/2016/04/01/09/03/oranges-1305193_1280.jpg'],
                            ['name'=>'Omega 3 Package','desc'=>'Supports heart and brain health.','price'=>'₱1,800','value'=>'omega_3','img'=>'https://cdn.pixabay.com/photo/2017/09/20/14/41/fish-2769236_1280.jpg'],
                            ['name'=>'Multivitamin Package','desc'=>'Daily essential vitamins and minerals.','price'=>'₱2,100','value'=>'multivitamin','img'=>'https://cdn.pixabay.com/photo/2016/03/05/19/02/vitamins-1238649_1280.jpg'],
                            ['name'=>'Vitamin D Package','desc'=>'Supports bone and immune health.','price'=>'₱1,500','value'=>'vitamin_d','img'=>'https://cdn.pixabay.com/photo/2016/02/24/16/53/pills-1225412_1280.jpg'],
                            ['name'=>'Calcium + Magnesium','desc'=>'Supports strong bones and muscles.','price'=>'₱1,900','value'=>'calcium_magnesium','img'=>'https://cdn.pixabay.com/photo/2015/04/10/00/41/pills-715496_1280.jpg'],
                            ['name'=>'Probiotic Package','desc'=>'Supports digestive health.','price'=>'₱2,200','value'=>'probiotic','img'=>'https://cdn.pixabay.com/photo/2017/06/09/19/19/probiotics-2381940_1280.jpg'],
                            ['name'=>'Collagen Powder','desc'=>'Promotes healthy skin and joints.','price'=>'₱2,500','value'=>'collagen','img'=>'https://cdn.pixabay.com/photo/2017/08/30/13/48/collagen-2699786_1280.jpg'],
                            ['name'=>'Protein Powder','desc'=>'Supports muscle growth and recovery.','price'=>'₱2,800','value'=>'protein','img'=>'https://cdn.pixabay.com/photo/2015/06/24/15/45/protein-820234_1280.jpg'],
                            ['name'=>'Herbal Tea Bundle','desc'=>'Relaxing and detoxifying teas.','price'=>'₱1,400','value'=>'herbal_tea','img'=>'https://cdn.pixabay.com/photo/2016/03/05/19/02/tea-1238615_1280.jpg'],
                            ['name'=>'Green Coffee Extract','desc'=>'Supports metabolism and weight management.','price'=>'₱1,700','value'=>'green_coffee','img'=>'https://cdn.pixabay.com/photo/2017/02/21/19/20/coffee-2082280_1280.jpg'],
                            ['name'=>'Caffeine Capsules','desc'=>'Boosts energy and focus.','price'=>'₱1,300','value'=>'caffeine_capsules','img'=>'https://cdn.pixabay.com/photo/2016/03/05/19/02/pills-1238647_1280.jpg'],
                            ['name'=>'Vitamin B Complex','desc'=>'Supports energy and nervous system.','price'=>'₱1,600','value'=>'vitamin_b','img'=>'https://cdn.pixabay.com/photo/2016/03/05/19/02/vitamins-1238652_1280.jpg'],
                            ['name'=>'Iron + Folate','desc'=>'Supports blood health.','price'=>'₱1,750','value'=>'iron_folate','img'=>'https://cdn.pixabay.com/photo/2017/02/23/13/05/pills-2091126_1280.jpg'],
                            ['name'=>'Immune Boost Bundle','desc'=>'Vitamins and minerals for immunity.','price'=>'₱2,300','value'=>'immune_bundle','img'=>'https://cdn.pixabay.com/photo/2016/11/19/12/56/vitamins-1839040_1280.jpg'],
                            ['name'=>'Coffee + Supplement Combo','desc'=>'Energy and nutrition in one package.','price'=>'₱2,900','value'=>'coffee_supplement','img'=>'https://cdn.pixabay.com/photo/2016/03/05/19/02/coffee-1238749_1280.jpg'],
                        ];
                    @endphp

                    @foreach($products as $index => $product)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-radius-xl position-relative">
                            <div class="card-body text-center">
                                <img src="{{ $product['img'] }}" alt="{{ $product['name'] }}" class="w-50 mb-3" style="object-fit: cover;">
                                <h6 class="mb-2 text-dark">{{ $product['name'] }}</h6>
                                <p class="text-xs text-muted">{{ $product['desc'] }}</p>
                                <h5 class="text-gradient text-success font-weight-bold">{{ $product['price'] }}</h5>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="mlm_package" id="package{{ $index+1 }}" value="{{ $product['value'] }}">
                                    <label class="form-check-label" for="package{{ $index+1 }}">
                                        Select Package
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
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
    console.log('Selected Package:', selectedPackage.value);
    alert(`You selected: ${selectedPackage.value} package`);
});
</script>
@endsection
