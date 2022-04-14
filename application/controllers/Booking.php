<?php
defined('BASEPATH') or exit('No Direct Script Access Allowed');
date_default_timezone_set('Asia/Jakarta');

class Booking extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        cek_login();
        $this->load->model(['ModelBooking', 'ModelUser']);
    }

    public function index()
    {
        $id = ['bo.id_user' => $this->uri->segment(3)];
        $id_user = $this->session->userdata('id_user');
        $data['booking'] = $this->ModelBooking->joinOrder($id)->result();
        $user = $this->ModelUser->cekData(['email' => $this->session->userdata('email')])->row_array();
        foreach ($user as $a) {
            $data = [
                'image' => $user['image'],
                'user' => $user['nama'],
                'email' => $user['email'],
                'tanggal_input' => $user['tanggal_input']
            ];
        }
    $dtb = $this->ModelBooking->showtemp(['id_user' => $id_user])->num_rows();

    if ($dtb < 1) {
        $this->session->set_flashdata('pesan', '<div class="alert alert-message alert-danger" role="alert">Tidak Ada Buku dikeranjang</div>');
        redirect(base_url());
    } else {
        $data['temp'] = $this->db->query("select * from temp where id_user='$id_user'")->result_array();
    }
    $data['judul'] = "Data Booking";

    $this->load->view('templates/templates-user/header', $data);
    
    $this->load->view('booking/data-booking', $data)
    $this->load->view('templates/templates-user/modal');
    $this->load->view('templates/templates-user/footer');
    }
}

    public function tambahBooking()
    {
        $id_buku = $this->uri->segment(3);

        $d = $this->db->query("Select*from buku where id='$id_buku'")->row();

        $isi = [
            'id_buku' => $id_buku,
            'judul_buku' => $d->judul_buku,
            'id_user' => $this->session->userdata('id_user'),
            'email_user' => $this->session->userdata('email'),
            'tgl_booking' => date('Y-m-d H:i:s'),
            'image' => $d->image,
            'penulis' => $d->pengarang,
            'penerbit' => $d->penerbit,
            'tahun_terbit' => $d->tahun_terbit
        ];

        $temp = $this->ModelBooking->getDataWhere('temp', ['id_buku' => $id_buku])->num_rows();
        
        $userid = $this->session->userdata('id_user');

        $tempuser = $this->db->query("select*from temp where id_user ='$userid'")->num_rows();

        $databooking = $this->db->query("select*from booking where id_user='$userid'")->num_rows();
        if ($databooking > 0) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role="alert">Masih Ada booking buku sebelumnya yang belum diambil.<br> Abmil Buku yang dibooking atau tunggu 1x24 Jam untuk bisa booking kembali </div>');
            redirect(base_url());
        }

        if ($temp > 0) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role="alert">Buku ini Sudah anda booking </div>');
            redirect(base_url() . 'home');
        }

        if ($tempuser == 3) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role="alert">Booking Buku Tidak Boleh Lebih dari 3</div>');
            redirect(base_url() . 'home');
        }

        $this->ModelBooking->createTemp();
        $this->ModelBooking->insertData('temp', $isi);

        $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-message" role="alert">Buku berhasil ditambahkan ke keranjang 
        </div>');
        redirect(base_url() . 'home');
    }
}
public function hapusbooking()
    {
        $id_temp = $this->uri->segment(3);
        $id_user = $this->session->userdata('id_user');

        $this->ModelBooking->deleteData(['id' => $id_temp], 'temp');
        $kosong = $this->db->query("select*from temp where id_user='$id_user'")->num_rows();
        if ($kosong < 1) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-messagege alert-danger" role="alert">Tidak Ada Buku dikeranjang</div>'); redirect(base_url());
        } else {
        redirect(base_url() . 'booking');
        }
    }
}