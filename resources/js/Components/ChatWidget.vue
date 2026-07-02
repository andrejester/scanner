<template>
  <div class="fixed bottom-5 right-5 w-[350px] z-50">
    <div class="bg-orange-50 p-4 rounded-xl shadow-lg">
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-orange-600 font-bold">CitraLand Puncak Tidar Malang</h2>
        <button @click="show = false" class="text-gray-500 text-xl">&times;</button>
      </div>
      <p>Selamat datang di <strong>CitraLand Puncak Tidar Malang</strong>! Saya <strong>Chintya</strong>, Ciputra Virtual Assistant 😊</p>
      <p class="mt-2 text-sm">Ada penawaran khusus! Semua Tipe Rumah Ready Stock mulai <strong>1.2M-an</strong> + <strong>FREE BIAYA ADMINISTRASI</strong>!</p>
      <p class="mt-2">Ingin kami kirimkan Brosur & Pricelist promo unitnya?</p>

      <div class="mt-4 space-y-2">
        <button @click="sendBrochure"
          class="w-full bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
          📄 Tentu, kirimkan Brosur dan Pricelist-nya!
        </button>
        <button @click="chatWithSales"
          class="w-full bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
          👩‍💼 Halo, Saya mau berbicara dengan Sales dulu
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatWidget',
  data() {
    return {
      show: true,
    };
  },
  methods: {
    sendBrochure() {
      alert("Link brosur dan pricelist telah dikirim ke WhatsApp Anda!");
      // Bisa juga pakai Axios untuk POST ke Laravel Controller
    },
    chatWithSales() {
      window.open('https://wa.me/6281234567890', '_blank');
    }
  }
}
</script>

<style scoped>
/* Tambahkan animasi atau styling tambahan di sini */
</style>
