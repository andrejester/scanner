<!DOCTYPE html>
<html lang="id">
<head>
<?php MVC::LoadComponent(true,false) ?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="<?= $data['asset_address'] . 'images/logo/assist.ico' ?>">
<title>Memeriksa Aktivasi...</title>
<style>
  body, html {
    margin: 0; padding: 0; height: 100%;
    font-family: 'Inter', sans-serif;
    background: #fafafa;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .loading-wrapper {
    text-align: center;
    color: #444;
  }
  .spinner {
    width: 60px;
    height: 60px;
    border: 6px solid #ddd;
    border-top-color: #d8251d;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px auto;
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
</style>
</head>
<body>
  <div class="loading-wrapper">
    <div class="spinner"></div>
    <p>Memeriksa aktivasi...</p>
  </div>
</body>
</html>
<script>
document.addEventListener("DOMContentLoaded", function () {
	setTimeout(() => {
		location.reload() ;
	}, 9000);
});
</script>
