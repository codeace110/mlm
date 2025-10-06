<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    @yield('title', 'Admin Panel')
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/soft-ui-dashboard.css') }}" rel="stylesheet">

  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  <!-- SweetAlert2 CSS and JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

  <!-- Custom Alert Functions -->
  <script>
  // Custom SweetAlert2 configurations for MLM app
  const Alert = {
      // Success alert
      success: function(title, message = '', timer = 3000) {
          return Swal.fire({
              icon: 'success',
              title: title,
              text: message,
              timer: timer,
              timerProgressBar: true,
              showConfirmButton: false,
              toast: true,
              position: 'top-end',
              background: '#d1fae5',
              color: '#065f46',
              iconColor: '#10b981'
          });
      },

      // Error alert
      error: function(title, message = '', timer = 5000) {
          return Swal.fire({
              icon: 'error',
              title: title,
              text: message,
              timer: timer,
              timerProgressBar: true,
              showConfirmButton: true,
              confirmButtonColor: '#ef4444',
              toast: true,
              position: 'top-end',
              background: '#fef2f2',
              color: '#991b1b',
              iconColor: '#ef4444'
          });
      },

      // Warning alert
      warning: function(title, message = '', timer = 4000) {
          return Swal.fire({
              icon: 'warning',
              title: title,
              text: message,
              timer: timer,
              timerProgressBar: true,
              showConfirmButton: true,
              confirmButtonColor: '#f59e0b',
              toast: true,
              position: 'top-end',
              background: '#fffbeb',
              color: '#92400e',
              iconColor: '#f59e0b'
          });
      },

      // Info alert
      info: function(title, message = '', timer = 3000) {
          return Swal.fire({
              icon: 'info',
              title: title,
              text: message,
              timer: timer,
              timerProgressBar: true,
              showConfirmButton: false,
              toast: true,
              position: 'top-end',
              background: '#eff6ff',
              color: '#1e40af',
              iconColor: '#3b82f6'
          });
      },

      // Confirmation dialog
      confirm: function(title, message, confirmText = 'Yes', cancelText = 'Cancel') {
          return Swal.fire({
              title: title,
              text: message,
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#10b981',
              cancelButtonColor: '#6b7280',
              confirmButtonText: confirmText,
              cancelButtonText: cancelText,
              background: '#ffffff',
              color: '#374151'
          });
      },

      // Loading alert
      loading: function(title = 'Please wait...', message = '') {
          return Swal.fire({
              title: title,
              text: message,
              allowOutsideClick: false,
              allowEscapeKey: false,
              showConfirmButton: false,
              didOpen: () => {
                  Swal.showLoading();
              },
              background: '#ffffff',
              color: '#374151'
          });
      },

      // Close any open alert
      close: function() {
          Swal.close();
      }
  };

  // Auto-show flash messages from Laravel
  document.addEventListener('DOMContentLoaded', function() {
      // Check for Laravel flash messages
      const flashMessages = document.querySelectorAll('[data-flash]');
      flashMessages.forEach(function(element) {
          const type = element.dataset.flash;
          const message = element.textContent.trim();

          if (type && message) {
              switch(type) {
                  case 'success':
                      Alert.success('Success!', message);
                      break;
                  case 'error':
                      Alert.error('Error!', message);
                      break;
                  case 'warning':
                      Alert.warning('Warning!', message);
                      break;
                  case 'info':
                      Alert.info('Info', message);
                      break;
              }
          }
      });
  });
  </script>
</head>