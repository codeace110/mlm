<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // For now, provide empty collections or default data
        // In a real app, these would come from models
        $products = collect([]); // Replace with Product::all() or similar
        $achievements = collect([
            [
                'value' => '₱1M+',
                'label' => 'Total Sales',
                'icon' => 'bi-cash-stack',
                'color' => 'warning'
            ],
            [
                'value' => '500+',
                'label' => 'Happy Customers',
                'icon' => 'bi-emoji-smile',
                'color' => 'success'
            ],
            [
                'value' => '50+',
                'label' => 'Products Delivered',
                'icon' => 'bi-box-seam',
                'color' => 'info'
            ]
        ]);
        $testimonials = collect([]); // Replace with Testimonial::all() or similar

        return view('home', compact('products', 'achievements', 'testimonials'));
    }
}