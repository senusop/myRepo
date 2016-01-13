<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {


	public function index()
	{
		$data['judul'] = "home page";
		
		$this->load->view('chat/viewhome',$data);
	}
	
	public function kirim_chat()
	{
		$this->load->view("fungsiRSA");
		/* 
		-- keterangan Masing Masing Fungsi yang dipake dari Library gmp --

		gmp_div_qr = Bagi;
		gmp_add    = Tambah;
		gmp_mul    = Kali;
		gmp_sub    = Kurang;
		gmp_gcd    = Menghitung Nilai phi;
		gmp_strval = Convert Nomer ke String;

		*/

		// Inisialisasi P = 113 & Q = 157 (Masing Masing adalah Bilangan Prima) <--- Lebih Besar Lebih Bagus
		// Menghitung N = P*Q
		$n = gmp_mul(113, 157);
		$valn = gmp_strval($n);

		// Menghitung Nilai M =(p-1)*(q-1)
		$m = gmp_mul(gmp_sub(113, 1),gmp_sub(157, 1));
		 
		// Mencari E (Kunci Public --> (e,n))
		// Inisialisasi E = 5
		// Membuktikan E = FPB (Faktor Persekutuan Terbesar) dari E dan M = 1
		for($e = 5; $e < 1000; $e++){  // Mencoba dengan Perulangan 1000 Kali 
			$fpb = gmp_gcd($e, $m);
			if(gmp_strval($fpb)=='1') // Jika Benar E adalah FPB dari E dan M = 1 <-- Hentikan Proses
			break;
		}

		// Menghitung D (Kunci Private --> (d,n))
		// D = (($m * $i) + 1) / e = $key[1] <-- Perulangan Do While
		$i=1;
		 do {      
			$key = gmp_div_qr(gmp_add(gmp_mul($m,$i),1), $e);
			$i++;
			if($i==1000) // Dengan Perulangan 1000 Kali
				break;
		} 
		// Sampai $key[1]=0
		while(gmp_strval($key[1])!='0');
		// Hasil D = $key[0] 
		$d = $key[0];
		$vald =gmp_strval($d); 
		
		$user=$this->input->post("user");
		$pesan=$this->input->post("pesan");
		$userid=$this->input->post("iduser");
		$hasilenkripsi = enkripsi($pesan, $n, $e);
		$insert="insert into chat (user,pesan,id_user) VALUES ('$user','$hasilenkripsi','$userid')";
		$this->db->query($insert);
		redirect ("home/ambil_pesan");
	}
	
	public function ambil_pesan()
	{
		$this->load->database();
		$tampil="select * from chat,tb_user where chat.id_user = tb_user.id_user order by waktu desc ";
		$row=$this->db->query($tampil)->result();
		foreach($row as $r)
		{
			?>
			<li class="left clearfix">
                <span class="chat-img pull-left">
                    <img src="<?php echo base_url();?><?php echo $r->path;?>/<?php echo $r->avatar;?>" width="50px" alt="User Avatar" class="img-circle" />
                </span>
                <div class="chat-body clearfix">
                    <div class="header">
                        <strong class="primary-font"><?php echo $r->user;?></strong>
                        <small class="pull-right text-muted">
                            <i class="fa fa-clock-o fa-fw"></i> <?php echo $r->waktu;?>
                        </small>
                    </div>
                    <p class="bg-warning pesan">
                        <?php echo $r->pesan;?>
                    </p>
                </div>
            </li>
		
		<?php
		}

	}
}
