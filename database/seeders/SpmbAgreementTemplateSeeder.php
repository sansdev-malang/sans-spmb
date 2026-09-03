<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpmbUnit;
use App\Models\SpmbAgreementTemplate;

class SpmbAgreementTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agreementBody = '<p>Saya yang bertanda tangan di bawah ini selaku Orang Tua / Wali murid dari calon siswa:</p>
<div class="pl-4 my-2 space-y-1 font-semibold text-slate-800 dark:text-slate-200">
    <p>Nama Calon Siswa : <strong>{{nama_calon_siswa}}</strong></p>
    <p>Unit & Program : <strong>{{nama_unit}} - {{nama_kelas}}</strong></p>
    <p>Tahun Ajaran : <strong>{{tahun_ajaran}}</strong></p>
</div>
<p class="mt-4">Menyatakan dengan sesungguhnya dan penuh kesadaran bahwa:</p>
<ol class="list-decimal pl-5 space-y-3 mt-2 text-slate-700 dark:text-slate-350">
    <li><strong>Kami bersedia dan menyetujui:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Mematuhi semua peraturan, ketentuan, tata tertib, kebijakan, dan prosedur yang dibuat dan berlaku di Sekolah Anak Saleh, baik yang telah berlaku maupun yang akan diberlakukan di kemudian hari (selama tidak ada pihak yang dirugikan), baik tertulis maupun tidak tertulis, termasuk pada Peraturan Yayasan Pendidikan Anak Saleh, Tata Tertib, Kode Etik, dan lain-lain.</li>
            <li>Menerima visi, misi, tujuan, metode, dan tata kelola Sekolah Anak Saleh dalam mendidik semua murid di Sekolah Anak Saleh dan karenanya kami percaya bahwa segala peraturan dan arah kerja pendidikan yang dibuat oleh Sekolah Anak Saleh adalah untuk kebaikan murid dan pihak sekolah.</li>
            <li>Menerima implementasi, internalisasi, sosialisasi dan kulturisasi Panca Karakter Anak Saleh sebagai basis utama pembelajaran dan pendidikan karakter di lingkungan Sekolah Anak Saleh.</li>
            <li>Semua murid wajib ikut serta dan/atau berpartisipasi dalam semua kegiatan Sekolah Anak Saleh. Kami menerima bahwa ketidakikutsertaan murid dalam kegiatan tersebut dapat berakibat kepada penilaian hasil belajarnya di Sekolah Anak Saleh dan tidak menggugurkan kewajiban kami untuk membayar biaya pendidikan sesuai ketentuan yang berlaku sama bagi seluruh murid yang mengikuti kegiatan Sekolah Anak Saleh.</li>
            <li>Aktif mengikuti, partisipasi, dan mendukung dalam kegiatan Parenting, Pengajian, Bakti Sosial/Sosial Keagamaan dan kegiatan outing (outbound, home visit, moving home, family Inn, field trip, dan kegiatan-kegiatan lainnya) yang diadakan Sekolah Anak Saleh dan/atau bekerjasama dengan Forkel (Forum Kelas) atau Komite Sekolah.</li>
            <li>Menerima dan tidak mempertentangkan ajaran Islam Ahli Sunnah wal Jama’ah yang Rahmatan Lil ‘Alamin yang diajarkan di lingkungan Sekolah Anak Saleh.</li>
            <li>Menerima dan tidak menentang landasan yang digunakan di lingkungan Sekolah Anak Saleh yakni Al-Qur’an, Al-Hadits, Ijma’, dan Qiyas (termasuk di dalamnya hukum-hukum yang sah menurut Undang-Undang dan peraturan lain yang berlaku di Negara Kesatuan Republik Indonesia).</li>
            <li>Tidak membawa kepentingan apapun ke dalam lingkungan Sekolah Anak Saleh baik kepada wali murid lain maupun kepada warga sekolah yang berkaitan dengan politik, ormas, dan kegiatan yang bersifat SARA atau yang dapat berpotensi memecah belah kerukunan.</li>
            <li>Menerima dan mendukung segala bentuk program inklusi di lingkungan Sekolah Anak Saleh serta tidak mempermasalahkan keberadaan Murid Berkebutuhan Khusus (ABK/Special Need) di lingkungan Sekolah Anak Saleh sebagai komitmen bersama education for all.</li>
            <li>Apabila ananda pada saat didaftarkan oleh kami sebagai orangtua masuk jalur reguler (bukan jalur Murid Berkebutuhan Khusus (ABK)) yang selanjutnya dinyatakan diterima, akan tetapi ternyata hasil dari observasi dalam perkembangannya ananda mengalami kendala yang masuk dalam kategori Murid Berkebutuhan Khusus (ABK), maka kami sebagai orangtua siap menyediakan GPK (Guru Pembimbing Khusus) serta tidak mempertentangkan atas keputusan pihak sekolah dalam memutuskan kebutuhan GPK terhadap ananda.</li>
            <li>Memahami dan menyadari jika anak merupakan amanah Allah SWT yang harus dijaga dan dididik oleh orangtua/wali sebagai penerima utama amanah tersebut, sehingga akan berikhtiar dalam bekerjasama dengan Sekolah Anak Saleh untuk memperhatikan dan mengusahakan kesejahteraan fisik dan mental, tumbuh kembang dan pendidikannya dalam semangat cinta dan kasih.</li>
            <li>Tidak melakukan ujaran maupun tindakan provokasi terhadap kebijakan sekolah serta menyampaikan kritik dan saran secara santun sesuai adab ketimuran dan karakter Anak Saleh, kepada pimpinan sekolah, dan tidak mengelaborasi masalah yang dapat menyebabkan disharmonisasi atau polemik diantara sekolah maupun wali murid lainnya.</li>
            <li>Memahami posisi dan kewenangan sebagai wali murid dengan tidak mancampuri urusan yang menjadi ranah dan kewenangan Sekolah Anak Saleh antara lain: kurikulum dan pembelajaran, administrasi dan manajemen sekolah, organisasi dan kelembagaan sekolah, ketenagaan, keuangan, sarana dan prasarana, serta program kegiatan.</li>
            <li>Apabila dikemudian hari terdapat perselisihan dengan pihak sekolah maka bersedia untuk menyelesaikan dengan cara kekeluargaan serta tidak melibatkan pihak luar seperti Lembaga Swadaya Masyarakat (LSM) dan sejenisnya.</li>
            <li>Bersedia mengganti rugi atas kelalaian yang disengaja maupun tidak atas kerusakan fasilitas sekolah yang disebabkan oleh anak kami.</li>
            <li>Apabila terjadi hal-hal yang diluar kendali atau Force majeure akan tetap berkomitmen untuk menyelesaikan segala bentuk kewajiban dengan tidak mempertentangkan kebijakan atau langkah strategis yang diambil oleh Yayasan Pendidikan Anak Saleh demi kebaikan bersama.</li>
            <li>Mengikuti segala hal berdasarkan aturan dan penjelasan resmi dari pimpinan sekolah bukan atas pernyataan sepihak apalagi kabar burung atau yang tidak berdasar dari pihak manapun.</li>
        </ol>
    </li>
    <li class="mt-4"><strong>Kami menyetujui pembiayaan di Sekolah Anak Saleh:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Bahwa biaya pendidikan (musa’adah, syahriyah, dan pembiayaan lain yang ditentukan Yayasan Pendidikan Anak Saleh) yang harus dipenuhi selama murid mengikuti pendidikan di Sekolah Anak Saleh dengan cara dan waktu pembayaran yang ditetapkan oleh Yayasan menjadi tanggungjawab kami untuk membayarnya secara tepat waktu.</li>
            <li>Bahwa kewajiban pembayaran biaya pendidikan yang terhitung (tunggakan) tidak dapat terhapus meski murid sudah tidak mengikuti pendidikan di Sekolah Anak Saleh. Kami akan tetap menyelesaikan tunggakan sebagai hutang yang harus dibayarkan. Oleh karenanya kami akan menyelesaikan tunggakan tersebut sebelum anak kami tidak lagi mengikuti Pendidikan di Sekolah Anak Saleh.</li>
            <li>Khusus mengenai uang kegiatan murid, kami sepakat besaran uang kegiatan murid adalah bagian tak terpisahkan dari biaya pendidikan yang wajib dibayar secara penuh, baik murid yang bersangkutan ikut ataupun tidak dengan kegiatan dimaksud karena alasan apapun.</li>
            <li>Sekolah Anak Saleh berwenang sepenuhnya untuk mengelola dan menggunakan uang musa’adah, syahriyah, uang kegiatan, uang amal, serta bantuan operasional oleh pemerintah untuk kegiatan murid atau keperluan lain yang dianggap perlu dan baik guna kemajuan Sekolah Anak Saleh.</li>
            <li>Disadari bahwa beban-beban penyelenggaraan pendidikan harus terus-menerus disesuaikan mengikuti inflasi, kenaikan biaya dan harga-harga, kenaikan berkala gaji asatidz (bisyaroh) dan sebagainya. Oleh karena itu kami orangtua dan/wali murid bersedia menerima kenaikan syahriyah (SPP) selama sesuai dengan keadaan dan kebutuhan riil yang berkembang.</li>
            <li>Bila kami belum bisa melunasi kewajiban biaya pendidikan yang harus dibayarkan, maka kami menyadari dan menerima bahwa setiap saat Sekolah Anak Saleh berwenang menunda pemberian hak akademik murid yang bersangkutan, menahan raport, ijazah dan/atau dokumen lainnya sampai dengan kewajiban biaya pendidikan yang harus dibayarkan dapat dilunasi.</li>
            <li>Tidak akan memberikan hadiah/gratifikasi kepada asatidz dan karyawan maupun pimpinan Sekolah Anak Saleh secara perseorangan sehingga pemberian tersebut, disengaja untuk dapat mempengaruhi obyektivitas asatidz dan karyawan maupun pimpinan Sekolah Anak Saleh terhadap murid.</li>
            <li>Seluruh biaya pendaftaran dan pendidikan yang telah dibayarkan, tidak dapat diminta kembali dengan alasan apapun.</li>
        </ol>
    </li>
    <li class="mt-4"><strong>Kami menyetujui bahwa Sekolah Anak Saleh berwenang untuk:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Memberikan nilai dan keterangan terhadap murid yang akan tertuang di dalam raport oleh asatidz sesuai dengan penilaian-penilaian yang telah diberlakukan.</li>
            <li>Menentukan penempatan kelas murid atas pertimbangan oleh tim asatidz dengan persetujuan kepala sekolah.</li>
            <li>Menentukan murid yang dapat/tidak dapat naik kelas dan yang dapat/tidak dapat melanjutkan pendidikan di Sekolah Anak Saleh.</li>
            <li>Mengambil segala tindakan yang perlu, termasuk memberhentikan murid bila didapati di kemudian hari bahwa orangtua dan/wali murid telah memberikan keterangan yang salah, palsu dan/atau menghilangkan sebagian atau seluruh keterangan tertentu mengenai data-data murid serta dokumen-dokumen pendukungnya.</li>
            <li>Mengambil segala tindakan yang perlu, termasuk memberhentikan murid bila didapati dikemudian hari bahwa orangtua dan/wali murid melakukan tindakan-tindakan merugikan warga sekolah maupun wali murid lainnya seperti provokasi, menyebarkan berita bohong, dan tindakan merugikan lainnya.</li>
            <li>Menindaklanjuti dengan menyesuaikan dengan program inklusi yang ada di Sekolah Anak Saleh ketika dalam proses perkembangannya murid didiagnosa mengalami kebutuhan khusus dengan data-data dan hasil asesmen yang jelas dan terukur dari ahli tumbuh kembang anak.</li>
            <li>Mengambil segala tindakan yang dianggap perlu, termasuk memberi peringatan, melakukan skorsing hingga memberhentikan murid apabila murid kedapatan melakukan tindakan melanggar peraturan sekolah, bullying, perusakan fasilitas, penggunaan obat terlarang, pornografi/pornoaksi, pencemaran nama baik, atau tindakan vandalisme lainnya.</li>
            <li>Mengadakan pengujian klinis atas penggunaan obat-obatan terlarang, baik secara acak maupun menyeluruh, sekolah juga berhak untuk mengambil tindakan tertentu yang dianggap baik untuk kepentingan sekolah secara keseluruhan.</li>
        </ol>
    </li>
    <li class="mt-4"><strong>Kami bertanggung jawab atas urusan antar jemput:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Bahwa antar jemput murid ke/dari sekolah-rumah merupakan tanggungjawab kami sebagai keluarga.</li>
            <li>Bahwa penggunaan mobil antar-jemput sekolah didasarkan pada unit yang ditunjuk dan direferensikan oleh sekolah. Kami tidak menyalahkan atau meminta tanggungjawab (menuntut) sekolah bilamana terjadi sesuatu hal karena penggunaan mobil antar jemput bukan yang ditunjuk/direferensi sekolah.</li>
            <li>Bahwa pada waktu pulang sekolah, murid wajib dijemput orangtua/wali atau yang mewakili dengan kesepakatan terlebih dahulu antara orangtua/wali dengan sekolah. Wali kelas harus diberitahu jika orang yang menjemput berbeda dari biasanya.</li>
        </ol>
    </li>
</ol>';

        $units = SpmbUnit::all();
        foreach ($units as $unit) {
            SpmbAgreementTemplate::updateOrCreate(
                ['spmb_unit_id' => $unit->id],
                [
                    'title' => 'SURAT PERNYATAAN KESANGGUPAN MEMATUHI PERATURAN & BIAYA PENDIDIKAN YAYASAN PENDIDIKAN ANAK SALEH',
                    'content' => $agreementBody,
                    'rules_consent_label' => 'Saya menyetujui seluruh tata tertib dan peraturan akademik Sekolah Anak Saleh.',
                    'fees_consent_label' => 'Saya menyanggupi pemenuhan seluruh rincian biaya pendidikan dan administrasi masuk yayasan.',
                    'place' => 'Malang',
                    'principal_name' => 'Dra. Hj. Mike Supraptiwi, S.Psi, M.Pd',
                    'principal_title' => 'Kepala Sekolah',
                ]
            );
        }
    }
}
