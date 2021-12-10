<!DOCTYPE html>
<html lang='en'>

<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <meta http-equiv='X-UA-Compatible' content='ie=edge'>
  <title>Document</title>

  {{-- Disable favicon request --}}
  <link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">
</head>

<body>
  <div id='content'>This text should be gone after page is rendered</div>

  <script>
    setTimeout(() => {
      document.getElementById('content').innerHTML = '<h1>Rendered!</h1><p>{{ $slug }}</p>'
    }, 300);
  </script>
</body>

</html>
