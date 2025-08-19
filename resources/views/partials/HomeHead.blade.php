<!-- resources/views/partials/head.blade.php -->
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title', 'Home')</title>
  <meta name="description" content="@yield('description', 'Default description here')">

  <meta property="og:title" content="@yield('og_title', 'My Laravel App')">
  <meta property="og:type" content="@yield('og_type', 'website')">
  <meta property="og:url" content="@yield('og_url', url()->current())">
  <meta property="og:image" content="@yield('og_image', asset('default-og.png'))">
  <meta property="og:image:alt" content="@yield('og_image_alt', 'Default OG image')">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">



  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/icon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="icon.png">

  <link rel="manifest" href="site.webmanifest">
  <meta name="theme-color" content="#fafafa">

  {{-- Styles --}}
  @vite('resources/css/app.css')
  @stack('styles')

</head>
