<!DOCTYPE html>
<html lang="id">
<head>
<?php MVC::LoadComponent(true,false) ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PT. ASSIST SOFTWARE INDONESIA PRATAMA</title>
<link rel="icon" type="image/png" href="<?= $data['asset_address'] . 'images/logo/assist.ico' ?>">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

  html, body {
    margin: 0;
    padding: 0;
    height: 100vh; /* penuh 1 layar */
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #ffffff, #f8f4f4, #e5dede);
    color: #3a3a3a;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center; /* center vertikal & horizontal */
    box-sizing: border-box;
  }

  .logo-wrapper {
    margin-bottom: 30px;
    max-width: 300px;
    width: 80vw;
  }
  .logo-wrapper img {
    width: 100%;
    height: auto;
    display: block;
  }

  .container {
    background: rgba(255 255 255 / 0.9);
    padding: 30px 20px;
    border-radius: 20px;
    box-shadow: 0 12px 30px rgba(216, 37, 29, 0.15);
    max-width: 360px;
    width: 90%;
    text-align: center;
    animation: fadeInUp 0.5s ease forwards;
    color: #3a3a3a;
  }

  .icon-wrapper {
    position: relative;
    background: #d8251d;
    width: 72px;
    height: 72px;
    margin: 0 auto 25px auto;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
  }
  .icon-wrapper::before {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 3px solid #d8251d;
    transform: translate(-50%, -50%) scale(1);
    opacity: 0.6;
    animation: pulseRing 2.5s ease-out infinite;
    pointer-events: none;
    z-index: 0;
  }
  @keyframes pulseRing {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
    70% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; }
    100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; }
  }
  .icon-wrapper svg {
    position: relative;
    z-index: 1;
    width: 36px;
    height: 36px;
    fill: #fff;
  }

  h1 {
    margin: 0 0 12px 0;
    font-weight: 600;
    font-size: 1.6rem;
    letter-spacing: 0.04em;
    color: #d8251d;
  }
  p {
    font-weight: 400;
    font-size: 1rem;
    line-height: 1.5;
    margin-bottom: 20px;
    color: #4b4b4b;
  }
  button {
    background: #d8251d;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    padding: 12px 28px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 14px rgba(216, 37, 29, 0.5);
    transition: background-color 0.3s ease, color 0.3s ease;
  }
  button:hover {
    background-color: #a01b17;
    color: #fff;
    box-shadow: 0 8px 20px rgba(160, 27, 23, 0.7);
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Responsif untuk layar pendek */
  @media (max-height: 500px) {
    html, body {
      justify-content: flex-start;
      padding-top: 20px;
    }
  }
</style>
</head>
<body>
  <div class="logo-wrapper" role="img" aria-label="Logo Assistindo">
    <img src="https://assistindo.id/wp-content/uploads/2019/05/aa-1024x275.png" alt="Logo Assistindo" />
  </div>

  <div class="container" role="alert" aria-live="assertive">
    <div class="icon-wrapper" aria-hidden="true">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.001 10h2v5h-2zm0 7h2v2h-2z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10a9.96 9.96 0 0 0 6.98-2.91l1.41 1.41 1.41-1.41-1.41-1.41A9.96 9.96 0 0 0 22 12c0-5.52-4.48-10-10-10zM12 20c-4.42 0-8-3.58-8-8 0-4.41 3.58-8 8-8s8 3.59 8 8c0 4.42-3.58 8-8 8z"/></svg>
    </div>
    <h1>Akses Ditolak</h1>
    <p>Anda harus melakukan aktivasi terlebih dahulu melalui ekstensi browser kami agar bisa melanjutkan.</p>
    <p>
      <a href="https://chromewebstore.google.com/detail/assistindo-web-activation/kgffjagpojoklbdmbobdaejdihbmadon" 
         target="_blank" rel="noopener"
         style="color:#d8251d; font-weight:bold; text-decoration:none;">
        👉 Download Ekstensi di sini
      </a>
    </p>
  </div>

  <input type="hidden" name="cToken" id="cToken" value='<?= $data['cToken'] ?>'>
</body>
</html>
