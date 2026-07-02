<?php


// use Auth;

use App\Models\Backend\Category;

class Helper
{
    public static function getHeaderCategory()
    {
        $category = new Category();
        // dd($category);
        $menu = $category->getAllParentWithChild();

        if ($menu) {
?>

            <li>
                <a href="javascript:void(0);">Category<i class="ti-angle-down"></i></a>
                <ul class="dropdown border-0 shadow">
                    <?php
                    foreach ($menu as $cat_info) {
                        if ($cat_info->child_cat->count() > 0) {
                    ?>
                            <li><a href="<?php echo route('product-cat', $cat_info->slug); ?>"><?php echo $cat_info->title; ?></a>
                                <ul class="dropdown sub-dropdown border-0 shadow">
                                    <?php
                                    foreach ($cat_info->child_cat as $sub_menu) {
                                    ?>
                                        <li><a href="<?php echo route('product-sub-cat', [$cat_info->slug, $sub_menu->slug]); ?>"><?php echo $sub_menu->title; ?></a></li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </li>
                        <?php
                        } else {
                        ?>
                            <li><a href="<?php echo route('product-cat', $cat_info->slug); ?>"><?php echo $cat_info->title; ?></a></li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </li>
<?php
        }
    }

    public static function ucapanSelamat()
    {
        // Set zona waktu jika belum diatur
        date_default_timezone_set('Asia/Jakarta');

        // Mendapatkan waktu saat ini menggunakan Carbon
        $waktu_sekarang = date('H:i');

        // Periksa apakah saat ini sudah siang
        if ($waktu_sekarang >= '07:00' && $waktu_sekarang < '11:59') {
            $ucapan = 'Selamat Pagi';
        } else if ($waktu_sekarang >= '12:00' && $waktu_sekarang < '18:00') {
            $ucapan = 'Selamat Siang';
        } else {
            $ucapan = 'Selamat Malam'; // Anda dapat menyesuaikan sesuai kebutuhan
        }

        // Mengirimkan data ucapan ke tampilan
        return $ucapan;
    }

    public static function chatWa($no = '', $title = '')
    {
        if (!empty($no)) {
            // Pesan yang ingin dikirimkan
            $pesan = 'Halo, ' . Helper::ucapanSelamat() . '.' . chr(10) . 'Saya mau menanyakan terkait dengan produk ' . $title . ' ' . chr(10)
                . 'Nama : ' . chr(10)
                . 'Alamat : ' . chr(10)
                . 'Tlp/wA : ' . chr(10);
            $link_whatsapp = 'https://api.whatsapp.com/send?phone=' . $no . '&text=' . urlencode($pesan);
        } else {
            $link_whatsapp = "";
        }
        return $link_whatsapp;
    }
}

?>