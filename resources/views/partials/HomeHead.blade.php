<!-- resources/views/partials/head.blade.php -->
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title', 'Aken')</title>
  <meta name="description" content="@yield('description', 'Default description here')">

  <!-- Open Graph -->
  <meta property="og:title" content="@yield('og_title', 'Aken')">
  <meta property="og:type" content="@yield('og_type', 'website')">
  <meta property="og:url" content="@yield('og_url', url()->current())">
  <meta property="og:image" content="@yield('og_image', asset('default-og.png'))">
  <meta property="og:image:alt" content="@yield('og_image_alt', 'Default OG image')">

  <!-- Bootstrap -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
    rel="stylesheet" 
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" 
    crossorigin="anonymous">
   <!-- Bootstrap -->
  

  <!-- Favicon -->
  <link rel="icon" href="{{ ('logo.ico') }}" type="image/x-icon">
  <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
  <meta name="theme-color" content="#fafafa">
</head>
